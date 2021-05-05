{assign var=selected value=$vvc.selected_bank}
<tr>
	<td width=110>
	<b>Select Bank</b>
	</td>
	
	<td width=150>
	<select id=bank name="bank" onchange="refresh_bank(this);">
  	{foreach key=key item=item from=$vvc.bank.bank_name}
	<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
	{/foreach}
	</select>	
	</td>
	
	<!--
	<th align=left>Banker Code</th><td align=left><input id=banker_code name=banker_code readonly value="{$vvc.bank.banker_code.$selected}" size=12></td>
	-->
	<th align=left>Banker Code : </th>
	<td align=left width=100>
	<span id=banker_code>{$vvc.bank.banker_code.$selected}</span>
	<input type=hidden name=banker_code value="{$vvc.bank.banker_code.$selected}">		
	<th align=left id=td_acct_code>Acct Code : </th>
	<td align=left id=td_acct_code_1 width=120>
	{if $form.voucher_type ne '4'}
		{if $form.total_acct_code>1}
		<select id=acct_code_1 name="acct_code">
	  	{foreach key=k item=a_c from=$vvc.acct_code}
		<option value={$a_c} {if $form.acct_code==$a_c}selected{/if}>{$a_c}</option>
		{/foreach}
		</select>
		{if $form.alert}
		<script>alert("{$form.vendor}"+' have more than 1 acct code.');</script>
		{/if}
		{else}
		<!--
		<input id=acct_code_1 name=acct_code size=8 value="{$vvc.acct_code.0}" readonly>
		-->
		<span id=acct_code_1>{$vvc.acct_code.0}</span>
		<input type=hidden name=acct_code value="{$vvc.acct_code.0}">			
		{/if}
	{/if}
	</td>
</tr>
{literal}
<script>
if(g_type=='4'){
	$('td_acct_code').style.display='none';
	$('td_acct_code_1').style.display='none';
}

</script>
{/literal}
