<span id="{$container_id}" sid="{$photo_info.sid}" class="parent_container">

	{if $photo_info.photo_list or ($photo_info.url_to_get_photo && !$config.single_server_mode)}
		<div class="photo_icon" id="div_photo_icon-{$container_id}">
			<img src="/ui/icons/images.png" border="0" alt="Detail" id="img_default-{$container_id}" onClick="SHARED_SKU_PHOTO.show_img_full('{$container_id}', this);" />
			
			<div class="photo_hide">
				{* Apply Photo and Actual Photo which already know path *}
				{if $photo_info.photo_list}
					{foreach from=$photo_info.photo_list item=p name=loop}
						<img width="100" height="100" align="absmiddle" vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=100&h=100&cache=1&img={$p|urlencode}" border=1 style="cursor:pointer" onClick="SHARED_SKU_PHOTO.show_img_full('{$container_id}', this);" title="View" class="sku_photo" img_url="{$p|escape}" />
					{/foreach}
				{/if}

				{* Actual Photo at HQ when using multi server mode *}
				{if $photo_info.url_to_get_photo && !$config.single_server_mode}
					<span class="span_loading_actual_photo" id="span_loading_actual_photo-{$container_id}" url_to_get_photo="{$photo_info.url_to_get_photo}">
						<img src="/ui/clock.gif" align="absmiddle" />
						Loading Photo...
					</span>
				{/if}
			</div>
		</div>
	{/if}
</span>

<script>
	SHARED_SKU_PHOTO.initialise('{$container_id}', '{$show_as_first_image}');
</script>