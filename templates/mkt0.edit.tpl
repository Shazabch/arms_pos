{include file=header.tpl}
{literal}
<style>
.keyin{
	background-color:yellow;
}
.st_block {
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
}
.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.st_block th { background:#efffff; padding:4px; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
textarea[disabled], input[disabled]{
	background:transparent;
	color:#000;
	border:1px solid #ccc;
}

.st_block input[disabled]{
	border:1px solid #fff;
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
function init_calendar()
{
	Calendar.setup({
	    inputField     :    "t_from",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "b_from",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});

	Calendar.setup({
	    inputField     :    "t_to",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "b_to",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	for(var i=1;i<6;i++){
	Calendar.setup({
	    inputField     :    "t_due["+(i-1)+"]",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "b_due["+(i-1)+"]",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	}
	for(var i=1;i<6;i++){
		Calendar.setup({
		    inputField     :     "p_date["+i+"]",  // id of the input field
		    ifFormat       :    "%e/%m/%Y",      // format of the input field
		    button         :    "b_p_date["+i+"]",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
	}
}

function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}
function do_confirm()
{
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
}

function do_delete()
{
	if (confirm('Delete this Sales Target?'))
	{
		document.f_a.a.value='delete';
		document.f_a.submit();
	}
}

function do_cancel()
{
	if (confirm('Cancel this Sales Target?'))
	{
		document.f_a.a.value='cancel';
		document.f_a.submit();
	}
}

function mr3(obj){
	obj.value = round(obj.value,3);
}

function check_a()
{
	if(document.f_a.submit_due_date_0.value=='' || document.f_a.submit_due_date_1.value=='' || document.f_a.submit_due_date_2.value=='' || document.f_a.submit_due_date_3.value=='' || document.f_a.submit_due_date_4.value==''){
		alert('Please enter all the submit due date.');
	}
	else
	return true;
}

function add_rows(parent,template,n)
{
	if (n==undefined) n=3;

	while(n>0)
	{
		var new_row = $(template).cloneNode(true);
		new_row.style.display='';
		new_row.id='';
		$(parent).appendChild(new_row);
		_init_focus_input_class("#expenses_table input", new_row);
  		_init_enter_to_skip(new_row);
		n--;
	}
}

</script>
{/literal}
<h1>{$PAGE_TITLE} {if $form.id}(MKT{$form.id|string_format:"%05d"}){else}(New){/if}</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form enctype='multipart/form-data' name=f_a method=post>
<input type=hidden name=a value=save>
<input type=hidden name=id value={$form.id}>

<div class=stdframe style="background:#fff">
<table cellspacing=0 cellpadding=2 border=0 class=tl>
{if $form.id}
<tr>
	<td colspan=2 class=small>Created: {$form.added}, Last Update: {$form.last_update}</td>
</tr>
{/if}
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	<input type=checkbox id={$branch.id} name=branches[{$br}] value={$branch.id} {if $form.branches[$br]}checked{/if}> {$branch.code}
	{/foreach}
	</td>
</tr><tr>
	<th>Promotion Title</th>
	<td><input name=title size=80 value="{$form.title|escape}"></td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		<input type="text" name="offer_from" id="t_from" value="{$form.offer_from|date_format:'%d/%m/%Y'}" size=12>
		<img align=absbottom src="ui/calendar.gif" id="b_from" style="cursor: pointer;" title="Select Date">
		<b> &nbsp; To &nbsp; </b>
		<input type="text" name="offer_to" id="t_to" value="{$form.offer_to|date_format:'%d/%m/%Y'}" size=12>
		<img align=absbottom src="ui/calendar.gif" id="b_to" style="cursor: pointer;" title="Select Date">
	</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr>
<th>Submit Due Date</th>
<td>
{section name=y start=1 loop=6}
{assign var=y value=$smarty.section.y.iteration}
<b>mkt{$y-1} : </b>
{assign var=z value=$y-1}
{assign var=submit_due_date value="submit_due_date_$z"}
<input type="text" name="submit_due_date_{$z}" id="t_due[{$y-1}]" value="{$form.$submit_due_date|date_format:'%d/%m/%Y'}" size=8>
  <img align=absbottom src="ui/calendar.gif" id="b_due[{$y-1}]" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;
 {/section}
	</td>
</tr>
<tr><td>&nbsp;</td></tr>

<tr>
<th>Publish Date</td><td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
<b> #{$x} : </b>
<input type="text" name="publish_dates[{$x}]" id="p_date[{$x}]" value="{$form.publish_dates[$x]}" size=10>
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;
{/section}
</td>
</tr>

<tr><td>&nbsp;</td></tr>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
<tr>
	<th>Attachment #{$x}</th>
	<td>
		Name: <input size=50 name=attachments[name][{$x}] value="{$form.attachments.name[$x]}">
		{if $smarty.request.a ne 'view'}
	 	File: <input name=files[{$x}] type=file>
		<input type=hidden name=attachments[file][{$x}] value="{$form.attachments.file[$x]}">
		<input type=hidden name=filepath[{$x}] value="{$form.filepath[$x]}">
		{/if}
		{if $form.filepath[$x]}(<a {if BRANCH_CODE eq 'HQ'}href="/{$form.filepath[$x]}"{else}href="javascript:void(window.open('{$image_path}{$form.filepath[$x]}'))"{/if} target=_blank>{$form.attachments.file[$x]}</a>){/if}
	</td>
</tr>
{/section}

<tr>
<th colspan=2>
<br>Promotion Period Remark<br>
<textarea rows="5" cols="80" name=remark>{$form.remark|escape}</textarea>
</th>
</table>
</div>

<h4>A&P Expenses</h4>
- Rows without 'Material' column will not be saved.<br><br>
<table id=expenses_table class=st_block cellpadding=0 cellspacing=0 border=0>
<tr>
	<th>Material</th>
	<!--th>Qty</th-->
	<th>Production<br>Cost</th>
	<th>Hanging Fee</th>
	<th>Distribution<br>Fee</th>
	<th>Permit Fee</th>
</tr>
<tbody id=expenses_rows>
{foreach from=$form.expenses.material key=i item=dummy}
<tr>
	<td>
	<input class="{if $smarty.request.a eq 'view'}readonly{else}keyin{/if}" size=60 name=expenses[material][] value="{$form.expenses.material[$i]}">
	</td>
	<td>
	<input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mr3(this) size=10 name=expenses[production_cost][] value="{$form.expenses.production_cost[$i]}"></td>
	<td>
	<input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[hanging_fee][] value="{$form.expenses.hanging_fee[$i]}">
	</td>
	<td>
	<input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[dist_fee][] value="{$form.expenses.dist_fee[$i]}">
	</td>
	<td>
	<input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[permit_fee][] value="{$form.expenses.permit_fee[$i]}">
	</td>
</tr>
{/foreach}
</tbody>
<tr id=newrow_template style="display:none">
	<td><input class="{if $smarty.request.a eq 'view'}readonly{else}keyin{/if}"size=60 name=expenses[material][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mr3(this) size=10 name=expenses[production_cost][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[hanging_fee][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[dist_fee][]></td>
	<td><input class="{if $smarty.request.a eq 'view'}readonly r{else}keyin{/if}" onchange=mfz(this) size=10 name=expenses[permit_fee][]></td>
</tr>
</tbody>
</table>
<br>
{if $smarty.request.a ne 'view'}
<img src="/ui/table_row_insert.png" align=absmiddle> <a href="javascript:void(add_rows('expenses_rows','newrow_template'))">Add More Rows</a><br>
{/if}
</form>


<p id=submitbtn align=center>
{if $smarty.request.a ne 'view'}
<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >
{/if}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt0.php'">
{if $smarty.request.a ne 'view'}
{if $form.approval_history_id>0}
<input type=button value="Cancel" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_cancel()">
{else}
<input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_delete()">
{/if}
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
{/if}
</p>

{include file=footer.tpl}
<script>
{if $smarty.request.a eq 'view'}
Form.disable(document.f_a);
{else}
init_calendar();
_init_enter_to_skip(document.f_a);
_init_focus_input_class('#expenses_table input');
add_rows('expenses_rows','newrow_template');
{/if}
</script>

