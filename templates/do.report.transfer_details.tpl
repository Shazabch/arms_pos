{*

*}

<h3>
{if $smarty.request.do_type eq 'transfer'}Transfer DO
{elseif $smarty.request.do_type eq 'credit_sales'}Credit Sales DO
{elseif $smarty.request.do_type eq 'open'}Cash Sales DO{/if}
 - {$branch_info.code}</h3>

<div style="height:330px;">
<table border=0 cellpadding=4 cellspacing=1 width="100%">
<tr bgcolor="#ebe8d6">
	<th>&nbsp;</th>
	<th>DO No.</th>
	<th>Date</th>
	<th>Create By</th>
	<th>Amount</th>
</tr>
<tbody style="{if count($do_list)>=13}height:300px;{/if}overflow-x:hidden;overflow-y:auto;">
	{foreach from=$do_list item=do name=f}
	    <tr align="center" bgcolor="{cycle values='#eeeeee,#ffffff'}">
	        <td>{$smarty.foreach.f.iteration}.</td>
	        <td><a href="do.php?a=view&id={$do.id}&branch_id={$do.branch_id}" target="_blank">{$do.do_no}</a></td>
	        <td>{$do.do_date}</td>
	        <td>{$do.u}</td>
	        <td>{$do.total_amount|number_format:2}</td>
	    </tr>
	{/foreach}
</tbody>
</table>
</div>
<p align="center"><input type="button" value="Close" onClick="default_curtain_clicked();" /></p>
