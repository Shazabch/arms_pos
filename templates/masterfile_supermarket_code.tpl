{*
7/5/2011 2:26:33 PM Andy
- Add checking for category.
*}

{include file='header.tpl'}	

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function submit_form(){
	var cat_id = document.f_a['category_id'].value;
	if(!cat_id){
		alert('Please select a category.');
		return false;
	}
	
	var sku_code_list = $('sku_code_list2');
	for(var i=0; i<sku_code_list.length; i++){
	    sku_code_list.options[i].selected = true;
	}
	document.f_a.submit();
}

function save_supermarket_code(){
	if(!confirm('Are you sure?'))	return false;
	
	$('btn_save').value = 'Saving...';
	$('btn_save').disabled = true;
	
	var params = $('f_b').serialize()+'&a=ajax_save_supermarket_code';
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){
			$('btn_save').value = 'Save';
			$('btn_save').disabled = false;		
			
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';
			
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['msg']){ // success
                    alert(ret['msg']);
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

		    // prompt the error
		    alert(err_msg);
		}
	});
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>
{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
		<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" class="stdframe" method="post">
	<input type="hidden" name="load_data" value="1" />
	
	<b>Branch:</b>
	<select name="branch_id">
		{foreach from=$branches key=bid item=b}
		    {if !$branch_group.have_group.$bid}
		    	<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
		    {/if}
		{/foreach}
		{foreach from=$branch_group.header key=bgid item=bg}
		    <optgroup label="{$bg.code}">
		        {foreach from=$branch_group.items.$bgid key=bid item=b}
		            <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
		        {/foreach}
		    </optgroup>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Vendor:</b>
	<select name="vendor_id">
		<option value="">-- All --</option>
		{foreach from=$vendors key=vid item=r}
			<option value="{$vid}" {if $smarty.request.vendor_id eq $vid}selected {/if}>{$r.description} {if $r.prefix_code}[{$r.prefix_code}]{/if}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Price Type:</b>
	<select name="price_type">
		<option value="">-- All --</option>
		{foreach from=$price_type item=r}
			<option value="{$r.code}" {if $smarty.request.price_type eq $r.code}selected {/if}>{$r.code}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<p>
		{include file='category_autocomplete.tpl'}
	</p>

	<p>
		{include file='sku_items_autocomplete_multiple_add2.tpl' parent_form='document.f_a' is_dbl_sku=2}
	</p>
	<p>
		<b>Supermarket Code Filter: </b>
		<select name="scode_filter">
			<option value="">-- No Filter --</option>
			<option value="got_scode" {if $smarty.request.scode_filter eq 'got_scode'}selected {/if}>
				Already have supermarket code
			</option>
			<option value="no_scode" {if $smarty.request.scode_filter eq 'no_scode'}selected {/if}>
				No supermarket code yet
			</option>
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" onClick="submit_form();" value="Refresh" />
	</p>
</form>

{if $smarty.request.load_data and !$err}
	<h2>{$report_title}</h2>
	{if !$data}
		-- No Data --
	{else}
		<form name="f_b" onSubmit="return false;" id="f_b">
		<input type="hidden" name="branch_id" value="{$smarty.request.branch_id}" />

		<table width="100%" class="report_table">
			<thead>
				<tr class="header">
					<th width="20">&nbsp;</th>
					<th>ARMS Code</th>
					<th>Artno</th>
					<th>Description</th>
					<th>Price Type</th>
					<th>Supermarket Code</th>
				</tr>
			</thead>
			{foreach from=$data key=sid item=r name=f}
				<tr>
					<td>{$smarty.foreach.f.iteration}.</td>
					<td>{$r.sku_item_code}</td>
					<td>{$r.artno}</td>
					<td>{$r.description}</td>
					<td>{$r.price_type}</td>
					<td align="center">
						<input type="text" name="supermarket_code[{$sid}]" value="{$r.supermarket_code}" size="30" maxlength="20" />
					</td>
				</tr>
			{/foreach}
		</table>
		</form>
		
		<div style="position:fixed;bottom:0;background:#ddd;width:100%;text-align:center;left:0;padding:3px;opacity:0.8;">
			<input type="button" id="btn_save" value="Save" onClick="save_supermarket_code();" />
	</div>
	{/if}
{/if}
{include file='footer.tpl'}