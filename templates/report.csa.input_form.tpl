{*
8/9/2011 4:35:28 PM Alex
- remove rebate column

3/8/2012 2:17:37 PM Alex
- change title GRN Adjustment to GRN / GRA Adjustment / Write Off / IBT
*}

<input type='hidden' name=sku_type value='{$form_type}'>
<input type='hidden' name=root_id value='{$root_id}'>
<input type='hidden' name=dept_id value='{$dept_id}'>
<input type='hidden' name=vendor_id value='{$vendor_id}'>
<table border=0 class="report_table" id="report_tbl" align="center">
	<tbody id=form_input_data>
	{if $form_type=='OUTRIGHT'}
		<tr class='header'>
		    <td></td>
		    <th>GRN / GRA<br>Adjustment / Write Off / IBT</th>
		    <th>IDT</th>
		    <th>Other Income / Rebate</th>
		</tr>
		<tr>
		    <td><b>Cost Price</b></td>
		    <td align="absmiddle">{input class='r' size="10" name="vgrn_o_cost" onchange="this.value=round(this.value,2);" value=''}</td>
		    <td align="absmiddle">{input class='r' size="10" name="vidt_o_cost" onchange="this.value=round(this.value,2);" value=''}</td>
{*		    <td class="hide" rowspan=2>{input class='r' size="10" name="vr_o_selling" onchange="this.value=round(this.value,2);" value=''}</td>*}
		    <td rowspan=2 align="absmiddle">{input class='r' size="10" name="voi_o_selling" onchange="this.value=round(this.value,2);" value=''}</td>
		</tr>
		<tr>
		    <td><b>Selling Price</b></td>
		    <td align="absmiddle">{input class='r' size="10" name="vgrn_o_selling" onchange="this.value=round(this.value,2);" value=''}</td>
		    <td align="absmiddle">{input class='r' size="10" name="vidt_o_selling" onchange="this.value=round(this.value,2);" value=''}</td>
		</tr>
	{else}
		<tr class='header'>
		    <td></td>
		    <th>Actual Sales</th>
		    <th>GP(%)</th>
		    <th>Other Income / Rebate</th>
		</tr>
		<tr>
		    <td><b>Cost Price</b></td>
			<td><input class='r' size="10" id='vacs_c_cost_id' name="vacs_c_cost" disabled></td>
		    <td rowspan=2 align="absmiddle">{input class='r' size="10" id="vacs_c_gp_id" name="vacs_c_gp" onchange="this.value=round(this.value,2);vendor_form_calculate(this);" value=''}</td>
{*		    <td class="hide" rowspan=2>{input class='r' size="10" name="vr_c_selling" onchange="this.value=round(this.value,2);" value=''}</td>*}
		    <td rowspan=2 align="absmiddle">{input class='r' size="10" name="voi_c_selling" onchange="this.value=round(this.value,2);" value=''}</td>
		</tr>
		<tr>
		    <td><b>Selling Price</b></td>
			<td align="absmiddle"><input class='r' size="10" id='vacs_c_selling_id' name="vacs_c_selling" disabled></td>
		</tr>
	{/if}
	</tbody>
</table>

<div id='replace_button' style='position:absolute;bottom:10px;left:150px'>
	<input type='button' onclick="replace_data()" value='Update Data'> &nbsp;&nbsp;&nbsp;&nbsp;
	<input type='button' onclick="if (confirm('Are you sure to close this?')) $('form_vendor_id').hide();" value='Close'>
</div>

<script>
	load_form_data();
	
	{if $form_type=='OUTRIGHT'}
        $('form_vendor_id').style.width=480;
        $('form_vendor_id').style.height=170;
	{else}
        $('form_vendor_id').style.width=480;
        $('form_vendor_id').style.height=150;
//        $('replace_button').style.left=180;
	{/if}
	
</script>



{*
vgrn_o_cost,1210,1
vgrn_o_selling,1210,1

vidt_o_cost,1210,1
vidt_o_selling,1210,1

vr_o_selling,1210,1

voi_o_selling,1210,1

//////
vacs_c_gp,1210,125

vr_c_selling,1210,125

voi_c_selling,1210,125
*}
