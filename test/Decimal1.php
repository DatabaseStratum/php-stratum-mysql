<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Test;

/**
 * Class for decimals with toString() method.
 */
class Decimal1
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The value of this decimal.
   *
   * @var string
   */
  private string $value;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * Object constructor.
   */
  public function __construct(string $value)
  {
    $this->value = $value;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the value of this decimal.
   *
   * @return string
   */
  public function toString(): string
  {
    return $this->value;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
