<?php

use WordpressFinder\WordpressFinder;
use Webmozart\PathUtil\Path;

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

$DRUSH_LAUNCHER_VERSION = '@git-version@';

$VERSION = FALSE;

foreach ($_SERVER['argv'] as $arg) {
  // If a variable to set was indicated on the
  // previous iteration, then set the value of
  // the named variable (e.g. "ROOT") to "$arg".
  if ($VAR) {
    $$VAR = "$arg";
    $VAR = FALSE;
  }
  else {
    switch ($arg) {
      case "--version":
        $VERSION = TRUE;
        break;
    }
  }
}

$ROOT = getcwd();

$drupalFinder = new WordpressFinder();

if ($VERSION) {
  echo "Drush Launcher Version: {$DRUSH_LAUNCHER_VERSION}" . PHP_EOL;
}

if ($drupalFinder->locateRoot($ROOT)) {
  $drupalRoot = $drupalFinder->getWebRoot();

  // Detect WP-CLI version
  if (file_exists(Path::join($drupalFinder->getVendorDir(), 'wp-cli/wp-cli/VERSION'))) {
    $version_file = Path::join($drupalFinder->getVendorDir(), 'wp-cli/wp-cli/VERSION');
    $DRUSH_VERSION = file_get_contents($version_file);
  }

  if ($DRUSH_VERSION == '2.0.1') {
    require_once $drupalFinder->getVendorDir() . '/wp-cli/wp-cli/php/boot-fs.php';
    exit(0);
  }

  if (!$DRUSH_VERSION) {
    echo 'The Drush launcher could not find a local Drush in your Drupal site.' . PHP_EOL;
    echo 'Please add Drush with Composer to your project.' . PHP_EOL;
    echo 'Run \'cd "' . $drupalFinder->getComposerRoot() . '" && composer require wp-cli/wp-cli\'' . PHP_EOL;
    exit(1);
  }
}

echo 'The Drush launcher could not find a Drupal site to operate on. Please do *one* of the following:' . PHP_EOL;
echo '  - Navigate to any where within your Drupal project and try again.' . PHP_EOL;
echo '  - Add --root=/path/to/drupal so Drush knows where your site is located.' . PHP_EOL;
exit(1);
