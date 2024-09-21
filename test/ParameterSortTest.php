<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for parameters with maximum length.
 */
class ParameterSortTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with normal length.
   */
  public function test1()
  {
    $row = $this->dataLayer->tstTestParameterMax('Manzarek', null, null);
    self::assertIsArray($row);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with long length.
   */
  public function test2()
  {
    $row = $this->dataLayer->tstTestParameterMax(str_repeat('x', 2048), null, null);
    self::assertNull($row);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
