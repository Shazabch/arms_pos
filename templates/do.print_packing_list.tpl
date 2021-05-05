{*
6/1/2018 1:35 PM HockLee
- New template for print packing list.

8/30/2018 4:00PM HockLee
- Fixed css error.
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
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
.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}
div.info {
	font-size: 16px;
	text-align: left;
}

{/literal}
</style>

<script type="text/javascript">
var packing_list = '{$form.packing}';

var doc_no = '{$form.do_no}';
if (doc_no == '') doc_no = '{$form.prefix}{$form.id|string_format:"%05d"}(DD)';

{literal}
function start_print(){
	if(packing_list == 0){
		alert('The DO has not been approved or no packing information.\nOnly approved DO can be input packing information.');
		window.close();
	}
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{foreach from=$total_carton key=item_index1 item=carton}
{foreach from=$do_items key=item_index item=r name=i}
{if $r.pack_carton eq $carton}
{section name=foo start=0 loop=$r.pack_carton}
<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="small">
	<tr>
		<td colspan="3" width="100%" style="font-size: 80px;text-align: center;">
			Packing List
		</td>
		<td rowspan=2 align=right>
			<table class="xlarge" border="0">
				<tr>
					<td colspan=2  nowrap>
						<div class="info">
							<b>
							Carton(s): {$r.pack_carton}
							<br>
							Net Weight: {$r.pack_weight} kg
							</b>
						</div>
						<br>
					</td>
				</tr>
				<tr bgcolor="#cccccc" height=22>
					<td nowrap>DO No.</td>
					<td nowrap>{$form.do_no}</td>
				</tr>
				{if !$config.do_printing_allow_hide_date or !$no_show_date}
					<tr height=22>
						<td nowrap>DO Date</td>
						<td nowrap>{$form.do_date|date_format:$config.dat_format}</td>
					</tr>
				{/if}
				<tr height=22>
					<td nowrap>PO No.</td>
					<td nowrap>{$form.po_no|default:"--"}</td>
				</tr>
				{if $form.offline_id}
					<tr height=22 bgcolor="#cccccc">
						<td nowrap>Offline ID</td>
						<td nowrap>#{$form.offline_id|string_format:"%05d"}</td>
					</tr>
				{/if}
				<tr bgcolor="#cccccc" height=22>
					<td nowrap>Printed By</td>
					<td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td>
				</tr>
				<tr bgcolor="#cccccc" height=22>
					<td colspan=2 align=center>{$page}</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="120px">
		<tr>
			<td valign=top width=50% style="border:1px solid #000; padding:5px">
			<h4>From </h4>
			<b>{$from_branch.description}</b><br>
			{$from_branch.address|nl2br}<br>
			Tel: {$from_branch.phone_1|default:"-"}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}<br>Fax: {$from_branch.phone_3}{/if}
			</td>
			
			{if !$form.do_branch_id && $form.open_info.name}
				<td valign=top style="border:1px solid #000; padding:5px">
				<h4>To</h4>
				<b>{$form.open_info.name}</b><br>
				{$form.open_info.address}<br>
				</td>
			{elseif $form.do_type eq 'credit_sales'}
				<td valign=top style="border:1px solid #000; padding:5px">
				<h4>To</h4>
				<b>{$form.debtor_description}</b><br>
				{$form.debtor_address}<br>
				Tel: {$form.debtor_phone|default:'-'}<br>
				Terms: {$form.debtor_term|default:'-'}<br>
				</td>
			{else}
				<td valign=top width=50% style="border:1px solid #000; padding:5px">
				<h4>Deliver To</h4>
				<b>{$to_branch.description} </b><br>
				{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}
				<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
				</td>
			{/if}

		</tr>
		</table>
		</td>
	</tr>
</table>

</div>
{/section}
{/if}
{/foreach}
{/foreach}
