<?
require_once("scripts-bootstrap.inc.php");

$rows = SQLLIB::selectRows("select * from platforms");
$platforms = array();
foreach($rows as $r)
{
  $platforms[ $r->id ] = get_object_vars($r);
  unset($platforms[ $r->id ]["id"]);
  $platforms[ $r->id ]["slug"] = strtolower(preg_replace("/[^a-zA-Z0-9]+/","",$platforms[ $r->id ]["name"]));
}
ksort($platforms);
$data = "<"."?\n\$PLATFORMS = ".var_export($platforms,true).";\n?".">";
file_put_contents(POUET_ROOT_LOCAL . "/include_pouet/enum-platforms.inc.php",$data);

$css = "";
foreach($platforms as $p)
  $css .= sprintf(".os_%s { background: url('http://www.pouet.net/gfx/os/%s'); }\n",$p["slug"],$p["icon"]);
file_put_contents("platforms.css",$css);

$rows = SQLLIB::selectRows("select * from platformcaps");
$platformcaps = array();
foreach($rows as $r)
{
  $platformcaps[ $r->id ] = get_object_vars($r);
  unset($platformcaps[ $r->id ]["id"]);
}
ksort($platformcaps);
$data = "<"."?\n\$PLATFORMCAPS = ".var_export($platformcaps,true).";\n?".">";
file_put_contents(POUET_ROOT_LOCAL . "/include_pouet/enum-platformcaps.inc.php",$data);
?>