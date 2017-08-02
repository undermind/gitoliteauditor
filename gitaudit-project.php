<?php

//require_once 'vendor/autoload.php';
$configfile=".gitolite.conf";
$keydir=".keydir/";

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
  // print_r($repos);
  // print_r($users);

   //print_r($repos["TestClone"]);
   //print_r($users["user.name"]);
   
//foreach($repos as $repid=>$repdat){ echo $repid."=>".print_r(CountOfSubreps($repdat),true)."\n";}
   

echo '<!DOCTYPE html><html><head> <meta charset="utf-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>GIToLITE MATRIX</title>';

//echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.js"></script>';
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/floatthead/2.0.3/jquery.floatThead.min.js" integrity="sha256-2Uhne1l42Oh0kPdYdDpIYx+mSMQO/HGOjZFEsBjQntY=" crossorigin="anonymous"></script>';

/*echo '<style>
            th
            {
                background-color: grey;
                color: white;
                text-align: center;
                vertical-align: bottom;
                height: 150px;
                padding-bottom: 3px;
                padding-left: 5px;
                padding-right: 5px;
            }

            .verticalText
            {
                text-align: center;
                vertical-align: middle;
                width: 20px;
                margin: 0px;
                padding: 0px;
                padding-left: 3px;
                padding-right: 3px;
                padding-top: 10px;
                white-space: nowrap;
                -webkit-transform: rotate(-90deg); 
                -moz-transform: rotate(-90deg);                 
            };</style>';*/
echo '</head><body><center>';
echo "<table border=1 class='table'>\n";
echo "<thead><tr><th>GIT Repository:</th><th>Group within Git Repository</th><th>Rights (R - read; W - write; + - enforce)</th><th>User Name:</th><th>Comments</th></tr></thead>\n";

$secondrow="";
foreach($repos as $header=>$repdat) 
{
 $c=CountOfSubreps($repdat);
 if ($c)
 {
   echo "<th colspan=\"".(count($c)+1)."\">".$header."</th>";
   $secondrow.="<th><div class=\"verticalText\">".$header."</div></th>";
   foreach($c as $h) $secondrow.="<th><div class=\"verticalText\">".$h."</div></th>";
 }
 else echo "<th rowspan=\"2\"><div class=\"verticalText\">".$header."</div></th>";
 
}
echo "</tr><tr><th>User</th>".$secondrow."</tr></thead>\n";
//$secondrow="";

foreach($users as $usrname=>$usr)
  {
    echo "<tr><td>".$usrname."</td>".(isset($usr["keys"])?"<td>".count($usr["keys"]):"<td bgcolor=\"gray\">")."</td>";

foreach($repos as $header=>$repdat) 
{
 $c=CountOfSubreps($repdat);
 if ($c)
 {
   echo (isset($usr[$header])?"<td>".(is_array($usr[$header])?implode("\n",$usr[$header]):$usr[$header]):"<td bgcolor=\"gray\">")."</td>";
   foreach($c as $h) 
     echo (isset($usr[$header."@".$h])?"<td>".(is_array($usr[$header."@".$h])?implode("\n",$usr[$header."@".$h]):$usr[$header."@".$h]):"<td bgcolor=\"gray\">")."</td>";

 }
 else 
     echo (isset($usr[$header])?"<td>".(is_array($usr[$header])?implode("\n",$usr[$header]):$usr[$header]):"<td bgcolor=\"gray\">")."</td>";
 
}
    echo "</tr>\n";
  }
  echo "</table><script>$('table').floatThead({ position: 'absolute'});</script></body></html>";


//end of matrix   
} else { die("Config file read error1");
} }

function CountOfSubreps($rep)
{
$c=0;
$subs=array();
foreach($rep as $repd)
{
 foreach($repd as $subid=>$reprule) if (is_array($reprule)) {$c++;$subs[]=$subid;}
}
return $c?$subs:null;
}


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

?>