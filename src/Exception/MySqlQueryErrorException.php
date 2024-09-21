<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Exception;

use SetBased\Stratum\Middle\Exception\QueryErrorException;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Exception thrown when the execution of MySQL query fails.
 */
class MySqlQueryErrorException extends MySqlDataLayerException implements QueryErrorException
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The failed query.
   *
   * @var string
   */
  protected string $query;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param int    $errno  The error code value of the error ($mysqli->errno).
   * @param string $error  Description of the last error ($mysqli->error).
   * @param string $method The name of the executed method.
   * @param string $query  The failed query.
   */
  public function __construct(int $errno, string $error, string $method, string $query)
  {
    $this->query = $query;

    parent::__construct($errno, $error, $method);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns whether this exception is caused by an invalid SQL statement.
   */
  public function isQueryError(): bool
  {
    return ($this->errno===1064);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns an array with the lines of the SQL statement. The line where the error occurred will be styled.
   *
   * @param string $style The style for highlighting the line with error.
   */
  public function styledQuery(string $style = 'error'): array
  {
    $query = trim($this->query); // MySQL ignores leading whitespace in queries.

    if ($this->isQueryError())
    {
      // Query is a multi line query.
      // The format of a 1064 message is: %s near '%s' at line %d
      $errorLineNumber = trim(strrchr($this->error, ' '));

      // Prepend each line with line number.
      $lines   = explode(PHP_EOL, $query);
      $digits  = ceil(log(sizeof($lines) + 1, 10));
      $format  = sprintf('%%%dd %%s', $digits);
      $message = [];
      foreach ($lines as $i => $line)
      {
        if (($i + 1)===$errorLineNumber)
        {
          $message[] = sprintf('<%s>'.$format.'</%s>', $style, $i + 1, OutputFormatter::escape($line), $style);
        }
        else
        {
          $message[] = sprintf($format, $i + 1, OutputFormatter::escape($line));
        }
      }
    }
    else
    {
      $message = explode(PHP_EOL, $query);
    }

    return $message;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Composes the message of this exception as array of lines.
   */
  protected function composerMessage(): array
  {
    return array_merge($this->splitIntoTwoColumns('MySQL Errno', (string)$this->errno),
                       $this->splitIntoTwoColumns('Error', $this->error),
                       $this->splitIntoTwoColumns('Query', $this->query),
                       $this->splitIntoTwoColumns('Method', $this->method));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
