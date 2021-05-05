{*
7/15/2011 2:00:47 PM Andy
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
.member_box{
	width:48%;
	float: left;
	height:150px;
	border: 1px solid black;
	padding: 2px;
	margin: 2px;
}

{/literal}
</style>
<body onload="window.print()">
{/if}

<!-- print sheet -->
<div class=printarea>
<div style="text-align:right">Page {$page} of {$totalpage}</div>


{foreach from=$items item=r name=f}
	<div class="member_box">
		<span class="large"><b>{$r.address|nl2br}<br />{$r.postcode} {$r.city}<br /><br />Attn: {$r.name}</b>
		</span>
		<br /><br />
		<b>Member No: {$r.card_no|default:'-'}</b><br />
		(Member Since : {$r.issue_date|date_format:'%Y-%m-%d'|default:'-'} Exp: {$r.next_expiry_date|date_format:'%Y-%m-%d'|default:'-'})
	</div>

{/foreach}
</div>
