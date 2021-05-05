{*
6/11/2018 4:22 PM HockLee
- new template
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

.driver_width {
	float: left;
	width: 15%;
}

{/literal}
</style>


{literal}
<script type="text/javascript">
var doc_no = 'DO Assignment Note';
function start_print(){
	document.title = doc_no;
	window.print();
}
</script>
{/literal}

<body onload="start_print();">
{/if}



{foreach from=$do_assign item=assign key=plate_no}
	<!-- print sheet -->
	<div class="printarea">
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td>
					<h1>
						DO Assignment Note {if $batch_code}(Batch Code: {$batch_code}){/if}<br>
						{if $plate_no}Plate No: {$plate_no}{/if}
					</h1>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<table width=100% cellspacing=5 cellpadding=0 height="120px">
				<tr>
					{foreach from=$assign item=transporter}
					<td valign=top width=50% style="border:1px solid #000; padding:5px">
					<h4>Consign To </h4>
					{$transporter.company_name}
					<br>
					{$transporter.address|nl2br}
					<br>
					<span class="driver_width"><b>Tel</b>: {$transporter.phone_1}{if $transporter.phone_2} / {$transporter.phone_2}{/if}</span>
					<span class="driver_width"><b>Fax</b>: {$transporter.fax}</span>
					<br>
					<br>
					<span class="driver_width"><b>Driver Name</b>: {$transporter.driver_name}</span>
					<span class="driver_width"><b>Driver IC No</b>: {$transporter.driver_ic_no}</span>
					<span class="driver_width"><b>Tel</b>: {$transporter.driver_phone_1}{if $transporter.$driver.phone_2} / {$transporter.driver_phone_2}{/if}</span>			
					</td>		
					{/foreach}
				</tr>
				</table>
				</td>
			</tr>
		</table>

		<br>

		<table border="1" cellspacing="0" cellpadding="4" width="100%">
			<tr bgcolor="#cccccc">
				<th width="5">&nbsp;</th>
				<th>DO Date</th>
				<th>Do No.</th>
				<th>Debtor Code</th>
				<th>Debtor Name</th>
				<th>Area</th>
				<th>Total Carton</th>
				<th>Total Weight (kg)</th>
				<th>Mark</th>
			</tr>
			{assign var=counter value=0}
			{assign var=item_no value=0}

			{foreach from=$transporter.do key=item_index item=r name=i}
				<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
					<td align="center" nowrap>
						{assign var=item_no value=$item_no+1}
						{$item_no}			
					</td>
					<td align="center" nowrap>{$r.do_date|date_format:$config.dat_format}</td>
					<td align="center" nowrap>
							{$r.do_no|default:'&nbsp;'}
					</td>
					<td>{$r.code}</td>
					<td width = "30%">{$r.description}</td>	
					<td>{$r.area}</td>	
					<td align="right">{$r.ttl_carton|default:'-'}</td>
					<td align="right">{$r.ttl_weight|default:'-'}</td>
					<td align="right">&nbsp;</td>
				</tr>
			{/foreach}

				<tr class="total_row">
					<th align="right" colspan="6">Total</th>
					<th align="right">{$transporter.sum_ttl_carton|default:'-'}</th>
					<th align="right">{$transporter.sum_ttl_weight|default:'-'}</th>
					<th>&nbsp;</th>
				</tr>

		</table>
		<p align="center" class="small">** This document is for reference purpose only **</p>  
	</div>
{/foreach}



