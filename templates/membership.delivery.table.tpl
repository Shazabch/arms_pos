{*
3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.
*}

{*{$pagination}*}
<table width="100%" cellspacing="1" cellpadding="2" border="0" style="padding:1px;border:1px solid #000;">
  <tr bgcolor="#ffee99">
    <th></th>
    <th>Receipt No.</th>
    <th>Receipt Date</th>
    <th>Status</th>
    <th>Delivered Date</th>
    <th>Remark</th>
    <th>Receipient</th>
    <th>Delivery Address</th>
    <th>Contact</th>
  </tr>
  {foreach from=$list item=l}
  <tr bgcolor="{cycle values="#ffffff,#eeeeee"}">
    <td><a href="javascript:void(0);" onclick="javascript:d_print({$l.counter_id},{$l.branch_id},{$l.pos_id},'{$l.date}','{$l.type}');"><img border="0" title="Print DO Draft" src="ui/print.png"></a></td>
    <td align="center">{receipt_no_prefix_format branch_id=$l.branch_id counter_id=$l.counter_id receipt_no=$l.receipt_no}</td>
    <td align="center">{$l.pos_time|date_format:"%Y-%m-%d"}</td>
    <td align="center">{if $l.is_delivery}Delivered{else}-{/if}</td>
    <td align="center">{if $l.delivery_date eq "0000-00-00 00:00:00"}-{else}{$l.delivery_date|date_format:"%Y-%m-%d"}{/if}</td>
    <td>{$l.remark|nl2br}</td>
    <td>{$l.delivery_name}</td>
    <td>{$l.delivery_address}</td>
    <td>{$l.delivery_phone}</td>
  </tr>
  {foreachelse}
  <tr>
    <td colspan="8" align="center"><b>NO RECORD</b></td>
  </tr>
  {/foreach}
</table>

