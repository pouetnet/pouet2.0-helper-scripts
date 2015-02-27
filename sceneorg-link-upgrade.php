<?
require_once("scripts-bootstrap.inc.php");

function replace_sceneorg_url( $urlIn, &$urlOut )
{
  if(strstr($urlIn,"scene.org/file.php") !== false)
  {
    $url = parse_url($urlIn);
    parse_str($url["query"],$res);
    if ($res["file"])
    {
      $urlOut = "https://files.scene.org/view/".ltrim($res["file"],"/");
      return true;
    }
  }
  else if(strstr($urlIn,"scene.org/dir.php") !== false)
  {
    $url = parse_url($urlIn);
    parse_str($url["query"],$res);
    if ($res["dir"])
    {
      $urlOut = "https://files.scene.org/browse/".ltrim($res["dir"],"/");
      return true;
    }
  }
  else if(strstr($urlIn,"scene.org/file_dl.php") !== false)
  {
    $url = parse_url($urlIn);
    parse_str($url["query"],$res);
    if ($res["url"])
    {
      //$urlOut = "https://files.scene.org/browse/".ltrim($res["dir"],"/");
      $mirrors = array(
        "ftp://ftp.scene.org/pub/",
        "ftp://ftp.de.scene.org/pub/",
        "http://http.de.scene.org/pub/",
        "http://http.fr.scene.org/",
        "http://http.se.scene.org/pub/demos/scene.org/",
      );
      foreach($mirrors as $m)
      {
        if (strpos($res["url"],$m)===0)
        {
          $urlOut = "https://files.scene.org/view/".str_replace($m,"",$res["url"]);
          return true;
        }
      }
    }
  }
  return false;
}

function doReplace($table,$field)
{
  echo "\nParsing ".$table.".".$field."\n";
  $rows = SQLLib::SelectRows("select id,".$field." from ".$table." where ".$field." like '%scene.org/file%' or ".$field." like '%scene.org/dir%'");
  $a = array();
  foreach($rows as $row)
  {
    $url = "";
    if (replace_sceneorg_url($row->{$field},$url))
    {
      printf("%s\n-> %s\n",$row->{$field},$url);
      $row = array();
      $row["id"] = $row->id;
      $row[$field] = $url;
      $a[] = $row;
    }
      
    if (count($a) >= 100)
    {
      echo "Update...\n";
      //SQLLib::UpdateRowMulti($table,"id",$a);
      $a = array();
    } 
  }
  if (count($a) >= 0)
  {
    echo "Final update...\n";
    //SQLLib::UpdateRowMulti($table,"id",$a);
    $a = array();
  } 
}

doReplace("prods","download");
doReplace("downloadlinks","link");
doReplace("partylinks","download");

?>