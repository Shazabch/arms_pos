{*
9/4/2018 5:09 PM Andy
- Enhanced "Print Full Tax Invoice" to able to print non-gst transaction.

9/28/2018 5:14 PM Andy
- Enhanced "Print Official Receipt" to able to print non-gst transaction.

10/12/2020 4:14 PM William
- Added new tax checking.
*}

<form id="data-form-remark" method="post" enctype="multipart/form-data" onSubmit="return false;">
	<input type="hidden" name="is_gst" value="{$data.got_tax}" />
	
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	<table>
	  <tr>
		<td valign=top>
		  <table width="100%">
			{foreach from=$data.remark_list key=remark_key item=v}
			 <tr>
			  <td><b>{$remark_key}</b></td>
			  <td>
				<input type="text" name="remark[{$remark_key}]" value="{$v}" {if $data.got_tax}class="required"{/if} title="{$remark_key}" />
				{if $data.got_tax}
					<img src="ui/rq.gif" align="absbottom" title="Required Field">
				{/if}
			  </td>
			</tr>
			{/foreach}
		  </table>
		</td>
	  </tr>
	  <tr>
		<td align="center">
			{if $config.full_tax_invoice_print_official_receipt}
				<button type="button" name="print_type" onClick="PRINT_FULL_TAX.print_start('print_official_receipt');">Print Official Receipt</button>
			{/if}
			<button type="button" name="print_type" onClick="PRINT_FULL_TAX.print_start('print_invoice');">Print Invoice</button>
			<button type="button" onclick="javascript:void(PRINT_FULL_TAX.close_remark())">Close</button>
		</td>
	  </tr>
	</table>

</form>