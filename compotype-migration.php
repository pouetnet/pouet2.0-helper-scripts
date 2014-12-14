<?
require_once("scripts-bootstrap.inc.php");

SQLLib::Query("truncate compotypes");

$row = SQLLib::selectRow("DESC prods partycompo");
preg_match_all("/'([^']+)'/",$row->Type,$m);
$compos = $m[1];

foreach($compos as $v)
{
  $compoID = SQLLib::InsertRow("compotypes",array("componame"=>$v));
  printf("%3d - %s\n",$compoID,$v);
  
  SQLLib::Query(sprintf_esc("update prods set party_compo = %d where partycompo ='%s'",$compoID,$v));
  SQLLib::Query(sprintf_esc("update prodotherparty set party_compo = %d where partycompo ='%s'",$compoID,$v));
}

?>