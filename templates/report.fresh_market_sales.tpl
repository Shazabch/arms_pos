{*
2/11/2011 3:59:30 PM Andy
- Add color for those sales without stock take.
- Add cost, gp and gp % for sales without stock take.

2/15/2011 11:49:58 AM Andy
- Reconstruct daily category sales report to show fresh market data.

06/30/2020 10:28 AM Sheila
- Updated button css.
*}

{include file='header.tpl'}

{if !$no_header_footer}

{literal}
<style>
#show_sku {
	padding:10px 0;
}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}

.col_no_sc{
	background-color: #fcf;
}
.negative{
	color:red;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

var submitted_data = {ldelim}
	'branch_id': '{$smarty.request.branch_id}',
	'sku_type': '{$smarty.request.sku_type}',
	'date_from': '{$smarty.request.date_from}',
	'date_to': '{$smarty.request.date_to}',
	'include_zero_sales': '{$smarty.request.include_zero_sales}'
{rdelim};

{literal}
function check_form(){
	if(!document.f_a['date_from']){ // no this element
		alert('No date from');
		return false;
	}else{
		if(document.f_a['date_from'].value==''){    // no select date
			alert('Please select date from');
			return false;
		}
	}
	if(!document.f_a['date_to']){   // no this element
		alert('No date to');
		return false;
	}else{
        if(document.f_a['date_to'].value==''){  // no select date
			alert('Please select date to');
			return false;
		}
	}
	
	if(document.f_a['date_from'].value>=document.f_a['date_to'].value){  // date to early than date from
		alert('Date to cannot early than or same to date from');
		return false;
	}
	return true;
}

function branch_changed(){
	var bid = document.f_a['branch_id'].value;
	
	$('span_date_list').update(_loading_);
	new Ajax.Request(phpself+'?a=load_date&branch_id='+bid, {
		onComplete: function(msg){
			var data = msg.responseText;

			try{
                ret = JSON.parse(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                $('span_date_list').update(ret['html']);
	                return;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

      		// prompt the error
		    alert(err_msg);
		}
	});
}

function show_sub(root_id){
	document.f_a['cat_id'].value = root_id
	document.f_a.submit();
}

function expand_sub(root_id, indent, el, root_per){
	if(el.src.indexOf('clock')>0)   return;

	el.onClick='';
	el.src = '/ui/clock.gif';
	
	var str = $H(submitted_data).toQueryString();
	new Ajax.Request(phpself+'?'+str+"&a=ajax_load_category&ajax=1&cat_id="+root_id+"&indent="+indent+'&root_per='+root_per,
	{
		onComplete: function(e) {
			new Insertion.After($('tbody_cat-'+root_id), e.responseText);
			el.remove();
		},
	});
}

function show_sku(root_id, img){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';

	$('div_show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	var str = $H(submitted_data).toQueryString();
	new Ajax.Updater('div_show_sku',phpself+'?'+str+"&a=ajax_load_sku&cat_id="+root_id,{
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}
{/literal}
</script>
{/if}
<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<div class="noprint stdframe">
<form name="f_a" method="get" onSubmit="return check_form();">
    <input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="cat_id" value="{$smarty.request.cat_id}" />

    {if $can_select_branch}
		<b>Branch</b>
        <select name="branch_id" onChange="branch_changed();">
		{foreach from=$branches key=bid item=r}
			{if !$branches_group.have_group.$bid}
				<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
			{/if}
		{/foreach}
		{if $branches_group.header}
			{foreach from=$branches_group.header key=bgid item=bg}
				<optgroup label='{$bg.code}'>
		    	    {foreach from=$branches_group.items.$bgid item=r}
		    	        <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
		    	    {/foreach}
		    	</optgroup>
	    	{/foreach}
		{/if}
		</select>&nbsp;&nbsp;
	{/if}
	
	<b>SKU Type</b>
	<select name="sku_type">
	    <option value="">-- All --</option>
	    {foreach from=$sku_type item=r}
	        <option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;
	
	<span id="span_date_list">
	    {include file='report.fresh_market_sales.date_list.tpl'}
	</span>
	
	<p>
		<input type="submit" value='Show Report' /> &nbsp;&nbsp;

		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button class="btn btn-primary" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>&nbsp;&nbsp;
		{/if}
	    <input type="checkbox" name="include_zero_sales" value="1" {if $smarty.request.include_zero_sales}checked {/if} /> Include zero sales category
	</p>
</form>
</div>
{/if}

<br />
{if $smarty.request.load_report}
	{if !$cat_data}
	    {if $err}
	        <ul>
	            {foreach from=$err item=e}
	                <li>{$e}</li>
	            {/foreach}
	        </ul>
	    {else}
	        ** No Data **
	    {/if}
	{else}
	    <h3>{$report_title}</h3>
	    
	    {if !$no_header_footer}
		    <p>
				&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
				{if $cat_info}
				    {foreach from=$cat_info.cat_tree_info item=ct}
				        <a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
				    {/foreach}
				    {$cat_info.description} /
				{/if}
				<ul>
					<li>Click on a sub-category for further detail. Click <img src="/ui/icons/table.png" align="absmiddle" />  to display SKU in the category.</li>
					<li>The sales items must have at least one start and end stock take, else they will goes into "amt w/o stk".</li>
	    			<li>Item will use last fresh market cost, if no fresh market cost then it will use grn cost.</li>
	    			<li><span class="col_no_sc">* Sales with this color means amount without stock take.</span> (This means the GP is inaccurate)</li>
				</ul>
			</p>
		{/if}
		
	    <table class="tb" cellspacing="0" cellpadding="2" border="0">
	        <tr>
	            <th align="left" colspan="2">&nbsp;</th>
	            {foreach from=$date_cols key=dt item=d}
				    <th valign="bottom">
						{if $lastm ne $d.m or $lasty ne $d.y}
						    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
						    {assign var=lastm value=$d.m}
							{assign var=lasty value=$d.y}
						{/if}
						{$d.d}
					</th>
				{/foreach}
				<th>Total</th>
				<th>Contrib<br>(%)</th>
	        </tr>
	        
	        {include file='report.fresh_market_sales.table.tpl'}
	        
	        {assign var=rowspan value=2}
			{if $sessioninfo.show_report_gp}
			    {assign var=rowspan value=$rowspan+4}
			{/if}
			{if $sessioninfo.show_cost}
			    {assign var=rowspan value=$rowspan+2}
			{/if}

	        {assign var=cat_row value=$cat_data.total}
	        <tfoot>
				<tr>
				    <td rowspan="{$rowspan}" align="right">
						<b>Total</b>
					</td>
					<td>Amt</td>
					{foreach from=$date_cols key=dt item=d}
					    <td align="right" title="Amount">{$cat_row.pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
					{/foreach}

					<!-- Total -->
					<td align="right" title="Amount">{$cat_row.pos.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				</tr>
				{if $sessioninfo.show_cost}
				    <!-- Cost -->
				    <tr>
					    <td>Cost</td>
						{foreach from=$date_cols key=dt item=d}
						    <td align="right" title="Cost">{$cat_row.pos.$dt.cost|number_format:2|ifzero:'&nbsp;'}</td>
						{/foreach}

						<!-- Total -->
						<td align="right" title="Cost">{$cat_row.pos.total.cost|number_format:2|ifzero:'&nbsp;'}</td>
					</tr>
				{/if}
				{if $sessioninfo.show_report_gp}
				    <!-- GP -->
					<tr>
					    <td>GP</td>
						{foreach from=$date_cols key=dt item=d}
						    {assign var=gp value=$cat_row.pos.$dt.amt-$cat_row.pos.$dt.cost}
						    <td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
						{/foreach}

						<!-- Total -->
						{assign var=gp value=$cat_row.pos.total.amt-$cat_row.pos.total.cost}
						<td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
					</tr>
					<!-- GP %-->
					<tr>
					    <td>GP%</td>
						{foreach from=$date_cols key=dt item=d}
						    {assign var=gp_per value=0}
						    {if $cat_row.pos.$dt.amt}
						        {assign var=gp value=$cat_row.pos.$dt.amt-$cat_row.pos.$dt.cost}
						        {assign var=gp_per value=$gp/$cat_row.pos.$dt.amt*100}
						    {/if}
						    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
						{/foreach}

						<!-- Total -->
						{assign var=gp_per value=0}
					    {if $cat_row.pos.total.amt}
					        {assign var=gp value=$cat_row.pos.total.amt-$cat_row.pos.total.cost}
					        {assign var=gp_per value=$gp/$cat_row.pos.total.amt*100}
					    {/if}
					    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
					</tr>
				{/if}
				<tr class="col_no_sc">
				    <td>amt w/o stk</td>
					{foreach from=$date_cols key=dt item=d}
					    <td align="right" title="Amount without stock check">{$cat_row.no_sc_pos.$dt.amt|number_format:2|ifzero:'&nbsp;'}</td>
					{/foreach}

					<!-- Total -->
					<td align="right" title="Amount without stock check" >{$cat_row.no_sc_pos.total.amt|number_format:2|ifzero:'&nbsp;'}</td>
				</tr>
				{if $sessioninfo.show_cost}
				    <!-- Cost -->
				    <tr class="col_no_sc">
					    <td>Cost</td>
						{foreach from=$date_cols key=dt item=d}
						    <td align="right" title="Cost">{$cat_row.no_sc_pos.$dt.cost|number_format:2|ifzero:'&nbsp;'}</td>
						{/foreach}

						<!-- Total -->
						<td align="right" title="Cost">{$cat_row.no_sc_pos.total.cost|number_format:2|ifzero:'&nbsp;'}</td>
					</tr>
				{/if}
				{if $sessioninfo.show_report_gp}
				    <!-- GP -->
					<tr class="col_no_sc">
					    <td>GP</td>
						{foreach from=$date_cols key=dt item=d}
						    {assign var=amt value=$cat_row.no_sc_pos.$dt.amt|round2}
							{assign var=cost value=$cat_row.no_sc_pos.$dt.cost|round2}
						    {assign var=gp value=$amt-$cost}
						    <td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
						{/foreach}

						<!-- Total -->
						{assign var=amt value=$cat_row.no_sc_pos.total.amt|round2}
						{assign var=cost value=$cat_row.no_sc_pos.total.cost|round2}
					    {assign var=gp value=$amt-$cost}
						<td align="right" title="GP">{$gp|number_format:2|ifzero:'&nbsp;'}</td>
					</tr>
					<!-- GP %-->
					<tr class="col_no_sc">
					    <td>GP%</td>
						{foreach from=$date_cols key=dt item=d}
						    {assign var=gp_per value=0}
						    {if $cat_row.no_sc_pos.$dt.amt}
						        {assign var=gp value=$cat_row.no_sc_pos.$dt.amt-$cat_row.no_sc_pos.$dt.cost}
						        {assign var=gp_per value=$gp/$cat_row.no_sc_pos.$dt.amt*100}
						    {/if}
						    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
						{/foreach}

						<!-- Total -->
						{assign var=gp_per value=0}
					    {if $cat_row.no_sc_pos.total.amt}
					        {assign var=gp value=$cat_row.no_sc_pos.total.amt-$cat_row.no_sc_pos.total.cost}
					        {assign var=gp_per value=$gp/$cat_row.no_sc_pos.total.amt*100}
					    {/if}
					    <td align="right" title="GP %">{$gp_per|num_format:2|ifzero:'&nbsp;':'%'}</td>
					</tr>
				{/if}
			</tfoot>
	    </table>
	{/if}
	
	<br />
	<div id="div_show_sku"></div>
{/if}

{include file='footer.tpl'}
