{*
05/25/2010 05:41:51 PM yinsee
change the title to "Export SKU Items"

5/10/2011 6:05:41 PM Alex
- add show example of output

3/16/2012 12:09:30 PM Andy
- Add Export to excel format.
- Add Vendor and Brand filter.

6/15/2012 12:14:00 PM Andy
- Change some label word in filter settings.

3/13/2013 5:44 PM Justin
- Enhanced to change some of the wording errors.

1/6/2014 10:56 AM Justin
- Enhanced to add notes for Item Last Update and Price Change dates.

04/14/2016 16:25 Edwin
- Added new fields: uom code, fraction, input tax, output tax, inclusive tax, scale type, active, brand, vendor, parent arms code, parent artno, parent mcode in excel and txt.
- Modified on item last update date caption

8/24/2016 4:21 PM Andy
- Change advance settings description.

1/6/2017 4:38 PM Andy
- Change Export Excel to Export CSV.

3/20/2017 13:01 PM Qiu Ying
- Enhanced to add product description column before receipt description

7/4/2017 9:00 AM Qiu Ying
- Enhanced to filter parent & child

8/15/2019 11:05 AM William
- Remove department search filter.
- Enhanced to add can select by category search filter and add new export column "min","max","moq","notify username".

03/24/2020 03:07 PM Sheila
- Added id and class name to table for the css

6/23/2020 10:30 AM Sheila
- Updated button css

9/15/2020 1:09 PM William
- Enhanced to add new column "Additional Description".
*}
{include file=header.tpl}
{literal}
<style>
b.menu_spacing{
	padding-right: 5px;
}
span.menu_spacing{
	padding-right: 25px;
}
</style>
{/literal}
<script>
var sku_enable_additional_description = int('{$config.sku_enable_additional_description}');
{literal}
function bsel(v)
{
	var s = v.split(",");
	document.f_a.branch_id.value = s[0];
	document.f_a.items_last_update.value = s[1];
	document.f_a.price_last_update.value = s[2];
}

function hide_show_category(){

	$$(".dept").each(function (ele,index){
		if ($("show_cat_id").checked)
			$(ele).show();
		else
			$(ele).hide();
	});
}

function get_data(type){
	var checkbox_all_category = $('all_category');
	var input_category_id = $('category_id');
	if(checkbox_all_category.checked == false && input_category_id.value == ""){
		alert("Please select the category.");
		return false;
	}
	document.f_a['export_type'].value=type;
	document.f_a.submit();
}

function hide_export_txt_btn(){
	var checkbox_additional_desc = $('checkbox_additional_desc');
	var span_export_txt = $('span_export_txt');
	$$(".additional_desc").each(function (ele,index){
		if(checkbox_additional_desc.checked == true){
			$(ele).show();
			span_export_txt.hide();
		}else{
			$(ele).hide();
			span_export_txt.show();
		}
	});
}
</script>
{/literal}
<h1>Export SKU Items</h1>
{if $status}<p>{$status}</p>{/if}

<iframe style="display:none;" name="if1">
</iframe>

<form name="f_a" target="if1" onSubmit="return false;" class="stdframe">
<input type=hidden name=a value=export >
<input type=hidden name=branch_id value="{$sessioninfo.branch_id}" />
<input type="hidden" name="export_type" />

<span class="menu_spacing">
<b class="menu_spacing">Branch</b>
	{if $BRANCH_CODE eq 'HQ'}
		<select name=branch_sel onchange="bsel(this.value)">
		{foreach from=$branch item=b}
			<option value="{$b.id},{$b.update1},{$b.update2}" {if $b.id eq $sessioninfo.branch_id}selected {/if}>{$b.code}</option>
		{/foreach}
		</select>
	{else}
		<input readonly value="{$BRANCH_CODE}">
	{/if}
</span>
<span class="menu_spacing">
{*<b class="menu_spacing">Department</b>
	<select name=dept>
		<option value="all">-- All --</option>
		{foreach from=$dept item=r}
			<option value="{$r.id}" {if $smarty.request.dept eq $r.id} selected {/if}>{$r.description}</option>
		{/foreach}
	</select>
</span>*}
<span>
	{include file='category_autocomplete.tpl' all=1  allow_select_line=1 skip_dept_filter=1}
