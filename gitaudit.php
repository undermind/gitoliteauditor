#!/usr/bin/php
<?php
//require_once 'vendor/autoload.php';
$filename="../gitolite-admin/conf/gitolite.conf";
if (file_exists($filename))
{
$groups=array();
$repos=array();
$handle = fopen($filename, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $line = trim ($line);
      echo $line."\n";
    }
    fclose($handle);
} else {
} }

/*
if (PHP_SAPI === 'cli') {
    $name = $argv[1];
    $mail = $argv[2];
    $task = $argv[3];
}
else {
    $name = $_GET['name'];
    $mail = $_GET['mail'];
    $task = $_GET['task'];
}
 $task = preg_replace('/\D/', '', $task );
  print "name:".$name; print "\n";
  print "mail:".$mail; print "\n";
  print "task:".$task; print "\n";

try {
foreach (glob("./passwords/*.txt") as $filename) {
    if (!empty($_GET['debug'])) echo "\nLoading ".$filename."...<br/>";
}


} catch (Exception $e) {    echo 'Exception: ',  $e->getMessage(), "\n";}
*/
?>