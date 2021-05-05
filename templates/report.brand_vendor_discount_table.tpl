{*
6/21/2011 11:25:16 AM Andy
- Add auto reload vendor/brand, price type and department list base on user selection.

06/30/2020 02:25 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

{if !$no_header_footer}
<style>
{literal}
.div_sel{
	height: 200px;
	width: 200px;
	overflow-x:hidden;
	overflow-y:auto;
}

.ul_sel{
	list-style: none;
	padding: 0;
}
.ul_sel li{
	padding: 0;
	margin: 0;
	white-space:nowrap;
}
.dept_row{
	background: #ddd;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var VB_DISCOUNT_TABLE = {
	form_element: undefined,
	ajax_sel: undefined,
	var_timeout: {'vb':undefined, 'price_type':undefined, 'dept':undefined},
	initialize: function(){
		this.form_element = document.f_a;
		if(!this.form_element){
			alert('Report failed to initialize.');
			return false;
		}
	},
	// function when user toggle all checkbox
	toggle_chx: function(chx, type){
		var c = chx.checked;
		// find parent ul and get all the li
		var parent_ul = chx.parentNode.parentNode
		$(parent_ul).getElementsBySelector('input[type=checkbox]').each(function(ele){
			ele.checked = c;
		});
		
		// reload list
		switch(type){
			case 'branch':
				this.reload_available_sel('vb');	// vendor/brand
				break;
			case 'vb':
				this.reload_available_sel('price_type');	// price_type
				break;
			case 'price_type':
				this.reload_available_sel('dept');	// dept
				break;
		}
	},
	// function to reload available select
	reload_available_sel: function(sel_type){
		if(this.ajax_sel)	this.ajax_sel.transport.abort();
		
		if(sel_type=='vb'){	// reload vendor list
			$('div_vb_sel').update(_loading_);
			$('div_price_type_sel').update('');
			$('div_dept_sel').update('');
			// clear ajax timeout
			if(this.var_timeout.price_type)	clearTimeout(this.var_timeout.price_type);
			if(this.var_timeout.dept)	clearTimeout(this.var_timeout.dept);
		}else if(sel_type=='price_type'){	// reload price type list
			$('div_price_type_sel').update(_loading_);
			$('div_dept_sel').update('');
			if(this.var_timeout.dept)	clearTimeout(this.var_timeout.dept);
		}else if(sel_type=='dept'){	// reload dept
			$('div_dept_sel').update(_loading_);
		}else{
			alert('Invalid Selection list');
			return false;
		}
		
		var params = $(this.form_element).serialize()+'&sel_type='+sel_type+'&a=ajax_reload_available_sel';
		var div_container = $('div_'+sel_type+'_sel');
		
		this.ajax_sel = new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	            		$(div_container).update(ret['html']);        
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(err_msg)	alert(err_msg);
			    $(div_container).update('');
			}
		});
	},
	// function when user check/uncheck branch
	branch_chx_changed: function(){
		if(this.var_timeout.vb)	clearTimeout(this.var_timeout.vb);
		// call the function after 1 second
		this.var_timeout.vb = setTimeout("VB_DISCOUNT_TABLE.reload_available_sel('vb')", 1000);
	},
	// function when user check/uncheck vendor
	vendor_chx_changed: function(){
		if(this.var_timeout.price_type)	clearTimeout(this.var_timeout.price_type);
		// call the function after 1 second
		this.var_timeout.price_type = setTimeout("VB_DISCOUNT_TABLE.reload_available_sel('price_type')", 1000);
	},
	// function when user check/uncheck price type
	price_type_chx_changed: function(){
		if(this.var_timeout.dept)	clearTimeout(this.var_timeout.dept);
		// call the function after 1 second
		this.var_timeout.dept = setTimeout("VB_DISCOUNT_TABLE.reload_available_sel('dept')", 1000);
	}
};

function toggle_chx(chx, type){
	VB_DISCOUNT_TABLE.toggle_chx(chx, type);
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}


