{*
3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".
*}

{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = '{$smarty.request.t|default:1}';
var id = '{$smarty.request.id}';
var branch_id = '{$smarty.request.branch_id}';
var print_bn = '{$smarty.request.print_bn}';
var page_num = 0;
{literal}
var search_str='';

function list_sel(selected){
	var url = '';

	if(selected==3){
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
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');

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
		list_sel(3);
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

{/literal}
</script>

<iframe name="if_print" style="visibility:hidden;width:1px;height:1px;"></iframe>

<form name="f_print" style="display:none;" action="masterfile_sku_items.batch_no_setup.php">
	<input type="hidden" name="a" value="do_print" />
	<input type="hidden" name="id" />
	<input type="hidden" name="branch_id" />
</form>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="tab row mb-3 mx-3" style="white-space:nowrap;">
	<a href="javascript:void(list_sel(1))" id=lst1 class="btn btn-outline-primary btn-rounded a_tab">Waiting for Setup</a>
&nbsp;&nbsp;	<a href="javascript:void(list_sel(2))" id=lst2 class="btn btn-outline-primary btn-rounded a_tab">Confirmed</a>
<a class="a_tab" id=lst3>
	<div class="form-inline">
	&nbsp;&nbsp;	Find GRN 
	&nbsp;	<input class="form-control" id="inp_item_search" onKeyPress="search_input_keypress(event);" value="{$smarty.request.search_str}" /> 
	&nbsp;	<input type="button" class="btn btn-primary" value="Go" onClick="list_sel(3);" /></a>
	</div>
</div>

<div id="items_list" >
</div>

<script>
list_sel(tab_num);
if(print_bn) do_print(id, branch_id);
</script>
{include file='footer.tpl'}
