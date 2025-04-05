<?php

namespace Dashifen\WordPress\Build;

/**
 * Composer Post Update Command
 *
 * This object encapsulates the behaviors that we want to perform after a
 * composer update or install.  Currently, it does three things:
 *
 *  1. moves our MU plugin loader into the right location
 *  2. deletes the core plugins that we're not using
 *  3. deletes the core themes that we're not using
 */
class PostUpdate
{
  private string $root;
  
  /**
   * Instantiates the post update script.
   */
  public function __construct()
  {
    // we're in the scripts folder; the root of our project is one folder up.
    
    $this->root = dirname(__DIR__);
  }
  
  /**
   * Executes the post-update behaviors.
   *
   * @return void
   */
  public function run(): void {
    $this->setupMuLoader();
    $this->removeUnusedPlugins();
    $this->removeUnusedThemes();
  }
  
  /**
   * Moves the MU plugin loader, which lets us put MU plugins in subfolders
   * within mu-plugins, into the correct location.
   *
   * @return void
   */
  private function setupMuLoader(): void
  {
    $muSource = 'staging/wp-content/mu-plugins/mu-plugin-loader/mu-plugin-loader-loader.php';
    $muDestination = 'staging/wp-content/mu-plugins/mu-plugin-loader-loader.php';
    copy($muSource, $muDestination);
  }
  
  /**
   * Removes the Akismet and Hello, Dolly plugins.
   *
   * @return void
   */
  private function removeUnusedPlugins(): void
  {
    if(is_dir($this->root . '/staging/wp-content/plugins/akismet')) {
      $this->removeDirectory($this->root . '/staging/wp-content/plugins/akismet');
    }
    
    if (is_file($this->root . '/staging/wp-content/plugins/hello.php')) {
      unlink($this->root . '/staging/wp-content/plugins/hello.php');
    }
  }
  
  /**
   * Deletes the contents of $directory and then itself.
   *
   * @param string $directory
   *
   * @return void
   */
  private function removeDirectory(string $directory): void
  {
    shell_exec(sprintf('rm -rf "%s"', $directory));
  }
  
  /**
   * Removes the twenty* core themes.
   *
   * @return void
   */
  private function removeUnusedThemes(): void
  {
    chdir($this->root .'/staging/wp-content/themes');
    foreach(glob('twenty*', GLOB_ONLYDIR) as $theme) {
      $this->removeDirectory(getcwd() . DIRECTORY_SEPARATOR . $theme);
    }
    
    chdir($this->root);
  }
}

new PostUpdate()->run();
