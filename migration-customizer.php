<?
require_once("scripts-bootstrap.inc.php");

$users = SQLLib::SelectRows("select * from usersettings");
$a = array();
foreach($users as $user)
{
  $boxes = array(
    "leftbar" => array(
      array("box"=>"Login"),
      array("box"=>"CDC"           ,"limit"=>$user->{"indexcdc"} ),
      array("box"=>"LatestAdded"   ,"limit"=>$user->{"indexlatestadded"} ),
      array("box"=>"LatestReleased","limit"=>$user->{"indexlatestreleased"} ),
      array("box"=>"TopMonth"      ,"limit"=>$user->{"indextopprods"} ),
      array("box"=>"TopAlltime"    ,"limit"=>$user->{"indextopkeops"} ),
    ),
    "middlebar" => array(
      array("box"=>"LatestOneliner","limit"=>$user->{"indexoneliner"} ),
      array("box"=>"LatestBBS"     ,"limit"=>$user->{"indexbbstopics"},"hideResidue"=>$user->{"indexbbsnoresidue"} ),
      array("box"=>"NewsBoxes"     ,"limit"=>$user->{"indexojnews"} ),
    ),
    "rightbar" => array(
      array("box"=>"SearchBox"      ,"limit"=>$user->{"indexsearch"} ),
      array("box"=>"Stats"          ,"limit"=>$user->{"indexstats"} ),
      array("box"=>"AffilButton"    ,"limit"=>$user->{"indexlinks"} ),
      array("box"=>"LatestComments" ,"limit"=>$user->{"indexlatestcomments"} ),
      array("box"=>"Watchlist"      ,"limit"=>$user->{"indexwatchlist"} ),
      array("box"=>"LatestParties"  ,"limit"=>$user->{"indexlatestparties"} ),
      array("box"=>"UpcomingParties"),
      array("box"=>"TopGlops"       ,"limit"=>$user->{"indextopglops"} ),
    ),
  );
  foreach($boxes as &$box)
  {
    foreach($box as &$item)
    {
      if ($item["limit"] === 0)
        unset($item);
    }
  }
  $a[] = array("id"=>$user->id,"customizerJSON"=>json_encode(array("frontpage"=>$boxes)));
  if (count($a) >= 100)
  {
    echo "Update...\n";
    SQLLib::UpdateRowMulti("usersettings","id",$a);
    $a = array();
  }
  echo $user->id."\n";
}
if (count($a) > 0)
{
  echo "Final update...\n";
  SQLLib::UpdateRowMulti("usersettings","id",$a);
}
?>