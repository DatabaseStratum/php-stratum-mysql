<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Stratum\Backend\Config;
use SetBased\Stratum\Backend\StratumStyle;
use SetBased\Stratum\MySql\MySqlDataLayer;
use SetBased\Stratum\MySql\MySqlDefaultConnector;
use SetBased\Stratum\MySql\MySqlMetadataLayer;

/**
 * Base class for commands which needs a connection to a MySQL or MariaDB instance.
 */
class MySqlWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The metadata layer.
   *
   * @var MySqlMetadataLayer|null
   */
  protected ?MySqlMetadataLayer $dl;

  /**
   * The output object.
   *
   * @var StratumStyle
   */
  protected StratumStyle $io;

  /**
   * The settings from the PhpStratum configuration file.
   *
   * @var Config
   */
  protected Config $settings;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param Config       $settings The settings from the PhpStratum configuration file.
   * @param StratumStyle $io       The output object.
   */
  public function __construct(Config $settings, StratumStyle $io)
  {
    $this->settings = $settings;
    $this->io       = $io;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Disconnects from MySQL instance.
   */
  public function disconnect(): void
  {
    if ($this->dl!==null)
    {
      $this->dl->disconnect();
      $this->dl = null;
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects to a MySQL instance.
   */
  protected function connect(): void
  {
    $host     = $this->settings->manString('database.host');
    $user     = $this->settings->manString('database.user');
    $password = $this->settings->manString('database.password');
    $database = $this->settings->manString('database.database');
    $port     = $this->settings->manInt('database.port', 3306);

    $connector = new MySqlDefaultConnector($host, $user, $password, $database, $port);
    $dataLayer = new MySqlDataLayer($connector);
    $dataLayer->connect();

    $this->dl = new MySqlMetadataLayer($dataLayer, $this->io);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
