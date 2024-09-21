<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Loader\Helper;

use SetBased\Stratum\MySql\Exception\MySqlQueryErrorException;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Helper class for handling the SQL mode of the MySQL instance.
 */
class SqlModeHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The canonical SQL mode.
   *
   * @var string
   */
  private string $canonicalSqlMode;

  /**
   * The canonical SQL mode with ORACLE.
   *
   * @var ?string
   */
  private ?string $canonicalSqlModeWithOracle = null;

  /**
   * The current SQL mode (also in canonical order).
   *
   * @var string
   */
  private string $currentSqlMode;

  /**
   * The metadata layer.
   *
   * @var MySqlMetadataLayer
   */
  private MySqlMetadataLayer $dl;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param MySqlMetadataLayer $dl      The metadata layer.
   * @param string             $sqlMode The SQL mode.
   */
  public function __construct(MySqlMetadataLayer $dl, string $sqlMode)
  {
    $this->dl = $dl;

    try
    {
      $this->dl->setSqlMode('ORACLE');
      $hasOracleMode = true;
    }
    catch (MySqlQueryErrorException)
    {
      $hasOracleMode = false;
    }

    if ($hasOracleMode)
    {
      $parts   = explode(',', $sqlMode);
      $parts[] = 'ORACLE';
      $this->dl->setSqlMode(implode(',', $parts));
      $this->canonicalSqlModeWithOracle = $this->dl->getCanonicalSqlMode();
    }
    else
    {
      $this->canonicalSqlModeWithOracle = null;
    }

    $this->dl->setSqlMode($sqlMode);
    $this->canonicalSqlMode = $this->dl->getCanonicalSqlMode();
    $this->currentSqlMode   = $this->canonicalSqlMode;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds ORACLE to the current SQL_MODE if the initial SQL_MODE did not include ORACLE.
   */
  public function addIfRequiredOracleMode(): void
  {
    if ($this->currentSqlMode!==$this->canonicalSqlModeWithOracle)
    {
      $this->dl->setSqlMode($this->canonicalSqlModeWithOracle);
      $this->currentSqlMode = (string)$this->canonicalSqlModeWithOracle;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Compares a SQL mode with the current SQL mode with or without ORACLE when appropriate.
   *
   * @param string $sqlMode The SQL mode.
   */
  public function compare(string $sqlMode): bool
  {
    $parts = explode(',', $sqlMode);
    if (in_array('ORACLE', $parts))
    {
      return ($sqlMode===$this->canonicalSqlModeWithOracle);
    }

    return ($sqlMode===$this->canonicalSqlMode);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the SQL mode in canonical  order.
   */
  public function getCanonicalSqlMode(): string
  {
    return $this->canonicalSqlMode;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether the MySQL instance has ORACLE SQL mode.
   */
  public function hasOracleMode(): bool
  {
    return ($this->canonicalSqlModeWithOracle!==null);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Removes ORACLE from the current SQL_MODE if the initial SQL_MODE did not include ORACLE.
   */
  public function removeIfRequiredOracleMode(): void
  {
    if ($this->currentSqlMode!==$this->canonicalSqlMode)
    {
      $this->dl->setSqlMode($this->canonicalSqlMode);
      $this->currentSqlMode = $this->canonicalSqlMode;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
