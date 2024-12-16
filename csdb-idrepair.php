<?php
require_once("scripts-bootstrap.inc.php");

function fixCSDBFromDownloadID( $prod )
{
  printf("pouet: %6d | ",$prod->id);
  
  if (!preg_match("/csdb\.dk\/release\/download\.php\?id=(\d+)/",$prod->download,$m))
  {
    printf("regex error");
    return false;
  }
  
  printf("%6d -> ",$m[1]);
  $csdbResponse = @file_get_contents(sprintf("https://csdb.dk/misc/getreleaseidfromdownloadid.php?dlid=%d",$m[1]));
  
  $csdbID = (int)$csdbResponse;
  if (!$csdbID)
  {
    printf("error: '%s'",$csdbResponse);
    return false;
  }

  printf("csdb: %d",$csdbID);
  SQLLib::UpdateRow("prods",array("csdb"=>(int)$csdbID),sprintf_esc("id=%d",$prod->id));
  return true;
}


function fixCSDBFromGetinternalfileID( $prod )
{
  printf("pouet: %6d | ",$prod->id);
  
  if (!preg_match("/csdb\.dk\/getinternalfile\.php\/(\d+)\//",$prod->download,$m))
  {
    printf("regex error");
    return false;
  }
  
  printf("%6d -> ",$m[1]);
  $csdbResponse = @file_get_contents(sprintf("https://csdb.dk/misc/getreleaseidfromdownloadid.php?gifid=%d",$m[1]));
  
  $csdbID = (int)$csdbResponse;
  if (!$csdbID)
  {
    printf("error: '%s'",$csdbResponse);
    return false;
  }

  printf("csdb: %d",$csdbID);
  SQLLib::UpdateRow("prods",array("csdb"=>(int)$csdbID),sprintf_esc("id=%d",$prod->id));
  return true;
}

$prods = SQLLib::SelectRows("select * from prods ".
  " where download like '%csdb.dk/release/download.php%' and csdb = 0");
printf("download.php links: %d\n",count($prods));
$i = 0;
foreach($prods as $prod)
{
  printf("%5d / %5d | ",$i++,count($prods));
  fixCSDBFromDownloadID($prod);
  printf("\n");
}


$prods = SQLLib::SelectRows("select * from prods ".
  " where download like '%csdb.dk/getinternalfile.php%' and csdb = 0");
printf("getinternalfile.php links: %d\n",count($prods));  
$i = 0;
foreach($prods as $prod)
{
  printf("%5d / %5d | ",$i++,count($prods));
  fixCSDBFromGetinternalfileID($prod);
  printf("\n");
}
