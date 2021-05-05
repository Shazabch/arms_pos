{include file='header.tpl'}

{literal}
<style>
#upload_image_popup {
	border:2px solid #000;
	background:#fff;
	width:300px;
	height:120px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}

#edit_video_popup {
	border:2px solid #000;
	background:#fff;
	width:300px;
	height:120px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var banner_name = '{$banner.banner_name}';
var maximun_item_qty = int('{$banner_info.maximun_qty}');
var total_item = int('{$item_list|@count}');
{literal}
var BANNER_EDIT = {
	initialise: function(){
		this.f = document.f_a;
		
		// check move arrow
		this.check_update_list_arrow();
		
		UPLOAD_IMAGE_DIALOG.initialise();
		EDIT_VIDEO_DIALOG.initialise();
	},
	check_can_active: function(item_id){
		var item_type = this.f['item_list['+item_id+'][item_type]'].value;
		var errmsg = '';
		if($('inp_item_url-'+item_id).value == ''){
			if(item_type == 'image'){
				errmsg = 'Please upload an image.';
			}else{
				errmsg = 'Please set a video link.';
			}
			alert(errmsg);
			return false;
		}
		
		return true;
	},
	item_active_changed: function(item_id){
		var inp = this.f['item_list['+item_id+'][active]'];
		var is_active = inp.checked;
		
		if(is_active){
			// check can active or not
			if(!this.check_can_active(item_id)){
				inp.checked = false;
				return;
			}
		}
		
		var img_active_updating = $('img_active_updating-'+item_id);
		
		$(inp).hide();
		$(img_active_updating).show();
		
		var params = {
			a: 'ajax_update_active',
			banner_name: banner_name,
			item_id: item_id,
			is_active: is_active ? 1 : 0
		};
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$(inp).show();
				$(img_active_updating).hide();
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
				inp.checked = !is_active;
			}
		});
	},
	remove_item: function(item_id){
		if(!confirm('Are you sure?'))	return;
		
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			a: 'ajax_remove_item',
			banner_name: banner_name,
			item_id: item_id
		};
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						total_item -= 1;
						$('tr_item-'+item_id).remove();
						THIS.check_update_list_arrow();
						THIS.check_maximun_item();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click on edit banner link
	edit_banner_link: function(item_id){
		var image_click_link = this.f['item_list['+item_id+'][image_click_link]'].value.trim();
		var v = prompt('Please key in the link', image_click_link);
		if(v == undefined){
			return;
		}
		
		v = v.trim();
		if(v == ''){
			if(!confirm('Are you sure to remove link?'))	return false;
		}
		
		var params = {
			a: 'ajax_update_banner_item_link',
			banner_name: banner_name,
			item_id: item_id,
			image_click_link: v
		};
		var THIS = this;
		GLOBAL_MODULE.show_wait_popup();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){		
				GLOBAL_MODULE.hide_wait_popup();
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				//$('span_image_click_link_updating-'+item_id).update('');
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						$('image_click_url-'+item_id).value = v;
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// 
	add_item: function(banner_name, item_type){
		var params = {
			a: 'ajax_add_item',
			banner_name: banner_name,
			item_type: item_type
			
		};
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						new Insertion.Bottom($('tbody_item_list'), ret['html']);
						total_item += 1;
						THIS.check_update_list_arrow();
						THIS.check_maximun_item();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function to check arrow key
	check_update_list_arrow: function(){
		var tr_item_list = $$('#tbody_item_list tr.tr_item');
		
		for(var i=0,len=tr_item_list.length; i<len; i++){
			var tr_item = tr_item_list[i];
			
			var img_move_up = $(tr_item).getElementsBySelector('img.img_move_up')[0];
			var img_move_down = $(tr_item).getElementsBySelector('img.img_move_down')[0];
			
			$(img_move_up).style.visibility = i == 0 ? 'hidden' : '';
			$(img_move_down).style.visibility = i == len-1 ? 'hidden' : '';
		}
	},
	move_item: function(item_id, direction){
		var curr_tr_item = $('tr_item-'+item_id);
		var swap_tr_item;
		if(direction == 'up'){
			swap_tr_item = $(curr_tr_item).previousElementSibling;
		}else{
			swap_tr_item = $(curr_tr_item).nextElementSibling ;
		}
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			a: 'ajax_move_item',
			banner_name: banner_name,
			item_id: item_id,
			direction: direction
		};
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update html
						swap_ele(curr_tr_item, swap_tr_item);
						THIS.check_update_list_arrow();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	check_maximun_item: function(){
		var button_list = $$('.button_list');
		if(button_list.length > 0){
			for(let i=0; i < button_list.length; i++){
				if(total_item >= maximun_item_qty && maximun_item_qty > 0){
					button_list[i].hide();
				}else{
					button_list[i].show();
				}
			}
		}
	}
}

//upload video function
var EDIT_VIDEO_DIALOG = {
	f: undefined,
	initialise: function(){
		this.f = document.f_video;
	},
	open: function(item_id){
		this.f['item_id'].value = item_id;
		
		this.enable_submit_button(true);
		curtain(true);
		center_div($('edit_video_popup').show());
	},
	close: function(){
		$('edit_video_popup').hide();
		curtain(false);
	},
	// function to enable / disble action button
	enable_submit_button: function(is_enable){
		$$('#edit_video_popup p.p_submit input').each(function(inp){
			inp.disabled = !is_enable;
		});
	},
	//
	validate_file: function(){
		var inp_file = this.f['video_file'];
		if(!inp_file){
			alert('File Input Not Found.');
			return false;
		}
		
		var filename = inp_file.value.trim();
		
		// Check File Extension
		if (filename == '') {
			alert('Please select a file to upload');
			return false;
		}else if (!/\.mp4|\.avi|\.webm|\.ogv/i.test(filename)){
			alert("Selected file must be a valid MP4/AVI/WEBM/OGV video");
			return false;
		}
		
		// File Size
		var oFile = inp_file.files[0];
		if (oFile.size > 30000000 ) // 30 mb for bytes.
		{
			alert("Video File Size is limited to a maximum of 30MB only.");
			return false;
		}
		
		return true;
	},
	// function when user click on upload button
	upload_clicked: function(){
		// Check File
		if(!this.validate_file()){
			return false;
		}
		
		this.enable_submit_button(false);
		
		// Upload
		this.f.submit();
	},
	// function when upload success
	upload_video_done: function(item_id, filepath){
		// Display Image Path
		$('video-'+item_id).src = filepath;
		this.f['video_file'].value = '';
		this.close();
	},
	// function when upload failed
	upload_video_failed: function(errmsg){
		alert(errmsg);
		this.enable_submit_button(true);
	}
};

//upload image function
var UPLOAD_IMAGE_DIALOG = {
	f: undefined,
	initialise: function(){
		this.f = document.f_upload_image;
	},
	open: function(item_id){
		this.f['item_id'].value = item_id;
		// reset the selected file path
		this.f['image_file'].outerHTML = this.f['image_file'].outerHTML;
		this.enable_submit_button(true);
		curtain(true);
		center_div($('upload_image_popup').show());
	},
	close: function(){
		$('upload_image_popup').hide();
		curtain(false);
	},
	// Core function to validate filename
	validate_file: function(){
		
		var inp_file = this.f['image_file'];
		if(!inp_file){
			alert('File Input Not Found.');
			return false;
		}
		
		var filename = inp_file.value.trim();
		
		// Check File Extension
		if (filename == '') {
			alert('Please select a file to upload');
			return false;
		}else if (!/\.jpg|\.jpeg|\.png|\.gif/i.test(filename)){
			alert("Selected file must be a valid JPG/JPEG/PNG/GIF image");
			return false;
		}
		
		// File Size
		var oFile = inp_file.files[0];
		if (oFile.size > 5000000 ) // 5 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 5MB only.");
			return false;
		}
		
		return true;
	},
	// function to enable / disble action button
	enable_submit_button: function(is_enable){
		$$('#upload_image_popup p.p_submit input').each(function(inp){
			inp.disabled = !is_enable;
		});
	},
	// function when user click on upload button
	upload_clicked: function(){
		// Check File
		if(!this.validate_file()){
			return false;
		}
		
		this.enable_submit_button(false);
		
		// Upload
		this.f.submit();
	},
	// function when upload success
	upload_image_done: function(item_id, filepath){
		// Display Image Path
		$('img_image-'+item_id).src = filepath;
		
		$('inp_item_url-'+item_id).value = filepath;
		
		this.close();
	},
	// function when upload failed
	upload_image_failed: function(item_id, errmsg){
		alert(errmsg);
		this.enable_submit_button(true);
	}
};

