{*
8/26/2013 10:51 AM Andy
- Allow user to click on receipt no to view receipt details even deposit have no item.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

2/14/2017 10:49 AM Andy
- Fixed item details wrongly group same receipt_no amount together.
- Change group by receipt_no to group by receipt_ref_no.
*}

{foreach from=$data key=receipt_ref_no item=d}
	<tr bgcolor="#FFF380" class="date_child_{$bid}_{$date}">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td align="center">
			<a onclick="trans_detail('{$d.counter_id}','{$d.cashier_id}','{$d.date}','{$d.pos_id}','{$d.rcv_branch_id}')" class="clickable">{$receipt_ref_no}</a>
		</td>
		<td align="center">{$d.cashier_name|default:'-'}</td>
		<td align="center">{$d.approved_name|default:'-'}</td>
		
		<td align="right">{$d.rcv_amt|number_format:2|ifzero:'-'}</td>
		<td align="right">{$d.used_amt|number_format:2|ifzero:'-'}</td>
		
		{* Refund *}
		<td align="right" class="{if $d.refund_amt>0}negative{/if}">{$d.refund_amt*-1|number_format:2|ifzero:'-'}</td>
		
		{* Cancel Previous *}
		<td align="right" class="{if $d.cancel_amt<0}negative{/if}">{$d.cancel_amt|number_format:2|ifzero:'-'}</td>
	</tr>
{/foreach}
