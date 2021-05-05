{*
2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
- Add can Print by branch.

3/4/2019 9:58 AM Andy
- Added popup information for print by branch.
*}

<script>

{literal}

var PROMO_PRINT = {
	f: undefined,
	type_mix_and_match: 'mix_and_match',
	promo_bid_list: [],
	selected_print_type: '',
	promo_type: '',
	active: 0,
	status: 0,
	approved: 0,
	initialise: function(){
		this.f = document.f_promo_print;
	},
	// function to show the dialog
	show: function(bid, promo_id, promo_type, print_type, str_promo_bid_list, active, status, approved){
		// Store Branch ID and Promotion ID
		this.f['branch_id'].value = bid;
		this.f['id'].value = promo_id;
		
		// Store Promotion Branch ID
		promo_bid_list = [];
		if(bid == 1 && str_promo_bid_list){
			promo_bid_list = str_promo_bid_list.split(',');
		}
		this.promo_bid_list = promo_bid_list;
		
		// Promotion Type (Discount or Mix and Match)
		if(!promo_type)	promo_type = 'discount';
		this.promo_type = promo_type;
		
		// Print Type
		if(!print_type)	print_type = '';
		this.selected_print_type = print_type;
		
		this.active = int(active);
		this.status = int(status);
		this.approved = int(approved);
		
		// Reset form all field to default
		this.reset();
		
		// Show Popup
		curtain(true);
		center_div($('print_promo_dialog').show());
	},
	// function to close the dialog
	close: function(){
		default_curtain_clicked();
	},
	// function to reset all dialog field to default
	reset: function(){
		this.f.reset();
		
		if(this.promo_type == this.type_mix_and_match){
			this.f.action = 'promotion.mix_n_match.php';
		}else{
			this.f.action = 'promotion.php';
		}
		
		this.print_by_branch_changed();
		
		// Hide all Promotion Branch
		$$('#div_promo_branch_list span.span_chx_promo_branch_id').invoke('hide');
		var THIS = this;
		// Select Print Type
		//if(this.selected_print_type){
			$$('#main_print_menu input.inp_print_type').each(function(inp){
				var print_type = inp.value;
				var allowed = true;
				
				if(print_type == 'mot'){	// Ministry of Trade must at least under approval
					if(THIS.promo_type != 'discount' || THIS.active != 1 || THIS.status != 1){
						allowed = false;
					}
				}
				
				var span_print_type = $('span_print_type-'+print_type);
				if(allowed){
					$(span_print_type).show();
					
					if(THIS.selected_print_type == print_type){
						inp.checked = true;
					}
				}else{
					$(span_print_type).hide();
				}
			});
		//}
		
		// Check whether need to hide print by branch
		var tr_print_by_branch = $('tr_print_by_branch');
		if(this.f['branch_id'].value == 1){
			// Only show the list if there are more than 1 promo branch
			//if(this.promo_bid_list.length > 1){
				for(i=0,len=this.promo_bid_list.length; i<len; i++){
					var promo_bid = this.promo_bid_list[i];
					var span = $('span_chx_promo_branch_id-'+promo_bid);
					if(span){
						$(span).show();
						$('chx_promo_branch_id-'+promo_bid).checked = true;
					}				
				}
				$(tr_print_by_branch).show();
			//}else{
			//	$(tr_print_by_branch).hide();
			//}
		}else{
			$(tr_print_by_branch).hide();
		}
		
		// check whether need to hide other settings
		var active_tr_count = 0;
		$$('#div_common_print_promo_setting tr.tr_print_other_settings').each(function(tr){
			if(tr.style.display == ''){
				active_tr_count++;
			}
		});
		if(active_tr_count>0){
			$('div_common_print_promo_setting').show();
		}else{
			$('div_common_print_promo_setting').hide();
		}
		
		// Show remarks
		var li_promo_print_hide_zero_stock = $('li_promo_print_hide_zero_stock');
		if(li_promo_print_hide_zero_stock){
			if(this.promo_type == 'discount'){
				$(li_promo_print_hide_zero_stock).show();
			}else{
				$(li_promo_print_hide_zero_stock).hide();
			}
		}
	},
	// function when print promo by branch checkbox changed
	print_by_branch_changed: function(){
		this.check_branch_selection();
	},
	// function to check whether to show promo branch list
	check_branch_selection: function(){
		var print_by_branch = this.f['print_by_branch'].checked;
		
		if(print_by_branch && this.f['branch_id'].value == 1){
			$('fs_branch_list').show();
		}else{
			$('fs_branch_list').hide();
		}
		
	},
	// function when user click print
	print_ok: function(){
		document.f_promo_print.submit();
	},
	// function to show unsave remark
	show_unsave_remark: function(){
		$('li_unsave_remark').show();
	}
}
{/literal}
</script>

