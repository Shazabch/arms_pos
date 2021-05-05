{*
10/29/2010 11:10:05 AM Justin
- Added the type checking to set the points either become positive or negative.

*}

<h1>Redemption List</h1>
<div style="overflow: auto; height: 350px;">
{if $type eq 'REDEEM'}
	{assign var=position value=-1}
{else}
	{assign var=position value=1}
{/if}
<table class="tb" cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr class="header" style="background: rgb(255, 238, 153);">
	    <th>Redemption No</th>
	    <th>Branch</th>
	    <th>Card No</th>
	    <th>NRIC</th>
	    <th>Qty</th>
	    <th>Points</th>
	</tr>
	{foreach from=$mr_list item=r}
	    <tr>
	        <td><a href="membership.redemption_history.php?a=view&id={$r.id}&branch_id={$r.branch_id}" target="_blank">{$r.redemption_no}</a></td>
	        <td>{$r.branch_code}</td>
	        <td>{$r.card_no}</td>
	        <td>{$r.nric}</td>
	        <td class="r">{$r.total_qty|number_format}</td>
	        <td class="r">
				{$r.total_pt_need*$position|number_format}
			</td>
	    </tr>
	{/foreach}
</table>
</div>
