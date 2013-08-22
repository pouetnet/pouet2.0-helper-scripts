<?
error_reporting(E_ALL ^ E_NOTICE);

//define("NEW_CHARSET","utf8");
define("NEW_CHARSET","utf8mb4");

if ($SERVER["REQUEST_URI"]) die("cmdline only!");

include_once("pouet/bootstrap.inc.php");

set_time_limit(0);

function convertTables()
{
  $tables = SQLLib::selectRows("SELECT TABLE_CATALOG, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, COLLATION_NAME, COLUMN_TYPE, CHARACTER_SET_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=\"pouet\"");
  
  $n = 0;
  foreach($tables as $table)
  {
    $n++;
    
    if ($table->COLUMN_TYPE!="text" && strstr($table->COLUMN_TYPE,"varchar")===false)
      continue;
      
    if ($table->CHARACTER_SET_NAME==NEW_CHARSET)
      continue;
    //$table->TABLE_NAME;
    //$table->COLUMN_NAME;
    //$table->CHARACTER_SET_NAME;
  
    $startTotal = microtime(true);
    
    echo "\n".$table->TABLE_NAME.".".$table->COLUMN_NAME." (".($n)."/".count($tables).")\n";
  
    echo "[".$table->TABLE_NAME.".".$table->COLUMN_NAME."] Dropping previous column...\n";

    try {
      SQLLib::Query("alter table ".$table->TABLE_NAME." DROP COLUMN `".$table->COLUMN_NAME."_utf8`");
    } catch(Exception $e) {};
  
    echo "[".$table->TABLE_NAME.".".$table->COLUMN_NAME."] Adding new column...\n";
  
    SQLLib::Query("alter table ".$table->TABLE_NAME." ADD COLUMN `".$table->COLUMN_NAME."_utf8` ".$table->COLUMN_TYPE." CHARACTER SET ".NEW_CHARSET." COLLATE ".NEW_CHARSET."_general_ci NOT NULL  AFTER `".$table->COLUMN_NAME."`");
  
//    echo " .";
  
    //$unescaped = SQLLib::SelectRow("select count(*) as c from ".$table->TABLE_NAME." WHERE `".$table->COLUMN_NAME."` regexp \"[^\\][\\'\\\"]\"")->c;
    //$escaped   = SQLLib::SelectRow("select count(*) as c from ".$table->TABLE_NAME." WHERE `".$table->COLUMN_NAME."` regexp \"[\\][\\'\\\"]\"")->c;
  
    //var_dump($unescaped,$escaped); 
    //exit();
    
//    echo " .";
  
    $r = SQLLib::Query("select id,`".$table->COLUMN_NAME."` from ".$table->TABLE_NAME);
    $num = mysqli_num_rows($r);
  
    echo "[".$table->TABLE_NAME.".".$table->COLUMN_NAME."] Starting conversion...\n";
  
    //echo "\n".$table->TABLE_NAME.".".$table->COLUMN_NAME.": ".$num."\n";
    
    $start = microtime(true);
    $n = 0;
    $a = array();
    while($o = SQLLib::Fetch($r))
    {
      $p = $o->{$table->COLUMN_NAME};
      //$p = mb_convert_encoding($p,"UTF-8","ISO-8859-1");
      $p = html_entity_decode($p,ENT_NOQUOTES,"UTF-8");
      if (strstr($p,"\\'")!==false || strstr($p,'\"')!==false || strstr($p,"\\"."\\")!==false)
        $p = stripslashes($p);
      
      $a[$o->id] = $p;
      //SQLLib::UpdateRow($table->TABLE_NAME,array($table->COLUMN_NAME."_utf8"=>$p),"id=".$o->id);
      
      if (count($a) == 20)
      {
        $cond = "";
        foreach($a as $k=>$v)
          $cond .= sprintf_esc(" WHEN %d THEN '%s' ",$k,$v);
        SQLLib::Query("update ".$table->TABLE_NAME." set `".$table->COLUMN_NAME."_utf8` = (CASE id ".$cond." END) WHERE id IN (".implode(",",array_keys($a)).")");
        $a = array();
      }
      if (($n % 100) == 0)
      {
        $elap = microtime(true) - $start;
        $rps = $n / $elap;
        $eta = $rps ? (int)($num / $rps) : 0;
        printf("%8d / %8d [%2d%%] (%8d) <%02d:%02d / %02d:%02d> [%.2frps]\r",$n,$num,(int)($n*100/$num),$o->id,(int)($elap/60),((int)$elap)%60,(int)($eta/60),((int)$eta)%60,$rps);
      }
      $n++;
    }
    if (count($a))
    {
      $cond = "";
      foreach($a as $k=>$v)
        $cond .= sprintf_esc(" WHEN %d THEN '%s' ",$k,$v);
      SQLLib::Query("update ".$table->TABLE_NAME." set `".$table->COLUMN_NAME."_utf8` = (CASE id ".$cond." END) WHERE id IN (".implode(",",array_keys($a)).")");
    }

    echo "\n";
    
    echo "[".$table->TABLE_NAME.".".$table->COLUMN_NAME."] Replacing columns...\n";

    SQLLib::Query("alter table ".$table->TABLE_NAME." DROP COLUMN `".$table->COLUMN_NAME."`");
    SQLLib::Query("alter table ".$table->TABLE_NAME." CHANGE COLUMN `".$table->COLUMN_NAME."_utf8` `".$table->COLUMN_NAME."` ".$table->COLUMN_TYPE." CHARACTER SET ".NEW_CHARSET." COLLATE ".NEW_CHARSET."_general_ci NOT NULL");
    //SQLLib::Query("alter table ".$table->TABLE_NAME." CONVERT TO CHARACTER SET utf8");
  
    $endTotal = microtime(true);
    
    printf("\n%.2f seconds\n\n",$endTotal-$startTotal);
  }
}
function convertBBSTopicCategories()
{
  global $THREAD_CATEGORIES;
  echo "\n\n";
  echo "convertBBSTopicCategories (1)\n";
  $a = array();
  foreach($THREAD_CATEGORIES as $v) $a[] = "'".$v."'";
  SQLLib::Query("alter table bbs_topics ADD COLUMN `category_en` ENUM(".implode(",",$a).")");
  
  echo "convertBBSTopicCategories (2)\n";
  $cond = "";
  foreach($THREAD_CATEGORIES as $k=>$v) $cond .= " WHEN ".$k." THEN '".$v."' ";
  SQLLib::Query("update bbs_topics set category_en = (CASE category ".$cond." END)");
  
  echo "convertBBSTopicCategories (3)\n";
  SQLLib::Query("alter table bbs_topics DROP COLUMN `category`");
  SQLLib::Query("alter table bbs_topics CHANGE COLUMN `category_en` `category` ENUM(".implode(",",$a).")");
}

convertTables();
convertBBSTopicCategories();

?>