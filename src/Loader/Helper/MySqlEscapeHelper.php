<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Loader\Helper;

use SetBased\Stratum\Common\Loader\Helper\EscapeHelper;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Object for escaping strings such that they are safe to use in SQL queries.
 */
class MySqlEscapeHelper implements EscapeHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The connection to the MySQL or MariaDB instance.
   *
   * @var MySqlMetadataLayer
   */
  private MySqlMetadataLayer $dl;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   */
  public function __construct(MySqlMetadataLayer $dl)
  {
    $this->dl = $dl;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function escapeString(string $string): string
  {
    return $this->dl->realEscapeString($string);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
