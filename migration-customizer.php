<?
require_once("scripts-bootstrap.inc.php");

$users = SQLLib::SelectRows("select * from usersettings");
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
      array("box"=>"LatestBBS"     ,"limit"=>$user->{"indexbbstopics"} ),
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
  SQLLib::UpdateRow("usersettings",array("customizerJSON"=>json_encode(array("frontpage"=>$boxes))),"id=".$user->id);
  echo $user->id."\n";
}
?>