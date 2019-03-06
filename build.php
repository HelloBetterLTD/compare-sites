#!/usr/bin/env php
<?php
use Secondtruth\Compiler\Compiler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

error_reporting(E_ALL & ~E_COMPILE_WARNING);

if(!defined('ROOT')) define('ROOT', dirname(__FILE__) . '/');
if(!defined('BUILD_FOLDER')) define('BUILD_FOLDER', tempnam(sys_get_temp_dir(),'') );


require_once(ROOT . '/vendor/autoload.php');

$fs = new Filesystem();

if ($fs->exists(BUILD_FOLDER)) { $fs->remove(BUILD_FOLDER); }
$fs->mkdir(BUILD_FOLDER);

$fs->mirror(ROOT . 'bin', BUILD_FOLDER . '/bin');
$fs->mirror(ROOT . 'src', BUILD_FOLDER . '/src');
$fs->copy(ROOT . 'composer.json', BUILD_FOLDER . '/composer.json');
$fs->copy(ROOT . 'composer.lock', BUILD_FOLDER . '/composer.lock');

$composerStatement = sprintf(
    'composer install --prefer-dist --no-dev --optimize-autoloader --optimize-autoloader --working-dir=%s',
    BUILD_FOLDER
);
$process = new Process($composerStatement);
$process->run();

$compiler = new Compiler(BUILD_FOLDER);
$compiler->addIndexFile('bin/console');

$compiler->addDirectory('src');
$compiler->addDirectory('vendor');

$compiledPath = ROOT . "compare-sites.phar";
if ($fs->exists($compiledPath)) { $fs->remove($compiledPath);}
$compiler->compile($compiledPath);
$fs->chmod($compiledPath, 0755);



$fs->remove(BUILD_FOLDER);
$process = new Process($compiledPath);
try {
    $process->mustRun();
    echo "Phar built to: $compiledPath \n";
} catch (ProcessFailedException $ex) {
    echo "ERROR!!";
    echo $ex->getMessage();
    exit(1);
}