<?php
if ( php_sapi_name() != "cli" ) 
  die("this is a commandline-only script");
  
include_once("../scripts-bootstrap.inc.php");

/*
 * Based on the code at
 * http://www.innvo.com/c/PHP/1315192249-css-sprites-with-php
 */
class images_to_sprite
{
  function images_to_sprite()
  {
    $this->files = array();
  }

  function add_file( $key, $filename )
  {
    $this->files[$key] = $filename;
    list($x,$y) = getimagesize($filename);
    $this->x = max($x,$this->x);
    $this->y = max($y,$this->y);
  }
    
  function create_sprite($output)
  {
    // yy is the height of the sprite to be created, basically X * number of images
//    $imgX = $this->y * count($this->files);
    $spritesX = (int)sqrt(count($this->files));
    $spritesY = (int)ceil(count($this->files) / $spritesX);
    $im       = imagecreatetruecolor($spritesX * $this->x, $spritesY * $this->y);
		
    // Add alpha channel to image (transparency)
    imagesavealpha($im, true);
    $alpha = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $alpha);
    
    // Append images to sprite and generate CSS lines
    $i  = $ii = 0;
    echo "Writing " . $output . ".css/png\n";
    
    $fp = fopen($output . '.css', 'w');
//    fwrite($fp, '.' . $this->output . ' { width: ' . $this->x . 'px; height: ' . $this->y . 'px; background-image: url(' . $this->output . '.png); text-align:center; }' . "\n");

    $x = 0;
    $y = 0;
    $files = array();
    foreach ($this->files as $key => $file)
    {
      if($files[$file])
      {
        fwrite($fp, '.' . $key . ' { background-position: -' . $files[$file]->x . 'px -' . $files[$file]->y . 'px; }' . "\n");
        continue;
      }
      fwrite($fp, '.' . $key . ' { background-position: -' . $x . 'px -' . $y . 'px; }' . "\n");
      $files[$file] = new stdClass();
      $files[$file]->x = $x;
      $files[$file]->y = $y;
      $openfunc = Array(
        1 =>"imagecreatefromgif",
        2 =>"imagecreatefromjpeg",
        3 =>"imagecreatefrompng",
      );
      list($ix,$iy,$type,$attr) = getimagesize($file);
      
      $im2 = $openfunc[$type]($file);
      imagecopy($im, $im2, $x, $y, 0, 0, $ix, $iy);
      $x += $this->x;
      $i++;
      if (($i % $spritesX) == 0)
      {
        $y += $this->y;
        $x = 0;
      }
    }
    fclose($fp);
    imagepng($im, $output . '.png'); // Save image to file
    //imagegif($im, $output . '.gif'); // Save image to file
    imagedestroy($im);
  }
}

$files = array();

/*
$f = file("types.old.css");
foreach($f as $v)
{
  if (preg_match("/.([a-zA-Z_0-9]*) { background: url\('http:\/\/www.pouet.net\/(.*)'\); }/",$v,$m))
  {
    $files[ $m[1] ] = $m[2];
    if (strstr($m[1],"type_")!==false)
      copy( POUET_CONTENT_LOCAL . $m[2], POUET_CONTENT_LOCAL . "gfx/types.clean/".strtolower(preg_replace("/[^a-zA-Z0-9]+/","",str_replace("type_","",$m[1]))).".gif" );
  }
}
exit();
*/

function sanitize_type($s)
{
  return str_replace(" ","_",$s);
}

$row = SQLLib::selectRow("DESC prods type");
$types = enum2array($row->Type);
foreach($types as $type)
  $files[ "type_" . sanitize_type($type) ] = "gfx/types/".sanitize_type($type).".gif";

function sanitize_platform($s)
{
  return strtolower(preg_replace("/[^a-zA-Z0-9]+/","",$s));
}

$rows = SQLLib::SelectRows("select * from platforms order by name");
foreach($rows as $row)
  $files[ "os_" . sanitize_platform($row->name) ] = "gfx/os/".$row->icon;

$class = new images_to_sprite();

foreach($files as $k=>$v)
  $class->add_file($k,POUET_CONTENT_LOCAL . $v);
  
$class->create_sprite("types");

?>