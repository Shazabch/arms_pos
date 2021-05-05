<?php
/*
3/12/2020 2:40 PM Justin
- Newly added functions that used by Mobile Suite app.

4/27/2020 6:16 PM Justin
- Bug fixed on loadGRRDoc function having issue on verifying IBT DO.
*/
class grrManager{
	// public var
	
	
	// private var
	
	function __construct(){
		global $smarty, $con, $appCore;

	
	}
	
	function recalculateGRRAmount($id, $branch_id){
		global $con, $config;
	    
        $q1 = $con->sql_query("select sum(ctn) as total_ctn, sum(pcs) as total_pcs, sum(amount) as total_amt, sum(gst_amount) as total_gst_amt, sum(tax) as total_tax_amt
							   from grr_items
							   where branch_id=".mi($branch_id)." and grr_id=".mi($id));
		$gi_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		$upd = array();
		$upd['grr_ctn'] = $gi_info['total_ctn'];
		$upd['grr_pcs'] = $gi_info['total_pcs'];
		$upd['grr_amount'] = $gi_info['total_amt'];
		$upd['grr_gst_amount'] = $gi_info['total_gst_amt'];
		$upd['grr_tax'] = $gi_info['total_tax_amt'];
		
		$con->sql_query("update grr set ".mysql_update_by_field($upd)." where branch_id = ".mi($branch_id)." and id = ".mi($id));
	}
	
	function searchPONo($original_docno, &$ret, $bid){
		global $con, $config;

		if (preg_match("/^([A-Z]+)(\d+)\(PP\)$/", $original_docno, $matches)){
			$pp_report_prefix=$matches[1];
			$pp_po_id=$matches[2];
			
			if($pp_report_prefix=='HQ'){
				$q1=$con->sql_query("select po_no from po where hq_po_id = ".mi($pp_po_id)." and po_branch_id = ".mi($bid));
				$r1 = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);				
			}else{
				$q1=$con->sql_query("select id from branch where report_prefix = ".ms($pp_report_prefix));
				$b = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				$pp_branch_id=$b['id'];
				unset($b);
				
				$q1=$con->sql_query("select po_no from po where branch_id = ".mi($pp_branch_id)." and id = ".mi($pp_po_id));
				$r1 = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			}		

			if($r1){
				$reset_doc_no=$r1['po_no'];
				unset($r1);
			}
			
		}	
		$q1 = $con->sql_query("select id as po_id, active, vendor_id, branch_id, po_branch_id, partial_delivery, delivered, department_id, cancel_date, po_no from po where approved = 1 and po_no = ".ms($reset_doc_no));		
		$ret = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		return $reset_doc_no;
	}
	
	public function setGRRImage($file_obj, $prms=array()){
		global $con, $appCore, $LANG;
		
		// check the image if it was valid image
		$result = $appCore->isValidUploadImageFile($file_obj);
		if(!$result['ok'])	return $result;
		
		$ext = trim($result['ext']);
		if(!$ext) return array('error_code' => "invalid_file_extension", "error" => 'Invalid File Extension');
		
		// construct the image path
		$img_path = "attch/grr/".$prms['branch_id']."/".$prms['grr_id'];
		$img_name = $prms['branch_id']."_".$prms['grr_id'];
		if($prms['is_grr_item_image'] && $prms['grr_item_id']) $img_name .= "_".$prms['grr_item_id'];
		$grr_image_url = $img_path."/".$img_name.".".$ext;
		
		// Already have similar image
		if(file_exists($grr_image_url)){
			// Rename extension
			$grr_image_url = preg_replace("/\.(jpg|jpeg|png|gif)$/i", ".".$ext, $grr_image_url);
		}else{ // store as new image
			// Generate New URL
			$folder_1 = $prms['branch_id'];
			$folder_2 = $prms['grr_id'];
			
			//$directory = dirname(__FILE__)."/../attch"
			$img_path = "attch/grr";
			if(!check_and_create_dir($img_path)){
				return array('error_code' => "failed_to_create_folder", 'error' => "Create Image Folder Failed");
			}
			
			$img_path .= "/".$folder_1;
			if(!check_and_create_dir($img_path)){
				return array('error_code' => "failed_to_create_folder", 'error' => "Create Image Folder Failed");
			}
			
			$img_path .= "/".$folder_2;
			if(!check_and_create_dir($img_path)){
				return array('error_code' => "failed_to_create_folder", 'error' => "Create Image Folder Failed");
			}
			
			unset($folder_1, $folder_2);
		}
		
		// Move Uploaded File
		if(!move_uploaded_file($file_obj['tmp_name'], $grr_image_url)){
			return array('error_code' => "failed_to_move_file", 'error' => "Failed to Move Uploaded File.");
		}
		
		// loop the extension list to seek for similar file name but having different extension
		$ext_list = array("jpg", "jpeg", "png", "gif");
		foreach($ext_list as $tmp_ext){
			$tmp_grr_image_url = $img_path."/".$img_name.".".$tmp_ext;
			// Delete old image that are using different extension but having similar name
			if(file_exists($tmp_grr_image_url) && $tmp_grr_image_url != $grr_image_url){
				unlink($tmp_grr_image_url);
			}
		}
		
		$ret = array();
		$ret['ok'] = 1;
		$ret['image_url'] = $grr_image_url;
		$ret['image_name'] = $img_name.".".$ext;
		return $ret;
	}
	
