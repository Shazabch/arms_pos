{*
2017-08-25 15:08 PM Qiu Ying
- Enhanced to add Debtor blocklist for Credit Sales DO at debtor masterfile
*}

<script type="text/javascript">
{literal}
submit_form = function(action){
	if(action=='close'){
        if(!confirm('Are you sure?'))   return false;
        default_curtain_clicked();
        return false;
	}else if(action=='save'){
		if(!confirm('Are you sure?'))   return false;
        
        $('btn_save').disable().value = 'Saving...';

        ajax_request(phpself,{
			parameters: document.f_v.serialize(),
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='OK'){
					alert(msg);
					$('btn_save').enable().value = 'Save';
					return;
				}
				reload_table(true);
				alert('Save successfully.');
				default_curtain_clicked();
			}
		})
	}
}
{/literal}
</script>
<form method="post" name="f_v" onSubmit="return false;">
	<input type="hidden" name="a" value="save_blocklist">
	<input type="hidden" name="debtor_id" id="debtor_id" value="{$form.debtor_id}">
	<div align="center" width="100%">
		<h3>{$form.debtor}</h3>
	</div>
	<br/>
	<table width="100%">
		<tr>
			<td>&nbsp;</td>
			{foreach from=$branches item=branch}
				<th width="50">{$branch.code}</th>
			{/foreach}
		</tr>

		{foreach from=$type key=tid item=tp}
			<tr>
				<th align="left" nowrap>{$tp}</th>
				{foreach from=$branches item=b}
					{assign var=bid value=$b.id}
					<td align='center'>
						<input type="checkbox" name="block[{$tid}][{$bid}]" value="1" {if $block.$tid.$bid}checked {/if}>
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
	<br />
	<div align="center" width="100%">
		<input type=button value="Save" id="btn_save" onclick="submit_form('save');" />
		<input type=button value="Close" onclick="submit_form('close');" />
	</div>
</form>