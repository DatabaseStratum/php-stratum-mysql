<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Test cases for PHP parameter names.
 */
class ParameterTypeTest extends DataLayerTestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test parameter names.
   */
  public function test1()
  {
    $reflection_class  = new \ReflectionClass(TestMySqlDataLayer::class);
    $reflection_method = $reflection_class->getMethod('tstTestParameterType');

    $doc_block = $reflection_method->getDocComment();

    self::assertStringContainsString('@param mixed $pPhpType1', $doc_block);
    self::assertStringContainsString('@param mixed $pPhpType2', $doc_block);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
