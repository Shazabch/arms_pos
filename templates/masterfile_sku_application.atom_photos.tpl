{*
1/3/2011 5:21:50 PM Andy
- Add checking for sku application photo size. (need config)

11/8/2011 1:25:26 PM Andy
- Add checking to show local image if browser support FileReader.

11/29/2011 3:55:43 PM Justin
- Added new feature to delete on the spot added photo.

6/3/2014 11:32 AM Justin
- Enhanced to have new ability that can upload images by PDF file.

11/14/2019 4:24 PM William
- Enhanced to add "POS / Promotion" image to add and edit.

2/28/2020 3:24 PM William
- Enhanced to added remark to "Promotion / POS Image".

3/10/2020 9:08 AM William
- Remove SKU application photo can upload pdf word.
*}

<h4>Photo Attachment</h4>
<!-- display image error messages -->
{if $errm.photo}
<div id=err[{$item_n|default:0}]><div class=errmsg><ul>
{foreach from=$errm.photo item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}
<div id="msgAttach[{$item_n|default:0}]"></div>

<!-- display previously uploaded images -->
{if $items[$item_n].saved_photo}
<b>Uploaded Photos</b><br>
{foreach name=i from=$items[$item_n].saved_photo item=p}
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center;" id="saved_photo_{$item_n|default:0}_{$smarty.foreach.i.iteration}">
<img width=50 height=50 align=absmiddle vspace=4 hspace=4 alt="Photo #{$smarty.foreach.i.iteration}" src="thumb.php?w=50&h=50&img={$p|escape:'url'}" border=0 style="cursor:pointer" onclick="window.open('{$p}','','width=800,height=600')" title="View">
<input type=hidden size=50 name="photo_{$item_n|default:0}[]" value="{$p}" readonly><br>
<a href="javascript:void(0)" onclick="window.open('{$p}','','width=800,height=600,scroll=auto')"><img src=/ui/zoomin.png border=0 title="View"></a>
<a href="javascript:void(0)" onclick="$('saved_photo_{$item_n|default:0}_{$smarty.foreach.i.iteration}').innerHTML = ''; $('saved_photo_{$item_n|default:0}_{$smarty.foreach.i.iteration}').style.display = 'none'; "><img src=/ui/del.png border=0 title="Delete"></a>
</div>
{/foreach}
<div style="clear:both"></div>
<br>
{/if}

<ul>
<li>photo uploading is optional for SOFTLINE.</li>
<li>Photo must be a valid JPEG image or JPG file.</li>
<li>All photos must be saved in JPEG format
{if !$config.sku_application_photo_size} and the recommended image resolution is 800x600{/if}.</li>
{if $config.sku_application_photo_size}
	<li>
	    Photo size must be
	    <b>{$config.sku_application_photo_size.width}
	    x
	    {$config.sku_application_photo_size.height}</b>
	    .
	</li>
{/if}
</ul>

<!-- file upload inputs -->
<div style="padding-bottom:2px;">
#1: <input type=file size=50 name="photo_{$item_n|default:0}[]" id="photo1_{$item_n|default:0}" onchange="check_jpg(this,prev1{$item_n|default:0}, 'div_photo_{$item_n|default:0}_2')">
</div>
<div style="padding-bottom:2px; display:none" id="div_photo_{$item_n|default:0}_2">
#2: <input type=file size=50 name="photo_{$item_n|default:0}[]" id="photo2_{$item_n|default:0}" onchange="check_jpg(this,prev2{$item_n|default:0}, 'div_photo_{$item_n|default:0}_3')">
</div>
<div style="padding-bottom:2px; display:none" id="div_photo_{$item_n|default:0}_3">
#3: <input type=file size=50 name="photo_{$item_n|default:0}[]" id="photo3_{$item_n|default:0}" onchange="check_jpg(this,prev3{$item_n|default:0}, 'div_photo_{$item_n|default:0}_4')">
</div>
<div style="padding-bottom:2px; display:none" id="div_photo_{$item_n|default:0}_4">
#4: <input type=file size=50 name="photo_{$item_n|default:0}[]" id="photo4_{$item_n|default:0}" onchange="check_jpg(this,prev4{$item_n|default:0}, 'div_photo_{$item_n|default:0}_5')">
</div>
<div style="padding-bottom:2px; display:none" id="div_photo_{$item_n|default:0}_5">
#5: <input type=file size=50 name="photo_{$item_n|default:0}[]" id="photo5_{$item_n|default:0}" onchange="check_jpg(this,prev5{$item_n|default:0}, '')">
</div>

