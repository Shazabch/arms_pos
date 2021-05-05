{*
7/15/2011 1:53:47 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.
*}

{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.membership_listing_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}

{/literal}
</style>
{*<body onload="window.print()">*}
{/if}

<!-- print sheet -->
<div class="printarea">
Page {$page} of {$totalpage}
<table width="100%" cellspacing="0" cellpadding="4" class="tb" border="0">
	<tr bgcolor="#cccccc">
	    <th>Branch</th>
	    {foreach from=$trade_discount_type item=t}
	        <th>{$t.code}</th>
	    {/foreach}
	</tr>
	{foreach from=$branches item=b}
	    {assign var=bid value=$b.id}
	    <tr>
	        <td>{$b.code}</td>
	        {foreach from=$trade_discount_type item=t}
	            {assign var=tid value=$t.id}
	            <td align="center">{$data.$bid.$tid|number_format:1|ifzero:'&nbsp;':'%'}</td>
	        {/foreach}
	    </tr>
	{/foreach}
</table>
<br>
</div>
