#!/usr/bin/php
<?php
use fajr\FajrConfig;

// register our autoloader
require_once (__DIR__ . '/../src/libfajr/libfajr.php');
Loader::register();
Loader::searchForClasses(__DIR__ . '/../src', true);

if (!FajrConfig::isConfigured()) {
  echo 'Chyba: Fajr nie je nakonfigurovany'. "\n";
  return;
}

if (!FajrConfig::get('Template.Cache')) {
  echo 'Info: Template cache je vypnuta, nema zmysel ju mazat'. "\n";
  return;
}

$path = FajrConfig::getDirectory('Template.Cache.Path');
echo 'Info: Template cache je ' . $path . "\n";

foreach (new DirectoryIterator($path) as $fileInfo) {
  if (!$fileInfo->isDot() && $fileInfo->isFile()) {
    unlink($fileInfo->getPathname());
  }
}
