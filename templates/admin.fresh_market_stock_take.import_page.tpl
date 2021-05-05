{*
11/27/2017 3:10 PM Justin
- Enhanced to have auto fill zero feature.
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

function check_form(f){
	if(!f['stock_take_date']||f['stock_take_date'].value==''){
		alert('No Stock Date Selected.');
		return false;
	}
	if(!confirm('Are you sure?'))  return false;
	return true;
}

function stock_branch_change(imported){
	var type = imported>0 ? 'reset' : 'import';
	var f = $('f_'+type);
	var bid = f['branch_id'].value;
	if(bid==''){    // no branch selected
		$(f['stock_take_date']).update('<option value="">-- No Data --</option>');
		$('btn_'+type).disabled = false;
	}else{
	    $('div_'+type+'_stock_take_date').update(_loading_);
	    $('btn_'+type).disabled = true;
		new Ajax.Updater('div_'+type+'_stock_take_date',phpself,{
			parameters:{
				a: 'check_available_stock_take_date',
				branch_id: bid,
				imported: imported
			},
			onComplete: function(e){
                $('btn_'+type).disabled = false;
			},
			evalScripts: true
		});
	}
}

function stock_date_changed(imported){
	
	var type = imported>0 ? 'reset' : 'import';
	var f = $('f_'+type);
	var s_date = f['stock_take_date'].value;
	var bid = f['branch_id'].value;
	
	if(s_date==''){    // no branch selected
		$(f['sku_type']).update('<option value="">-- No Data --</option>');
		$('btn_'+type).disabled = false;
	}else{
	    $('div_'+type+'_stock_take_sku').update(_loading_);
	    $('btn_'+type).disabled = true;
		new Ajax.Updater('div_'+type+'_stock_take_sku',phpself,{
			parameters:{
				a: 'check_available_stock_take_sku',
				branch_id: bid,
				date: s_date,
				imported: imported
			},
			onComplete: function(e){
                $('btn_'+type).disabled = false;
			},
			evalScripts: true
		});
	}
}


{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<!-- Import -->
<fieldset>
<legend><b>Import</b></legend>
<form name="import_stock_take" id="f_import" onSubmit="return check_form(this);">
<input type="hidden" name="a" value="import_stock_take" />
<table>
	<tr>
		{if $BRANCH_CODE eq 'HQ'}
			<td><b>Branch</b> <select name="branch_id" onchange="stock_branch_change(0);">
			<option value="">-- Please Select --</option>
			{foreach from=$branches item=r}
				<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code} - {$r.description}</option>
			{/foreach}
			</select>&nbsp;&nbsp;&nbsp;</td>
		{else}
		    <input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
		{/if}
		<td>
			<div id="div_import_stock_take_date">
				{include file='admin.fresh_market_stock_take.import_page.stock_take_date.tpl' available_date=$date_data.import type=0}
			</div>
		</td>
		<td>
			<div id="div_import_stock_take_sku">
			    <b>SKU Type</b>
				<select name='sku_type'>
				    <option value=''>-- No Data --</option>
				</select>
			</div>
		</td>
		<td>&nbsp;&nbsp;&nbsp;
			<input type="submit" value="Import" id="btn_import" />
		</td>
	</tr>
	<tr>
		<td>
		    <b>Action </b> [<a href="javascript:void(alert('
			1. No Auto Fill Zero\n
			- The system will import all Stock Take according to the selected branch and date only, without zerolise the rest of the non-scanned Stock Take items.\n
			- For those Stock Take SKU, system will auto zerolise for all the child SKU while import.\n\n
			2. Auto Add Zero for Non-scan Items\n
			- The system will import all Stock Take according to the selected branch and date, and then zerolise the rest of the non-scanned Stock Take items.
			'));">?</a>]
			<select name="fill_zero_options" onchange="check_options(this);">
		    	<option value="no_fill">No auto fill zero</option>
		    	<option value="fill_zero">Auto add zero for non-scan items</option>
		    </select>
		</td>
	</tr>
</table>
</form>
	{if $smarty.request.t eq 'import' and $smarty.request.msg}
	    <span style="color:blue;">- {$smarty.request.msg}</span>
	{/if}
</fieldset>

<!-- Reset -->
<fieldset>
<legend><b>Reset</b></legend>
<form name="reset_stock_take" id="f_reset" onSubmit="return check_form(this);">
<input type="hidden" name="a" value="reset_stock_take" />
<table>
	<tr>
		{if $BRANCH_CODE eq 'HQ'}
			<td><b>Branch</b> <select name="branch_id" onchange="stock_branch_change(1);">
			<option value="">-- Please Select --</option>
			{foreach from=$branches item=r}
				<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id}selected {/if}>{$r.code} - {$r.description}</option>
			{/foreach}
			</select>&nbsp;&nbsp;&nbsp;</td>
        {else}
		    <input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
		{/if}
		<td>
			<div id="div_reset_stock_take_date">
				{include file='admin.fresh_market_stock_take.import_page.stock_take_date.tpl' available_date=$date_data.reset type=1}
			</div>
		</td>
		<td>
			<div id="div_reset_stock_take_sku">
			    <b>SKU Type</b>
				<select name='sku_type'>
				    <option value=''>-- No Data --</option>
				</select>
			</div>
		</td>
		<td>&nbsp;&nbsp;&nbsp;
			<input type="submit" value="Reset" id="btn_reset" />
		</td>
	</tr>
</table>
</form>
	{if $smarty.request.t eq 'reset' and $smarty.request.msg}
	    <span style="color:blue;">- {$smarty.request.msg}</span>
	{/if}
</fieldset>
{include file='footer.tpl'}