	public function loadGRRImage($prms=array()){
		if(!$prms['grr_id'] || !$prms['branch_id']) return;
		
		// build the image directory
		$ret = array();
		$img_path = "attch/grr/".$prms['branch_id']."/".$prms['grr_id'];
		$file_name = $prms['branch_id']."_".$prms['grr_id'];
		if($prms['grr_item_id']) $file_name .= "_".$prms['grr_item_id'];
		if(file_exists($img_path)){ // if found the path was existed
			// grab those files with specified extension only
			$have_images = glob($img_path."/*.{jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF}", GLOB_BRACE);
			
			if(!empty($have_images)){
				$img_files = scandir($img_path);
				$ext_list = array();
				
				// load all the extensions of the images available from the folder
				foreach($img_files as $file){
					if($file != "." && $file != ".."){
						$tmp_img_path = $img_path."/".$file;
						if(!is_dir($tmp_img_path)){ // do this if it is not a directory
							if(preg_match("/\.(jpg|jpeg|png|gif)$/i", $tmp_img_path, $ext)){
								$ext_list[$ext[1]] = $ext[1];
							}
							unset($ext);
						}
						unset($tmp_img_path);
					}
				}
				
				if($ext_list){
					// loop the extension list to see if the file was existed base on the combination of file name + extension
					foreach($ext_list as $tmp_ext){
						$actual_img_path = $img_path."/".$file_name.".".$tmp_ext; // combine directory + file name + extension
						if(file_exists($actual_img_path)){
							$ret['ok'] = 1;
							$ret['image_url'] = $actual_img_path;
							break;
						}
					}
				}
				
				unset($img_files, $ext_list);
			}
		}
		unset($img_path, $file_name);
		
		return $ret;
	}
	
	public function loadGRRDoc($prms=array()){
		global $con, $config;
		
		if(!$prms['grr_id'] || !$prms['branch_id']) return;
		
		// get the doc no and type from grr_items
		$q1 = $con->sql_query("select doc_no, type, branch_id from grr_items where branch_id = ".mi($prms['branch_id'])." and grr_id = ".mi($prms['grr_id'])." order by type");
		
		$is_ibt_do = false;
		$ret = $doc_list = array();
		while($r = $con->sql_fetchassoc($q1)){
			if($r['type'] == "DO" && $config['do_skip_generate_grn']){ // check if the DO was from IBT
				$filter = "do_branch_id = ".mi($r['branch_id'])." and do_type = 'transfer'";
				$q2 = $con->sql_query("select id from do where do_no = ".ms($r['doc_no'])." and ".$filter);
				if($con->sql_numrows($q2) > 0){  // means it is IBT DO
					$is_ibt_do = true;
				}
				$con->sql_freeresult($q2);
			}
			
			$doc_list[$r['type']][] = $r['doc_no'];
		}
		$con->sql_freeresult($q1);
		
		if($is_ibt_do && count($doc_list['DO']) > 0){ // it is IBT DO
			$ret['doc_type'] = "DO";
			$ret['is_ibt_do'] = true;
		}elseif(count($doc_list['PO']) > 0){ // it is PO
			$ret['doc_type'] = "PO";
		}elseif(count($doc_list['INVOICE']) > 0){ // it is INVOICE
			$ret['doc_type'] = "INVOICE";
		}elseif(count($doc_list['DO']) > 0){ // it is external DO
			$ret['doc_type'] = "DO";
		}else{ // it is OTHER
			$ret['doc_type'] = "OTHER";
		}
		
		// create the list of document no
		if($doc_list[$ret['doc_type']]) $ret['doc_no'] = join(", ", $doc_list[$ret['doc_type']]);
		unset($is_ibt_do, $doc_list);
		
		return $ret;
	}
}
?>
