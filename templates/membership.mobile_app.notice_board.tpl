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
	height:180px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var NOTICE_BOARD = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
		// check move arrow
		this.check_update_list_arrow();
		
		UPLOAD_IMAGE_DIALOG.initialise();
		EDIT_VIDEO_DIALOG.initialise();
	},
	// function to get item id
	get_item_id_by_ele: function(ele){
		var tr_item = $(ele).closest("tr.tr_item");
		if(!tr_item)	return 0;
		
		var item_id = tr_item.id.split('-')[1];
		return int(item_id);
	},
	// function when users click on add item
	add_item: function(item_type){
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			a: 'ajax_add_item',
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
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						new Insertion.Bottom($('tbody_item_list'), ret['html']);
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
	// function when user changed image click link
	image_click_link_changed: function(item_id){
		var image_click_link = this.f['item_list['+item_id+'][image_click_link]'].value.trim();
		var params = {
			a: 'ajax_update_image_click_link',
			item_id: item_id,
			image_click_link: image_click_link
		};
		var THIS = this;
		$('span_image_click_link_updating-'+item_id).update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_image_click_link_updating-'+item_id).update('');
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
			}
		});
	},
	// Core function to check if this item can go active
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
	// function when user change item active
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
	// function when user click on remove item
	remove_item: function(item_id){
		if(!confirm('Are you sure?'))	return;
		
		GLOBAL_MODULE.show_wait_popup();
		
		var params = {
			a: 'ajax_remove_item',
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
						$('tr_item-'+item_id).remove();
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
	// function when user click on move up / down
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
	}
}

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
		curtain(true, 'curtain2');
		center_div($('upload_image_popup').show());
	},
	close: function(){
		$('upload_image_popup').hide();
		curtain(false, 'curtain2');
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
		if (oFile.size > 1000000 ) // 1 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 1MB only.");
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

var EDIT_VIDEO_DIALOG = {
	f: undefined,
	initialise: function(){
		this.f = document.f_video;
	},
	open: function(video_site, item_id){
		this.f['video_site'].value = video_site;
		this.f['item_id'].value = item_id;
		this.f['video_link'].value = '';
		
		this.enable_submit_button(true);
		curtain(true, 'curtain2');
		center_div($('edit_video_popup').show());
		this.f['video_link'].focus();
	},
	close: function(){
		$('edit_video_popup').hide();
		curtain(false, 'curtain2');
	},
	// function to enable / disble action button
	enable_submit_button: function(is_enable){
		$$('#edit_video_popup p.p_submit input').each(function(inp){
			inp.disabled = !is_enable;
		});
	},
	// function when user click on button ok
	video_link_ok: function(){
		var video_link = this.f['video_link'].value.trim();
		if(!video_link){
			alert('Please key in Video Link');
			this.f['video_link'].focus();
			return;
		}
		
		var item_id = this.f['item_id'].value;
		var video_site = this.f['video_site'].value;
		
		var params = {
			a: 'ajax_update_video_url',
			item_id: item_id,
			video_site: video_site,
			video_link: video_link,
		};
		var THIS = this;
		
		this.enable_submit_button(false);
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				THIS.enable_submit_button(true);
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['item_url']){ // success
						$('iframe_video-'+item_id).src = ret['item_url'];
						$('inp_item_url-'+item_id).value = ret['item_url'];
						// Update html
						THIS.close();
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
	}
};

function upload_image_done(item_id, filepath){
	UPLOAD_IMAGE_DIALOG.upload_image_done(item_id, filepath);
}

function upload_image_failed(item_id, errmsg){
	UPLOAD_IMAGE_DIALOG.upload_image_failed(item_id, errmsg);
}

{/literal}
</script>

{* popup Image div *}
<div id="upload_image_popup" style="display:none;">
	<form onsubmit="return false;" name="f_upload_image" target="_ifs" enctype="multipart/form-data" method="post">
		<h4>Select an image to add</h4>
		<input type="hidden" name="a" value="add_item_photo" />
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
	<form onsubmit="return false;" name="f_video" tmethod="post">
		<input type="hidden" name="item_id" value="0" /> 
		<input type="hidden" name="video_site" /> 
		<h4>Youtube Video ID</h4>
		https://www.youtube.com/watch?v=<input name="video_link" type="text" style="width:80px;" /><br />
		
		<br />
		<div style="background-color: #FAF8A7;border: 1px solid red;">
			<b>How to know Youtube Video ID:</b><br />
			https://www.youtube.com/watch?v=<span style="background-color: yellow">abcd1234</span><br />
			* Look at the browser URL, the Hightlighted is the Video ID
		</div>
		
		<br>
		<p class="p_submit">
			<input type="button" value="OK" onClick="EDIT_VIDEO_DIALOG.video_link_ok();" />
			<input type="button" value="Cancel" onclick="EDIT_VIDEO_DIALOG.close()">
		</p>
	</form>
</div>

<h1>{$PAGE_TITLE}</h1>
   
<form name="f_a" onSubmit="return false;">
	<table class="report_table" width="100%" id="tbl_item_list">
		<tr class="header">
			<th width="50">&nbsp;</th>
			<th width="50">Active</th>
			<th>Data</th>
		</tr>

		<tbody id="tbody_item_list">
			{foreach from=$item_list item=item name=fitem}
				{include file='membership.mobile_app.notice_board.item.tpl' item_id=$item.id}
			{/foreach}
		</tbody>
	</table>
	
	<p>
		<input type="button" value="Add Image" onClick="NOTICE_BOARD.add_item('image');" />
		<input type="button" value="Add Video" onClick="NOTICE_BOARD.add_item('video');" />
	</p>
</form>

<script>NOTICE_BOARD.initialise();</script>
{include file='footer.tpl'}