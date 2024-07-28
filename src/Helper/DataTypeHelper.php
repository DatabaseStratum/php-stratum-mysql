<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Helper;

use SetBased\Exception\FallenException;

/**
 * Utility class for deriving information based on a MySQL data type.
 */
class DataTypeHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the corresponding PHP type hinting of a MySQL column type.
   *
   * @param string[] $dataTypeInfo Metadata of the MySQL data type.
   *
   * @return string
   */
  public static function columnTypeToPhpTypeHinting(array $dataTypeInfo): string
  {
    switch ($dataTypeInfo['data_type'])
    {
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'year':
        $phpType = 'int';
        break;

      case 'decimal':
        $phpType = 'int|float|string';
        break;

      case 'float':
      case 'double':
        $phpType = 'float';
        break;

      case 'bit':
      case 'varbinary':
      case 'binary':
      case 'char':
      case 'varchar':
      case 'time':
      case 'timestamp':
      case 'date':
      case 'datetime':
      case 'enum':
      case 'inet4':
      case 'inet6':
      case 'set':
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
        $phpType = 'string';
        break;

      case 'list_of_int':
        $phpType = 'string|int[]';
        break;

      default:
        throw new FallenException('data type', $dataTypeInfo['data_type']);
    }

    return $phpType;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the widths of a field based on a MySQL data type.
   *
   * @param array $dataTypeInfo Metadata of the column on which the field is based.
   *
   * @return int|null
   */
  public static function deriveFieldLength(array $dataTypeInfo): ?int
  {
    switch ($dataTypeInfo['data_type'])
    {
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'float':
      case 'double':
        $ret = $dataTypeInfo['numeric_precision'];
        break;

      case 'decimal':
        $ret = $dataTypeInfo['numeric_precision'];
        if ($dataTypeInfo['numeric_scale']>0)
        {
          $ret += 1;
        }
        break;

      case 'char':
      case 'varchar':
      case 'binary':
      case 'varbinary':
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
      case 'bit':
      case 'json':
        $ret = $dataTypeInfo['character_maximum_length'];
        break;

      case 'datetime':
      case 'timestamp':
        $ret = 16;
        break;

      case 'inet4':
        $ret = 15;
        break;

      case 'inet6':
        // Fully written out IPv4 mapped addresses are not supported.
        $ret = 39;
        break;

      case 'year':
        $ret = 4;
        break;

      case 'time':
        $ret = 8;
        break;

      case 'date':
        $ret = 10;
        break;

      case 'enum':
      case 'set':
        // We don't assign a width to column with type enum and set.
        $ret = null;
        break;

      default:
        throw new FallenException('data type', $dataTypeInfo['data_type']);
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns PHP code escaping the value of a PHP expression that can be safely used when concatenating a SQL statement.
   *
   * @param array  $dataTypeInfo Metadata of the column on which the field is based.
   * @param string $expression   The PHP expression.
   *
   * @return string The generated PHP code.
   */
  public static function escapePhpExpression(array $dataTypeInfo, string $expression): string
  {
    switch ($dataTypeInfo['data_type'])
    {
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'year':
        $ret = "'.\$this->quoteInt(".$expression.").'";
        break;

      case 'float':
      case 'double':
        $ret = "'.\$this->quoteFloat(".$expression.").'";
        break;

      case 'char':
      case 'varchar':
      case 'time':
      case 'timestamp':
      case 'date':
      case 'datetime':
      case 'enum':
      case 'inet4':
      case 'inet6':
      case 'set':
        $ret = "'.\$this->quoteString(".$expression.").'";
        break;

      case 'binary':
      case 'varbinary':
        $ret = "'.\$this->quoteBinary(".$expression.").'";
        break;

      case 'decimal':
        $ret = "'.\$this->quoteDecimal(".$expression.").'";
        break;

      case 'bit':
        $ret = "'.\$this->quoteBit(".$expression.").'";
        break;

      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
        $ret = '?';
        break;

      case 'list_of_int':
        $ret = "'.\$this->quoteListOfInt(".$expression.", '".
          addslashes($dataTypeInfo['delimiter'])."', '".
          addslashes($dataTypeInfo['enclosure'])."', '".
          addslashes($dataTypeInfo['escape'])."').'";
        break;

      default:
        throw new FallenException('data type', $dataTypeInfo['data_type']);
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the type of a bind variable.
   *
   * @see http://php.net/manual/en/mysqli-stmt.bind-param.php
   *
   * @param array $dataTypeInfo Metadata of the column on which the field is based.
   *
   * @return string
   */
  public static function getBindVariableType(array $dataTypeInfo): string
  {
    $ret = '';
    switch ($dataTypeInfo['data_type'])
    {
      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'year':
        $ret = 'i';
        break;

      case 'float':
      case 'double':
        $ret = 'd';
        break;

      case 'time':
      case 'timestamp':
      case 'binary':
      case 'enum':
      case 'bit':
      case 'set':
      case 'char':
      case 'varchar':
      case 'date':
      case 'datetime':
      case 'varbinary':
      case 'decimal':
      case 'inet4':
      case 'inet6':
      case 'list_of_int':
        $ret = 's';
        break;

      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
        $ret .= 'b';
        break;

      default:
        throw new FallenException('parameter type', $dataTypeInfo['data_type']);
    }

    return $ret;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether MySQL column type is a BLOB or a CLOB.
   *
   * @param string $dataType Metadata of the MySQL data type.
   *
   * @return bool
   */
  public static function isBlobParameter(string $dataType): bool
  {
    switch ($dataType)
    {
      case 'tinytext':
      case 'text':
      case 'mediumtext':
      case 'longtext':
      case 'tinyblob':
      case 'blob':
      case 'mediumblob':
      case 'longblob':
        $isBlob = true;
        break;

      case 'tinyint':
      case 'smallint':
      case 'mediumint':
      case 'int':
      case 'bigint':
      case 'year':
      case 'decimal':
      case 'float':
      case 'double':
      case 'time':
      case 'timestamp':
      case 'binary':
      case 'enum':
      case 'inet4':
      case 'inet6':
      case 'bit':
      case 'set':
      case 'char':
      case 'varchar':
      case 'date':
      case 'datetime':
      case 'varbinary':
      case 'list_of_int':
        $isBlob = false;
        break;

      default:
        throw new FallenException('data type', $dataType);
    }

    return $isBlob;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the corresponding PHP type declaration of a MySQL column type.
   *
   * @param string $phpTypeHint The PHP type hinting.
   *
   * @return string
   */
  public static function phpTypeHintingToPhpTypeDeclaration(string $phpTypeHint): string
  {
    $phpType = '';

    switch ($phpTypeHint)
    {
      case 'array':
      case 'array[]':
      case 'bool':
      case 'float':
      case 'int':
      case 'string':
      case 'void':
        $phpType = $phpTypeHint;
        break;

      default:
        $parts = explode('|', $phpTypeHint);
        $key   = array_search('null', $parts);
        if (sizeof($parts)==2 && $key!==false)
        {
          unset($parts[$key]);

          $tmp = static::phpTypeHintingToPhpTypeDeclaration(implode('|', $parts));
          if ($tmp!=='')
          {
            $phpType = '?'.$tmp;
          }
        }
    }

    return $phpType;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
