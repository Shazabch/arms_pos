{include file='header.tpl'}

{literal}
<style>

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var BANNER_EDIT = {
	initialise: function(){
	},
	// function when users click on upload banner
	upload_banner: function(banner_num){
		if(!this.validate_file(banner_num)){
			return false;
		}
		
		if(!confirm('Are you sure?'))	return false;
		
		var f_banner = document['f_banner-'+banner_num];
		
		this.set_banner_uploading(banner_num, true);
		
		f_banner.submit();
	},
	// set banner uploading is true or false
	set_banner_uploading: function(banner_num, is_uploading){
		if(is_uploading){
			$('btn_submit_banner-'+banner_num).disabled = true;
			$('span_loading_banner-'+banner_num).show();
		}else{
			$('btn_submit_banner-'+banner_num).disabled = false;
			$('span_loading_banner-'+banner_num).hide();
		}
	},
	// Core function to validate filename
	validate_file: function(banner_num){
		if(!banner_num){
			alert('Invalid Banner Num');
			return false;
		}
		
		// Get input object
		var f_banner = document['f_banner-'+banner_num];
		var inp_file = f_banner['banner'];
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
	// callback function when upload failed
	banner_uploaded_failed: function(banner_num){
		this.set_banner_uploading(banner_num, false);
	},
	// callback function when upload success
	banner_uploaded: function(banner_num, filepath){
		var img_banner = $('img_banner-'+banner_num);
		var img_banner_option = $('img_banner_option-'+banner_num);
		
		$(img_banner).src = filepath;
		$(img_banner_option).show();
		this.set_banner_uploading(banner_num, false);
	},
	// function when user click on remove banner
	remove_banner: function(banner_num){
		if(!confirm('Are you sure?'))	return false;
		
		GLOBAL_MODULE.show_wait_popup();
		
		var f_banner = document['f_banner-'+banner_num];
		
		var params = $(f_banner).serialize()+'&a=ajax_delete_banner';
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
						$('img_banner_option-'+banner_num).hide();
						$('img_banner-'+banner_num).src = '';
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
	edit_banner_link: function(banner_num){
		var f_banner = document['f_banner-'+banner_num];
		var inp_banner_link = f_banner['banner_link'];
		var curr_link = inp_banner_link.value.trim();
		var v = prompt('Please key in the link', curr_link);
		if(v == undefined){
			return;
		}
		
		v = v.trim();
		if(v == ''){
			if(!confirm('Are you sure to remove link?'))	return false;
		}
		
		inp_banner_link.value = v;
		
		var params = $(f_banner).serialize()+'&a=ajax_update_banner_link';
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
						alert('Link Updated.');
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
{/literal}
</script>

<h1>Banner Name: {$banner.banner_description}</h1>

<div class="stdframe" style="background:#fff" id="div_banner">
	<table width="100%">
		<tr valign="top">
			<td>
				<h3>Size: ({$banner.banner_width}x{$banner.banner_height})</h3>
				<h4>Current:</h4>
							
				{section loop=$banner.max_photo_count name=s_banner_num}
					{assign var=i value=$smarty.section.s_banner_num.iteration}
					{* Banner *}
					<form name="f_banner-{$i}" onSubmit="return false;" method="post" enctype="multipart/form-data" action="{$smarty.server.PHP_SELF}" target="if_banner" >
						<input type="hidden" name="a" value="upload_banner_photo" />
						<input type="hidden" name="banner_num" value="{$i}" />
						<input type="hidden" name="banner_name" value="{$banner.banner_name}" />
						
						<div>
							{* Banner Image *}
							<div id="div_banner_photo_list">
								<div class="div_banner_photo">
									Photo #{$i}<br />
									<table>
										<tr>
											<td>
												{assign var=img_path value=$banner.banner_info.banner_list.$i.path}
												<img style="border:2px solid black;width:{$banner.banner_width}px;height:{$banner.banner_height}px;" src="{$img_path}" title="Banner #{$i}" id="img_banner-{$i}" />
											</td>
											
											<td>
												<span id="img_banner_option-{$i}" style="{if !$img_path}display:none;{/if}">
													<input type="hidden" name="banner_link" value="{$banner.banner_info.banner_list.$i.link}" />
													<img src="ui/icons/picture_link.png" title="Edit Link" onClick="BANNER_EDIT.edit_banner_link('{$i}');" />
													<img src="ui/del.png" title="Remove Photo" class="clickable" onClick="BANNER_EDIT.remove_banner('{$i}');" />
												</span>
											</td>
										</tr>
									</table>
									
									<p>
										<b>Please Select Image: </b>
										&nbsp;&nbsp;&nbsp;
										<input type="file" name="banner"/>&nbsp;&nbsp;&nbsp;
										<input type="button" value="Upload" onClick="BANNER_EDIT.upload_banner('{$i}');" id="btn_submit_banner-{$i}" />
										<span id="span_loading_banner-{$i}" style="display:none;background:yellow;padding:2px;">
											<img src="/ui/clock.gif" align="absmiddle" /> Loading...
										</span>
									</p>
								</div><br />
							</div>
						</div>
					</form>
				{/section}
				<iframe name="if_banner" style="width:1px;height:1px;visibility:hidden;"></iframe>
				
				<table>
					<tr>
						<td style="color:#0000ff;">
							<b>Note:</b>
							<ul>
								<li> Please ensure the file is a valid image file (JPG/JPEG/PNG/GIF).</li>
								<li> Uploaded banner will replace existing one.</li>
								<li> Image File Size is limited to a maximum of 1MB only.</li>
							</ul>
						</td>
					</tr>
				</table>
			</td>
			
			<td align="right">
				{* wireframe_url *}
				<img src="{$banner.wireframe_url}" style="max-height:500px;margin-right:20px;" />
			</td>
		</tr>
	</table>
	
	
	
</div>

<script>BANNER_EDIT.initialise();</script>
{include file='footer.tpl'}