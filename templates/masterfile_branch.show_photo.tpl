{*
11/22/2019 5:27 PM William
- Add new branch outlet photo to branch.
*}

<form name="f_outlet_photo" onSubmit="return false;" method="post" enctype="multipart/form-data" target="if_outlet_photo" >
	<input type="hidden" name="a" value="upload_outlet_photo" />
	<input type="hidden" name="branch_id" value="{$form.branch_id}" />
	
	<table width="100%">
		<tr>
			<td align="center">
				<div>
					<img style="border:1px solid black;width:150px;max-height:150px;" src="{$form.outlet_photo_url}?t={$smarty.now}" title="Photo" id="img_branch_outlet_photo" />
					<br>
					<span id="span_del_outlet_photo" {if !$form.outlet_photo_url}style="display:none;"{/if} ><img onclick="OUTLET_PHOTO_DIALOG.delete_outlet_photo({$form.branch_id})" src="/ui/del.png" align="absmiddle" /> Delete</span>
				</div>
			</td>
		</tr>
		
		<tr>
			<td style="color:#0000ff;">
				<b>Note:</b>
				<ul>
					<li> Please ensure the file is a valid image file (JPG/JPEG/PNG/GIF).</li>
					<li> Uploaded image will replace existing one.</li>
					<li> Image File Size is limited to a maximum of 1MB only.</li>
				</ul>
			</td>
		</tr>
		
		<tr>
			<td>
				<b>Please Select Image: </b>
				&nbsp;&nbsp;&nbsp;
				<input type="file" name="outlet_photo" style="width:240;" />&nbsp;&nbsp;&nbsp;
				<input type="button" value="Upload" onClick="OUTLET_PHOTO_DIALOG.upload_outlet_photo();" id="btn_submit_branch_outlet_photo" /><br />
				<div style="height:20px;">
					<span id="span_loading_branch_outlet_photo" style="display:none;background:yellow;padding:2px;">
						<img src="/ui/clock.gif" align="absmiddle" /> Loading...
					</span>
				</div>
			</td>
		</tr>
	</table>
</form>