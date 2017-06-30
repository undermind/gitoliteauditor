#!/usr/bin/php
<?php

/*
function Determinator($string)
{
# Regex for repo name []repo foo/bar]
REPO_NAME_REGEX = /\Arepo (.*)\n\z/
# Regex for project description [foo/bar "owner" = "Description of repo"], owner may be empty
REPO_INFO_REGEX = /\A([^ ]*) "([^"]*)" = "(.*)"\n\z/
# Regex for access rights [  RW+ = username1 username2]
REPO_ACCESS_REGEX = /\A  ([^ ]*) = (.*)\n\z/

http://gitolite.com/gitolite/conf/index.html

RW\+?C?D?M?|-|R - regex for permissions
http://gitolite.com/gitolite/conf-2/#summary-of-permissions

\n\s*(RW\+?C?D?M?|R|-)([^ ]*)(\S*)\s*=(.*)
https://regex101.com/r/HKxH2e/4
}
https://regex101.com/r/HKxH2e/3
*/

//require_once 'vendor/autoload.php';
$filename="../gitolite-admin/conf/gitolite.conf";

//$filename="gitolite.conf";
if (file_exists($filename))
{
$groups=array();
$repos=array();
$handle = fopen($filename, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $line = explode("#",$line);
      if (isset($line[1])) 
      {//echo "###".$line[1]; 
      continue;}
      $line=$line[0];
      $line = trim ($line);

      ////////////////////////////////////////////////////////////
      if (preg_match("/repo (.*)/",$line, $regresult))
      {
        //new repo on hands
        if (isset($currentrepo))
        {
          $repoinfo=array();
          $repoinfo[]=$currentrepo; //reponame
          $repoinfo[]=$reporights; //rights on repo
          unset($currentrepo);unset($reporights);
        }
        $currentrepo=trim($regresult[1]);$reporights=array();
        //echo "\nNew repo ".$currentrepo;
        //print_r($regresult);
        continue;        
      }
      ////////////////////////////////////////////////////////////
      if (preg_match('/\@(.*) = (.*)\w*/',$line,$regresult))
      {
         //group
         $groupsusers=explode(" ",$regresult[2]);
          ///subgoups
         $subcolletctor=array();
         foreach($groupsusers as $subidx=>$subval)
         {
           if (substr($subval,0,1)==="@")
           {
             echo $subval;
             $subcolletctor=array_merge($subcolletctor,$groups[substr($subval,1)]);
             unset($groupsusers[$subidx]);
           }
         }
         //merge subgroups
         $groupsusers = array_merge($groupsusers,$subcolletctor);
/// end of subgroups
         $oldcount = count($groupsusers);
         $groupsusers=array_unique($groupsusers);
         if (count($groupsusers)!=$oldcount) echo "We cleaned from ".$oldcount." to ".count($groupsusers)." at ".$regresult[1];
         //$groupsusers=array_filter($groupsusers);//filter empty elements

         $groups[$regresult[1]]= isset($groups[$regresult[1]])?array_merge($groups[$regresult[1]],$groupsusers):$groupsusers;

         //print_r($regresult);
         /*
         echo "\n";
         print_r($regresult[1]);
         echo "=";
         print_r($groups[$regresult[1]]);*/

         continue;
      }
      //////////////////////////////////////////////////////////////
/// looks like rule
       
    }
    fclose($handle);
    print_r($groups);
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