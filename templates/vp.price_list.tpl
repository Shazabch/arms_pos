{*
7/24/2012 1:49 PM Andy
- Add to show Old Code.

7/31/2012 10:38 AM Andy
- Add sorting/print/export feature for vendor price list.

1/16/2013 3:14 PM Justin
- Added to show stock balance.

1:03 PM 3/28/2015 Andy
- Enhanced to show GST information.
*}

{include file="header.tpl"}

{if !$no_header_footer}
{literal}
<style>
.negative{
	font-weight: bold;
	color: red;
}

.tr_date_total td{
	background-color: #cfcfcf;
}
</style>
{/literal}

<script type="text/javascript">

{literal}
var VENDOR_PRICE_LIST = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
	},
	// function when user click refresh
	submit_form: function(type){
		this.f['submit_type'].value = '';
		if(type == 'excel')	this.f['submit_type'].value = type;
		
		this.f.submit();
	},
	// function when user click print
	print_form: function(){
		window.print();
	}
};
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

{if !$no_header_footer}
<form name="f_a" method="post" class="stdframe noprint">
	<input type="hidden" name="submit_type" value="" />
	
	<b>Sort by</b>
	<select name="order_by">
		{foreach from=$sort_list key=k item=r}
			<option value="{$k}" {if $smarty.request.order_by eq $k}selected {/if}>{$r.label}</option>
		{/foreach}
	</select>
	<select name="order_seq">
		<option value="asc" {if $smarty.request.order_seq eq 'asc'}selected {/if}>Ascending</option>
		<option value="desc" {if $smarty.request.order_seq eq 'desc'}selected {/if}>Descending</option>
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="button" value="Refresh" onClick="VENDOR_PRICE_LIST.submit_form();" />
	<input type="button" value="Print" onClick="VENDOR_PRICE_LIST.print_form();" />
	<button onClick="VENDOR_PRICE_LIST.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
</form>
<script>VENDOR_PRICE_LIST.initialize();</script>
{/if}

{if !$data}
 * No Data
{else}
	<h3>{$report_title}</h3>
	<p>
	<font size="3px" color="red">*</font> indicate the Stock Balance not up to date.
	</p>
	<table width="100%" class="report_table" cellpadding="0" cellspacing="0">
		<thead>
			<tr class="header">
				<th>MCode</th>
				<th>{$config.link_code_name}</th>
				<th>ARMS Code</th>
				<th>Description</th>
				<th>Open Price</th>
				<th>Scale Type</th>
				{if $vp_session.is_under_gst}
					<th>Selling Price Before GST</th>
					<th>GST Code</th>
					<th>GST Amt</th>
					<th>Selling Price After GST</th>
				{else}
					<th>Selling Price</th>
				{/if}
				
				<th>Stock Balance</th>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$data item=r}
				<tr>
					<td>{$r.mcode|default:'-'}</td>
					<td>{$r.link_code|default:'-'}</td>
					<td>{$r.sku_item_code}</td>
					<td>{$r.description|default:'-'}</td>
					<td>{if $r.open_price}Open{else}Fixed{/if}</td>
					<td>{$r.scale_type_label}</td>
					{if $vp_session.is_under_gst}
						<td class="r">{$r.before_tax_price|number_format:2}</td>
						<td align="center">{$r.output_gst_code}@{$r.output_gst_rate}%</td>
						<td class="r">{$r.gst_amt|number_format:2}</td>
						<td class="r">{$r.price_included_gst|number_format:2}</td>
					{else}
						<td class="r">{$r.price|number_format:2}</td>
					{/if}
					<td class="r">{$r.stock_bal|qty_nf} {if $r.changed}<font size="3px" color="red">*</font>{/if}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
{/if}

{include file="footer.tpl"}