<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Stratum\Backend\Backend;
use SetBased\Stratum\Backend\Config;
use SetBased\Stratum\Backend\ConstantWorker;
use SetBased\Stratum\Backend\CrudWorker;
use SetBased\Stratum\Backend\RoutineLoaderWorker;
use SetBased\Stratum\Backend\RoutineWrapperGeneratorWorker;
use SetBased\Stratum\Backend\StratumStyle;

/**
 * The PhpStratum's backend for MySQL and MariaDB using mysqli.
 */
class MySqlBackend extends Backend
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  public function createConstantWorker(Config $settings, StratumStyle $io): ?ConstantWorker
  {
    return new MySqlConstantWorker($settings, $io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  public function createCrudWorker(Config $settings, StratumStyle $io): ?CrudWorker
  {
    return new MySqlCrudWorker($settings, $io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  public function createRoutineLoaderWorker(Config $settings, StratumStyle $io): ?RoutineLoaderWorker
  {
    return new MySqlRoutineLoaderWorker($settings, $io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritDoc
   */
  public function createRoutineWrapperGeneratorWorker(Config       $settings,
                                                      StratumStyle $io): ?RoutineWrapperGeneratorWorker
  {
    return new MySqlRoutineWrapperGeneratorWorker($settings, $io);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