{* Print Dialog *}
<div id="print_promo_dialog" style="background:#fff;border:3px solid #000;width:350px;position:absolute; padding:10px; display:none;z-index:10000;" class="curtain_popup">
	<form name="f_promo_print" method="get" target="_blank" action="/promotion.php">
		<input type="hidden" name="a" value="do_print">
		<input type="hidden" name="branch_id" />
		<input type="hidden" name="id" />
		<input type="hidden" name="load" value="1" />

		<div style="position: absolute; top:10px; right:10px;">
			<img src="ui/print64.png" hspace="10" align="left"> 
		</div>
		<div style="float:left;width:100%;">
			<h3>Print Options</h3>

			<div id="main_print_menu">
				<span id="span_print_type-default"><input type="radio" name="print_type" class="inp_print_type" value="default" checked /> Print Promotion<br /></span>
				<span id="span_print_type-mot"><input type="radio" name="print_type" class="inp_print_type" value="mot" /> Print Ministry of Trade <img src="ui/my.gif" border="0" title="Print Ministry of Trade" /><br /></span>
			</div>

			<div id="div_common_print_promo_setting">
				<br />
				<fieldset>
					<legend>Other Settings</legend>
					<table width="100%">
						<tr id="tr_print_by_branch" class="tr_print_other_settings">
							<td width="150">
								<b>Print by Branch</b>
								[<a href="javascript:void(alert('Please take note not all the information will change to branch value.\n\nBelow column will change to branch value\n- Stock Balance (Branch latest stock)\n\nBelow column will remain as HQ value\n- Cost\n- Selling'))">?</a>]
							</td>
							<td>
								<input type="checkbox" name="print_by_branch" value="1" onChange="PROMO_PRINT.print_by_branch_changed();" /> Yes
								<fieldset id="fs_branch_list" style="display:none;">
									<legend>Select Branch:</legend>
									<div id="div_promo_branch_list" style="margin-left:20px;max-height:100px;overflow-y:auto;">
										{foreach from=$branch item=b}
											<span id="span_chx_promo_branch_id-{$b.id}" class="span_chx_promo_branch_id"><input type="checkbox" id="chx_promo_branch_id-{$b.id}" value="{$b.id}" name="print_promo_bid[]" />{$b.code}<br /></span>
										{/foreach}
									</div>
								</fieldset>
								
							</td>
						</tr>
					</table>
				</fieldset>
			</div>
			
			<ul>
				{if $config.promo_print_hide_zero_stock and !$config.promo_print_hide_column_stock}
					<li id="li_promo_print_hide_zero_stock">item with zero or negative Stock Balance will not be printed. Document will failed to print if no item have valid stock.</li>					
				{/if}
				<li id="li_unsave_remark" style="display:none;">
					<font color="red">Unsaved changes in Promotion will not be printed</font><br>
				</li>
			</ul>
		
			<p align="center">
				<input type="button" value="Print" onclick="PROMO_PRINT.print_ok()">
				<input type="button" value="Cancel" onclick="PROMO_PRINT.close();">
			</p>
		</div>
		
		
	</form>
</div>