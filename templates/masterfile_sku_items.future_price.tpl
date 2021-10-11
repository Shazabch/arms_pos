{*
6/4/2013 10:41 AM Justin
- Bug fixed on pagination not working properly.

7/29/2013 5:15 PM Andy
- Fix Batch Price Change after confirm and no message will display if the document get directly approved.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

3/13/2015 6:00 PM yinsee
- Add import from CSV (Mcode/Armscode, Price Type, Price) -- function import_csv()

3/21/2015 2:04 PM Justin
- Enhanced to have new link "Import from Price Wizard".
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = '{$smarty.request.t|default:1}';
var id = '{$smarty.request.id}';
var branch_id = '{$smarty.request.branch_id}';
var page_num = 0;
{literal}
var search_str='';

function list_sel(selected){
	var url = '';

	if(selected==6){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			return;
		}else 	search_str = tmp_search_str;
	}
	
	if(document.f_a) url = Form.serialize(document.f_a);
	
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}
	var all_tab = $$('.tab .a_tab');
	//alert(all_tab.length);
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('selected');
	}
	$('lst'+tab_num).addClassName('selected');

	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num+'&'+url,{
		parameters:{
			search_str: search_str
		},
		onComplete: function(msg){
		},
		evalScripts: true
	});
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(6);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function do_print(id,branch_id){
	document.f_print.target = 'if_print';
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = branch_id;
	//document.f_print.target = '_blank';
	document.f_print.submit();
}

function cancel_redemption(id,branch_id){
	if(!confirm('Are you sure to cancel this redemption?')) return;
	var rdmpt_code = $('rdmpt_code_'+branch_id+"_"+id).value;
	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=cancel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters:{
			search_str: search_str,
			id: id,
			branch_id: branch_id
		},
		onComplete: function(msg){
			$('status_msg').innerHTML = "<img src=/ui/icons/delete.png align=top> Membership Redemption No "+rdmpt_code+" has been Cancelled<br><br>";
		},
		evalScripts: true
	});
}

function print_report(id, branch_id){
	document.f_print.id.value=id;
	document.f_print.branch_id.value=branch_id;
	document.f_print.target = "_blank";
	document.f_print.submit();
}

function toggle_import(){
	if($('div_import').style.display == ""){
		hidediv('div_import');
	}else{
		showdiv('div_import');
	}
}

function do_cancel(id, bid){
	if(!confirm("Are you sure want to cancel?")) return;
	
	document.f_cancel['id'].value = id;
	document.f_cancel['branch_id'].value = bid;
	document.f_cancel.submit();
}

{/literal}
</script>

<iframe name="if_print" style="visibility:hidden;width:1px;height:1px;"></iframe>

<form name="f_print" style="display:none;" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="do_print" />
	<input type="hidden" name="id" />
	<input type="hidden" name="branch_id" />
</form>

<form name="f_cancel" style="display:none;" action="{$smarty.server.PHP_SELF}">
	<input type="hidden" name="a" value="cancel" />
	<input type="hidden" name="id" />
	<input type="hidden" name="branch_id" />
	<input type="hidden" name="is_ajax" value="1" />
</form>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		
{if $smarty.request.act}
<div id="show_last">
{if $smarty.request.act eq 'save'}
	<img src="/ui/approved.png" align="absmiddle"> Change Future Price saved as ID#{$smarty.request.id|string_format:"%05d"}<br>
{elseif $smarty.request.act eq 'confirm'}
	<img src="/ui/approved.png" align="absmiddle"> 
	{if $smarty.request.la}
		Change Future Price had been saved as ID#{$smarty.request.id|string_format:"%05d"} and being approved
	{else}
		Change Future Price had been saved as ID#{$smarty.request.id|string_format:"%05d"} and had sent to approval
	{/if}<br>
{elseif $smarty.request.act eq 'cancel'}
	<img src="/ui/cancel.png" align="absmiddle"> Change Future Price ID#{$smarty.request.id|string_format:"%05d"} was cancelled<br>
{elseif $smarty.request.act eq 'recall'}
	<img src="/ui/notify_sku_reject.png" align="absmiddle"> Change Future Price ID#{$smarty.request.id|string_format:"%05d"} was recalled<br>
{elseif $smarty.request.act eq 'reset'}
	<img src="/ui/notify_sku_reject.png" align="absmiddle"> Change Future Price ID#{$smarty.request.id|string_format:"%05d"} was reset.
{elseif $smarty.request.act eq 'approve'}
	<img src="/ui/approved.png" align="absmiddle"> 
	Change Future Price had been saved as ID#{$smarty.request.id|string_format:"%05d"} and being approved
{/if}
</div>
<br />
{/if}

<div class="row">
	<div class="col-md-4">
			<ul class="list-group ">
			
				<li class="list-group-item list-group-item-action">
					<img src="ui/new.png" align="absmiddle"> <a href="{$smarty.server.PHP_SELF}?a=open">Create New {$PAGE_TITLE}</a><br />
				</li>
			</ul>
	</div>
			<div class="col-md-4">
				<li class="list-group-item list-group-item-action">
					<img src="ui/new.png" align="absmiddle"> <a href="{$smarty.server.PHP_SELF}?a=open_csv">Import from Price Wizard (CSV)</a><br />
				</li>
			</div>
		<div class="col-md-4">
			<li class="list-group-item list-group-item-action">
				<img src="ui/new.png" align="absmiddle"> <a href="#" onclick="toggle_import();">Import from CSV</a> <br>
				
			</li>
			&nbsp;&nbsp;<small>(Columns: Mcode/Armscode, Price Type, Price)</small>
		</div>
		
	
</div>
<div class="stdframe" id="div_import" style="display:none;">
<form action="?a=open" method="post" enctype="multipart/form-data">
<input type="file" name="csv">
<input type="submit" value="Upload">
</form>
</div>

	</div>
</div>
<div class="row mx-3">
	<div class="col">
		<div class="tab" style="white-space:nowrap;">
			<a href="javascript:void(list_sel(1))" id="lst1" class="a_tab btn btn-outline-primary btn-rounded">Saved</a>
			<a href="javascript:void(list_sel(2))" id="lst2" class="a_tab btn btn-outline-primary btn-rounded">Waiting for Approval</a>
			<a href="javascript:void(list_sel(3))" id="lst3" class="a_tab btn btn-outline-primary btn-rounded">Rejected</a>
			<a href="javascript:void(list_sel(4))" id="lst4" class="a_tab btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
			<a href="javascript:void(list_sel(5))" id="lst5" class="a_tab btn btn-outline-primary btn-rounded">Approved</a>
		
		</div>
	</div>
	<div class="col">
<div class="form-inline">
	<a class="a_tab" id="lst6">Find Doc No
		<input class="form-control" id="inp_item_search" onKeyPress="search_input_keypress(event);" value="{$smarty.request.search_str}" /> 
		<input class="btn btn-primary" type="button" value="Go" onClick="list_sel(6);" />
	</a>
</div>
	</div>
</div>

<div class="card mx-3 mt-3">
	<div class="card-body">
		<div id="items_list">
		</div>
	</div>
</div>

<script>
list_sel(tab_num);
</script>
{include file='footer.tpl'}
