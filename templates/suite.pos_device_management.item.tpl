<tr class="tr_item" id="tr_item-{$item_id}">
	<td nowrap>
		<input type="hidden" name="item_list[{$item_id}][id]" value="{$item_id}" />
		<input type="hidden" name="item_list[{$item_id}][item_type]" value="{$item.item_type}" />
		<input type="hidden" name="item_list[{$item_id}][item_url]" value="{$item.item_url}" id="inp_item_url-{$item_id}" />
		<input type="hidden" name="item_list[{$item_id}][image_click_link]" value="{$item.image_click_link}" id="image_click_url-{$item_id}" />
	
		{* Delete *}
		<img src="ui/del.png" title="Delete" class="clickable" onClick="BANNER_EDIT.remove_item('{$item_id}');" />
		
		{* Up *}
		<img src="/ui/icons/arrow_up.png" title="Move Up" class="clickable img_move_up" onClick="BANNER_EDIT.move_item('{$item_id}', 'up');" />
		
		{* Down *}
		<img src="/ui/icons/arrow_down.png" title="Move Down" class="clickable img_move_down" onClick="BANNER_EDIT.move_item('{$item_id}', 'down');" />
		
		{* Edit Link *}
		<img src="ui/icons/picture_link.png" title="Edit Link" onClick="BANNER_EDIT.edit_banner_link('{$item_id}');" />
	</td>
	
	<td align="center">
		<input type="checkbox" name="item_list[{$item_id}][active]" {if $item.active}checked{/if} title="Active" onChange="BANNER_EDIT.item_active_changed('{$item_id}');" />
		<img src="ui/clock.gif" id="img_active_updating-{$item_id}" style="display:none;" />
	</td>
	
	{* Data *}
	<td>
		{if $item.item_type eq 'image'}
			{* Image *}
			<div>
				<img style="border:2px solid black;width: 180px;height: 230px;" src="{$item.item_url}?t={$item.str_last_update}" title="Banner #{$item_id}" id="img_image-{$item_id}" />
				<br /><br />
				
				<div style="color: blue;">
					<b>Note:</b>
					<ul>
						<li> Please ensure the file is a valid image file (JPG/JPEG/PNG/GIF).</li>
						<li> Uploaded image will replace existing one.</li>
						<li> Image File Size is limited to a maximum of 5MB only.</li>
					</ul>
				</div>
				<input type="button" value="Select Image to Upload" onClick="UPLOAD_IMAGE_DIALOG.open('{$item_id}');" />
			</div>
		{else}
			{* Video *}
			<div>
				<video style="border:2px solid black" id="video-{$item_id}" src="{$item.item_url}?t={$item.str_last_update}" width="250" height="180" controls></video>
				<br /><br />
		
				<div style="color: blue;">
					<b>Note:</b>
					<ul>
						<li> Please ensure the video is a valid video (MP4/AVI/WEBM/OGV).</li>
						<li> Uploaded video will replace existing one.</li>
						<li> Image File Size is limited to a maximum of 30MB only.</li>
					</ul>
				</div>
				
				<input type="button" value="Select Video to Upload" onClick="EDIT_VIDEO_DIALOG.open('{$item_id}');" />
			</div>
		{/if}
	</td>
</tr>