<!-- preview images -->
<div style="padding-bottom:2px; height:120; display:none;" id="div_uploaded_photo_{$item_n}">
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center; display:none;" id="uploaded_photo1_{$item_n|default:0}">
<img name="prev1{$item_n|default:0}" id="prev1{$item_n|default:0}" width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #1" class=clickable onclick="window.open(this.src,'','width=800,height=600')"><br />
<a id="del_prev1{$item_n|default:0}" href="javascript:void(0)" onclick="$('prev1{$item_n|default:0}').src = ''; $('uploaded_photo1_{$item_n|default:0}').style.display = 'none'; $('del_prev1{$item_n|default:0}').style.display = 'none'; $('photo1_{$item_n|default:0}').value = '';" style="display:none;"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center; display:none;" id="uploaded_photo2_{$item_n|default:0}">
<img name="prev2{$item_n|default:0}" id="prev2{$item_n|default:0}" width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #2" class=clickable onclick="window.open(this.src,'','width=800,height=600')"><br />
<a id="del_prev2{$item_n|default:0}" href="javascript:void(0)" onclick="$('prev2{$item_n|default:0}').src = ''; $('uploaded_photo2_{$item_n|default:0}').style.display = 'none'; $('del_prev2{$item_n|default:0}').style.display = 'none'; $('photo2_{$item_n|default:0}').value = '';" style="display:none;"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center; display:none;" id="uploaded_photo3_{$item_n|default:0}">
<img name="prev3{$item_n|default:0}" id="prev3{$item_n|default:0}" width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #3" class=clickable onclick="window.open(this.src,'','width=800,height=600')"><br />
<a id="del_prev3{$item_n|default:0}" href="javascript:void(0)" onclick="$('prev3{$item_n|default:0}').src = ''; $('uploaded_photo3_{$item_n|default:0}').style.display = 'none'; $('del_prev3{$item_n|default:0}').style.display = 'none'; $('photo3_{$item_n|default:0}').value = '';" style="display:none;"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center; display:none;" id="uploaded_photo4_{$item_n|default:0}">
<img name="prev4{$item_n|default:0}" id="prev4{$item_n|default:0}" width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #4" class=clickable onclick="window.open(this.src,'','width=800,height=600')"><br />
<a id="del_prev4{$item_n|default:0}" href="javascript:void(0)" onclick="$('prev4{$item_n|default:0}').src = ''; $('uploaded_photo4_{$item_n|default:0}').style.display = 'none'; $('del_prev4{$item_n|default:0}').style.display = 'none'; $('photo4_{$item_n|default:0}').value = '';" style="display:none;"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center; display:none;" id="uploaded_photo5_{$item_n|default:0}">
<img name="prev5{$item_n|default:0}" id="prev5{$item_n|default:0}" width=100 height=100 align=absmiddle vspace=4 hspace=4 alt="Photo #5" class=clickable onclick="window.open(this.src,'','width=800,height=600')"><br />
<a id="del_prev5{$item_n|default:0}" href="javascript:void(0)" onclick="$('prev5{$item_n|default:0}').src = ''; $('uploaded_photo5_{$item_n|default:0}').style.display = 'none'; $('del_prev5{$item_n|default:0}').style.display = 'none'; $('photo5_{$item_n|default:0}').value = '';" style="display:none;"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
</div>
<p>* if the preview image are not displayed correctly, please contact MIS *</p>

<h4>Promotion / POS Image</h4>
{if $sessioninfo.branch_id eq 1 && $BRANCH_CODE eq 'HQ'}
<ul>
	<li>Photo must be a valid JPEG image or JPG file.</li>
</ul>
<div style="float:left; background:#fff; border:1px solid #000; margin:2px;text-align:center;{if !$items[$item_n].promotion_photo}display:none;{/if}" id="promotion_div_{$item_n}">
	<img name="promotion_img_{$item_n}" id="promotion_img_{$item_n}"  width="50" height="50" align="absmiddle" vspace="4" hspace="4" alt="Promotion / POS Image" style="cursor:pointer;border=0;" src="thumb.php?w=50&h=50&img={$items[$item_n].promotion_photo|escape:'url'}"  onclick="window.open('{$items[$item_n].promotion_photo}','','width=800,height=600')" title="View">
	<input type="hidden" id="saved_promotion_photo_{$item_n|default:0}" name="saved_promotion_photo_{$item_n|default:0}" value="{if $items[$item_n].promotion_photo}{$items[$item_n].promotion_photo}{/if}">
	<br>
	<a id="promo_view_{$item_n}" href="javascript:void(0)" onclick="window.open('{$items[$item_n].promotion_photo}','','width=800,height=600,scroll=auto');"><img src=/ui/zoomin.png border=0 title="View"></a>
	<a href="javascript:void(0)" onclick="delete_promotion_img({$item_n})"><img src=/ui/del.png border=0 title="Delete"></a>
</div>
<div style="clear:both"></div>
<div style="padding-bottom:2px;">
	<input type="file" size="50" name="promotion_photo_{$item_n|default:0}" id="promotion_photo_{$item_n|default:0}" onclick="check_promo_img_exist(this,{$item_n})" onchange="check_promotion_img(this,{$item_n})">
</div>
{else}
<p>* The pos image only can apply on HQ branch*</p>
{/if}