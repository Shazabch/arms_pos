{*
7/26/2010 2:51:11 PM Andy
- Add branch, year and month dropdown for monthly report.

7/28/2010 1:38:35 PM Andy
- Add delete function to consignment monthly report (prompt to enter reason before delete).
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;

{literal}
var search_str = '';

function list_sel(selected){
	var div_mr_list = $('div_mr_list');
	if(!div_mr_list) return;

	if(selected==3){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
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

	$('div_mr_list').update(_loading_);
	new Ajax.Updater('div_mr_list',phpself+'?a=ajax_list_sel&skip_init_load=1&t='+tab_num+'&p='+page_num,{
		parameters:{
			search_str: search_str
		},
		onComplete: function(msg){

		},
		evalScripts: true
	});
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(3);
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div id=show_last>
{if $smarty.request.t eq 'delete'}
<img src="/ui/cancel.png" align="absmiddle"> {$smarty.request.msg}
{/if}
</div><br />

<form method="get" class="form" name="f_mr" target="_blank">
<input type="hidden" name="a" value="load_table">
<b>Month / Year :</b>
<select name="month">
	{foreach from=$months key=k item=m}
	    <option value="{$k}">{$m}</option>
	{/foreach}
</select>
<select name="year">
	{foreach from=$years item=y}
	    <option value="{$y}" {if $y eq $smarty.now|date_format:'%Y'}selected {/if}>{$y}</option>
	{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
<b>Branch</b>
<select name="branch">
	{foreach from=$branches key=bid item=b}
	    {if !$branch_group.have_group.$bid}
	    	<option value="{$bid}" {if $bid eq $form.ci_branch_id}selected {/if}>{$b.code} - {$b.description}</option>
	    {/if}
	{/foreach}
	{foreach from=$branch_group.header key=bgid item=bg}
	    <optgroup label="{$bg.code}">
	        {foreach from=$branch_group.items.$bgid key=bid item=b}
	            <option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
	        {/foreach}
	    </optgroup>
	{/foreach}
</select>

<input type="submit" value="Show Report" />
</form>

<br />

<div class="tab" style="height:25px;white-space:nowrap;">&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(list_sel(1))" id="lst1" class="active a_tab">Saved</a>
	<a href="javascript:void(list_sel(2))" id="lst2" class="a_tab">Confirmed</a>
	<a class="a_tab" id="lst3">Search by Branch / month <span class="clickable" onClick="alert('Sample Search\nBranch: b15 (Search for branch code b15)\nMonth: 201001(search all branch for 2010 jan)');"><img src="/ui/icons/information.png" align="abstop" /></span> <input id="inp_item_search" onKeyPress="search_input_keypress(event);" /> <input type="button" value="Go" onClick="list_sel(3);" /></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>
<div id="div_mr_list" style="border:1px solid #000">
No Data
</div>

{include file='footer.tpl'}
<script>
list_sel();
{literal}

{/literal}
</script>
