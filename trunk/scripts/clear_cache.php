#!/usr/bin/php
<?php
use fajr\config\FajrConfigLoader;

// register our autoloader
require_once (__DIR__ . '/../src/libfajr/libfajr.php');
Loader::register();
Loader::searchForClasses(__DIR__ . '/../src', true);

if (!FajrConfigLoader::isConfigured()) {
  echo 'Chyba: Fajr nie je nakonfigurovany'. "\n";
  return;
}

$config = FajrConfigLoader::getConfiguration();

if (!$config->get('Template.Cache')) {
  echo 'Info: Template cache je vypnuta, nema zmysel ju mazat'. "\n";
  return;
}

$path = $config->getDirectory('Template.Cache.Path');
echo 'Info: Template cache je ' . $path . "\n";

foreach (new DirectoryIterator($path) as $fileInfo) {
  if (!$fileInfo->isDot() && $fileInfo->isFile()) {
    unlink($fileInfo->getPathname());
  }
}