</span>
<br /><br />
<span class="menu_spacing">
<b class="menu_spacing">Vendor</b>
	<select name="vendor_id">
		<option value="">-- All --</option>
		{foreach from=$vendors item=r}
			<option value="{$r.id}">{$r.description}</option>
		{/foreach}
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Brand</b>
	<select name="brand_id">
		<option value="">-- All --</option>
		<option value="0">UNBRANDED</option>
		{foreach from=$brands item=r}
			<option value="{$r.id}">{$r.description}</option>
		{/foreach}
	</select>
</span>
<br><br>
<span class="menu_spacing">
<b class="menu_spacing">Input Tax</b>
	<select name="input_tax">
		<option value="">-- All --</option>
		{foreach from=$input_tax_list key=rid item=r}
			<option value="{$r.id}">{$r.code} ({$r.rate}%)</option>
		{/foreach}
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Output Tax</b>
	<select name="output_tax">
		<option value="">-- All --</option>
		{foreach from=$output_tax_list key=rid item=r}
			<option value="{$r.id}">{$r.code} ({$r.rate}%)</option>
		{/foreach}
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Selling Price Inclusive Tax</b>
	<select name="inclusive_tax">
		<option value="">-- All --</option>
		<option value="yes">YES</option>
		<option value="no">NO</option>
	</select>
</span>
<br><br>
<span class="menu_spacing">
<b class="menu_spacing">SKU Type</b>
	<select name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=r}
		    <option value="{$r.sku_type}">{$r.sku_type}</option>
		{/foreach}
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Scale Type</b>
	<select name="scale_type">
		<option value="">-- All --</option>
		{foreach from=$scale_type_list key=st_value item=st_name}
		{if $st_value >= 0}
			<option value="{$st_value}">{$st_name}</option>
		{/if}
		{/foreach}
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Active</b>
	<select name="active">
		<option value="">-- All --</option>
		<option value="1">Yes</option>
		<option value="0">No</option>
	</select>
</span>
<span class="menu_spacing">
<b class="menu_spacing">Parent Child?</b>
	<select name="parent_child_filter">
		<option value="">-- All --</option>
		<option value="1">Yes</option>
		<option value="0">No</option>
    </select>
</span>
<br><br>
<b class="menu_spacing">Price Markup%</b><span class="menu_spacing"><input size=3 name="price_markup"></span>
<b class="menu_spacing">Cost Markup%</b><span class="menu_spacing"><input size=3 name="cost_markup"></span>
<br><br>
<table>
	<td><h4>Advanced Settings</h4></td>
	<tr><td><b>Items Last Update Date</b> (yyyy-mm-dd)</td><td><input name=items_last_update size=10 value="{$lastid[0]}"> (left empty will not export any items by last update, put '0' will export all items)</td></tr>
	<tr><td><b>Price Change Date</b> (yyyy-mm-dd)</td><td><input name=price_last_update size=10 value="{$lastid[1]}"> (left empty or zero will not export any items with price change)</td></tr>
	<tr><td><label for="show_cat_id"><b>Show Category</b></label></td><td><input type="checkbox" name="show_cat" id="show_cat_id" onclick="hide_show_category()"></td></tr>
	{if $config.sku_enable_additional_description}
	<tr><td><label for="show_cat_id"><b>Show Additional Description</b></label></td><td><input type="checkbox" name="show_additional_desc" id="checkbox_additional_desc" onclick="hide_export_txt_btn()"></td></tr>
	{/if}
</table>
<span>
	<span id="span_export_txt" class="menu_spacing">
		<input class="btn btn-primary" type="button" value="Get SKU by TXT" onClick="get_data('txt');" />
	</span>
	<input class="btn btn-primary" type="button" value="Get SKU by CSV" onClick="get_data('csv');" />
</span>
</form>

