<?php

use WordpressFinder\WordpressFinder;
use Humbug\SelfUpdate\Updater;

set_time_limit(0);

$autoloaders = [
  __DIR__ . '/../../../autoload.php',
  __DIR__ . '/../vendor/autoload.php',
];

foreach ($autoloaders as $file) {
  if (file_exists($file)) {
    $autoloader = $file;
    break;
  }
}

if (isset($autoloader)) {
  require_once $autoloader;
}
else {
  echo 'You must set up the project dependencies using `composer install`' . PHP_EOL;
  exit(1);
}

$PATH = FALSE;
$VERSION_CHECK = FALSE;
$SELF_UPDATE = FALSE;

foreach ($_SERVER['argv'] as $arg) {
  // If a variable to set was indicated on the previous iteration,
  // then set the value of the named variable (e.g. "ROOT") to "$arg".
  if ($VAR) {
    $$VAR = "$arg";
    $VAR = FALSE;
  }
  else {
    switch ($arg) {
      case "--version":
        $VERSION_CHECK = TRUE;
        break;
      case "self-update":
        $SELF_UPDATE = TRUE;
        break;
    }
    if (substr($arg, 0, 7) == "--path=") {
      $PATH = substr($arg, 7);
    }
  }
}

$LAUNCHER_VERSION = '@git-version@';

if ($VERSION_CHECK) {
  echo "WP-CLI Launcher Version: {$LAUNCHER_VERSION}" . PHP_EOL;
  exit(0);
}

if ($SELF_UPDATE) {
  if ($LAUNCHER_VERSION === '@' . 'git-version' . '@') {
    echo "Automatic update not supported.\n";
    exit(1);
  }
  else {
    echo "WP-CLI Launcher Version: {$LAUNCHER_VERSION}" . PHP_EOL;
  }
  $updater = new Updater(NULL, FALSE);
  $updater->setStrategy(Updater::STRATEGY_GITHUB);
  $updater->getStrategy()->setPackageName('leymannx/wp-cli-launcher');
  $updater->getStrategy()->setPharName('wp-cli.phar');
  $updater->getStrategy()->setCurrentLocalVersion($LAUNCHER_VERSION);
  try {
    $result = $updater->update();
    echo $result ? "Updated!\n" : "No update needed!\n";
    echo "WP-CLI Launcher Version: " . $updater->getNewVersion() . PHP_EOL;
    exit(0);
  } catch (\Exception $e) {
    echo "Automatic update failed, please download the latest version from https://github.com/leymannx/wp-cli-launcher/releases\n";
    exit(1);
  }
}

if ($PATH === FALSE) {
  $PATH = getcwd();
}

$wordpressFinder = new WordpressFinder();

if ($wordpressFinder->locateRoot($PATH)) {
  $webRoot = $wordpressFinder->getWebRoot();

  // Detect WP-CLI version.
  if (file_exists($wordpressFinder->getVendorDir() . '/wp-cli/wp-cli/VERSION')) {
    $version_file = $wordpressFinder->getVendorDir() . '/wp-cli/wp-cli/VERSION';
    $WP_CLI_VERSION = file_get_contents($version_file);
    $WP_CLI_VERSION = str_replace(["\r", "\n"], '', $WP_CLI_VERSION);
  }

  // For now WP-CLI version must be anywhere between 2.0.0 and 3.0.0.
  if (gettype($WP_CLI_VERSION) == 'string' && version_compare($WP_CLI_VERSION, '2.0.0') && version_compare('3.0.0', $WP_CLI_VERSION)) {

    // We need to be in the WordPress directory to fire the command.
    // Drush Launcher does this via Drush's `drush_set_option('root', $webRoot);`.
    // Maybe WP-CLI has something similar.
    chdir($webRoot);

    // Fire command.
    require_once $wordpressFinder->getVendorDir() . '/wp-cli/wp-cli/php/boot-fs.php';

    // And change back.
    chdir($PATH);

    exit(0);
  }

  if (!$WP_CLI_VERSION) {
    echo 'The WP-CLI Launcher could not find a local WP-CLI in your WordPress site.' . PHP_EOL;
    echo 'Please add WP-CLI with Composer to your project.' . PHP_EOL;
    echo 'Run \'cd "' . $wordpressFinder->getComposerRoot() . '" && composer require wp-cli/wp-cli\'' . PHP_EOL;
    exit(1);
  }
}

echo 'The WP-CLI Launcher could not find a WordPress site to operate on. Please do *one* of the following:' . PHP_EOL;
echo '  - Navigate to any where within your WordPress project and try again.' . PHP_EOL;
echo '  - Add --path=path/to/wordpress so WP-CLI knows where your site is located.' . PHP_EOL;
exit(1);
