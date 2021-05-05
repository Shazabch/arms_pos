<?php
define("TERMINAL",1);
include("../config.php");
require_once("../include/mysql.php");
require_once("../include/functions.php");

$args = $_SERVER['argv'];
array_shift($args); //drop the first option
while($args)
{
	$a = strtolower(array_shift($args));
	if(preg_match("/^-mode=/", $a)){	
		// date
		$mode = trim(str_replace("-mode=", "", $a));
	}else{
		$err[] = "Unknown option $a\n";
	}
}

if($mode != 'all' && $mode !='apply_photo' && $mode != 'actual_photo' && $mode != 'done')	$err[] = "Invalid mode";
if($err){
	print_r($err);exit;
}



class CONVERT_SKU_PHOTO_MODULE {
	var $indicator_filename = "use_new_sku_photo_path.txt";
	
	function start(){
		global $mode;
		
		$this->mode = $mode;
		
		$this->sku_photo_folder = "../sku_photos";
		$this->apply_photo_path = $this->sku_photo_folder."/apply_photo";
		$this->actual_photo_path = $this->sku_photo_folder."/actual_photo";
		
		
		if(!check_and_create_dir($this->sku_photo_folder))	die('sku_photos folder cannot be created');
		if(!check_and_create_dir($this->apply_photo_path))	die('apply_photo folder cannot be created');
		if(!check_and_create_dir($this->actual_photo_path))	die('actual_photo folder cannot be created');
	
		// convert apply photo
		if($mode == 'all' || $mode == 'apply_photo'){
			print "\nCopying SKU Apply Photo...\n";
			$this->convert_apply_photo_path();
			print " Done.\n";
		}
		
		// convert actual photo
		if($mode == 'all' || $mode == 'actual_photo'){
			print "\nCopying SKU Actual Photo...\n";
			$this->convert_actual_photo_path();
			print "Done.\n";
		}
		
		if($mode == 'delete'){
			//$this->delete_old_photo();
		}
		
		if($mode == 'done'){
			file_put_contents($this->sku_photo_folder."/".$this->indicator_filename, "1");
			print "Done.\n";
		}
	}
	
	private function convert_apply_photo_path(){
		$folder_list = glob($this->sku_photo_folder."/*", GLOB_ONLYDIR);
		$total_folder = count($folder_list);
		print "Total $total_folder folder\n";
		$folder_count = 0;
		$count = 0;
		
		foreach($folder_list as $f){
			$folder_name = basename($f); 
			$folder_count++;
			
			print "\r$folder_count / $total_folder.....";
			
			if(is_numeric($folder_name)){
				//print "$folder_name\n";
				
				$sku_apply_items_id = mi($folder_name);
				$group_num = ceil($sku_apply_items_id/10000);
				
				check_and_create_dir($this->apply_photo_path."/".$group_num);
				
				$new_sku_apply_photo_path = $this->apply_photo_path."/".$group_num."/".$sku_apply_items_id;
				check_and_create_dir($new_sku_apply_photo_path);
				
				$image_list = glob($f."/*.jpg");
				if(!$image_list)	continue;	// no photo for this sku

				foreach($image_list as $image){
					$filename = str_replace($f."/", "", $image);
					copy($image, $new_sku_apply_photo_path."/".$filename);
					$count++;
				}
			}
		}
		print "\n$count photo copied.";
	}
	
	private function convert_actual_photo_path(){
		$folder_list = glob($this->sku_photo_folder."/a/*", GLOB_ONLYDIR);
		$total_folder = count($folder_list);
		print "Total $total_folder folder\n";
		$folder_count = 0;
		$count = 0;
		
		foreach($folder_list as $f){
			$folder_name = basename($f); 
			$folder_count++;
			
			print "\r$folder_count / $total_folder.....";
			if(is_numeric($folder_name)){
				//print "$folder_name\n";
				
				$sku_apply_items_id = mi($folder_name);
				$group_num = ceil($sku_apply_items_id/10000);
				
				check_and_create_dir($this->actual_photo_path."/".$group_num);
				
				$new_sku_apply_photo_path = $this->actual_photo_path."/".$group_num."/".$sku_apply_items_id;
				check_and_create_dir($new_sku_apply_photo_path);
				
				$image_list = array_merge(glob("$f/*.jpg"),glob("$f/*.JPG"));
				if(!$image_list)	continue;	// no photo for this sku

				foreach($image_list as $image){
					$filename = str_replace($f."/", "", $image);
					copy($image, $new_sku_apply_photo_path."/".$filename);
					$count++;
				}
			}
		}
		print "\n$count photo copied.";
	}
	
	private function delete_old_photo(){
		if(!file_exists($this->sku_photo_folder."/".$this->indicator_filename)){
			die('photo cannot be delete when still no mark complete.');
		}
	}
}

$CONVERT_SKU_PHOTO_MODULE = new CONVERT_SKU_PHOTO_MODULE();
$CONVERT_SKU_PHOTO_MODULE->start();
?>
