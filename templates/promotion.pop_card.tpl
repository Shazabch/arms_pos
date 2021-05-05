{*
12/18/2020 4:32 PM William
- Bug fixed promotion price not using non-member-price when config "membership_module" inactive.
*}
{include file=header.tpl}
<script>
var membership_module = int('{$config.membership_module}');
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var PROMO_POP_CARD = {
	f: undefined,
	initialise: function(){
		this.f = document.f;
	},
	upload_promo_pop_card: function(){
		if (document.f['promo_pop_photo'].value == '') {
			alert('Please select a file to upload.');
			return false;
		}else if (!/\.jpg|\.jpeg/i.test(document.f['promo_pop_photo'].value)){
			alert("Selected file must be a valid JPG/JPEG image.");
			return false;
		}
		
		var oFile = document.f['promo_pop_photo'].files[0];
		if (oFile.size > 1048576){ // 5 mb for bytes.
			alert("Image File Size is limited to a maximum of 1 MB only.");
			return false;
		}
		
		if(!confirm('Are you sure?')) return false;
		
		document.f['btn_upload'].disabled = true;
		this.set_photo_uploading(true);
		
		this.f['a'].value = 'upload_promo_pop_card_photo';
		this.f.target = "if_upload"; 
		this.f.submit();
	},
	set_photo_uploading: function(is_uploading){
		if(is_uploading){
			$('btn_upload').disabled = true;
			$('span_loading_promo_pop_photo').show();
		}else{
			$('btn_upload').disabled = false;
			$('span_loading_promo_pop_photo').hide();
		}
	},
	// callback function when upload failed
	pop_promo_card_uploaded_failed: function(){
		this.set_photo_uploading(false);
	},
	// callback function after banner uploaded
	pop_promo_card_uploaded: function(filepath){
		$('promo_pop_photo').src = filepath;
		this.f['promo_pop_photo'].value ='';
		this.set_photo_uploading(false);
	},
	change_radio_promo_img: function(){
		var background_image_setting = this.f['background_image_setting'];
		for(var i=0;i<background_image_setting.length;i++){
			if(background_image_setting[i].checked == true){
				var value_setting = background_image_setting[i].value;
				if(value_setting == 'use_system_img'){
					$('div_promo_img').show();
					$('div_promo_upload').hide();
				}else if(value_setting == 'use_upload_img'){
					$('div_promo_upload').show();
					$('div_promo_img').hide();
				}else if(value_setting == 'no_backgroud_img'){
					$('div_promo_img').hide();
					$('div_promo_upload').hide();
				}
			}
		}
	},
	change_promo_pop_cards_bg: function(){
		var promo_pop_cards_bg = this.f['promo_pop_cards_bg'];
		var file_path = promo_pop_cards_bg.options[promo_pop_cards_bg.selectedIndex].value;
		$('img_promo_pop_cards_bg').src = file_path;
	},
	print_promo_pop_card: function(){
		var promo_items = [];
		var promo_items_list = document.querySelectorAll('[id^="promo_items_list-"]');
		
		var background_image_setting = this.f['background_image_setting'];
		for(var i=0;i<background_image_setting.length;i++){
			if(background_image_setting[i].checked == true){
				var value_setting = background_image_setting[i].value;
				if(value_setting == 'use_upload_img' && $('promo_pop_photo').getAttribute('src') == ""){
					alert('Please upload photo.');
					return false;
				}
			}
		}
		
		if(typeof(promo_items_list) !='undefined'){
			for(var i=0;i<promo_items_list.length;i++){
				if(promo_items_list[i].checked == true){
					promo_items.push(promo_items_list[i].value);
				}
			}
		}
		
		if(promo_items.length  <= 0){
			alert("Please select the promotion item.");
			return false;
		}
		
		if(membership_module){
			var member_discount = this.f['member_discount'];
			var invalid_non_member_discount = [];
			var invalid_member_discount = [];
			
			for(var i=0;i < promo_items.length;i++){
				var item_id = promo_items[i];
				var member_disc_p = $('member_disc_p['+item_id+']');
				var non_member_disc_p = $('non_member_disc_p['+item_id+']');
				var member_disc_a = $('member_disc_a['+item_id+']');
				var non_member_disc_a = $('non_member_disc_a['+item_id+']');
				
				//select member type all or non member
				if(member_discount.value == '' || member_discount.value == 'non_member'){
					if(non_member_disc_p.value == 0 && non_member_disc_a.value == 0){
						invalid_non_member_discount.push($('sku_item_code['+item_id+']').value);
					}
				}
				
				//select member type all or member
				if(member_discount.value == '' || member_discount.value == 'member'){
					if(member_disc_p.value == 0 && non_member_disc_p.value == 0 && member_disc_a.value == 0 && non_member_disc_a.value == 0){
						invalid_member_discount.push($('sku_item_code['+item_id+']').value);
					}
				}
			}
			
			if(invalid_non_member_discount.length > 0){
				invalid_non_member_discount = invalid_non_member_discount.join(', ');
				alert("The selected SKU("+invalid_non_member_discount+") doesn't have non-member discount.");
				return false;
			}
			
			if(invalid_member_discount.length > 0){
				invalid_member_discount = invalid_member_discount.join(', ');
				alert("The selected SKU("+invalid_member_discount+") doesn't have member discount.");
				return false;
			}
		}
		
		this.f['a'].value = 'print_promo_pop_card';
		var dataform = this.f.serialize()
		window.open(phpself+'?&'+dataform);
	},
	check_all_promo_item: function(obj){
		var promo_items_list = document.querySelectorAll('[id^="promo_items_list-"]');
		
		if(typeof(promo_items_list) !='undefined'){
			for(var i=0;i<promo_items_list.length;i++){
				if(obj.checked == true){
					promo_items_list[i].checked = true;
				}else{
					promo_items_list[i].checked = false;
				}
			}
		}
	}
}
{/literal}
</script>
	<h1>Promotion Pop Card</h1>
	<form name="f" onSubmit="return false;" enctype="multipart/form-data" method="post">
		<input type="hidden" name="a"/>
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		
		<div class="stdframe" style="background:#fff" id="div_settings">
			<h3>Settings</h3>
			<table style="width: 100%;">
				<tr>
					<td valign="top">
						<table>
							<tr>
								<td><b>Title</b></td>
								<td>{$form.title}</td>
							</tr>
							<tr>
								<td><b>Printing Format</b></td>
								<td>
									<select name="printing_format">
										<option {if $form.printing_format eq 'format1'}selected{/if} value="format1">Format 1</option>
										<option {if $form.printing_format eq 'format2'}selected{/if} value="format2">Format 2</option>
									</select>
								</td>
							</tr>
							<tr>
								<td valign="top"><b>Background Image</b></td>
								<td>
									<label><input name="background_image_setting" {if $form.background_image_setting eq 'use_system_img' || $form.background_image_setting eq ''}checked{/if} value="use_system_img" onchange="PROMO_POP_CARD.change_radio_promo_img()" type="radio" /> Use System Default Image </label><br/>
									<label><input name="background_image_setting" {if $form.background_image_setting eq 'use_upload_img'}checked{/if} value="use_upload_img" onchange="PROMO_POP_CARD.change_radio_promo_img()" type="radio" /> Upload Image</label><br/>
									<label><input name="background_image_setting" {if $form.background_image_setting eq 'no_backgroud_img'}checked{/if} value="no_backgroud_img" onchange="PROMO_POP_CARD.change_radio_promo_img()" type="radio" /> No Background Image</label><br/>
								</td>
							</tr>
							
							{if $config.membership_module}
							<tr>
								<td><b>Member Discount</b></td>
								<td>
									<select name="member_discount">
										<option {if $form.member_discount eq ''}selected{/if} value="">All</option>
										<option {if $form.member_discount eq 'member'}selected{/if} value="member">Member</option>
										<option {if $form.member_discount eq 'non_member'}selected{/if} value="non_member">Non-Member</option>
									</select>
								</td>
							</tr>
							{else}
							<tr>
								<input type="hidden" name="member_discount" value="non_member" />
								<td><b>Discount</b></td>
								<td>Non-Member</td>
							</tr>
							{/if}
							
							{if $BRANCH_CODE eq 'HQ'}
							<tr>
								<td><b>Branch Selling Price</b></td>
								<td>
									<select name="selling_price_branch_id">
									{foreach from=$form.promo_branch_id key=bid item=r}
										<option value="{$bid}" {if $form.selling_price_branch_id eq $bid}selected{/if}>{$r}</option>
									{/foreach}
									</select>
								</td>
							</tr>
							{else}
								<input name="selling_price_branch_id" type="hidden" value="{$sessioninfo.branch_id}" />
							{/if}
							
							<tr>
								<td><b>Card Per Page</b></td>
								<td>
									<select name="card_per_page">
										<option {if $form.card_per_page eq '1'}selected{/if} value="1">1 (portrait)</option>
										<option {if $form.card_per_page eq '2'}selected{/if} value="2">2 (landscape)</option>
									</select>
								</td>
							</tr>
						</table>
					</td>
					<td align="right">
						{* system image *}
						<div id="div_promo_img">
							<table>
								<tr>
									<td><img style="border:2px solid black;width: 230px;height: 320px;" title="" id="img_promo_pop_cards_bg" /></td>
								</tr>
								<tr>
									<td>
									<b>Please Select Image</b>
									<select name="promo_pop_cards_bg" onchange="PROMO_POP_CARD.change_promo_pop_cards_bg()">
										{foreach from=$form.promo_pop_cards_bg key=file_name item=r}
											<option {if $form.promo_pop_cards_bg_path eq $r}selected{/if} value="{$r}">{$file_name}</option>
										{/foreach}
									</select>
									</td>
								</tr>
							</table>
						</div>
						
						{* upload image *}
						<div id="div_promo_upload">
							<table>
								<tr>
									<td><img style="border:2px solid black;width: 230px;height: 320px;" src="{$form.promo_pop_photo}" id="promo_pop_photo" /></td>
								</tr>
								<tr>
									<td>
										<b>Note:</b>
										<ul>
											<li> Please ensure the file is a valid image file (JPG/JPEG).</li>
											<li> Uploaded image will replace existing one.</li>
											<li> Image File Size is limited to a maximum of 1 MB only.</li>
										</ul>
									</td>
								</tr>
								<tr>
									<td><input type="file" name="promo_pop_photo"/></td>
								</tr>
								<tr>
									<td>
										<input type="button" value="Upload" onClick="PROMO_POP_CARD.upload_promo_pop_card();" id="btn_upload" />
										<span id="span_loading_promo_pop_photo" style="display:none;background:yellow;padding:2px;">
											<img src="/ui/clock.gif" align="absmiddle" /> Loading...
										</span>
									</td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
			<div style="text-align: center">
				<input type="button" value="Save & Print" onClick="PROMO_POP_CARD.print_promo_pop_card();" />
			</div>
		</div>
		<br/>
		<div class="stdframe" style="background:#fff" id="div_items">
			<h3>Item List</h3>
			
			<table id="report_table" class="report_table" width="100%">
				<tr class="header">
					<th width="30" rowspan="2">&nbsp;</th>
					<th width="100" rowspan="2"><input title="Select All Item" onClick="PROMO_POP_CARD.check_all_promo_item(this)" type="checkbox" /></th>
					<th width="100" rowspan="2">Promo Photo</th>
					<th width="120" rowspan="2">ARMS Code</th>
					<th width="120" rowspan="2">MCode</th>
					<th width="120" rowspan="2">Art No</th>
					<th width="120" rowspan="2">{$config.link_code_name}</th>
					<th rowspan="2">Description</th>
					{if $config.membership_module}
						<th colspan="2">Member</th>
					{/if}
					<th colspan="2">Non Member</th>
				</tr>
				<tr class="header">
					{if $config.membership_module}
					<th>Discount</th>
					<th>Price</th>
					{/if}
					<th>Discount</th>
					<th>Price</th>
				</tr>
				{foreach from=$promo_items item=r name=fp}
					{assign var=item_id value=$r.id}
					{assign var=sku_item_code value=$r.sku_item_code}
					<tr>
						<input type="hidden" id="sku_item_code[{$item_id}]" value="{$r.sku_item_code}" />
						{if $config.membership_module}
						<input type="hidden" id="member_disc_p[{$item_id}]" value="{$r.member_disc_p}" />
						<input type="hidden" id="member_disc_a[{$item_id}]" value="{$r.member_disc_a}" />
						{/if}
						<input type="hidden" id="non_member_disc_p[{$item_id}]" value="{$r.non_member_disc_p}" />
						<input type="hidden" id="non_member_disc_a[{$item_id}]" value="{$r.non_member_disc_a}" />
						<td>{$smarty.foreach.fp.iteration}.</td>
						<td align="center">
							<input type="checkbox" id="promo_items_list-{$item_id}" name="promo_items[{$item_id}]" value="{$item_id}" {if $item_id|in_array:$form.promo_items}checked{/if} />
						</td>
						<td align="center">
							{if $r.promo_photo_url}
								<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Promo Photo" src="/thumb.php?w=110&h=100&cache=1&img={$r.promo_photo_url|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$r.promo_photo_url|escape:javascript}');" title="View" />
							{else}
								-
							{/if}
						</td>
						<td align="center">{$r.sku_item_code}</td>
						<td align="center">{$r.mcode}</td>
						<td align="center">{$r.artno}</td>
						<td align="center">{$r.link_code}</td>
						<td>{$r.description}</td>
						{if $config.membership_module}
							{* Member *}
							<td>{$r.member_disc_p|ifzero:'&nbsp;'}</td>
							<td>{$r.member_disc_a|ifzero:'&nbsp;'}</td>
						{/if}
						{* Non Member *}
						<td>{$r.non_member_disc_p|ifzero:'&nbsp;'}</td>
						<td>{$r.non_member_disc_a|ifzero:'&nbsp;'}</td>
					</tr>
				{/foreach}
			</table>
		</div>
		<br/>
		<div <div class="stdframe" style="text-align: center;background:#fff">
			<input type="button" value="Save & Print" onClick="PROMO_POP_CARD.print_promo_pop_card();" />
		</div>
	</form>
	<iframe name="if_upload" style="width:1px;height:1px;visibility:hidden;"></iframe>
<script>
PROMO_POP_CARD.initialise();
PROMO_POP_CARD.change_radio_promo_img();
PROMO_POP_CARD.change_promo_pop_cards_bg();
</script>
{include file=footer.tpl}
