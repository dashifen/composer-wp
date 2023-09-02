<?php

use Dashifen\ComposerScripts\AbstractComposerScript;
use Dashifen\ComposerScripts\ComposerScriptException;

if (!is_file($autoloader = realpath(__DIR__ . '/../vendor/autoload.php'))) {
  die('Run "composer install" to download dependencies before running this script.');
}

require $autoloader;

class ResetScript extends AbstractComposerScript
{
  /**
   * run
   *
   * Executes this script.
   *
   * @return int
   * @throws ComposerScriptException
   */
  public function run(): int
  {
    if (is_dir('public_html')) {
      $this->emptyDirectory('public_html');
    }
    
    if (is_dir('vendor')) {
      $this->emptyDirectory('vendor');
    }
    
    if (is_file('composer.lock')) {
      unlink('composer.lock');
    }
    
    return 0;
  }
}

(function() {
  try {
    $resetScript = new ResetScript();
    $resetScript->run();
  } catch (ComposerScriptException $e) {
    echo $e->getMessage();
  }
})();