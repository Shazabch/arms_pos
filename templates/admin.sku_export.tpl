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
<div class="container-fluid">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Export SKU Items</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div> 
</div>

<div class="container-fluid">
	<div class="card mx-3">
		<div class="card-body">
					{if $status}<p>{$status}</p>{/if}

		<iframe style="display:none;" name="if1">
		</iframe>

		<form name="f_a" target="if1" onSubmit="return false;" class="stdframe">
		<input type=hidden name=a value=export >
		<input type=hidden name=branch_id value="{$sessioninfo.branch_id}" />
		<input type="hidden" name="export_type" />

		<span class="menu_spacing">
		<label class="menu_spacing form-label mt-2 font-weight-bold">Branch</label>
			{if $BRANCH_CODE eq 'HQ'}
				<select class="form-control select2" name=branch_sel onchange="bsel(this.value)">
				{foreach from=$branch item=b}
					<option value="{$b.id},{$b.update1},{$b.update2}" {if $b.id eq $sessioninfo.branch_id}selected {/if}>{$b.code}</option>
				{/foreach}
				</select>
			{else}
				<input class="form-control select2" readonly value="{$BRANCH_CODE}">
			{/if}
		</span>
			
		<div class="row">
			<div class="">
				<span class="menu_spacing">
					{*<label class="menu_spacing form-label mt-2 font-weight-bold">Department</label>
						<select class="form-control select2" name=dept>
							<option value="all">-- All --</option>
							{foreach from=$dept item=r}
								<option value="{$r.id}" {if $smarty.request.dept eq $r.id} selected {/if}>{$r.description}</option>
							{/foreach}
						</select>
					</span>*}
			</div>

		</div>

		
			<span>
				{include file='category_autocomplete.tpl' all=1  allow_select_line=1 skip_dept_filter=1}
			</span>
		
	</div>
	</div>
<div class="card mx-3">
<div class="card-body">
<div class="row">
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Vendor</label>
				<select class="form-control select2" name="vendor_id">
					<option value="">-- All --</option>
					{foreach from=$vendors item=r}
						<option value="{$r.id}">{$r.description}</option>
					{/foreach}
				</select>
		</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Brand</label>
			<select class="form-control select2" name="brand_id">
					<option value="">-- All --</option>
					<option value="0">UNBRANDED</option>
				{foreach from=$brands item=r}
					<option value="{$r.id}">{$r.description}</option>
				{/foreach}
			</select>
		</span>	
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Input Tax</label>
				<select class="form-control select2" name="input_tax">
					<option value="">-- All --</option>
					{foreach from=$input_tax_list key=rid item=r}
						<option value="{$r.id}">{$r.code} ({$r.rate}%)</option>
					{/foreach}
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Output Tax</label>
				<select class="form-control select2" name="output_tax">
					<option value="">-- All --</option>
					{foreach from=$output_tax_list key=rid item=r}
						<option value="{$r.id}">{$r.code} ({$r.rate}%)</option>
					{/foreach}
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Selling Price Inclusive Tax</label>
				<select class="form-control select2" name="inclusive_tax">
					<option value="">-- All --</option>
					<option value="yes">YES</option>
					<option value="no">NO</option>
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">SKU Type</label>
				<select class="form-control select2" name="sku_type">
					<option value="">-- All --</option>
					{foreach from=$sku_type item=r}
						<option value="{$r.sku_type}">{$r.sku_type}</option>
					{/foreach}
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Scale Type</label>
				<select class="form-control select2" name="scale_type">
					<option value="">-- All --</option>
					{foreach from=$scale_type_list key=st_value item=st_name}
					{if $st_value >= 0}
						<option value="{$st_value}">{$st_name}</option>
					{/if}
					{/foreach}
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Active</label>
				<select class="form-control select2" name="active">
					<option value="">-- All --</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</span>
	</div>
	<div class="col-md-4">
		<span class="menu_spacing">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Parent Child?</label>
				<select class="form-control select2" name="parent_child_filter">
					<option value="">-- All --</option>
					<option value="1">Yes</option>
					<option value="0">No</option>
				</select>
			</span>
	</div>

</div>
<div class="row">
	<div class="col-md-4">

		<div class="form-group">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Price Markup%</label>
			<span class="menu_spacing"><input class="form-control" size=3 name="price_markup"></span>
		</div>
</div>
<div class="col-md-4">

		<div class="form-group">
			<label class="menu_spacing form-label mt-2 font-weight-bold">Cost Markup%</label>
			<span class="menu_spacing"><input class="form-control" size=3 name="cost_markup"></span>
		</div>

</div>
</div>
</div>
</div>
<div class="card mx-3">
<div class="card-body">
<table>
	<b>Advanced Settings</b><br>
	<label class="mt-2" ><b class="text-muted">Items Last Update Date</b> &nbsp; (yyyy-mm-dd) </label>
	<input class="form-control" name=items_last_update size=10 value="{$lastid[0]}"> <small >(left empty will not export any items by last update, put '0' will export all items)</small>
	<br>
	<label class="mt-2" ><b class="text-muted">Price Change Date</b> &nbsp; (yyyy-mm-dd)</label> 
	<input class="form-control" name=price_last_update size=10 value="{$lastid[1]}"> <small >(left empty or zero will not export any items with price change)</small>
	<br>
	<label for="show_cat_id" class="mt-2"><b class="text-muted">Show Category</b></label>
	&nbsp;<input type="checkbox" name="show_cat" id="show_cat_id" onclick="hide_show_category()">
	{if $config.sku_enable_additional_description}
	<label for="show_cat_id" class="mt-2"><b class="text-muted">Show Additional Description</b></label><input type="checkbox" name="show_additional_desc" id="checkbox_additional_desc" onclick="hide_export_txt_btn()">
	{/if}
</table>
<span>
	<span id="span_export_txt" class="menu_spacing">
		<input class="btn btn-primary" type="button" value="Get SKU by TXT" onClick="get_data('txt');" />
	</span>
	<input class="btn btn-primary mt-2 mt-md-0" type="button" value="Get SKU by CSV" onClick="get_data('csv');" />
</span>
</form>

<br><br>
<div class="alert alert-primary" style="max-width: 660px;">
	<b>Note:</b> Below is the example of output. The first line title is not included in the output format (TXT).
</div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div class="card mx-3">
		<div class="card-body">
			
<div class="table-responsive">
	<table id="tbl-export-sku" class="report_table table mb-0 text-md-nowrap  table-hover " >
	<thead class="bg-gray-100">
		<tr>
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
	</thead>
	<tbody class="fs-08">
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
	</tbody>
	</table>
</div>
		</div>
	</div>
</div>

<!--iframe name=_ifr width=1 height=1 style="visibility:hidden"></iframe-->
{include file=footer.tpl}
<script>
	hide_show_category();
	if(sku_enable_additional_description)  hide_export_txt_btn();
</script>