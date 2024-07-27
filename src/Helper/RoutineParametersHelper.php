<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Helper;

use SetBased\Stratum\Backend\StratumStyle;
use SetBased\Stratum\Common\DocBlock\DocBlockReflection;
use SetBased\Stratum\Common\Exception\RoutineLoaderException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\MySqlMetaDataLayer;

/**
 * Class for handling routine parameters.
 */
class RoutineParametersHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The meta data layer.
   *
   * @var MySqlMetaDataLayer
   */
  private MySqlMetaDataLayer $dl;

  /**
   * The DocBlock reflection object.
   *
   * @var DocBlockReflection
   */
  private DocBlockReflection $docBlockReflection;

  /**
   * The Output decorator.
   *
   * @var StratumStyle
   */
  private StratumStyle $io;

  /**
   * The information about the parameters of the stored routine.
   *
   * @var array[]
   */
  private array $parameters = [];

  /**
   * Information about parameters with specific format (string in CSV format etc.).
   *
   * @var array
   */
  private array $parametersAddendum = [];

  /**
   * The name of the stored routine.
   *
   * @var string
   */
  private string $routineName;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param MySqlMetaDataLayer $dl                 The meta data layer.
   * @param StratumStyle       $io                 The Output decorator.
   * @param DocBlockReflection $docBlockReflection The DocBlock reflection object.
   * @param string             $routineName        The name of the stored routine.
   */
  public function __construct(MySqlMetaDataLayer $dl,
                              StratumStyle       $io,
                              DocBlockReflection $docBlockReflection,
                              string             $routineName)
  {
    $this->dl                 = $dl;
    $this->io                 = $io;
    $this->docBlockReflection = $docBlockReflection;
    $this->routineName        = $routineName;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts DocBlock parts of the stored routine parameters to be used by the wrapper generator.
   *
   * @return array
   */
  public function extractDocBlockPartsWrapper(): array
  {
    $lookup = [];
    foreach ($this->docBlockReflection->getTags('param') as $tag)
    {
      $lookup[$tag['arguments']['name']] = $tag['description'];
    }

    $parameters = [];
    foreach ($this->parameters as $parameter)
    {
      $parameters[] = ['parameter_name' => $parameter['parameter_name'],
                       'php_type'       => DataTypeHelper::columnTypeToPhpTypeHinting($parameter).'|null',
                       'dtd_identifier' => $parameter['dtd_identifier'],
                       'description'    => $lookup[($parameter['parameter_name'])] ?? []];
    }

    return $parameters;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts info about the parameters of the stored routine.
   *
   * @throws RoutineLoaderException
   * @throws MySqlQueryErrorException
   */
  public function extractRoutineParameters(): void
  {
    $this->parameters = $this->dl->routineParameters($this->routineName);
    $this->enhanceTypeOfParameters();
    $this->enhanceCharacterSet();
    $this->extractParametersAddendum();
    $this->enhanceParametersWithAddendum();
    $this->validateParameterLists();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the metadata of all parameters.
   *
   * @return array[]
   */
  public function getParameters(): array
  {
    return $this->parameters;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Enhances parameters with character data with their character set.
   */
  private function enhanceCharacterSet(): void
  {
    foreach ($this->parameters as $key => $parameter)
    {
      if ($parameter['parameter_name'])
      {
        $dataTypeDescriptor = $parameter['dtd_identifier'];
        if (isset($parameter['character_set_name']))
        {
          $dataTypeDescriptor .= ' character set '.$parameter['character_set_name'];
        }
        if (isset($parameter['collation_name']))
        {
          $dataTypeDescriptor .= ' collation '.$parameter['collation_name'];
        }

        $parameter['dtd_identifier'] = $dataTypeDescriptor;

        $this->parameters[$key] = $parameter;
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Updates parameters with data from parameter addendum tags.
   *
   * @throws RoutineLoaderException
   */
  private function enhanceParametersWithAddendum(): void
  {
    foreach ($this->parametersAddendum as $parameterName => $addendum)
    {
      $exists = false;
      foreach ($this->parameters as $key => $parameter)
      {
        if ($parameter['parameter_name']===$parameterName)
        {
          $this->parameters[$key] = array_merge($this->parameters[$key], $addendum);
          $exists                 = true;
          break;
        }
      }
      if (!$exists)
      {
        throw new RoutineLoaderException("Specific parameter '%s' does not exist", $parameterName);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @throws RoutineLoaderException
   * @throws MySqlQueryErrorException
   */
  private function enhanceTypeOfParameters(): void
  {
    foreach ($this->parameters as $key => $parameter)
    {
      if ($parameter['data_type']==='TYPE OF')
      {
        $n = preg_match('/^("(?<schema>[a-zA-Z0-9_]+)"\.)?("(?<table>[a-zA-Z0-9_]+)")\.("(?<column>[a-zA-Z0-9_]+)")%TYPE$/',
                        $parameter['dtd_identifier'],
                        $matches);
        if ($n!==1)
        {
          throw new RoutineLoaderException('Unable to parse data type description %s of parameter %s.',
                                           $parameter['dtd_identifier'],
                                           $parameter['parameter_name']);
        }

        $schemaName = $matches['schema'] ?? null;
        $tableName  = $matches['table'];
        $columnName = $matches['column'];

        $column = $this->dl->tableColumn($schemaName, $tableName, $columnName);

        $this->parameters[$key]['data_type']          = $column['data_type'];
        $this->parameters[$key]['numeric_precision']  = $column['numeric_precision'];
        $this->parameters[$key]['numeric_scale']      = $column['numeric_scale'];
        $this->parameters[$key]['character_set_name'] = $column['character_set_name'];
        $this->parameters[$key]['collation_name']     = $column['collation_name'];
        $this->parameters[$key]['dtd_identifier']     = $column['column_type'];
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts parameter addendum of the routine parameters.
   *
   * @throws RoutineLoaderException
   */
  private function extractParametersAddendum(): void
  {
    $tags = $this->docBlockReflection->getTags('paramAddendum');
    foreach ($tags as $tag)
    {
      $parameterName = $tag['arguments']['name'];
      $dataType      = $tag['arguments']['type'];
      $delimiter     = $tag['arguments']['delimiter'];
      $enclosure     = $tag['arguments']['enclosure'];
      $escape        = $tag['arguments']['escape'];

      if ($parameterName==='' || $dataType=='' || $delimiter==='' || $enclosure==='' || $escape==='')
      {
        throw new RoutineLoaderException('Expected: @paramAddendum <field_name> <type_of_list> <delimiter> <enclosure> <escape>.');
      }

      if (isset($this->parametersAddendum[$parameterName]))
      {
        throw new RoutineLoaderException("Duplicate @paramAddendum tag for parameter '%s'", $parameterName);
      }

      $this->parametersAddendum[$parameterName] = ['name'      => $parameterName,
                                                   'data_type' => $dataType,
                                                   'delimiter' => $delimiter,
                                                   'enclosure' => $enclosure,
                                                   'escape'    => $escape];
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Validates the parameters found the DocBlock in the source of the stored routine against the parameters from the
   * metadata of MySQL and reports missing and unknown parameters names.
   */
  private function validateParameterLists(): void
  {
    // Make list with names of parameters used in database.
    $databaseParametersNames = [];
    foreach ($this->parameters as $parameter)
    {
      $databaseParametersNames[] = $parameter['parameter_name'];
    }

    // Make list with names of parameters used in dock block of routine.
    $docBlockParametersNames = [];
    foreach ($this->docBlockReflection->getTags('param') as $tag)
    {
      $docBlockParametersNames[] = $tag['arguments']['name'];
    }

    // Check and show warning if any parameters is missing in doc block.
    $tmp = array_diff($databaseParametersNames, $docBlockParametersNames);
    foreach ($tmp as $name)
    {
      $this->io->logNote('Parameter <dbo>%s</dbo> is missing from doc block', $name);
    }

    // Checks and shows a warning if fond unknown parameters in doc block.
    $tmp = array_diff($docBlockParametersNames, $databaseParametersNames);
    foreach ($tmp as $name)
    {
      $this->io->logNote('Unknown parameter <dbo>%s</dbo> found in doc block', $name);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
