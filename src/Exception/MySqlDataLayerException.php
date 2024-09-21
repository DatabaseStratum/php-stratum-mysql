<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Exception;

use SetBased\Stratum\Middle\Exception\DataLayerException;

/**
 * Exception for situations where the execution of s SQL query has failed.
 */
class MySqlDataLayerException extends \RuntimeException implements DataLayerException
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The error code value of the error ($mysqli->errno).
   *
   * @var int
   */
  protected int $errno;

  /**
   * Description of the last error ($mysqli->error).
   *
   * @var string
   */
  protected string $error;

  /**
   * The method.
   *
   * @var string
   */
  protected string $method;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int    $errno  The error code value of the error ($mysqli->errno).
   * @param string $error  Description of the last error ($mysqli->error).
   * @param string $method The name of the executed method.
   */
  public function __construct(int $errno, string $error, string $method)
  {
    $this->errno  = $errno;
    $this->error  = $error;
    $this->method = $method;

    parent::__construct($this->implodeMessage($this->composerMessage()));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the error code of the error.
   */
  public function getErrno(): int
  {
    return $this->errno;
  }
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the description of the error.
   */
  public function getError(): string
  {
    return $this->error;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  public function getName(): string
  {
    return 'MySQL Error';
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Composes the message of this exception as array of lines.
   */
  protected function composerMessage(): array
  {
    return array_merge($this->splitIntoTwoColumns('MySQL Errno', (string)$this->errno),
                       $this->splitIntoTwoColumns('Error', $this->error),
                       $this->splitIntoTwoColumns('Method', $this->method));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Splits a string (may contain multiple lines) into columns: The first columns will contain a header, the second
   * column the lines of the string.
   *
   * @param string $header The header.
   * @param string $string The string possible with multiple lines.
   */
  protected function splitIntoTwoColumns(string $header, string $string): array
  {
    $lines = [];

    $parts = explode(PHP_EOL, trim($string));
    foreach ($parts as $i => $part)
    {
      $lines[] = [($i===0 ? $header : ''), $part];
    }

    return $lines;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Implodes an array with lines of an error message to a string. Each line of the message consists out of two
   * columns.
   *
   * @param array $lines The lines of the error message.
   */
  private function implodeMessage(array $lines): string
  {
    $max = 0;
    foreach ($lines as $line)
    {
      $max = max($max, mb_strlen($line[0]));
    }

    $format = sprintf('%%-%ds: %%s', $max);
    $tmp    = [''];
    foreach ($lines as $line)
    {
      $tmp[] = sprintf($format, $line[0], $line[1]);
    }

    return implode(PHP_EOL, $tmp);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