function default_curtain_clicked(){
	EDIT_VIDEO_DIALOG.close();
	UPLOAD_IMAGE_DIALOG.close();
}
{/literal}
</script>

{* popup Image div *}
<div id="upload_image_popup" style="display:none;">
	<form onsubmit="return false;" name="f_upload_image" target="_ifs" enctype="multipart/form-data" method="post">
		<h4>Select an image to add</h4>
		<input type="hidden" name="a" value="add_item_photo" />
		<input type="hidden" name="banner_name" value="{$banner.banner_name}" /> 
		<input type="hidden" name="item_id" value="0" /> 
		<input name="image_file" type="file" />
		<br>
		<p class="p_submit">
			<input type="button" value="Upload" onClick="UPLOAD_IMAGE_DIALOG.upload_clicked();" />
			<input type="button" value="Cancel" onclick="UPLOAD_IMAGE_DIALOG.close()">
		</p>
	</form>
	<iframe name="_ifs" width="1" height="1" style="visibility:hidden"></iframe>
</div>

{* popup Video div *}
<div id="edit_video_popup" style="display:none;">
	<form onsubmit="return false;" name="f_video" target="_ifs2" enctype="multipart/form-data" method="post">
		<h4>Select a video to add</h4>
		<input type="hidden" name="a" value="add_item_video" />
		<input type="hidden" name="banner_name" value="{$banner.banner_name}" /> 
		<input type="hidden" name="item_id" value="0" /> 
		<input name="video_file" type="file" />
		<br>
		<p class="p_submit">
			<input type="button" value="Upload" onClick="EDIT_VIDEO_DIALOG.upload_clicked();" />
			<input type="button" value="Cancel" onclick="EDIT_VIDEO_DIALOG.close()">
		</p>
	</form>
	<iframe name="_ifs2" width="1" height="1" style="visibility:hidden"></iframe>
