{*
2/24/2010 11:04:11 AM Andy
- add print function

7/15/2011 2:59:35 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

1/7/2013 9:58 AM Justin
- Enhanced to show date with times.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}

{include file='header.print.tpl'}

<body onload="window.print()">
{literal}
<style>

</style>
{/literal}

{if !$pos}
  {if $smarty.request.submit && !$err}-- No data --{/if}
{else}

{assign var=row_counter value=0}

  {foreach from=$pos key=bid item=b}
    {assign var=branch_total value=0}
    {assign var=branch_transaction_count value=0}
    
    <h2><div align="center">
    {$branches.$bid.description} {if $branches.$bid.company_no}({$branches.$bid.company_no}){/if}<br />
    Members Sales History {$smarty.request.date_from} to {$smarty.request.date_to}</div></h2>
    
    <div>Member Name: {$member_info.name} &nbsp;&nbsp;&nbsp;&nbsp; {$config.membership_cardname} No: {$member_info.card_no} &nbsp;&nbsp;&nbsp;&nbsp; IC No: {$member_info.nric}</div>
    <table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">
    <tr class="header">
      <th>Receipt No</th>
      <th>Counter</th>
      <th>Date</th>
      <th colspan="3">Amount</th>
    </tr>
    {foreach from=$b key=date item=c}
      {foreach from=$c key=cid item=p}
        {foreach from=$p key=pid item=r}
          {assign var=row_counter value=$row_counter+1}
          {assign var=pos_amt value=0}
          <tr class="receipt_header">
            <td>{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.receipt_no}</td>
            <td>{$r.network_name}</td>
            <td>{if $r.pos_time|date_format:'%Y-%m-%d' ne $r.date}{$r.date} <span class="small" style="color:blue;">({$r.pos_time})</span>{else}{$r.pos_time}{/if}</td>
            <td colspan="3" align="right">{$r.amount|number_format:2}</td>
          </tr>
          {assign var=branch_total value=$branch_total+$r.amount}
          {assign var=branch_transaction_count value=$branch_transaction_count+1}
        {/foreach}
      {/foreach}
    {/foreach}
      <tr class="header">
        <th colspan="5" align="left">Total: {$branch_transaction_count|number_format} transaction</th>
        <th align="right">{$branch_total|number_format:2}</th>
      </tr>
    </table>
  {/foreach}  
{/if}

<br />
<div style="text-align:center;">Printed by : {$sessioninfo.name}   Date : {$smarty.now|date_format:'%d/%m/%Y %H:%M'}</div>
