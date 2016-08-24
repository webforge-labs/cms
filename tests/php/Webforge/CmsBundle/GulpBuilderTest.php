<?php

namespace Webforge\CmsBundle;

use Symfony\Component\Process\Process;

class GulpBuilderTest extends \PHPUnit_Framework_TestCase {

  protected static $built = FALSE;

  public function setUp() {
    if (!self::$built) {

      $process = new Process('gulp build --dev');
      $process->setWorkingDirectory($GLOBALS['env']['root']->wtsPath());

      $process->mustRun();

      self::$built = TRUE;
    }
  }

  public static function provideAssetFiles() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test('fonts/TitilliumMaps29L001.woff');
    $test('fonts/fontawesome-webfont.woff');

    $test('js/load-require.js');

    $test('js/bootstrap/button.js');
    $test('js/bootstrap/popover.js');
    $test('js/bootstrap/dropdown.js');

    $test('js/jquery.js');

    $test('js/knockout.js');
    $test('js/lodash.js');
    $test('js/moment.js');
    $test('js/knockout-collection.js');
    $test('js/knockout-mapping.js');

    $test('js/cms/modules/dispatcher.js');

    $test('js/WebforgeCmsBundle/translations-compiled.json');

    $test('js/cms/main.js');

    $test('css/webforge-cms.css');

    $test('js/text.js');
    $test('js/json.js');
  
    return $tests;
  }

  /**
   * @dataProvider provideAssetFiles
   */
  public function testSeveralFilesAreWrittenToAssetsFolderWhenGulpBuildIsExecuted($file) {
    $root = $GLOBALS['env']['root']->sub('www/assets/');
    $this->assertFileExists((string) $root->getFile($file), $file.' does not exist in assets folder after build.');
  }
}
