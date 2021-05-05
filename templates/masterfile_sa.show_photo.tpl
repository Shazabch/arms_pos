{*
10/22/2019 3:05 PM Andy
- Added Sales Agent Photo.
*}

<form name="f_sa_photo" onSubmit="return false;" method="post" enctype="multipart/form-data" target="if_sa_photo" >
	<input type="hidden" name="a" value="upload_sa_photo" />
	<input type="hidden" name="sa_id" value="{$form.id}" />
	
	<table width="100%">
		<tr>
			<td align="center">
				<img style="border:1px solid black;width:150px;max-height:150px;" src="{$form.photo_url}?t={$smarty.now}" title="Photo" id="img_sales_agent_photo" />
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
				<input type="file" name="sa_photo"/>&nbsp;&nbsp;&nbsp;
				<input type="button" value="Upload" onClick="SALES_AGENT_PHOTO_DIALOG.upload_sa_photo_clicked();" id="btn_submit_sales_agent_photo" /><br />
				<div style="height:20px;">
					<span id="span_loading_sales_agent_photo" style="display:none;background:yellow;padding:2px;">
						<img src="/ui/clock.gif" align="absmiddle" /> Loading...
					</span>
				</div>
			</td>
		</tr>
	</table>
</form>