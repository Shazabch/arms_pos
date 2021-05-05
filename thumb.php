<?php
/*
5/23/2011 6:13:08 PM Andy
- Fix load image from remote URL spacing problem.

6/1/2011 11:53:02 AM Andy
- Fix load image from remote URL spacing problem. default no need to urlencode, if failed then urlencode and change "+"
- Add thumb can accept crop, fit and pad. default will show full image based on user passed height and width. 

11/23/2015 10:20 AM Qiu Ying
- Accept JPEG file

6/17/2016 10:43 AM Andy
- Enhanced to check whether thumbs folder is writable, and show error image if got problem.

7/11/2017 4:00 PM Andy
- Enhanced create image from image type to check all the type.

11/5/2018 3:20 PM Andy
- Fixed if cache=0 will have ImageJPEG error.
*/
ini_set("display_errors", 0);
ini_set("memory_limit", "256M");
header('Content-type: image/jpeg');

$imgname = $_REQUEST["img"];
// check last modified and return 304 if no change
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($imgname))) {
  header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
  exit;
}
header('Last-Modified: '.date('r', filemtime($imgname)));

$crop = isset($_REQUEST['crop']);
$fit = isset($_REQUEST['fit']);
$cache = intval($_REQUEST["cache"]);

if ($cache)
{
	$hash = md5($imgname) . "_" . intval($_REQUEST["w"]) . "x" . intval($_REQUEST["h"]) . ($crop?"_crop":"")  . ($fit?"_fit":"")  . ".jpg";
	if (!is_dir("thumbs"))
	{
		mkdir("thumbs");
		chmod(0777, "thumbs");
	}
	if(!is_writable("thumbs")){
		//die("Thumbs folder permission error.");
		readfile("ui/loaderr.gif");exit;
	}

	if (!file_exists("thumbs/$hash") || filemtime("thumbs/$hash") < filemtime("$imgname"))
	{
		generate_thumb($hash);
	}
	readfile("thumbs/$hash");
}
else
	generate_thumb();

function generate_thumb($hash = '')
{
	global $imgname, $crop, $fit;

	$w = $_REQUEST["w"];
	$c = $_REQUEST["c"];	// cache?
	$h = $_REQUEST["h"];
	$bd = $_REQUEST["bd"];
	$bdc = $_REQUEST["bdc"];
	$bgc = $_REQUEST["bg"];
	$q = $_REQUEST["q"];
	$pad = !($_REQUEST["pad"]==='0');
	
	if (!$w) $w = 100;
	if (!$h) $h = $w;
	if (!$c) $c = 0;
	if (!$bdc) $bdc = '000000';
	if (!$bd) $bd = 0;
	if (!$bgc) $bgc = 'ffffff';
	if (!$q) $q = 80;

	$saveimg = 1;

	
	
	$im = create_image_handler($imgname);	// create image from multiple function
	
	if(!$im){	// if cannot access, try fix the spacing and reconnect
		$oldimgname = $imgname;
		// fix spacing problem, convert "+" to "%2520"
		$imgname = urlencode($imgname);
		$imgname = str_replace("+", "%2520", $imgname);
		$imgname = urldecode($imgname);
		
		if($oldimgname != $imgname){	// only try if path got changed
			$im = create_image_handler($imgname);	// create image from multiple function
		}
	}
	
	if (!$im)
	{
		$saveimg = 0;
		$im = @imagecreatefromjpeg("errload.jpg");
	}

	if ($im)
	{
		/*$thumbnail = ImageCreatetruecolor($w, $h);

		$bgc = h2color($thumbnail, $bgc);
		imagefilledrectangle ($thumbnail, 0, 0, $w, $h, $bgc);

		$iw = imagesx($im);
		$ih = imagesy($im);

		if ($iw < $ih)
		{
			$sw = $w;
			$sh = $w / $iw * $ih; // w * ($ih / $iw);
		}
		else
		{
			$sh = $h;
			$sw = $h / $ih * $iw; //$h * ($iw / $ih);
		}
		imagecopyresampled ($thumbnail, $im, ($w - $sw) / 2, ($h - $sh) / 2, 0, 0, $sw, $sh, $iw, $ih);*/
		
		$iw = imagesx($im);
		$ih = imagesy($im);
		$sw = $iw; $sh = $ih;
		
		if (!$crop)
		{
			// resize to fit whole image in
			if (isset($_REQUEST['w']) && $w>0 && $iw > $w)
			{
				$sw = $w;
				$sh = $w / $iw * $ih;
			}

			if (isset($_REQUEST['h']) && $h>0 && $sh > $h)
			{
				$sw = $h / $sh * $sw;
				$sh = $h;
			}
		}
		else
		{
			if ($iw < $ih || ($iw==$ih && $w>$h))
			{
				$sw = $w;
				$sh = $w / $iw * $ih; 
			}
			else
			{
				$sh = $h;
				$sw = $h / $ih * $iw;
			}
		}

		if ($fit)
		{
			$h = $sh; $w = $sw;
		}
		elseif (!$pad) { 
			if (!isset($_REQUEST['h'])) $h = $sh; 
			if (!isset($_REQUEST['w'])) $w = $sw; 	
		}
        $thumbnail = ImageCreatetruecolor($w, $h);

    	$bgc = h2color($thumbnail, $bgc);
        imagefilledrectangle ($thumbnail, 0, 0, $w, $h, $bgc);
		imagecopyresampled ($thumbnail, $im, ($w - $sw) / 2, ($h - $sh) / 2, 0, 0, $sw, $sh, $iw, $ih);
	}
	else
		$saveimg = 0;


	if ($bd)
	{
		$bdc = h2color($thumbnail, $bdc);
		imagerectangle($thumbnail, 0, 0, $w-1, $h-1, $bdc);
	}

	if ($hash && $saveimg)
		ImageJPEG($thumbnail, "thumbs/$hash", $q);
	else
		ImageJPEG($thumbnail, null, $q);
}

function h2color($img, $s)
{
	$r = hexdec(substr($s,0,2));
	$g = hexdec(substr($s,2,2));
	$b = hexdec(substr($s,4,2));
	return imagecolorallocate($img, $r, $g, $b);
}

function create_image_handler($imgname){
	$tested_type = array();
	$all_image_type = array('jpg', 'png', 'gif');
	
	if (preg_match("/\.jpg$/i", $imgname) || preg_match("/\.jpeg$/i", $imgname)){
		$im = @imagecreatefromjpeg($imgname);
		$tested_type['jpg'] = 1;
	} 
	elseif (preg_match("/\.png$/i", $imgname)){
		$im = @imagecreatefrompng($imgname);
		$tested_type['png'] = 1;
	} 
	elseif (preg_match("/\.gif$/i", $imgname)){
		$im = @imagecreatefromgif($imgname);
		$tested_type['gif'] = 1;
	}
	
	// failed to generate image
	if(!$im){
		foreach($all_image_type as $img_type){
			if($tested_type[$img_type])	continue;	// this image type tested
			
			switch($img_type){
				case 'png':
					$im = @imagecreatefrompng($imgname);
					break;
				case 'gif':
					$im = @imagecreatefromgif($imgname);
					break;
				default:
					$im = @imagecreatefromjpeg($imgname);
					break;
			}
			$tested_type[$img_type] = 1;
			if($im)	return $im;
		}
	}
	
	return $im;
}
?>
