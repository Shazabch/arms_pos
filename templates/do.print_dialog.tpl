{*
3/4/2013 5:39 PM Justin
- Enhanced to have print receipt option while config "do_generate_receipt_no" is turned on.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour".

1/13/2015 4:24 PM Andy
- Change the invoice markup to become readonly.
- Change invoice markup to default zero.

2/25/2015 5:16 PM Justin
- Enhanced to allow user use invoice markup as previously if enable_gst is not turned on.

6/16/2015 5:00 PM Eric
- Hide the invoice markup box when is gst DO

4/11/2017 3:13 PM Justin
- Enhanced to have "Print DO Picking List" option.

2017-09-07 14:07 PM Qiu Ying
- Enhanced to have default DO Size & Color Print Template

9/25/2018 1:52 PM Andy
- Remove DO print receipt feature.

1/31/2019 3:52 PM Andy
- Enhanced Print DO to able to select "Print Item Sequence".

3/12/2019 3:15 PM Andy
- Added "Print DO (Group by same Color)".

12/15/2020 11:53 AM William
- Enhanced to add new print option "SKU Code Column" to print do/invoice.
*}

<script>
var do_type = '{$do_type}';
var do_checkout_cash_sales_alt_print_template = '{$config.do_checkout_cash_sales_alt_print_template}';
var do_checkout_alt_print_template = '{$config.do_checkout_alt_print_template}';
var do_cash_sales_alt_print_template = '{$config.do_cash_sales_alt_print_template}';
var do_alt_print_template = '{$config.do_alt_print_template}';
var do_checkout_invoice_cash_sales_alt_print_template = '{$config.do_checkout_invoice_cash_sales_alt_print_template}';
var do_checkout_invoice_alt_print_template = '{$config.do_checkout_invoice_alt_print_template}';
var replace_docs_arms_code_with_link_code = int('{$config.replace_docs_arms_code_with_link_code}');
var checkout = false;
var do_invoice_separate_number = {if $config.do_invoice_separate_number}true{else}false{/if};


{literal}
var do_inv_no = {};
var do_branch_id_list = {};