</div>

<h1>Banner Name: {$banner.banner_description}  ({$banner.banner_height} X {$banner.banner_width})</h1>
<form name="f_a" onSubmit="return false;">
	<p class="button_list" style="display:{if !$banner_info.maximun_qty || ($banner_info.maximun_qty && $item_list|@count < $banner_info.maximun_qty)}block{else}none{/if}">
		{if $banner_info.allow_upload_image}<input type="button" value="Add Image" onClick="BANNER_EDIT.add_item('{$banner.banner_name}', 'image');" />{/if}
		{if $banner_info.allow_upload_video}<input type="button" value="Add Video" onClick="BANNER_EDIT.add_item('{$banner.banner_name}', 'video');" />{/if}
	</p>
	
	<table class="report_table" width="100%" id="tbl_item_list">
		<tr class="header">
			<th width="50">&nbsp;</th>
			<th width="50">Active</th>
			<th>Data</th>
		</tr>

		<tbody id="tbody_item_list">
			{foreach from=$item_list item=item name=fitem}
				{include file='suite.pos_device_management.item.tpl' item_id=$item.id}
			{/foreach}
		</tbody>
	</table>
	
	<p class="button_list" style="display:{if !$banner_info.maximun_qty || ($banner_info.maximun_qty && $item_list|@count < $banner_info.maximun_qty)}block{else}none{/if}">
		{if $banner_info.allow_upload_image}<input type="button" value="Add Image" onClick="BANNER_EDIT.add_item('{$banner.banner_name}', 'image');" />{/if}
		{if $banner_info.allow_upload_video}<input type="button" value="Add Video" onClick="BANNER_EDIT.add_item('{$banner.banner_name}', 'video');" />{/if}
	</p>
	
</form>

<script>BANNER_EDIT.initialise();</script>
{include file='footer.tpl'}