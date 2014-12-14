<?
require_once("scripts-bootstrap.inc.php");

$rows = SQLLib::selectRows("select * from compotypes");

$compos = array();
foreach($rows as $v) $compos[$v->id] = $v->componame;

echo "<?\n\$COMPOTYPES = ".var_export($compos,true).";\n?>";
?>