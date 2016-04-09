<?
require_once("scripts-bootstrap.inc.php");

$data = file_get_contents("http://demozoo.org/api/adhoc/pouet/prod-demozoo-ids-by-pouet-id/");
$data = json_decode($data);
echo "prod count: ".count($data)."\n";
$a = array();
foreach($data as $v)
{
  $a[] = array("id"=>$v->pouet_id,"demozoo"=>$v->demozoo_id);
  if (count($a) >= 1000)
  {
    //var_dump($a);
    SQLLib::UpdateRowMulti("prods","id",$a);
    $a = array();
    echo ".";
  }
}
SQLLib::UpdateRowMulti("prods","id",$a);
echo ".\n";

$data = file_get_contents("http://demozoo.org/api/adhoc/pouet/group-demozoo-ids-by-pouet-id/");
$data = json_decode($data);
echo "group count: ".count($data)."\n";
$a = array();
foreach($data as $v)
{
  $a[] = array("id"=>$v->pouet_id,"demozoo"=>$v->demozoo_id);
  if (count($a) >= 1000)
  {
    //var_dump($a);
    SQLLib::UpdateRowMulti("groups","id",$a);
    $a = array();
    echo ".";
  }
}
SQLLib::UpdateRowMulti("groups","id",$a);
echo ".\n";

/*
$rows = SQLLib::SelectRows("select * from downloadlinks where link like '%demozoo.org/productions/%'");
foreach($rows as $v)
{
  if (preg_match("/productions\/(\d+)/",$v->link,$m))
  {
    SQLLib::UpdateRow("prods",array("demozoo"=>$m[1]),sprintf_esc("id=%d",$v->prod));
    SQLLib::Query(sprintf_esc("delete from downloadlinks where id=%d",$v->id));
    //printf("%d -> %d\n",$v->prod,$m[1]);
  }
}
*/
