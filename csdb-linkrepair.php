<?php
require_once("scripts-bootstrap.inc.php");

$id = (int)$argv[1] ?: 64280;
function fixCSDB( $prod )
{
  if (!$prod->csdb) return false;

  printf("%6d -> ",$prod->id);
  printf("%6d -> ",$prod->csdb);
  
  $csdbID = $prod->csdb;
  $csdbXML = file_get_contents(sprintf("http://csdb.dk/webservice/?type=release&id=%d",$csdbID));
  
  $url = false;
  $xml = @simplexml_load_string($csdbXML);
  if (!$xml)
  {
    printf("*xmlerror:%s*\n",substr($csdbXML,0,10));
    return;
  }
  if ($xml->Release->DownloadLinks)
  {
    foreach($xml->Release->DownloadLinks->DownloadLink as $v)
    {
      if (strstr($v->Link,"getinternalfile.php")!==false && $v->Status == "Ok")
      {
        $url = $v->Link;
      }
    }
    if (!$url)
    {
      foreach($xml->Release->DownloadLinks->DownloadLink as $v)
      {
        if ($v->Status == "Ok")
        {
          $url = $v->Link;
        }
      }
    }
    if (!$url)
    {
      printf("*allurlsbroken*\n");
    }
  }
  if ($url)
  {
    printf("%s\n",$url);
    SQLLib::UpdateRow("prods",array("download"=>$url),sprintf_esc("id=%d",$prod->id));
    SQLLib::Query(sprintf_esc("delete from prods_linkcheck where prodID = %d",$prod->id));
    return true;
  }

  printf("*urlnotfound*\n");
  return false;
}

$prods = SQLLib::SelectRows("select * from prods_linkcheck ".
  " left join prods on prods_linkcheck.prodid = prods.id ".
  " where (returncode = 0 or returncode >= 400) and csdb > 0");
$i = 0;
foreach($prods as $prod)
{
  printf("%5d / %5d | ",$i++,count($prods));
  fixCSDB($prod);
  //exit();
}
