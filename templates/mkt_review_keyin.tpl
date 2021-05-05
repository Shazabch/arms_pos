{include file=header.tpl}
{literal}
<style>
#table_sheet { font: 12px "MS Sans Serif" normal;}
td, th { white-space:nowrap; }
.border{
	background-color:black;
}
.keyin{
	background-color:yellow;
	text-align: right;
}
</style>
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
function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "t_added1",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
		//,
		//onUpdate       :    load_data
	});   
}


function do_search(){
	document.f_m_r.a.value='search';
	if($('added1').value!=''){
		document.f_m_r.submit();
	}
	else{
		alert('Please select a date.');
		return;
	}
}
var last_obj;
var g_line;
var g_dept;

function do_edit(obj){
    last_obj = obj;
    var line = obj.title.split(",");
    g_line=line[0];
    g_dept=line[1];    
	$('edit_text').value =float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}

function save(){
	Element.hide('edit_popup');
	if(float(last_obj.innerHTML)!=float($('edit_text').value)){
		last_obj.innerHTML = 'Saving..';
		var newp = last_obj;
		new Ajax.Updater(newp,'mkt_review_keyin.php?line_id='+g_line+'&dept_id='+g_dept+'&value='+float($('edit_text').value)+'&'+Form.serialize(document.f_m_r)+'&a=save_edit',{onComplete:function(){update_table(newp)}});

	}
}

function update_table(cell,day_id){
	new Effect.Highlight(cell);
}
</script>

{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id=edit_text size=5 onblur=save()>
</div>

<form name=f_m_r method=post>
<input type=hidden name=a>

<div class=stdframe style="background:#fff;">
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch :</b> <select name="branch_id" onchange="do_search();">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
	</select>
{/if}
&nbsp;&nbsp;&nbsp;
<b>Select Date</b> 
<input type="text" name="selected_date" id="added1" size=12 value="{$form.selected_date|default:$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;
<input type=button onclick="do_search()" value="Refresh">
</div>
<br>

<br>
{if $smarty.request.a}
<div id=category_list>

<table class=tb border=0 cellspacing=0 cellpadding=2 id=table_sheet>

<tr valign=top>
{section name=lines loop=$category}
{assign var=line value=$category[lines]}
	<th bgcolor=#ffee99>{$line.description}</th>
{/section}
</tr>

<tr valign=top>
{section name=lines loop=$category}
{assign var=line value=$category[lines]}
{assign var=id value=$line.id}
	<td>
		<table class=tb cellspacing=0 cellpadding=4>
		<tr>
		<th>Department</th>
		<th>Sales Amount</th>
		</tr>
		{section name=key loop=$dept.$id.id}
			{assign var=key value=$smarty.section.key.iteration-1}
			<tr>
			<th align=left>{$dept.$id.description.$key}</th>
			<td class="keyin" title="{$id},{$dept.$id.id.$key}" onclick="do_edit(this)" >
			{$dept.$id.sales.$key|default:"&nbsp;"|number_format:2}</td>
			</tr>
		{/section}		
		</table>
	</td>
	
{/section}
</tr>

</table>
</div>
{/if}

</form>

{include file=footer.tpl}
<script>
init_calendar();
</script>
