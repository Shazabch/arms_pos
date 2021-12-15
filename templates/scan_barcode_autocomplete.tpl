	{*
12/14/2012 2:17:00 PM Fithri
- remove config checking on scan barcode

3/19/2018 2:11 PM Justin
- Enhanced to show/hide GRN barcoder option base on config.

6/11/2019 5:29 PM Andy
- Enhanced to can have alternative "Add" button value.

06/24/2020 10:48 AM Sheila
- Updated button css

*}

{literal}
<script>
function grn_barcode_type_changed(){
	$('grn_barcode').select();
}

/*
function add_grn_barcode_item(ele){
	//create own function at own file
}
*/
</script>
{/literal}
{if !$add_action}
	{assign var=add_action value="add_grn_barcode_item($('grn_barcode').value);"}
{/if}
{if !$no_need_table}
<div style="padding:3px;">
	<table>
{/if}
		{if $need_hr_top}
			<tr><td colspan=2><hr noshade size=1></td></tr>
		{/if}
		<tr>
			<b class="form-label">Scan Barcode </b>
			
			<td> 
				<div class="row">
					<div class="col">
						<div class="form-inline">
						<input class="form-control" id="grn_barcode" name="grn_barcode" onkeypress="if(event.keyCode==13){$add_action}" /> 
				{if !$no_button}&nbsp;
				<input class="addbutton btn btn-primary fs-08" type="button" value="{$_add_value|default:'Add'}" onclick="{$add_action}" />{/if}
						</div>
					</div>
				</div>
				<span id="scan_barcode_loading_id"></span>
			</td>
		</tr>
		{if !$no_options}
		<tr>
		
			<td>
				{if $config.enable_grn_barcoder}
				<input type="radio" name="grn_barcode_type" value="0" checked onclick="grn_barcode_type_changed();" /> GRN Barcoder &nbsp;&nbsp;&nbsp;&nbsp;
				{/if}
				<input type="radio" name="grn_barcode_type" value="1" {if !$config.enable_grn_barcoder}checked{/if} onclick="grn_barcode_type_changed();" />
				 <spn class="fs-09">ARMS Code / MCode / Art.No / {$config.link_code_name}</spn> &nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		{/if}
		{if $need_hr_bottom}
			<tr><td colspan=2></td></tr>
		{/if}
{if !$no_need_table}
	</table>
</div>
{/if}
{if $need_hr_out_bottom}
	
{/if}
