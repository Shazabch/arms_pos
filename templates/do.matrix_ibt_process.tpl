{include file='header.tpl'}

<style>
{literal}
.row_color1{
	background-color: #FEFBA7;
}
.row_stock_min{
	background-color: #FEA7F3;
}
.highligth_reorder{
	border: 3px solid red !important;
	background-color: #FFB9B9;
}
.highligth_available{
	border: 3px solid green !important;
	background-color: #00FF1B;
}
.inp_branch_transfer_qty{
	width: 100%;
	text-align: right;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var MATRIX_IBT = {
	f: undefined,
	f2: undefined,
	close_curtain2_btn: "<input type='button' value='Close' onClick='close_curtain2();' />",
	initialize: function(){
		this.f = document.f_a;
		
		// init sku autocomplete
		reset_sku_autocomplete(1);
		
		if(document.f2)	this.init_f2();
	},
	show_report: function(){
		if(!this.check_form())	return false;
		
		this.f.submit();
	},
	check_form: function(){
		if(!this.f){
			alert('Form is not yet initialise');
			return false;
		}
		
		if(!this.f['to_branch_id'].value){
			alert('Please Select Deliver Branch.');
			return false;
		}
		
		if(!this.f['sku_item_id'].value){
			alert('Please search and select SKU.');
			return false;
		}
		
		return true;
	},
	init_f2: function(){
		this.f2 = document.f2;
		
		new Draggable('div_generate_do_dialog',{ handle: 'div_generate_do_dialog_header'});
	},
	branch_transfer_qty_changed: function(bid, size_num){
		// convert to integer
		mi(this.f2['branch_transfer_qty['+size_num+']['+bid+']']);
		
		// sum all qty from same size
		var transfer_qty = 0;
		$(this.f2).getElementsBySelector("input.branch_transfer_qty-"+size_num).each(function(inp){
			transfer_qty += int(inp.value);
		});
		var require_qty = int(this.f2['require_qty['+size_num+']'].value);
		
		// update to main
		this.f2['transfer_qty['+size_num+']'].value = transfer_qty;
		if(transfer_qty > 0){
			$('span-transfer_qty-'+size_num).update(transfer_qty);
			$('transfer_qty-'+size_num).addClassName('highligth_available');
		}else{
			$('span-transfer_qty-'+size_num).update('&nbsp;');
			$('transfer_qty-'+size_num).removeClassName('highligth_available');
		}
		
		// update require qty
		var remain_qty = require_qty - transfer_qty;
		if(remain_qty <= 0){
			remain_qty = 0;
			$('require_qty-'+size_num).removeClassName('highligth_reorder');
		}else{
			$('require_qty-'+size_num).addClassName('highligth_reorder');
		}
		$('span-require_qty-'+size_num).update(remain_qty);
		
		// update all transfer qty
		var total_transfer_qty = 0;
		$(this.f2).getElementsBySelector("input.inp_transfer_qty").each(function(inp){
			total_transfer_qty += int(inp.value);
		});
		this.f2['total_transfer_qty'].value = total_transfer_qty;
		if(total_transfer_qty > 0){
			$('div-total_transfer_qty').show().update("Total: "+total_transfer_qty);
		}else{
			$('div-total_transfer_qty').hide().update("Total: 0");
		}
		
	},
	generate_do: function(){
		var total_transfer_qty = int(this.f2['total_transfer_qty'].value);
		if(total_transfer_qty <= 0){
			alert('No Item to Transfer.');
			return;
		}
		
		if(!confirm('Are you sure to transfer '+total_transfer_qty+' Qty?'))	return false;
		
		curtain(true, 'curtain2');
		center_div($('div_generate_do_dialog').show());
		$('div_generate_do_dialog_content').update('Generating DO...<br />'+_loading_);
		
		var THIS = this;
		new Ajax.Request(phpself+'?a=ajax_generate_do', {
            method: 'post',
			parameters: $(this.f2).serialize(),
			onComplete: function(msg){
                var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_generate_do_dialog_content').update(ret['html']+'<br>'+THIS.close_curtain2_btn);
	                    alert('DO Generate Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    close_curtain2();
			    alert(err_msg);
			}
		});
	}
}

function close_curtain2(){
    curtain(false, 'curtain2');
	$('div_generate_do_dialog').hide();
}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


{if $err}
	<ul class="errmsg">
		{foreach from=$err item=e}
			<div class="alert alert-danger rounded">
				<li> {$e}</li>
			</div>
		{/foreach}
	</ul>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" class="stdframe" onSubmit="return false;" method="post">
			<input type="hidden" name="show_report" value="1" />
			
			<span>
				<b class="form-label">Deliver To: </b>
				<select class="form-control" name="to_branch_id">
					<option value="">-- Please Select --</option>
					{foreach from=$branches key=bid item=b}
						{if $b.code ne 'HQ'}
							<option value="{$bid}" {if $smarty.request.to_branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
						{/if}
					{/foreach}
				</select>
			</span>
			
			<div>
				{include file='sku_items_autocomplete.tpl' no_add_button=1}
			</div>
			
			<span>
				<input type="checkbox" name="include_nrr" value="1" {if $smarty.request.include_nrr}checked {/if} /> &nbsp;Including Not Reorder Require (NRR)
			</span>
			
			<p>
				<button class="btn btn-primary mt-2" onClick="MATRIX_IBT.show_report();">Show</button>
			</p>
		</form>
	</div>
</div>

{if $smarty.request.show_report}
	{if !$data}
		<p> * No Data *</p>
	{else}
		<!-- Generate DO dialog -->
		<div id="div_generate_do_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:400px;height:200px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
			<div id="div_generate_do_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_generate_do_title">Generate DO</span>
				<span style="float:right;">
					{*<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>*}
				</span>
				<div style="clear:both;"></div>
			</div>
			<div id="div_generate_do_dialog_content" style="padding:2px;height:170px;overflow-y:auto;"></div>
		</div>
		<!-- End of Generate DO dialog -->

		<br />
		<div class="card mx-3">
			<div class="card-body">
				<form name="f2" onsubmit="return false;">
					<input type="hidden" name="a" value="ajax_generate_do" />
					<input type="hidden" name="sku_id" value="{$data.si.sku_id}" />
					<input type="hidden" name="to_branch_id" value="{$data.to_branch_id}" />
					<input type="hidden" name="total_transfer_qty" value="0" />
					
					{foreach from=$data.size_list key=size_num item=size}
						<input type="hidden" name="size_list[{$size_num}]" value="{$size|escape:html}" />
					{/foreach}
					
					<table class="report_table" width="100%">
						<tr>
							<th align="left">Branch</th>
							<td>{$branches[$data.to_branch_id].code} - {$branches[$data.to_branch_id].description}</td>
							<th align="left">ARMS Code</th>
							<td>{$data.si.sku_item_code}</td>
						</tr>
						<tr>
							<th align="left">Stock Age</th>
							<td>35 Days</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</table>
					
					<br />
					<table class="report_table" width="100%">
						<tr class="header">
							<th rowspan="2">&nbsp;</th>
							<th colspan="{count var=$data.size_list}">Size</th>
						</tr>
						
						{* Size *}
						<tr class="header">
							{foreach from=$data.size_list key=size_num item=size}
								<th width="50">{$size}</th>
							{/foreach}
						</tr>
						
						{* Stock Balance *}
						<tr>
							<td><b>Stock Balance</b></td>
							{foreach from=$data.size_list key=size_num item=size}
								<td align="right">{$data.main.stock_balance.$size_num|qty_nf}</td>
							{/foreach}
						</tr>
						
						{* 30 Days Sales *}
						<tr>
							<td><b>30 Days Sales</b></td>
							{foreach from=$data.size_list key=size_num item=size}
								<td align="right">{$data.main.30d_pos.$size_num|qty_nf}</td>
							{/foreach}
						</tr>
						
						{* Stock Min Qty *}
						<tr class="row_stock_min">
							<td><b>Stock Min Qty</b></td>
							{foreach from=$data.size_list key=size_num item=size name=sz}
								<td align="right">
									{if $data.main.stock_min_qty.$size_num}
										{$data.main.stock_min_qty.$size_num|qty_nf}
									{else}
										NRR
									{/if}
								</td>
							{/foreach}
						</tr>
						
						{* Reorder Qty *}
						<tr class="row_color1">
							<td><b>Reorder Qty</b></td>
							{foreach from=$data.size_list key=size_num item=size}
								<td align="right">
									<input type="hidden" name="reorder_qty[{$size_num}]" value="{$data.main.reorder_qty.$size_num}" />
									<span>{$data.main.reorder_qty.$size_num|qty_nf}</span>
								</td>
							{/foreach}
						</tr>
						
						{* Transfer Qty *}
						<tr>
							<td><b>Transfer Qty</b>
								<div style="float:right;display:none;" id="div-total_transfer_qty">
									Total: 0
								</div>
							</td>
							{foreach from=$data.size_list key=size_num item=size}
								<td align="right" id="transfer_qty-{$size_num}">
									<input type="hidden" name="transfer_qty[{$size_num}]" value="0" class="inp_transfer_qty" />
									<span id="span-transfer_qty-{$size_num}">&nbsp;</span>
								</td>
							{/foreach}
						</tr>
						
						{* Require Qty *}
						<tr class="row_color1">
							<td><b>Require Qty</b></td>
							{foreach from=$data.size_list key=size_num item=size}
								<td align="right" id="require_qty-{$size_num}" {if $data.main.reorder_qty.$size_num>0}class="highligth_reorder"{/if}>
									<input type="hidden" name="require_qty[{$size_num}]" value="{$data.main.reorder_qty.$size_num}" />
									<span id="span-require_qty-{$size_num}">{$data.main.reorder_qty.$size_num|qty_nf}</span>
								</td>
							{/foreach}
						</tr>
					</table>
					
					<h2>Branches Above Min</h2>
					
					{foreach from=$data.available_bid_list item=bid}
						<table class="report_table" width="100%">
							<tr class="header">
								<td rowspan="2">
									{*<input type="checkbox" name="enable_ibt[{$bid}]" value="1" />*}
									{$branches.$bid.code}
								</td>
								<th colspan="{count var=$data.size_list}">Size</th>
							</tr>
							
							{* Size *}
							<tr class="header">
								{foreach from=$data.size_list key=size_num item=size}
									<th width="50">{$size}</th>
								{/foreach}
							</tr>
						
							{* Stock Balance *}
							<tr>
								<td><b>Stock Balance</b></td>
								{foreach from=$data.size_list key=size_num item=size}
									<td align="right">{$data.available_branch.$bid.stock_balance.$size_num|qty_nf}</td>
								{/foreach}
							</tr>
							
							{* 30 Days Sales *}
							<tr>
								<td><b>30 Days Sales</b></td>
								{foreach from=$data.size_list key=size_num item=size}
									<td align="right">{$data.available_branch.$bid.30d_pos.$size_num|qty_nf}</td>
								{/foreach}
							</tr>
							
							{* Stock Min Qty *}
							<tr class="row_stock_min">
								<td><b>Stock Min Qty</b></td>
								{foreach from=$data.size_list key=size_num item=size name=sz}
									<td align="right">
										{if $data.main.stock_min_qty.$size_num}
											{$data.main.stock_min_qty.$size_num|qty_nf}
										{else}
											NRR
										{/if}
									</td>
								{/foreach}
							</tr>
							
							{* Available Qty *}
							<tr class="row_color1">
								<td><b>Available Qty</b></td>
								{foreach from=$data.size_list key=size_num item=size}
									<td align="right" {if $data.main.reorder_qty.$size_num>0 and $data.available_branch.$bid.available_qty.$size_num>0}class="highligth_available"{/if}>
										<input type="hidden" name="available_qty[{$size_num}][{$bid}]" value="{$data.available_branch.$bid.available_qty.$size_num}" />
										<span>{$data.available_branch.$bid.available_qty.$size_num|qty_nf}</span>
									</td>
								{/foreach}
							</tr>
							
							{* Transfer Qty *}
							<tr>
								<td><b>Transfer Qty</b></td>
								{foreach from=$data.size_list key=size_num item=size}
									<td align="center">
										{if $data.main.reorder_qty.$size_num>0 and $data.available_branch.$bid.available_qty.$size_num>0}
											<input type="text" name="branch_transfer_qty[{$size_num}][{$bid}]" value="0" class="inp_branch_transfer_qty branch_transfer_qty-{$size_num}" onChange="MATRIX_IBT.branch_transfer_qty_changed('{$bid}', '{$size_num}');" />
										{else}
											-
										{/if}
									</td>
								{/foreach}
							</tr>
						</table>
						<br />
					{/foreach}
				</form>
			</div>
		</div>
		
		<p align="center">
			<input type="button" class="btn btn-warning" value="Generate Transfer DO" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onClick="MATRIX_IBT.generate_do();" />
		</p>
		
	{/if}
{/if}
<script>
MATRIX_IBT.initialize();
</script>
{include file='footer.tpl'}