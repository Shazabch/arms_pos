{*
11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field
*}

<th>Select Banker</th>
<td>	
<select id=bank name="bank">
{foreach key=key item=item from=$bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
