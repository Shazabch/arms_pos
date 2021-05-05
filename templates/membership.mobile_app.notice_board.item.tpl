<tr class="tr_item" id="tr_item-{$item_id}">
	<td nowrap>
		<input type="hidden" name="item_list[{$item_id}][id]" value="{$item_id}" />
		<input type="hidden" name="item_list[{$item_id}][item_type]" value="{$item.item_type}" />
		<input type="hidden" name="item_list[{$item_id}][item_url]" value="{$item.item_url}" id="inp_item_url-{$item_id}" />
	
		{* Delete *}
		<img src="ui/del.png" title="Delete" class="clickable" onClick="NOTICE_BOARD.remove_item('{$item_id}');" />
		
		{* Up *}
		<img src="/ui/icons/arrow_up.png" title="Move Up" class="clickable img_move_up" onClick="NOTICE_BOARD.move_item('{$item_id}', 'up');" />
		
		{* Down *}
		<img src="/ui/icons/arrow_down.png" title="Move Down" class="clickable img_move_down" onClick="NOTICE_BOARD.move_item('{$item_id}', 'down');" />
	</td>
	
	<td align="center">
		<input type="checkbox" name="item_list[{$item_id}][active]" {if $item.active}checked{/if} title="Active" onChange="NOTICE_BOARD.item_active_changed('{$item_id}');" />
		<img src="ui/clock.gif" id="img_active_updating-{$item_id}" style="display:none;" />
	</td>
	
	{* Data *}
	<td>
		{if $item.item_type eq 'image'}
			{* Image *}
			<div>				
				<b>Image: </b><br />
				<img style="border:2px solid black; min-width:{$image_size.min_width}px; max-width:{$image_size.max_width}px; min-height:{$image_size.min_height}px; max-height:{$image_size.max_height}px;" src="{$item.item_url}" id="img_image-{$item_id}" />		
				
				
				<p>
					<b>Click Link [<a href="javascript:void(alert('When users click on the image will redirect them to this url'))">?</a>]: </b>
					<input name="item_list[{$item_id}][image_click_link]" value="{$item.image_click_link}" type="text" style="width:300px;" onChange="NOTICE_BOARD.image_click_link_changed('{$item_id}');" />
					<span id="span_image_click_link_updating-{$item_id}"></span>
				</p>
				
				<br />
				<div style="color: blue;">
					<b>Note:</b>
					<ul>
						<li> Please ensure the file is a valid image file (JPG/JPEG/PNG/GIF).</li>
						<li> Uploaded image will replace existing one.</li>
						<li> Image File Size is limited to a maximum of 1MB only.</li>
					</ul>
				</div>
					
				<p>
					<input type="button" value="Select Image to Upload" onClick="UPLOAD_IMAGE_DIALOG.open('{$item_id}');" />
				</p>
			</div>
		{else}
			{* Video *}
			<div>
				<iframe id="iframe_video-{$item_id}" src="{$item.item_url}" width="560" height="315"></iframe>
				
				<br />
				<div style="color: blue;">
					<b>Note:</b>
					<ul>
						<li> Please ensure the link is a valid video url.</li>
					</ul>
				</div>
				
				<p>
					<input type="button" value="Edit Youtube Link" onClick="EDIT_VIDEO_DIALOG.open('youtube', '{$item_id}');" />
				</p>
			</div>
		{/if}
	</td>
</tr>