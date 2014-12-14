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
var_export($platforms);
foreach($platforms as $p)
  printf(".os_%s { background: url('http://www.pouet.net/gfx/os/%s'); }\n",$p["slug"],$p["icon"]);
exit();

?>