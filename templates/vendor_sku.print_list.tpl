{*
REVISION HISTORY 
++++++++++++++++

10/29/2007 4:11:45 PM gary
- add department and brand option.

11/16/2015 3:30 PM Qiu Ying
- Add sorting by sku arms code,mcode,artno, desription and department.
- Add export excel

11/9/2018 2:48 PM Andy
- Enhanced to have "Print Additional Week Column".

06/24/2020 10:09 AM Sheila
- Updated button css
*}

{include file=header.tpl}
<script>
{literal}
function do_export(){
	$('ifprint').src = '/vendor_sku.print_list.php?a=export&'+Form.serialize(document.f1);
}

function do_preview(){
	$('list').innerHTML = _loading_;
	new Ajax.Updater('list', '/vendor_sku.print_list.php?a=list&'+Form.serialize(document.f1), { evalScripts:true });
}

function do_print(){
	$('ifprint').src = '/vendor_sku.print_list.php?a=print&'+Form.serialize(document.f1);
}

function change_sort_by(ele){
	if(ele.value=='')   $('span_sort_order').hide();
	else    $('span_sort_order').show();
}

function week_col_changed(){
	var v = int(document.f1['week_col'].value);
	if(v < 0)	v = 0;
	else if(v > 12)	v = 12;
	document.f1['week_col'].value = v;
}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f1">
			<p>
			<div class="mb-2">
				<b class="form-label">Select Vendor</b>
		    <select class="form-control" id="vendor_id" name=vendor_id>
			{section name=i loop=$vendors}
			<option value="{$vendors[i].id}">{$vendors[i].description}
			{/section}
			</select> 
		</div>
			<div >
				<input type=checkbox name="show_cost" {if $smarty.request.show_cost}checked{/if}>&nbsp;Show Last GRN Cost
			</div>
			</p>
			
			<p>
		<div class="row">
			<div class="col-md-4">
				<b class="form-label">Department</b>
			<select class="form-control" name=dept_id>
			<option value="">-- All --</option>
			{section name=i loop=$dept}
			<option value={$dept[i].id}>{$dept[i].description}</option>
			{/section}
			</select>
			</div>
			
		<div class="col-md-4">
			<b class="form-label">Brand</b>
			<select class="form-control" name=brand_id>
			<option value="">-- All --</option>
			{section name=i loop=$brand}
			<option value={$brand[i].id}>{$brand[i].description}</option>
			{/section}
			</select>
		</div>
		
		<div class="col-md-4">
			<b class="form-label">Sort By</b>
			<select class="form-control" name="sort_by" onChange="change_sort_by(this);">
				<option value="">--</option>
				<option value="sku_item_code">ARMS Code</option>
				<option value="artno">Art No</option>
				<option value="mcode">MCode</option>
				<option value="description">Description</option>
				<option value="department">Department</option>
			</select>
		<div class="mt-1">
			<span id="span_sort_order" style="{if !$smarty.request.sort_by}display:none;{/if}">
				<select class="form-control" name="sort_order">
					<option value="asc">Ascending</option>
					<option value="desc">Descending</option>
				</select>
			</span>
		</div>
		</div>
		</div>
			</p>
			
			<p><input class="btn btn-primary" type=button value="Load" onclick="do_preview()">
			{if $sessioninfo.privilege.EXPORT_EXCEL}
				<button class="btn btn-info" name="output_excel" onclick="do_export()" type="button"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
			</p>
			
			<fieldset >
				<legend><b>Print Settings</b></legend>
				<div class="form-inline">
					<b>Print Additional Week Column</b>
			&nbsp;&nbsp;	<input class="form-control" type="number" name="week_col" min="0" max="12" value="{$smarty.request.week_col|default:0}" style="width:50px;" onChange="week_col_changed();" />
					
			&nbsp;&nbsp;	<input class="btn btn-primary" type=button value="Print" onclick="do_print()">
		</div>
				
			</fieldset>
			</form>
			<div class="alert alert-primary rounded" style="max-width:325px;">
				Last GRN Cost is based on all vendors.
			</div>
			
	</div>
</div>

<div id=list style="padding:10px 0;">
</div>

<iframe id=ifprint width=1 height=1 style="visibility:hidden"></iframe>
{include file=footer.tpl}
