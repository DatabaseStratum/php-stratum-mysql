<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

use PHPUnit\Framework\TestCase;

/**
 * Parent class for all test cases.
 */
class DataLayerTestCase extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The data layer.
   *
   * @var TestDataLayer
   */
  protected $dataLayer;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Connects to the MySQL server.
   */
  protected function setUp()
  {
    $this->dataLayer = new TestDataLayer();

    $this->dataLayer->connect('localhost', 'test', 'test', 'test');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
