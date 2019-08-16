<?php
declare(strict_types=1);

namespace SetBased\Stratum\MySql\Backend;

use SetBased\Exception\FallenException;
use SetBased\Exception\RuntimeException;
use SetBased\Helper\CodeStore\PhpCodeStore;
use SetBased\Stratum\ConstantWorker;
use SetBased\Stratum\Helper\NonStatic;
use SetBased\Stratum\MySql\Wrapper\Wrapper;
use SetBased\Stratum\NameMangler\NameMangler;
use SetBased\Stratum\RoutineWrapperGeneratorWorker;

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
  private $codeStore;

  /**
   * Array with fully qualified names that must be imported.
   *
   * @var array
   */
  private $imports = [];

  /**
   * If true BLOBs and CLOBs must be treated as strings.
   *
   * @var bool
   */
  private $lobAsString;

  /**
   * The filename of the file with the metadata of all stored procedures.
   *
   * @var string
   */
  private $metadataFilename;

  /**
   * Class name for mangling routine and parameter names.
   *
   * @var string
   */
  private $nameMangler;

  /**
   * The class name (including namespace) of the parent class of the routine wrapper.
   *
   * @var string
   */
  private $parentClassName;

  /**
   * If true wrapper must declare strict types.
   *
   * @var bool
   */
  private $strictTypes;

  /**
   * The class name (including namespace) of the routine wrapper.
   *
   * @var string
   */
  private $wrapperClassName;

  /**
   * The type of the wrapper class. Either 'static' or 'non static'.
   *
   * @var string
   */
  private $wrapperClassType;

  /**
   * The filename where the generated wrapper class must be stored
   *
   * @var string
   */
  private $wrapperFilename;

  //--------------------------------------------------------------------------------------------------------------------

  /**
   * @inheritdoc
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
   */
  private function generateWrapperClass(): void
  {
    $this->io->title('Wrapper');

    $this->codeStore = new PhpCodeStore();

    /** @var NameMangler $mangler */
    $mangler  = new $this->nameMangler();
    $routines = $this->readRoutineMetadata();

    if (!empty($routines))
    {
      // Sort routines by their wrapper method name.
      $sorted_routines = [];
      foreach ($routines as $routine)
      {
        $method_name                   = $mangler->getMethodName($routine['routine_name']);
        $sorted_routines[$method_name] = $routine;
      }
      ksort($sorted_routines);

      // Write methods for each stored routine.
      foreach ($sorted_routines as $method_name => $routine)
      {
        // If routine type is hidden don't create routine wrapper.
        if ($routine['designation']!='hidden')
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

    // Write the header of the wrapper class.
    $this->writeClassHeader();

    // Write methods of the wrapper calls.
    $this->codeStore->append($wrappers, false);

    // Write the trailer of the wrapper class.
    $this->writeClassTrailer();

    // Write the wrapper class to the filesystem.
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
      $this->lobAsString      = $this->settings->manBool('wrapper.lob_as_string', false);
      $this->metadataFilename = $this->settings->manString('loader.metadata');
      $this->wrapperClassType = $this->settings->manString('wrapper.wrapper_type');
      $this->strictTypes      = $this->settings->manBool('wrapper.strict_types', true);
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the metadata of stored routines.
   *
   * @return array
   */
  private function readRoutineMetadata(): array
  {
    $data = file_get_contents($this->metadataFilename);

    $routines = (array)json_decode($data, true);
    if (json_last_error()!=JSON_ERROR_NONE)
    {
      throw new RuntimeException("Error decoding JSON: '%s'.", json_last_error_msg());
    }

    return $routines;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Writes the wrapper class to the filesystem.
   */
  private function storeWrapperClass(): void
  {
    $code = $this->codeStore->getCode();

    switch ($this->wrapperClassType)
    {
      case 'static':
        // Nothing to do.
        break;

      case 'non static':
        $code = NonStatic::nonStatic($code);
        break;

      default:
        throw new FallenException('wrapper class type', $this->wrapperClassType);
    }

    $this->writeTwoPhases($this->wrapperFilename, $code);
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
      $namespace  = ltrim(substr($this->wrapperClassName, 0, $p), '\\');
      $class_name = substr($this->wrapperClassName, $p + 1);
    }
    else
    {
      $namespace  = null;
      $class_name = $this->wrapperClassName;
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

    // If the child class and parent class have different names import the parent class. Otherwise use the fully
    // qualified parent class name.
    $parent_class_name = substr($this->parentClassName, strrpos($this->parentClassName, '\\') + 1);
    if ($class_name!=$parent_class_name)
    {
      $this->imports[]       = $this->parentClassName;
      $this->parentClassName = $parent_class_name;
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
    $this->codeStore->append(sprintf('class %s extends %s', $class_name, $this->parentClassName));
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
    $wrapper = Wrapper::createRoutineWrapper($routine, $this->codeStore, $nameMangler, $this->lobAsString);
    $wrapper->writeRoutineFunction();

    $this->imports = array_merge($this->imports, $wrapper->getImports());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