<br><br>&nbsp;&nbsp; <b>Note:</b> Below is the example of output. The first line title is not included in the output format (TXT).
<p class="table-parent-div">
<table id="tbl-export-sku" class="tbl-general" cellpadding="5px">
<tr style="background-color:#dddddd;">
	<th>SKU ITEM ID</th> 
	<th>ARMS CODE</th> 
	<th>ARTNO</th>
	<th>MCODE</th>
	<th>BARCODE</th>
	<th>PRODUCT DESCRIPTION</th> 
	<th>RECEIPT DESCRIPTION</th> 
	<th>SELLING PRICE</th>
	<th>COST PRICE</th>
	<th>PRICE TYPE</th>
	<th>SKU TYPE</th>
	<th class="dept">DEPARTMENT</th>
	<th class="dept">THIRD LEVEL OF CATEGORY</th> 
	<th>SKU / PRC</th>
	<th>UOM CODE</th>
	<th>FRACTION</th>
	<th>INPUT TAX</th>
	<th>OUTPUT TAX</th>
	<th>SELLING PRICE INCLUSIVE TAX</th>
	<th>SCALE TYPE</th>
	<th>ACTIVE</th>
	<th>BRAND</th>
	<th>VENDOR</th>
	<th>STOCK REORDER MIN QTY</th>
	<th>STOCK REORDER MAX QTY</th>
	<th>STOCK REORDER MOQ QTY</th>
	<th>STOCK REORDER NOTIFY USER NAME</th>
	<th>PARENT ARMS CODE</th>
	<th>PARENT ARTNO</th>
	<th>PARENT MCODE</th>
	{if $config.sku_enable_additional_description}<th class="additional_desc">ADDITIONAL DESCRIPTION</th>{/if}
</tr>
<tr>
	<td>73994</td>     
	<td>280572570000</td>        
	<td>SFSF9800105</td>         
	<td>2322030100122</td>       
	<td>232203010012 </td>
	<td>FOREST TARGET MINI BRIEFCASE</td>
	<td>FOREST TARGET MINI BRIEF</td>                
	<td>19.90</td>     
	<td>17.91 </td>    
	<td>B5</td>        
	<td>CONSIGN</td>   
	<td class="dept">SPORT</td>
	<td class="dept">SPORT WEAR</td>                    
	<td>SKU</td>
	<td>EACH</td>
	<td>1</td>
	<td>TX (6%)</td>
	<td>SR (6%)</td>
	<td>YES</td>
	<td>NO</td>
	<td>YES</td>
	<td>AMBER</td>
	<td>TWINLIGHT SDN BHD</td>
	<td>10</td>
	<td>20</td>
	<td>0</td>
	<td></td>
	<td>280572570000</td>
	<td>SFSF9800105</td>
	<td>2322030100122</td>
	{if $config.sku_enable_additional_description}<td class="additional_desc">OUTRIGHT ITEM</td>{/if}
<tr>
	<td>285204</td>    
	<td>281144810024</td>        
	<td>TP1</td>                 
	<td>&nbsp;</td>                    
	<td></td>                   
	<td>"PAID" TAPE 18MMX 50M RED & YELLOW</td>          
	<td>"PAID" TAPE 18MMX 50M RED & YE</td>          
	<td>2.10</td>      
	<td>2.20</td>    
	<td>&nbsp;</td>             
	<td>OUTRIGHT</td>  
	<td class="dept">ADMIN</td>                         
	<td class="dept">TAPE</td>
	<td>PRC</td>
	<td>CTN24</td>
	<td>24</td>
	<td>BL(6%)</td>
	<td>SR(6%)</td>
	<td>NO</td>
	<td>FIXED PRICE</td>
	<td>NO</td>
	<td>SCOTHY</td>
	<td>STATIONY SDN BHD</td>
	<td></td>
	<td></td>
	<td>0</td>
	<td>Alex</td>
	<td>281144810023</td>
	<td>TYR15</td>
	<td>3284852965</td>
	{if $config.sku_enable_additional_description}<td class="additional_desc"></td>{/if}
</tr>
</table>
</p>

<!--iframe name=_ifr width=1 height=1 style="visibility:hidden"></iframe-->
{include file=footer.tpl}
<script>
	hide_show_category();
	if(sku_enable_additional_description)  hide_export_txt_btn();
</script>