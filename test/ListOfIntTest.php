<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for a parameter with a list on integers.
 */
class ListOfIntTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a valid list of integers in CSV format.
   */
  public function test1()
  {
    $ids = "1,3";
    $ret = $this->dataLayer->tstTestListOfInt($ids);

    self::assertEquals(2, count($ret));
    self::assertArrayHasKey(1, $ret);
    self::assertArrayHasKey(3, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a valid array of integers.
   */
  public function test2()
  {
    $ids = [2, 4];
    $ret = $this->dataLayer->tstTestListOfInt($ids);

    self::assertEquals(2, count($ret));
    self::assertArrayHasKey(2, $ret);
    self::assertArrayHasKey(4, $ret);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a list with an invalid value in CSV format.
   */
  public function test3()
  {
    $this->expectException(\LogicException::class);
    $ids = "2,not_int";
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an array of with an invalid value.
   */
  public function test4a()
  {
    $this->expectException(\LogicException::class);
    $ids = ['not_int', 3];
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an array of with an invalid value.
   */
  public function test4b()
  {
    $this->expectException(\LogicException::class);
    $ids = [[], 3];
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an empty list of integers in CSV format.
   */
  public function test5()
  {
    $ids = null;
    $ret = $this->dataLayer->tstTestListOfInt($ids);
    self::assertEquals(0, count($ret));

    $ids = false;
    $ret = $this->dataLayer->tstTestListOfInt($ids);
    self::assertEquals(0, count($ret));

    $ids = '';
    $ret = $this->dataLayer->tstTestListOfInt($ids);
    self::assertEquals(0, count($ret));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an empty array/.
   */
  public function test6()
  {
    $ids = [];
    $ret = $this->dataLayer->tstTestListOfInt($ids);
    self::assertEquals(0, count($ret));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a list of integers and an empty value in CSV format.
   */
  public function test7a()
  {
    $this->expectException(\LogicException::class);
    $ids = "1,2,,3";
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a list of integers and an empty value in CSV format.
   */
  public function test7b()
  {
    $this->expectException(\LogicException::class);
    $ids = "1,2,";
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with a list of integers and an empty value in CSV format.
   */
  public function test7c()
  {
    $this->expectException(\LogicException::class);
    $ids = ",1,2";
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an array of integers and an empty value.
   */
  public function test8a()
  {
    $this->expectException(\LogicException::class);
    $ids = [1, 2, '', 3];
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an array of integers and an empty value.
   */
  public function test8b()
  {
    $this->expectException(\LogicException::class);
    $ids = [1, 2, 3, null];
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test with an array of integers and an empty value.
   */
  public function test8c()
  {
    $this->expectException(\LogicException::class);
    $ids = [false, 1, 2, 3];
    $this->dataLayer->tstTestListOfInt($ids);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
