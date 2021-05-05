{*
5/22/2008 5:39:22 PM  yinsee
change the way pictures are linked

12/9/2010 2:02:50 PM Andy
- Change get sku apply photo method.

12/14/2010 10:59:10 AM Andy
- Temporary remove the "Delete sku apply photo feature".
- Change the way to get sku apply photo to use back the old method.

5/17/2011 12:12:22 PM Andy
- Add checking for sku photo path and change path to show/add the image.

5/23/2011 10:48:53 AM Andy
- Add generate cache for thumb picture.

5/31/2011 10:33:44 AM Andy
- Change sku photo to load from default location instead of cache when view in popup.

11/21/2011 11:12:29 AM Andy
- Change to no use cache for photo preview.

12/19/2013 11:10 AM Andy
- Fix sku photo path if got special character will not able to show in popup.

11/12/2019 9:54 AM William
- Enhanced to display promotion photo.
*}

{*
	{if $items[i].photo_count > 0}
	<!-- display previously uploaded images -->
	<h5>Photo Attachment</h5>
	
	{section name=loop start=1 loop=`$items[i].photo_count+1`}
	{capture assign=p}{$image_path}sku_photos/{$items[i].id}/{$smarty.section.loop.iteration}.jpg{/capture}
	<img width=220 height=200 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=220&h=200&img={$p|urlencode}" border=0 style="cursor:pointer" onclick="popup_div('img_full', '<img width=640 src=\'{$p}\'>')" title="View">
	{/section}
	<div style="clear:both"></div>
	<br>
	
	{/if}
*}
{get_sku_apply_photos sku_apply_items_id=$items[i].id image_path=$image_path assign=images_list}
{if $images_list}
    <!-- display previously uploaded images -->
	<h5>Photo Attachment</h5>
	{foreach from=$images_list item=p name=loop}
	    <img width="220" height="200" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.loop.iteration}" src="thumb.php?w=220&h=200&cache=0&img={$p|urlencode}" border=0 style="cursor:pointer"  onClick="show_sku_image_div('{$p|escape:javascript}');" title="View" />
	{/foreach}
	<div style="clear:both"></div>
	<br>
{/if}

{if $items[i].promotion_photo}
	<h5>Promotion / POS Image</h5>
	<img width="220" height="200" align="absmiddle" vspace="4" hspace="4" alt="" src="thumb.php?w=220&h=200&img={$items[i].promotion_photo|urlencode}" border=0 style="cursor:pointer"  onClick="show_sku_image_div('{$items[i].promotion_photo|escape:javascript}');" title="View" />
	<div style="clear:both"></div>
	<br>
{/if}