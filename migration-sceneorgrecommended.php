<?
require_once("scripts-bootstrap.inc.php");

//SQLLib::Query("truncate awards");
//SQLLib::Query("truncate awards_categories");

$rows = SQLLib::SelectRows("select * from sceneorgrecommended_DEPRECATED");

foreach($rows as $v)
{
  $a = array();
  
  $series = "";
  $awardType = "";
  $category = $v->category;
  switch ($v->type)
  {
    case 'awardwinner':
      $series = "Scene.org Awards";
      $awardType = "winner";
      break;
    case 'awardnominee':
      $series = "Scene.org Awards";
      $awardType = "nominee";
      break;
    case 'viewingtip':
      $series = "Scene.org Viewing Tips";
      $awardType = "winner";
      $category = "Viewing Tip";
      break;
    case 'meteorikwinner':
      $series = "The Meteoriks";
      $awardType = "winner";
      break;
    case 'meteoriknominee':
      $series = "The Meteoriks";
      $awardType = "nominee";
      break;
  }

  $r = SQLLib::SelectRow(sprintf_esc("select * from awards_categories where series='%s' and category='%s'",$series,$category));
  if (!$r)
  {
    $id = SQLLib::InsertRow("awards_categories",array(
      "series" => $series,
      "category" => $category,
    ));
    $categoryID = $id;
  }
  else
  {
    $categoryID = $r->id;
  }
  
  SQLLib::InsertRow("awards",array(
    "prodID" => $v->prodid,
    "categoryID" => $categoryID,
  ));
  
  echo ".";
}

?>