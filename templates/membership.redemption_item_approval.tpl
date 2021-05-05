{*
1/20/2011 12:16:11 PM Justin
- Fixed the JS print wrong msg even found got error from php.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

06/29/2020 05:06 PM Sheila
- Updated button css.
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.tr_fix_selection{
    background:#f0f0f0;
}

tr.item_deleted{
	background:grey;
}

.nl{
	font-size:11;
	color:#e00;
	text-align:center;
}

.highlight_row_title {
	background:none repeat scroll 0 0 #FFFF66 !important;
	border-bottom:1px solid #FF0000;
	border-top:1px solid #FF0000;
	color:#FF0000;
	font-weight:bold;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = '{$smarty.request.branch_id}';
var tab_num = '{$smarty.request.t|default:1}';
var branches_id_list = [];
var items_row_count = 0;

{literal}var branches_info = {};{/literal}
{foreach from=$branches key=bid item=b}
    branches_id_list.push('{$bid}');
    branches_info['{$bid}'] = {literal}{}{/literal};
    branches_info['{$bid}']['code'] = '{$b.code}';
{/foreach}

{literal}

function list_sel(selected){
	var url = '';
	var branch_id = '';

	if($('branch_id')){
		branch_id = $('branch_id').value;
	}

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

	$('tb_div').update(_loading_);
	new Ajax.Updater('tb_div',phpself,{
		parameters:{
			a: 'refresh_item_list',
			branch_id: branch_id,
			t: tab_num
		},
		onComplete: function(msg){
		},
		evalScripts: true
	});
	
	if(selected == 1){
		$('div_reset_area').style.display = 'none';
		$('div_confirm_area').style.display = '';
	}else if(selected == 2){
		$('div_confirm_area').style.display = 'none';
		$('div_reset_area').style.display = '';
	}else{
		$('div_confirm_area').style.display = 'none';
		$('div_reset_area').style.display = 'none';
	}
}

function refresh_item_list(){
	if(document.f_item['branch_id']){
		branch_id = document.f_item['branch_id'].value;
	}
	$('tb_div').update(_loading_);
	new Ajax.Updater('tb_div',phpself,{
		parameters: "a=refresh_item_list&branch_id="+branch_id,
		onComplete: function(msg){
            reset_row_no();
		},
		evalScripts: true
	});
}

function toggle_all_status(ele){
	var c = ele.checked;
	var all_inp = $$('#tb_div input.chx_item');
	
	for(var i=0; i<all_inp.length; i++){
	    all_inp[i].checked = c;
	}
}

function init_calendar(inputfield, button){
	Calendar.setup({
		inputField     :    inputfield,
		ifFormat       :    "%Y-%m-%d",
		button         :    button,
		align          :    "Bl",
		singleClick    :    true
	});   
}

function update_selected_item(v){

	var all_chx = $$('#tbl_items input.chx_item');
	var item_array = [];

	for(var i=0; i<all_chx.length; i++){
	    var chx = all_chx[i];
	    if(!chx.checked) continue;
	    
	    var bid = $(chx).id.split(',')[1];
	    var id = $(chx).id.split(',')[2];
		
        item_array.push(bid+'_'+id);
	}

	// redirect either confirm or reset the selected items
	if(v == 1){
		var complete_msg = 'Successfully Approved';
		var confirm_msg = 'approve';
	}else if(v == 2){
		var complete_msg = 'Successfully Reseted';
		var confirm_msg = 'reset';
	}else if(v == 3){
		var complete_msg = 'Successfully Cancelled';
		var confirm_msg = 'cancel';
	}else if(v == 4){
		var complete_msg = 'Successfully Rejected';
		var confirm_msg = 'reject';
	}
	
	if(item_array.length != 0 && !confirm("Are you sure want to "+confirm_msg+" following selected item(s)?")) return false;

	if(item_array.length>0){
		new Ajax.Request(phpself,{
			parameters: {
				a: 'ajax_set_item_status',
				'item_array[]': item_array,
				v: v
			},
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='OK'){
					alert(msg);
				}else{
					alert(complete_msg);
					list_sel(tab_num);
				}
			}
		});
	}else{
		alert('No item to update.');
	}
}

</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
{if $msg}<p align=center><font color=red>{$msg}</font></p>{/if}

{if $BRANCH_CODE eq 'HQ'}
	<div style="padding:2px;">
		<b>Select Branch: </b>
		<select name="branch_id" id="branch_id" onchange="list_sel('{$smarty.request.t|default:1}');">
		<option value="">All</option>
			{foreach from=$filter_branches item=branch_id key=branch}
				<option value="{$branch_id}" {if $branch_id eq $smarty.request.branch_id}selected{/if}>{$branch}</option>
			{/foreach}
		</select>
	</div><br>
{/if}

<div class="tab" style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(list_sel(1))" id=lst1 class="active a_tab">Waiting for Approval</a>
	<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab">Approved</a>
	<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab">Inactive</a>
	&nbsp;&nbsp;<label class="highlight_row_title"><img width="17" src="/ui/pixel.gif" /></label> Highlighted item(s) indicate nearly expire
</div>

<form name="f_item">
    <div id="tb_div" style="border:1px solid #000"></div>
</form>

<div id="div_confirm_area">
    <p style="text-align:center;padding:10px;background:#fff;">
		<input class="btn btn-success" name="confirm_btn" type="button" value="Confirm Selected Item(s)" onclick="update_selected_item('1');" >&nbsp;&nbsp;
		<input class="btn btn-error" name="reject_btn" type="button" value="Reject Selected Item(s)" onclick="update_selected_item('4');" >
    </p>
</div>
<div id="div_reset_area">
    <p style="text-align:center;padding:10px;background:#fff;">
    	<input class="btn btn-error" name="reset_btn" type="button" value="Reset Selected Item(s)" onclick="update_selected_item('2');" >&nbsp;&nbsp;
    	<input class="btn btn-warning" name="cancel_btn" type="button" value="Cancel Selected Item(s)" onclick="update_selected_item('3');" >
    </p>
</div>
<script>
{literal}
list_sel(tab_num);
{/literal}
</script>

{include file=footer.tpl}
