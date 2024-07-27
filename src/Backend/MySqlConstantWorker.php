<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Exception\RuntimeException;
use SetBased\Stratum\Backend\ConstantWorker;
use SetBased\Stratum\Common\Helper\ClassReflectionHelper;
use SetBased\Stratum\Common\Helper\Util;
use SetBased\Stratum\MySql\Exception\MySqlConnectFailedException;
use SetBased\Stratum\MySql\Exception\MySqlDataLayerException;
use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\Helper\DataTypeHelper;

/**
 * Command for creating PHP constants based on column widths, auto increment columns and labels.
 */
class MySqlConstantWorker extends MySqlWorker implements ConstantWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Name of the class that contains all constants.
   *
   * @var string|null
   */
  private ?string $className;

  /**
   * All columns in the MySQL schema.
   *
   * @var array
   */
  private array $columns = [];

  /**
   * @var array All constants.
   */
  private array $constants = [];

  /**
   * Filename with column names, their widths, and constant names.
   *
   * @var string|null
   */
  private ?string $constantsFilename;

  /**
   * All primary key labels, their widths and constant names.
   *
   * @var array
   */
  private array $labels = [];

  /**
   * The previous column names, widths, and constant names (i.e. the content of $constantsFilename upon starting
   * this program).
   *
   * @var array
   */
  private array $oldColumns = [];

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   *
   * @throws MySqlConnectFailedException
   * @throws MySqlDataLayerException
   * @throws RuntimeException
   */
  public function execute(): int
  {
    $this->constantsFilename = $this->settings->optString('constants.columns');
    $this->className         = $this->settings->optString('constants.class');

    if ($this->constantsFilename!==null || $this->className!==null)
    {
      $this->io->title('PhpStratum: MySql Constants');

      $this->connect();

      $this->executeEnabled();

      $this->disconnect();
    }
    else
    {
      $this->io->logVerbose('Constants not enabled');
    }

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Enhances $oldColumns as follows:
   * If the constant name is *, is replaced with the column name prefixed by $this->myPrefix in uppercase.
   * Otherwise, the constant name is set to uppercase.
   */
  private function enhanceColumns(): void
  {
    foreach ($this->oldColumns as $table)
    {
      foreach ($table as $column)
      {
        $tableName  = $column['table_name'];
        $columnName = $column['column_name'];

        if ($column['constant_name']==='*')
        {
          $constantName                                               = strtoupper($column['column_name']);
          $this->oldColumns[$tableName][$columnName]['constant_name'] = $constantName;
        }
        else
        {
          $constantName                                               = strtoupper($this->oldColumns[$tableName][$columnName]['constant_name']);
          $this->oldColumns[$tableName][$columnName]['constant_name'] = $constantName;
        }
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Gathers constants based on column widths.
   *
   * @throws RuntimeException
   * @throws MySqlQueryErrorException
   */
  private function executeColumnWidths(): void
  {
    $this->loadOldColumns();

    $this->loadColumns();

    $this->enhanceColumns();

    $this->mergeColumns();

    $this->writeColumns();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Creates constants declarations in a class.
   *
   * @throws MySqlQueryErrorException
   * @throws RuntimeException
   */
  private function executeCreateConstants(): void
  {
    $this->loadLabels();

    $this->fillConstants();

    $this->writeConstantClass();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Executes the enabled functionalities.
   *
   * @throws RuntimeException
   * @throws MySqlQueryErrorException
   */
  private function executeEnabled(): void
  {
    if ($this->constantsFilename!==null)
    {
      $this->executeColumnWidths();
    }

    if ($this->className!==null)
    {
      $this->executeCreateConstants();
    }

    $this->logNumberOfConstants();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Searches for 3 lines in the source code of the class for constants. The lines are:
   * * The first line of the doc block with the annotation '@setbased.stratum.constants'.
   * * The last line of this doc block.
   * * The last line of continuous constant declarations directly after the doc block.
   * If one of these line can not be found the line number will be set to null.
   *
   * @param string $source The source code of the constant class.
   *
   * @return array With the 3 line number as described
   */
  private function extractLines(string $source): array
  {
    $tokens = token_get_all($source);

    $line1 = null;
    $line2 = null;
    $line3 = null;

    // Find annotation @constants
    $step = 1;
    foreach ($tokens as $token)
    {
      switch ($step)
      {
        case 1:
          // Step 1: Find doc comment with annotation.
          if (is_array($token) && $token[0]==T_DOC_COMMENT)
          {
            if (str_contains($token[1], '@setbased.stratum.constants'))
            {
              $line1 = $token[2];
              $step  = 2;
            }
          }
          break;

        case 2:
          // Step 2: Find end of doc block.
          if (is_array($token))
          {
            if ($token[0]==T_WHITESPACE)
            {
              $line2 = $token[2];
              if (substr_count($token[1], "\n")>1)
              {
                // Whitespace contains new line: end doc block without constants.
                $step = 4;
              }
            }
            else
            {
              if ($token[0]==T_CONST)
              {
                $line3 = $token[2];
                $step  = 3;
              }
              else
              {
                $step = 4;
              }
            }
          }
          break;

        case 3:
          // Step 4: Find en of constants declarations.
          if (is_array($token))
          {
            if ($token[0]==T_WHITESPACE)
            {
              if (substr_count($token[1], "\n")<=1)
              {
                // Ignore whitespace.
                $line3 = $token[2];
              }
              else
              {
                // Whitespace contains new line: end of const declarations.
                $step = 4;
              }
            }
            elseif ($token[0]==T_CONST || $token[2]==$line3)
            {
              $line3 = $token[2];
            }
            else
            {
              $step = 4;
            }
          }
          break;

        case 4:
          // Leave loop.
          break;
      }
    }

    // @todo get indent based on indent of the doc block.

    return [$line1, $line2, $line3];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Merges $columns and $labels (i.e. all known constants) into $constants.
   */
  private function fillConstants(): void
  {
    foreach ($this->columns as $tableName => $table)
    {
      foreach ($table as $columnName => $column)
      {
        if (isset($this->columns[$tableName][$columnName]['constant_name']))
        {
          $this->constants[$column['constant_name']] = $column['length'];
        }
      }
    }

    foreach ($this->labels as $label => $id)
    {
      $this->constants[$label] = $id;
    }

    ksort($this->constants);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Loads the width of all columns in the MySQL schema into $columns.
   *
   * @throws MySqlQueryErrorException
   */
  private function loadColumns(): void
  {
    $rows = $this->dl->allTableColumns();
    foreach ($rows as $row)
    {
      $row['length']                                          = DataTypeHelper::deriveFieldLength($row);
      $this->columns[$row['table_name']][$row['column_name']] = $row;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Loads all primary key labels from the MySQL database.
   *
   * @throws MySqlQueryErrorException
   */
  private function loadLabels(): void
  {
    $tables = $this->dl->allLabelTables();
    foreach ($tables as $table)
    {
      $rows = $this->dl->labelsFromTable($table['table_name'], $table['id'], $table['label']);
      foreach ($rows as $row)
      {
        $this->labels[$row['label']] = $row['id'];
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Loads from file $constantsFilename the previous table and column names, the width of the column,
   * and the constant name (if assigned) and stores this data in $oldColumns.
   *
   * @throws RuntimeException
   */
  private function loadOldColumns(): void
  {
    if (file_exists($this->constantsFilename))
    {
      $handle = fopen($this->constantsFilename, 'r');

      $lineNumber = 0;
      while (($line = fgets($handle)))
      {
        $lineNumber++;
        if ($line!="\n")
        {
          $n = preg_match('/^\s*(([a-zA-Z0-9_]+)\.)?([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s+(\d+)\s*(\*|[a-zA-Z0-9_]+)?\s*$/',
                          $line,
                          $matches);
          if ($n===0)
          {
            throw new RuntimeException("Illegal format at line %d in file '%s'.",
                                       $lineNumber,
                                       $this->constantsFilename);
          }

          if (isset($matches[6]))
          {
            $schemaName   = $matches[2];
            $tableName    = $matches[3];
            $columnName   = $matches[4];
            $length       = $matches[5];
            $constantName = $matches[6];

            if ($schemaName)
            {
              $tableName = $schemaName.'.'.$tableName;
            }

            $this->oldColumns[$tableName][$columnName] = ['table_name'    => $tableName,
                                                          'column_name'   => $columnName,
                                                          'length'        => $length,
                                                          'constant_name' => $constantName];
          }
        }
      }
      if (!feof($handle))
      {
        throw new RuntimeException("Error reading from file '%s'.", $this->constantsFilename);
      }

      $ok = fclose($handle);
      if ($ok===false)
      {
        throw new RuntimeException("Error closing file '%s'.", $this->constantsFilename);
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Logs the number of constants generated.
   */
  private function logNumberOfConstants(): void
  {
    $countIds    = count($this->labels);
    $countWidths = count($this->constants) - $countIds;

    $this->io->writeln('');
    $this->io->text(sprintf('Number of constants based on column widths: %d', $countWidths));
    $this->io->text(sprintf('Number of constants based on database IDs : %d', $countIds));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates PHP code with constant declarations.
   *
   * @return array The generated PHP code, lines are stored as rows in the array.
   */
  private function makeConstantStatements(): array
  {
    $width1    = 0;
    $width2    = 0;
    $constants = [];

    foreach ($this->constants as $constant => $value)
    {
      $width1 = max(mb_strlen($constant), $width1);
      $width2 = max(mb_strlen((string)$value), $width2);
    }

    $format = sprintf('  const %%-%ds = %%%dd;', $width1, $width2);
    foreach ($this->constants as $constant => $value)
    {
      $constants[] = sprintf($format, $constant, $value);
    }

    return $constants;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Preserves relevant data in $oldColumns into $columns.
   */
  private function mergeColumns(): void
  {
    foreach ($this->oldColumns as $tableName => $table)
    {
      foreach ($table as $columnName => $column)
      {
        if (isset($this->columns[$tableName][$columnName]))
        {
          $this->columns[$tableName][$columnName]['constant_name'] = $column['constant_name'];
        }
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes table and column names, the width of the column, and the constant name (if assigned) to
   * $constantsFilename.
   */
  private function writeColumns(): void
  {
    ksort($this->columns);

    $content = '';
    foreach ($this->columns as $table)
    {
      $width1 = 0;
      $width2 = 0;
      foreach ($table as $column)
      {
        $width1 = max(mb_strlen($column['column_name']), $width1);
        $width2 = max(mb_strlen((string)$column['length']), $width2);
      }

      foreach ($table as $column)
      {
        if (isset($column['length']))
        {
          if (isset($column['constant_name']))
          {
            $format  = sprintf("%%s.%%-%ds %%%dd %%s\n", $width1, $width2);
            $content .= sprintf($format,
                                $column['table_name'],
                                $column['column_name'],
                                $column['length'],
                                $column['constant_name']);
          }
          else
          {
            $format  = sprintf("%%s.%%-%ds %%%dd\n", $width1, $width2);
            $content .= sprintf($format,
                                $column['table_name'],
                                $column['column_name'],
                                $column['length']);
          }
        }
      }

      $content .= "\n";
    }

    // Save the columns, width and constants to the filesystem.
    Util::writeTwoPhases($this->constantsFilename, $content, $this->io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Inserts new and replace old (if any) constant declaration statements in a PHP source file.
   *
   * @throws RuntimeException
   */
  private function writeConstantClass(): void
  {
    // Read the source of the class without actually loading the class. Otherwise, we can not (re)load the class in
    // MySqlRoutineLoaderWorker::replacePairsConstants.
    $fileName    = ClassReflectionHelper::getFileName($this->className);
    $source      = file_get_contents($fileName);
    $sourceLines = explode("\n", $source);

    // Search for the lines where to insert and replace constant declaration statements.
    $lineNumbers = $this->extractLines($source);
    if (!isset($lineNumbers[0]))
    {
      throw new RuntimeException("Annotation not found in '%s'.", $fileName);
    }

    // Generate the constant declaration statements.
    $constants = $this->makeConstantStatements();

    // Insert new and replace old (if any) constant declaration statements.
    $tmp1        = array_splice($sourceLines, 0, $lineNumbers[1]);
    $tmp2        = array_splice($sourceLines, (isset($lineNumbers[2])) ? $lineNumbers[2] - $lineNumbers[1] : 0);
    $sourceLines = array_merge($tmp1, $constants, $tmp2);

    // Save the configuration file.
    Util::writeTwoPhases($fileName, implode("\n", $sourceLines), $this->io);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
