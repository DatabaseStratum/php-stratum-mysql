<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Loader\Helper;

use SetBased\Stratum\Common\Exception\RoutineLoaderException;
use SetBased\Stratum\Common\Loader\Helper\LoaderContext;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Class for handling routine parameters.
 */
class RoutineParametersHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The metadata layer.
   *
   * @var MySqlMetadataLayer
   */
  private MySqlMetadataLayer $dl;

  /**
   * The information about the parameters of the stored routine.
   *
   * @var array[]|null
   */
  private ?array $parameters = null;

  /**
   * Information about parameters with specific format (string in CSV format etc.).
   *
   * @var array
   */
  private array $parametersAddendum = [];

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param MySqlMetadataLayer $dl The metadata layer.
   *
   */
  public function __construct(MySqlMetadataLayer $dl)
  {
    $this->dl = $dl;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the metadata of all parameters.
   *
   * @param LoaderContext $context The loader context.
   */
  public function getParameters(LoaderContext $context): array
  {
    if ($this->parameters===null)
    {
      $this->parameters = $this->dl->routineParameters($context->storedRoutine->getName());
      $this->enhanceTypeOfParameters();
      $this->enhanceCharacterSet();
      $this->extractParametersAddendum($context);
      $this->enhanceParametersWithAddendum();
    }

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
   * @param LoaderContext $context The loader context.
   */
  private function extractParametersAddendum(LoaderContext $context): void
  {
    $tags = $context->docBlock->getTags('paramAddendum');
    foreach ($tags as $tag)
    {
      $n = preg_match('/^(@paramAddendum)\s+(?<name>\w+)\s+(?<type>\w+)\s+(?<delimiter>.)\s+(?<enclosure>.)\s+(?<escape>.)$/s',
                      $tag,
                      $matches);
      if ($n!==1)
      {
        throw new RoutineLoaderException('Expected: @paramAddendum <name> <type_of_list> <delimiter> <enclosure> <escape>. Found %s',
                                         $tag);
      }

      $parameterName = $matches['name'];
      if (isset($this->parametersAddendum[$parameterName]))
      {
        throw new RoutineLoaderException("Duplicate @paramAddendum tag for parameter '%s'", $parameterName);
      }

      $this->parametersAddendum[$parameterName] = ['name'      => $parameterName,
                                                   'data_type' => $matches['type'],
                                                   'delimiter' => $matches['delimiter'],
                                                   'enclosure' => $matches['enclosure'],
                                                   'escape'    => $matches['escape']];
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