var DO_PRINT = {
	initialise: function(){
		
	},
	do_print: function(id, bid, is_checkout, markup, is_under_gst){
		document.f_print.id.value=id;
		document.f_print.branch_id.value=bid;
		this.toggle_print_do_branch_id_div();
		document.f_print['is_draft'].value = 0;
		document.f_print['is_proforma'].value = 0;
			
		if($('main_print_menu') != undefined) $('main_print_menu').show();
		//if($('receipt_no_print_menu') != undefined) $('receipt_no_print_menu').hide();
		if(document.f_print['print_receipt'] != undefined) document.f_print['print_receipt'].checked = false;
		document.f_print['print_do'].checked = true;
		
		if(markup=='draft'||markup=='proforma'){
			//alert(do_branch_id_list[bid+'_'+id]);
			this.toggle_print_do_branch_id_div(do_branch_id_list[bid+'_'+id]);
			if(markup=='draft')	document.f_print['is_draft'].value = 1;
			if(markup=='proforma')	document.f_print['is_proforma'].value = 1;
		}else{
			$('markup').value = markup;
				
			//alert(do_inv_no[bid+'_'+id]);
			// get invoice number
			if(do_invoice_separate_number){
				//alert(do_inv_no[id]);
				document.f_print['selected_inv_no'].readOnly = false;

				if(do_inv_no[bid+'_'+id]){
					document.f_print['selected_inv_no'].value = do_inv_no[bid+'_'+id];
					document.f_print['selected_inv_no'].readOnly = true;
				}else{
					$('span_loading_inv_no').update(_loading_);
					new Ajax.Request('do.php',{
						parameters:{
							a: 'ajax_get_invoice_no',
							id: id,
							branch_id: bid
						},
						onComplete: function(msg){
							eval('var json='+msg.responseText);
							if(json['no_edit']){
								do_inv_no[bid+'_'+id] = json['inv_no'];
								document.f_print['selected_inv_no'].readOnly = true;
							}


							document.f_print['selected_inv_no'].value = json['inv_no'];
							$('span_loading_inv_no').update('');
						}
					});
				}
			}
		}
		
		curtain(true);
			
		if (is_checkout==undefined) is_checkout = true;
		document.f_print.print_invoice.checked=false;
		document.f_print.print_invoice.checked=false;
		document.f_print['acc_copy'].checked=false;
		document.f_print['store_copy'].checked=false;
		document.f_print['size_color_invoice'].checked=false;
		
		this.active_invoice_settings(false);
		document.f_print.print_invoice.disabled=!is_checkout;
		document.f_print.markup.disabled=!is_checkout;
					
		/*if(is_checkout && do_generate_receipt_no != 0){
			$('receipt_no_print_menu').hide();
		}*/
		
		if(is_checkout == true){
			document.f_print.print_pro_invoice.disabled = true;
			document.f_print.print_pro_invoice.checked = false;
		}
		else{
			document.f_print.print_pro_invoice.disabled = false;
			document.f_print.print_pro_invoice.checked = false;
		}

		if(is_under_gst == 1 || markup == 0){
			$('markup_row').style.display = "none";
		}else{
			$('markup_row').style.display = "";		
		}
		
		checkout = is_checkout;
		if(replace_docs_arms_code_with_link_code){
			$('print_col-link_code').checked = true;
		}else{
			$('print_col-sku_item_code').checked = true;
		}
		this.onchange_custom_printing_column();
		this.show_print_dialog();
	},
	toggle_print_do_branch_id_div: function(bid_list){
		$('div_branch_list_fieldset').hide();
		var all_chx = $$('#div_branch_list_fieldset .chx_do_branch_id');
		for(var i=0; i<all_chx.length; i++){
			all_chx[i].disabled = true;
			all_chx[i].checked = false;
			$('span_chx_do_branch_id_'+all_chx[i].value).hide();
		}
		
		if(bid_list){
			if(bid_list.length>1){
				for(var i=0; i<all_chx.length; i++){
					for(var j=0; j<bid_list.length; j++){
						if(bid_list[j]==all_chx[i].value){
							all_chx[i].checked = true;
							all_chx[i].disabled = false;
							$('span_chx_do_branch_id_'+all_chx[i].value).show();
							break;
						}
					}
				}
				$('div_branch_list_fieldset').show();
			}	
		}
	},
	active_invoice_settings: function (action){
		if(action){
			$('div_invoice_setting').style.display='';
		}
		else{
			$('div_invoice_setting').style.display='none';
		}
	},
	show_print_dialog: function(){
		center_div('print_dialog');
		$('print_dialog').style.display = '';
		$('print_dialog').style.zIndex = 10000;
	},
	print_invoice_changed: function(){
		var inp = document.f_print['print_invoice'];
		
		this.active_invoice_settings(inp.checked);
	},
	print_ok: function(){
		// check draft & proforma DO
		if(document.f_print['is_draft'].value==1||document.f_print['is_proforma'].value){
			if($('div_branch_list_fieldset').style.display==''){
				var all_chx = $$('#div_branch_list_fieldset .chx_do_branch_id');
				var available_chx = 0;
				var checked_chx = 0;
				for(var i=0; i<all_chx.length; i++){
					if(!all_chx[i].disabled){
						available_chx++;
						if(all_chx[i].checked)	checked_chx++;
					}
				}
				if(checked_chx<=0){
					alert('Please select at least one branch.');
					return;
				}
			}
			
		}
		
		if(document.f_print.print_invoice.checked || document.f_print.print_do.checked){
			var print_col_checked = 0;
			var print_col = document.querySelectorAll('[name^="print_col"]');
			if(print_col.length > 0){
				for(let i=0; i < print_col.length; i++){
					if(print_col[i].checked == true || print_col[i].disabled == true)  print_col_checked+= 1;
				}
			}
			
			if(print_col_checked <= 0){
				alert('Please select at least one SKU Code column.');
				return;
			}
		}
		
		$('print_dialog').style.display = 'none';
		document.f_print.a.value='print';
		//document.f_print.target = 'ifprint';
		document.f_print.target = '_blank';
		document.f_print.submit();	
		curtain(false);
	},
	onchange_custom_printing_column: function(){
		if(document.f_print.print_invoice.checked || document.f_print.print_do.checked){
			$('div_column_setting').show();	
			var not_available1 = true;
			var not_available2 = true;
			var err = [];
			if(document.f_print.print_do.checked == true){
				if(checkout){
					if((do_type == 'open' && do_checkout_cash_sales_alt_print_template) || do_checkout_alt_print_template){
						not_available1 = true;
					}else{
						not_available1 = false;
					}
				}else{
					if((do_type == 'open' && do_cash_sales_alt_print_template) || do_alt_print_template){
						not_available1 = true;
					}else{
						not_available1 = false;
					}
				}
				if(not_available1)  err.push("Print DO");
			}
			
			if(document.f_print.print_invoice.checked == true){
				if(document.f_print.size_color_invoice.checked){
					not_available2 = true;
				}else{
					if((do_type == 'open' && do_checkout_invoice_cash_sales_alt_print_template) || do_checkout_invoice_alt_print_template){
						not_available2 = true;
					}else{
						not_available2 = false;
					}
				}
				if(not_available2)  err.push("Print Invoice");
			}
			
			if(err.length > 0){
				var err_msg = err.join(" and ");
				$('div_column_setting_msg').innerText = err_msg +" are using own custom printing format, this feature is not available.";
			}else{
				$('div_column_setting_msg').innerText = "";
			}
			
			var print_col = document.querySelectorAll('[name^="print_col"]');
			if(print_col.length > 0){
				for(let i=0; i < print_col.length; i++){
					if(not_available1 && not_available2)  print_col[i].disabled = true;
					else  print_col[i].disabled = false;
				}
			}
		}else{
			$('div_column_setting').hide();	
		}
	}
}
{/literal}
</script>
<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:350px;position:absolute; padding:10px; display:none;">
<form name="f_print" method="get" target="_blank" action="/do.php">
	<input type="hidden" name="a" value="print">
	<input type="hidden" name="branch_id" />
	<input type="hidden" name="id" />
	<input type="hidden" name="is_draft" />
	<input type="hidden" name="is_proforma" /> 

	<div style="position: absolute; top:10px; right:10px;">
		<img src="ui/print64.png" hspace="10" align="left"> 
	</div>
	<div style="float:left;">
		<h3>Print Options</h3>

		<div id="main_print_menu">
			<input type="checkbox" name="print_do" onchange="DO_PRINT.onchange_custom_printing_column();" checked> Print DO<br />
			<div id="div_branch_list_fieldset" style="margin-left:20px;">
				<fieldset>
					<legend>Select Branch:</legend>
					{foreach from=$branch item=b}
						<span id="span_chx_do_branch_id_{$b.id}"><input type="checkbox" class="chx_do_branch_id" value={$b.id} name="print_branch_id[]" />{$b.code}<br /></span>
					{/foreach}
				</fieldset>
			</div>
		
			<input type="checkbox" name="print_do_picking_list" value="1"> Print DO Picking List<br />
			<input type="checkbox" name="print_sz_clr"> Print DO Matrix (Size & Color)<br />
			<input type="checkbox" name="print_group_same_clr"> Print DO (Group by same Color)<br />
			<input type="checkbox" name="print_invoice" onchange="DO_PRINT.print_invoice_changed();DO_PRINT.onchange_custom_printing_column();"> Print Invoice<br>
			<input type="checkbox" name="print_pro_invoice" > Print Proforma Invoice <br/>
			
			{if $config.do_printing_allow_hide_date}
				<input type="checkbox" name="no_show_date" value="1" /> Don't show date <br />
			{/if}

			<div id="div_invoice_setting" style="display:none;">
				<br />
				<fieldset>
					<legend>Invoice Settings</legend>
					<table width="100%">
						<tr id="markup_row" style="display:none;">
							<td><b>Markup</b></td>
							<td><input id="markup" name="markup" maxlength=3 size=3 onchange="mfz(this);" value="{$config.do_invoice_markup}" title="{$config.do_invoice_markup}" onclick="this.select();" readOnly>%</td>
						</tr>
						{if $config.do_invoice_separate_number}
							<tr>
								<td><b>Invoice Number</b></td>
								<td><input name="selected_inv_no" type="text" size="3" /> <span id="span_loading_inv_no"></span></td>
							</tr>
						{/if}
						<tr>
							<td colspan="2">
								<input name="acc_copy" type="checkbox" /><b>Account Copy</b>
								<input name="store_copy" type="checkbox" /><b>Store Copy</b>
							</td>
						</tr>
						<tr>
							<td>
								<input name="size_color_invoice" onchange="DO_PRINT.onchange_custom_printing_column();" type="checkbox" value="1" /><b>Size &amp; Color Format (Group by same Color)</b>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<div id="div_column_setting" style="display:none;">
				<br />
				<fieldset>
					<legend>Select SKU Code Column</legend>
					<table width="100%">
						<tr>
							<td colspan="2" align="center">
								This settings only for Print DO / Invoice
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<ul style="list-style:none;">
									<li> <input type="checkbox" class="cbx_print_col" id="print_col-sku_item_code" name="print_col[sku_item_code]" value="1" {if $config.do_print_col_list.sku_item_code}checked {/if}/> ARMS Code</li>
									<li> <input type="checkbox" class="cbx_print_col" id="print_col-mcode" name="print_col[mcode]" value="1" {if $config.do_print_col_list.mcode}checked {/if}/> MCode</li>
									<li> <input type="checkbox" class="cbx_print_col" id="print_col-artno" name="print_col[artno]" value="1" {if $config.do_print_col_list.artno}checked {/if}/> Art No</li>
									<li> <input type="checkbox" class="cbx_print_col" id="print_col-link_code" name="print_col[link_code]" value="1" {if $config.do_print_col_list.link_code}checked {/if}/> {$config.link_code_name}</li>
								</ul>
								<div id="div_column_setting_msg" style="color:red;"></div>
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
		</div>
		
		<div id="div_common_print_do_setting">
			<br />
			<fieldset>
				<legend>Other Settings</legend>
				<table width="100%">
					<tr>
						<td><b>Print Item Sequence</b></td>
						<td>
							<select name="print_item_sequence">
								<option value=''>Item Added Sequence</option>
								<option value='sku_item_code' {if $config.do_print_item_sequence eq 'sku_item_code'}selected {/if}>ARMS Code</option>
								<option value='mcode' {if $config.do_print_item_sequence eq 'mcode'}selected {/if}>MCode</option>
								<option value='artno' {if $config.do_print_item_sequence eq 'artno'}selected {/if}>Article No</option>
								{if $config.link_code_name}
									<option value='link_code' {if $config.do_print_item_sequence eq 'link_code'}selected {/if}>{$config.link_code_name}</option>
								{/if}
								<option value='description' {if $config.do_print_item_sequence eq 'description'}selected {/if}>SKU Description</option>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
		</div>
		{*
		<div id="receipt_no_print_menu" {if $form.do_type eq 'transfer' || !$form.paid}style="display:none;"{/if}>
			<input type="checkbox" name="print_receipt"> Print Receipt<br />
		</div>
		*}

		<p align="center"><input type="button" value="Print" onclick="DO_PRINT.print_ok()"> <input type="button" value="Cancel" onclick="curtain_clicked()"></p>
	</div>
</form>
</div>
<!--end print dialog-->
