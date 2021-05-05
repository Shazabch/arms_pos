<h5>{count var=$data} record{if count($data)>1}s{/if}</h5>

<table border="0" cellpadding="4" cellspacing="1" width="100%">

<tr>
<th bgcolor="#DDDDDD" width="40">&nbsp;</th>
<th bgcolor="#CCCCCC">Doc Type</th>
<th bgcolor="#CCCCCC">Doc No</th>
<th bgcolor="#CCCCCC">Created</th>
<th bgcolor="#CCCCCC">User</th>
<th bgcolor="#CCCCCC">Approval Type</th>
<th bgcolor="#CCCCCC">Approvals</th>
</tr>

{if $data}
{foreach from=$data item=i}
<tr onmouseover="this.bgColor='#FFFFCC';" onmouseout="this.bgColor='';">
<td bgcolor="#F3F3F0" nowrap>
	<a href="{$i.view_url}" target="_blank"><img src="ui/view.png" title="View" border="0" /></a>
	<a href="{$i.approval_url}"><img src="ui/chown.png" title="Approve On Behalf" border="0" /></a>
</td>
<td>{$i.module}</td>
<td align="center"><b>{$i.doc_no}</b></td>
<td align="center">{$i.added}</td>
<td align="center">{$i.user}</td>
<td align="center">
	{if $i.approval_order_id eq '1'}Follow Sequence
	{elseif $i.approval_order_id eq '2'}All (No Sequence)
	{elseif $i.approval_order_id eq '3'}Anyone
	{else}&nbsp;
	{/if}
</td>
<td>{$i.approvals}</td>
</tr>
{/foreach}

{else}
<td colspan="7" align="center">No record found</td>
{/if}

</table>
