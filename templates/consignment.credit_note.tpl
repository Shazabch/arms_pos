{*
6/3/2010 6:07:08 PM Andy
- Add Export UBS for CN/DN.
- Add Multiple print.

6/9/2010 6:26:40 PM Andy
- Remove duplicate footer.

11/8/2010 11:24:45 AM Andy
- Change consignment invoice, CN and DN when export ubs, prevent it to have negative month.
- Change word "Cancelled" to "Canceled".

11/8/2010 2:22:24 PM Alex
- add branch searching

12/13/2010 11:02:29 AM Andy
- Fix export ubs bugs: if financial month equal to or greater than 10 will cause negative month.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;

{literal}
var search_str = '';

function list_sel(selected){
	var div_cn_list = $('div_cn_list');
	if(!div_cn_list) return;

	if(selected==6){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
	}else if (selected==7){
	    var search_bid=$('search_bid').value;
	    search_str = search_bid;
	}

	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}

	var all_tab = $$('.tab .a_tab');
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');

	$('div_cn_list').update(_loading_);
	new Ajax.Updater('div_cn_list',phpself+'?a=ajax_list_sel&skip_init_load=1&t='+tab_num+'&p='+page_num,{
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

function do_print(id,bid){
	if(!confirm('Click OK to print'))   return false;
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = bid;
    document.f_print.a.value='print_cn';
	//document.f_print.target = 'ifprint';
	document.f_print.target = '_blank';
	document.f_print.submit();
}

function toggle_export_ubs_status(bid, id){
	key = bid+','+id;
	var img = $('img,export_ubs_flag,'+key);
	var update_to_status;
	// check if already updating, stop user to call multiple request on same sheet
	if(img.src.indexOf('clock')>0){
		alert('Please wait, updating...');
		return false;
	}

	// check update to "1" or "0"
	if(img.src.indexOf('flag_green.png')>0){
		update_to_status = '1';
	}else{
        update_to_status = '0';
	}

	// update image to clock
	img.src = '/ui/clock.gif';

	new Ajax.Request(phpself,{
		parameters:{
		    a: 'ajax_change_export_ubs_status',
		    branch_id: bid,
			id: id,
			update_to_status: update_to_status
		},
		onComplete: function(msg){
			if(msg.responseText=='OK'){
                if(update_to_status==1){
					img.src = '/ui/icons/flag_red.png';
				}else{
			        img.src = '/ui/icons/flag_green.png';
				}
			}else{
			    alert(msg.responseText);
                if(update_to_status==0){
					img.src = '/ui/icons/flag_red.png';
				}else{
			        img.src = '/ui/icons/flag_green.png';
				}
			}
		}
	});
}

function export_ubs(){
	curtain(true);
	check_and_change_financial_year();
	center_div($('div_export_ubs').show());
}

function refresh_list_after_ubs_export(){
	if(tab_num==4){
		list_sel(tab_num);
	}
}

function curtain_clicked(){
	$('div_export_ubs').hide();
}

function show_multiple_print(){
	curtain(true);
	center_div($('div_multiple_print').show());
}

function search_inv_no(){
	// check parameters
	if(document.f_multiple_print['inv_no_from'].value.trim()==''){
		alert('Please key in Invoice No from');
		document.f_multiple_print['inv_no_from'].focus();
		return false;
	}
	if(document.f_multiple_print['inv_no_to'].value.trim()==''){
		alert('Please key in Invoice No to');
		document.f_multiple_print['inv_no_to'].focus();
		return false;
	}

	$('btn_search_multiple_print').disabled = true;
	$('btn_start_multiple_print').disabled = true;

	$('div_multiple_print_list').update(_loading_);
	new Ajax.Updater('div_multiple_print_list', phpself+'?a=ajax_search_inv_no',{
		parameters: $(document.f_multiple_print).serialize(),
		evalScripts: true,
		onComplete: function(e){
			$('btn_search_multiple_print').disabled = false;
		}
	});
}

function create_financial_year_month_value(m, y){
	if(m<10)    m = '0'+m;
 	return y+''+m;
}

function check_and_change_financial_year(){
	var selected_m = int(document.f_ex['month'].value);
	var selected_y = int(document.f_ex['year'].value);
	var new_ym = create_financial_year_month_value(selected_m, selected_y);

	var f_m = int(document.f_ex['financial_month'].value);
	var f_y = selected_y;
	var current_selected_f_ym = create_financial_year_month_value(f_m, f_y);

	if(current_selected_f_ym>new_ym){
		f_y--;
		current_selected_f_ym = create_financial_year_month_value(f_m, f_y);
	}
	document.f_ex['financial_year'].value = f_y;
}
{/literal}
</script>

<!-- print iframe -->
<div style="display:none;">
<form name=f_print method=get>
<input type=hidden name="a">
<input type=hidden name="id">
<input type=hidden name="branch_id">
</form></div>
<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<!-- Export Invoice -->
<div id="div_export_ubs" style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;z-index:10000;">
<h1>Export to UBS</h1>

<form name="f_ex" target="ifprint" method="post">
<input type="hidden" name="a" value="export_ubs" />

<table>
	<tr>
	    <td><b>Branch</b></td>
	    <td>
	        <select name="branch_id" style="width:200px;">
	            <option value="">-- All --</option>
	            {foreach from=$branches key=bid item=b}
			    {if !$branch_group.have_group.$bid}
			    	<option value="{$bid}">{$b.code} - {$b.description}</option>
			    {/if}
			{/foreach}
			{foreach from=$branch_group.header key=bgid item=bg}
			    <optgroup label="{$bg.code}">
			        {foreach from=$branch_group.items.$bgid key=bid item=b}
			            <option value="{$bid}">{$b.code} - {$b.description}</option>
			        {/foreach}
			    </optgroup>
			{/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <td><b>Month / Year</b></td>
	    <td>
	        <select name="month" onChange="check_and_change_financial_year();">
				{foreach from=$months key=k item=m}
				    <option value="{$k}">{$m}</option>
				{/foreach}
			</select>
			<select name="year" onChange="check_and_change_financial_year();">
				{foreach from=$year_list item=c}
				    <option value="{$c.y}">{$c.y}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
	    <td><b>Financial Year (MM/YYYY)</b></td>
	    <td>
			<input type="text" name="financial_month" size="3" value="{$financial_date.m}" readonly />
			 /
			<input type="text" name="financial_year" size="5" value="{$financial_date.y}" readonly />
		</td>
	</tr>
</table>



<p class="r">
<input type="submit" value="Export" />
</p>
</form>
</div>
<!-- End of Export Invoice-->

{include file='consignment.credit_note.multiple_print.tpl'}

<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.err_msg}
    <p><img src="ui/cancel.png" align="absmiddle"> {$smarty.request.err_msg}</p>
{/if}

{if $smarty.request.t eq 'save'}
    <p><img src=/ui/approved.png align=absmiddle> {$sheet_type|upper} ID#{$smarty.request.save_id} was saved</p>
{elseif $smarty.request.t eq 'delete'}
	<p><img src="ui/terminated.png" align="absmiddle" /> {$sheet_type|upper} ID#{$smarty.request.save_id} was deleted</p>
{elseif $smarty.request.t eq 'cancel'}
	<p><img src="ui/cancel.png" align="absmiddle" /> {$sheet_type|upper} ID#{$smarty.request.save_id} was cancelled</p>
{elseif $smarty.request.t eq 'confirm'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> {$sheet_type|upper} ID#{$smarty.request.save_id} confirmed. </p>
{elseif $smarty.request.t eq 'reset'}
    <p><img src="ui/notify_sku_reject.png" align="absmiddle"> {$sheet_type|upper} ID#{$smarty.request.save_id} was reset.</p>
{elseif $smarty.request.t eq 'approve'}
	<p><img src="ui/approved.png" align="absmiddle"> {$sheet_type|upper} ID#{$smarty.request.save_id} was Fully Approved.</p>
{/if}

<ul>
	<li><img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New {$sheet_name|lower|capitalize}</a></li>
	<li><img src=ui/new.png align=absmiddle> <a href="javascript:export_ubs();">Export to UBS</a></li>
	<li><img src="ui/icons/printer.png" align=absmiddle> <a href="javascript:void(show_multiple_print());">Print Multiple Invoice</a></li>
</ul>

<div class=tab style="height:25px;white-space:nowrap;">&nbsp;&nbsp;&nbsp;
<a href="javascript:void(list_sel(1))" id=lst1 class="active a_tab">Saved</a>
<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab">Waiting for Approval</a>
<a href="javascript:void(list_sel(5))" id=lst5 class="a_tab">Rejected</a>
<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab">Cancelled/Terminated</a>
<a href="javascript:void(list_sel(4))" id=lst4 class="a_tab">Approved</a>
<a class="a_tab" id="lst6">Find {$sheet_type|upper} No <input id="inp_item_search" onKeyPress="search_input_keypress(event);" />
<input type="button" value="Go" onClick="list_sel(6);" /></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
{if $BRANCH_CODE eq 'HQ'  && $config.consignment_modules}
	<a class="a_tab" id="lst7">
		Branch
		<select name="branch_id" id="search_bid">
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
		    {/foreach}
		</select>
		<input type="button" value="Go" onClick="list_sel(7);" />
	</a>
{/if}
</div>
<div id="div_cn_list" style="border:1px solid #000">
</div>
</div>

{include file='footer.tpl'}
<script>
list_sel();
{literal}
new Draggable('div_multiple_print',{ handle: 'div_multiple_print_header'});
{/literal}
</script>

