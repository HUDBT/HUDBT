<?php
require_once("include/bittorrent.php");
dbconn();
$action = $_GET['action'];
$imagehash = $_GET['imagehash'];
if($action == "regimage")
{
		$query = "SELECT * FROM regimages WHERE imagehash= ".sqlesc($imagehash);
		$sql = sql_query($query);
		$regimage = _mysql_fetch_array($sql);
		$imagestring = $regimage['imagestring'];
		$newstring = '';
		$space = '';
		for($i=0;$i<strlen($imagestring);$i++)
		{
			$newstring .= $space.$imagestring[$i];
			$space = " ";
		}
		$imagestring = $newstring;
	
	if(function_exists("imagecreatefrompng"))
	{
		$fontwidth = imageFontWidth(5);
		$fontheight = imageFontHeight(5);
		$textwidth = $fontwidth*strlen($imagestring);
		$textheight = $fontheight;
	
		$randimg = rand(1, 5);
		$im = imagecreatefrompng("pic/regimages/reg".$randimg.".png");
	
		$imgheight = 40;
		$imgwidth = 150;
		$textposh = ($imgwidth-$textwidth)/2;
		$textposv = ($imgheight-$textheight)/2;		
		
		$dots = $imgheight*$imgwidth/35;
		$textcolor = imagecolorallocate($im, 0, 0, 0);
		for($i=1;$i<=$dots;$i++) {
		  imagesetpixel($im, rand(0, $imgwidth), rand(0, $imgheight), $textcolor);
		}
		
		imagestring($im, 5, $textposh, $textposv, $imagestring, $textcolor);
	
		// output the image
		header("Content-type: image/png");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		imagepng($im);
		imagedestroy($im);
		exit;
	}
	else
	{
		header("Location: pic/clear.gif");
	}
}
else
{
	die('invalid action');
}
?>
