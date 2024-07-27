<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Exception\RuntimeException;
use SetBased\Helper\CodeStore\PhpCodeStore;
use SetBased\Stratum\Backend\RoutineWrapperGeneratorWorker;
use SetBased\Stratum\Common\Helper\Util;
use SetBased\Stratum\Middle\NameMangler\NameMangler;
use SetBased\Stratum\MySql\Helper\RoutineLoaderHelper;
use SetBased\Stratum\MySql\Helper\StratumMetadataHelper;
use SetBased\Stratum\MySql\Wrapper\Wrapper;

/**
 * Command for generating a class with wrapper methods for calling stored routines in a MySQL database.
 */
class MySqlRoutineWrapperGeneratorWorker extends MySqlWorker implements RoutineWrapperGeneratorWorker
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Store php code with indention.
   *
   * @var PhpCodeStore
   */
  private PhpCodeStore $codeStore;

  /**
   * Array with fully qualified names that must be imported.
   *
   * @var array
   */
  private array $imports = [];

  /**
   * The filename of the file with the metadata of all stored procedures.
   *
   * @var string
   */
  private string $metadataFilename;

  /**
   * Class name for mangling routine and parameter names.
   *
   * @var string
   */
  private string $nameMangler;

  /**
   * The class name (including namespace) of the parent class of the routine wrapper.
   *
   * @var string
   */
  private string $parentClassName;

  /**
   * If true wrapper must declare strict types.
   *
   * @var bool
   */
  private bool $strictTypes;

  /**
   * The class name (including namespace) of the routine wrapper.
   *
   * @var string|null
   */
  private ?string $wrapperClassName;

  /**
   * The filename where the generated wrapper class must be stored
   *
   * @var string
   */
  private string $wrapperFilename;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   *
   * @throws RuntimeException
   */
  public function execute(): int
  {
    $this->readConfigurationFile();

    if ($this->wrapperClassName!==null)
    {
      $this->generateWrapperClass();
    }

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates the wrapper class.
   *
   * @throws RuntimeException
   */
  private function generateWrapperClass(): void
  {
    $this->io->title('PhpStratum: MySql Wrapper');

    $this->codeStore = new PhpCodeStore();

    /** @var NameMangler $mangler */
    $mangler  = new $this->nameMangler();
    $routines = $this->readRoutineMetadata();

    if (!empty($routines))
    {
      // Sort routines by their wrapper method name.
      $sortedRoutines = [];
      foreach ($routines as $routine)
      {
        $methodName                  = $mangler->getMethodName($routine['routine_name']);
        $sortedRoutines[$methodName] = $routine;
      }
      ksort($sortedRoutines);

      // Write methods for each stored routine.
      foreach ($sortedRoutines as $routine)
      {
        if ($routine['designation']!=='hidden')
        {
          $this->writeRoutineFunction($routine, $mangler);
        }
      }
    }
    else
    {
      echo "No files with stored routines found.\n";
    }

    $wrappers        = $this->codeStore->getRawCode();
    $this->codeStore = new PhpCodeStore();
    $this->writeClassHeader();
    $this->codeStore->append($wrappers, false);
    $this->writeClassTrailer();
    $this->storeWrapperClass();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Reads parameters from the configuration file.
   */
  private function readConfigurationFile(): void
  {
    $this->wrapperClassName = $this->settings->optString('wrapper.wrapper_class');
    if ($this->wrapperClassName!==null)
    {
      $this->parentClassName  = $this->settings->manString('wrapper.parent_class');
      $this->nameMangler      = $this->settings->manString('wrapper.mangler_class');
      $this->wrapperFilename  = $this->settings->manString('wrapper.wrapper_file');
      $this->metadataFilename = $this->settings->manString('loader.metadata');
      $this->strictTypes      = $this->settings->manBool('wrapper.strict_types', true);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the metadata of stored routines.
   *
   * @return array
   *
   * @throws RuntimeException
   */
  private function readRoutineMetadata(): array
  {
    $stratumMetadata = new StratumMetadataHelper($this->metadataFilename, RoutineLoaderHelper::METADATA_REVISION);

    return $stratumMetadata->getAllMetadata();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes the wrapper class to the filesystem.
   */
  private function storeWrapperClass(): void
  {
    $code = $this->codeStore->getCode();
    Util::writeTwoPhases($this->wrapperFilename, $code, $this->io);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generate a class header for stored routine wrapper.
   */
  private function writeClassHeader(): void
  {
    $p = strrpos($this->wrapperClassName, '\\');
    if ($p!==false)
    {
      $namespace = ltrim(substr($this->wrapperClassName, 0, $p), '\\');
      $className = substr($this->wrapperClassName, $p + 1);
    }
    else
    {
      $namespace = null;
      $className = $this->wrapperClassName;
    }

    // Write PHP tag.
    $this->codeStore->append('<?php');

    // Write strict types.
    if ($this->strictTypes)
    {
      $this->codeStore->append('declare(strict_types=1);');
    }

    // Write name space of the wrapper class.
    if ($namespace!==null)
    {
      $this->codeStore->append('');
      $this->codeStore->append(sprintf('namespace %s;', $namespace));
      $this->codeStore->append('');
    }

    // If the child class and parent class have different names import the parent class. Otherwise, use the fully
    // qualified parent class name.
    $parentClassName = substr($this->parentClassName, strrpos($this->parentClassName, '\\') + 1);
    if ($className!==$parentClassName)
    {
      $this->imports[]       = $this->parentClassName;
      $this->parentClassName = $parentClassName;
    }

    // Write use statements.
    if (!empty($this->imports))
    {
      $this->imports = array_unique($this->imports, SORT_REGULAR);
      foreach ($this->imports as $import)
      {
        $this->codeStore->append(sprintf('use %s;', $import));
      }
      $this->codeStore->append('');
    }

    // Write class name.
    $this->codeStore->append('/**');
    $this->codeStore->append(' * The data layer.', false);
    $this->codeStore->append(' */', false);
    $this->codeStore->append(sprintf('class %s extends %s', $className, $this->parentClassName));
    $this->codeStore->append('{');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generate a class trailer for stored routine wrapper.
   */
  private function writeClassTrailer(): void
  {
    $this->codeStore->appendSeparator();
    $this->codeStore->append('}');
    $this->codeStore->append('');
    $this->codeStore->appendSeparator();
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Generates a complete wrapper method for a stored routine.
   *
   * @param array       $routine     The metadata of the stored routine.
   * @param NameMangler $nameMangler The mangler for wrapper and parameter names.
   */
  private function writeRoutineFunction(array $routine, NameMangler $nameMangler): void
  {
    $wrapper = Wrapper::createRoutineWrapper($routine, $this->codeStore, $nameMangler);
    $wrapper->writeRoutineFunction();

    $this->imports = array_merge($this->imports, $wrapper->getImports());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
