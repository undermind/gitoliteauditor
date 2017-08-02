#!/usr/bin/php
<?php

//require_once 'vendor/autoload.php';
$configfile="../gitolite-admin/conf/gitolite.conf";
$keydir="../gitolite-admin/keydir/";

//$configfile="gitolite.conf";
if (file_exists($configfile))
{
$groups=array();$groups["all"]=array("!EvErYbOdY!");
$repos=array();
$users=array();
$handle = fopen($configfile, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
      $line = explode("#",$line);
      if (isset($line[1])) 
      {//echo "###".$line[1]; //comments
      continue;}
      $line=$line[0];
      $line = trim ($line);
      //    if ($currentrepo==="bogus-repo")   { echo $line."\n";          } //show info about repo

      ////////////////////////////////////////////////////////////
      if (preg_match("/repo\s(.*)/",$line, $regresult))
      {
        //new repo on hands
        if (isset($currentrepo))
        {
          $repos=array_merge($repos,array($currentrepo=>$reporights));
          unset($currentrepo);unset($reporights);
        }
        $currentrepo=trim($regresult[1]);$reporights=array();
        //echo "New repo ".$currentrepo."\n"; //message about new repo
        //print_r($regresult); 
        continue;        
      }
      
      ////////////////////////////////////////////////////////////
      if (preg_match('/\@(.*) = (.*)\w*/',$line,$regresult))
      {

         $groupsusers=ExplainList($regresult[2],true,$regresult[1]);

         $groups[$regresult[1]]= isset($groups[$regresult[1]])?array_merge($groups[$regresult[1]],$groupsusers):$groupsusers;

         continue;
      }
      //////////////////////////////////////////////////////////////
/// looks like rule
/// 
// echo $line;
      if (preg_match("/\s*(RW\+?C?D?M?|R|-)\s(.*)\s*=(.*)/i",$line, $regresult))
      {
        // print_r($regresult);
        //check we have repo on hands?
        if (isset($currentrepo))
        {
          $rule=trim($regresult[1]);
          $ruleext=trim($regresult[2]);
          $ruletarget=trim($regresult[3]);
          //echo "Rule for repo ".$currentrepo." => ".$rule." (".$ruleext.") -> ".$ruletarget." \n";
          $ruletarget=ExplainList(trim($regresult[3]),true,$currentrepo);
          
          $ruletarget=array_filter($ruletarget);

          foreach($ruletarget as $usr)
          { 
             //if (!empty($ruleext)) echo "user ".$usr." has ".$rule.(!empty($ruleext)?" (".$ruleext.")":"")."@ ".$currentrepo."\n";
             //$usrrul = array($currentrepo=>(empty($ruleext)?$rule:array($ruleext=>$rule)));
//             $usrrul = array($currentrepo=>(empty($ruleext)?array("@"=>$rule):array($ruleext=>$rule)));
//             $usrrul = array($currentrepo=>(empty($ruleext)?array("@"=>$rule):array($ruleext=>$rule)));
             $usrrul = array((empty($ruleext)?$currentrepo:$currentrepo."@".$ruleext)=>$rule);
             //$usrrul[$currentrepo] = array_filter ($usrrul[$currentrepo]);
             $users[$usr]= isset($users[$usr])? array_merge_recursive($users[$usr],$usrrul):$usrrul;
             //$users[$usr] = array_unique($users[$usr]);
             //if (!empty($ruleext)) print_r($usrrul);
             //if (!empty($ruleext)) print_r($users[$usr]);

//idiotten check
/*             if (is_array($users[$usr][(empty($ruleext)?$currentrepo:$currentrepo."@".$ruleext)]))
             {
echo (empty($ruleext)?$currentrepo:$currentrepo."@".$ruleext)."\n";
//$users[$usr][(empty($ruleext)?$currentrepo:$currentrepo."@".$ruleext)] = array_unique( $users[$usr][(empty($ruleext)?$currentrepo:$currentrepo."@".$ruleext)]);
             }
*/
//end of idiotten check
          }
          if (!empty($ruleext)) $ruletarget=array($ruleext=>$ruletarget);
          //print_r($ruletarget);
          
          $reporights[$rule]= isset($reporights[$rule])?array_merge($reporights[$rule],$ruletarget):$ruletarget;
          

        } else  die("RULE wo REPo!");
        continue;        
      }
    }
    fclose($handle);
   
   foreach($users as $usrid=>$user)
   {
    $keylist=glob($keydir.$usrid."*");
    // foreach ($keylist as $filename) {  echo $usrid."=>".$filename."\n";    }
    if (count($keylist)>0)
    $users[$usrid]["keys"]=$keylist;

   }
// oneline for users WO keyss
// foreach($users as $usrname=>$usr) if (!isset($usr["keys"])) {echo $usrname."\n";print_r($users[$usrname]);}

ksort($users); ksort($repos);
   //print_r($groups);
   //
   //print_r($repos);
   // print_r($users);
   
   //print_r($repos["TestClone"]);
   //print_r($users["user.name"]);
   
      
} else { die("Config file read error1");
} }



function ExplainList($list, $doclean=false, $info=null)
{
 $listusers=explode(" ",$list);
 $subcolletctor=array(); global $groups;
 foreach($listusers as $subidx=>$subval)
  {
    $subval=trim($subval);
      if (substr($subval,0,1)==="@") //group?
           {
             //echo $subval;
             if (isset($groups[substr($subval,1)]))
             {
              $subcolletctor=array_merge($subcolletctor,$groups[substr($subval,1)]); 
              unset($listusers[$subidx]);
             } else {echo "No group for '".substr($subval,1)."'\n";print_r($groups);die("!!"); }
           }
  }
         //merge subgroups
         $listusers = array_merge($listusers,$subcolletctor);
  if ($doclean)
  {
         if (isset($info))  $oldcount = count($listusers);
         $listusers=array_unique($listusers);
         if (isset($info))
         {
           if (count($listusers)!=$oldcount) echo "We cleaned from ".$oldcount." to ".count($listusers)." at ".$info;
         }
         $listusers=array_filter($listusers);//filter empty elements


  }
 return $listusers;

}



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
foreach (glob("./passwords/*.txt") as $configfile) {
    if (!empty($_GET['debug'])) echo "\nLoading ".$configfile."...<br/>";
}


} catch (Exception $e) {    echo 'Exception: ',  $e->getMessage(), "\n";}
*/
?>