{if !$no_header_footer}
<form name="f_a" method="post" class="stdframe">
	<input type="hidden" name="subm" value="1" />
	
	<table>
	    <tr>
			{if $can_select_branch}
			    <td>
				    <fieldset>
				        <legend>Branch</legend>
					    <div class="div_sel">
					        <ul class="ul_sel">
					        	<li><input type="checkbox" onChange="toggle_chx(this, 'branch');" /> <b>All</b></li>
						        {foreach from=$branches key=bid item=r}
						            {if !$branches_group.have_group.$bid}
						        		<li>
											<img src="ui/pixel.gif" width="20" height="1" />
											<input type="checkbox" name="branch_id[]" value="{$bid}" {if is_array($smarty.request.branch_id)}{if in_array($bid, $smarty.request.branch_id)}checked {/if}{/if} onChange="VB_DISCOUNT_TABLE.branch_chx_changed();" /> {$r.code}
										</li>
						        	{/if}
						        {/foreach}
						        {if $branches_group.header}
						            {foreach from=$branches_group.header key=bgid item=bg}
						                <li>
						                    <ul class="ul_sel">
						                        <li>
													<img src="ui/pixel.gif" width="20" />
													<input type="checkbox" onChange="toggle_chx(this, 'branch');" />
													<b>{$bg.code}</b>
												</li>
						                        {foreach from=$branches_group.items.$bgid item=r}
                                                    <li>
														<img src="ui/pixel.gif" width="40" height="1" />
														<input type="checkbox" name="branch_id[]" value="{$r.branch_id}" {if is_array($smarty.request.branch_id)}{if in_array($r.branch_id, $smarty.request.branch_id)}checked {/if}{/if} onChange="VB_DISCOUNT_TABLE.branch_chx_changed();" /> {$r.code}
													</li>
						                        {/foreach}
						                    </ul>
						                </li>
						            {/foreach}
						        {/if}
					        </ul>
					    </div>
				    </fieldset>
			    </td>
			{/if}
			<td>
			    <fieldset>
			        <legend>{$REPORT_TABLE_TYPE|capitalize}</legend>
				    <div class="div_sel" id="div_vb_sel">
				        {include file='report.brand_vendor_discount_table.brand_vendor_sel.tpl'}
				    </div>
			    </fieldset>
			</td>
			<td>
			    <fieldset>
			        <legend>Price Type</legend>
				    <div class="div_sel" id="div_price_type_sel">
				        {include file='report.brand_vendor_discount_table.price_type_sel.tpl'}
				    </div>
			    </fieldset>
			</td>
			<td>
			    <fieldset>
			        <legend>Department</legend>
				    <div class="div_sel" id="div_dept_sel">
				        {include file='report.brand_vendor_discount_table.dept_sel.tpl'}
				    </div>
			    </fieldset>
			</td>
	    </tr>
	</table>
	<input class="btn btn-primary" type="submit" value="Show" />
	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
	{/if}
</form>
{/if}

<script>VB_DISCOUNT_TABLE.initialize();</script>

{if !$data}
	{if $smarty.request.subm}No Data{/if}
{else}
<br />

<table class="report_table" width="100%">
	<tr class="header">
	    <th rowspan="2">Department / {$REPORT_TABLE_TYPE|capitalize}</th>
	    {foreach from=$price_type_list item=pt}
	        <th colspan="{$branch_count}">{$pt}</th>
	    {/foreach}
	</tr>
	<tr class="header">
	    {assign var=total_cols_count value=0}
	    {foreach from=$price_type_list item=pt}
	        {foreach from=$branch_id_list item=bid}
	            {assign var=total_cols_count value=$total_cols_count+1}
	            <th>{$branches.$bid.code}</th>
	        {/foreach}
	    {/foreach}
	</tr>
	{foreach from=$dept_id_list item=dept_id}
	    <tr class="dept_row">
	        <td colspan="{$total_cols_count+1}">
	            <img src="ui/collapse.gif" align="absmiddle" title="Show/Close Details" onClick="togglediv('tbody_dept_row_{$dept_id}', this);" class="clickable" />
				{$depts.$dept_id.description}
			</td>
	    </tr>
	    <tbody id="tbody_dept_row_{$dept_id}">
	    {foreach from=$commission_tbl_id_list item=cms_tbl_id}
	        <tr>
	            <td class="r">{$commission_tbl.$cms_tbl_id.description}</td>
	            {foreach from=$price_type_list item=pt}
                    {foreach from=$branch_id_list item=bid}
                        <td class="r">{$data.$bid.$dept_id.$pt.$cms_tbl_id|default:'-'}</td>
                    {/foreach}
	            {/foreach}
	        </tr>
	    {/foreach}
	    </tbody>
	{/foreach}
</table>
{/if}

{include file='footer.tpl'}
