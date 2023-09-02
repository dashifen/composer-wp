<?php

use Dashifen\ComposerScripts\AbstractComposerScript;
use Dashifen\ComposerScripts\ComposerScriptException;

if (!is_file($autoloader = realpath(__DIR__ . '/../vendor/autoload.php'))) {
  die('Run "composer install" to download dependencies before running this script.');
}

require $autoloader;

class PostUpdateScript extends AbstractComposerScript
{
  /**
   * run
   *
   * Executes this command.
   *
   * @return int
   * @throws ComposerScriptException
   */
  public function run(): int
  {
    $this->setupMuPluginLoader();
    $this->deleteCorePlugins();
    $this->deleteCoreThemes();
    return 0;
  }
  
  /**
   * setupMuPluginLoader
   *
   * Copies the MU plugin loader object out of its folder and into the core
   * mu-plugins folder where it then loads all of the other ones.
   *
   * @return void
   */
  private function setupMuPluginLoader(): void
  {
    copy(
      'public_html/wp-content/mu-plugins/wp-mu-plugin-loader/mu-plugin-loader-loader.php',
      'public_html/wp-content/mu-plugins/mu-plugin-loader-loader.php'
    );
  }
  
  /**
   * deleteCorePlugins
   *
   * Removes the Akismet and Hello, Dolly plugins, neither of which we use
   * at this time.
   *
   * @return void
   * @throws ComposerScriptException
   */
  private function deleteCorePlugins(): void
  {
    $root = $this->findRootDirectory();
    $helloDolly = $root . DIRECTORY_SEPARATOR . 'public_html/wp-content/plugins/hello.php';
    $akismet = $root . DIRECTORY_SEPARATOR . 'public_html/wp-content/plugins/akismet';
    
    if (is_file($helloDolly)) {
      unlink($helloDolly);
    }
    
    if (is_dir($akismet)) {
      $this->emptyDirectory($akismet);
    }
    
    chdir($root);
  }
  
  /**
   * deleteCoreThemes
   *
   * Removes the core themes that begin with the word "twenty" because we
   * don't use them.
   *
   * @return void
   * @throws ComposerScriptException
   */
  private function deleteCoreThemes(): void
  {
    $root = $this->findRootDirectory();
    $themes = $root . DIRECTORY_SEPARATOR . 'public_html/wp-content/themes';
    chdir($themes);
    
    foreach (glob('twenty*', GLOB_ONLYDIR) as $coreTheme) {
      $this->emptyDirectory($themes . DIRECTORY_SEPARATOR . $coreTheme);
    }
    
    chdir($root);
  }
}

(function() {
  try {
    $postUpdateCommand = new PostUpdateScript();
    $postUpdateCommand->run();
  } catch (ComposerScriptException $e) {
    echo $e->getMessage();
  }
})();