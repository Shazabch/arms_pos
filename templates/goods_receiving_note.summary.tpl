{*
10/29/2007 4:43:34 PM gary
- add vendor option.

3/19/2010 3:04:50 PM Andy
- Add note to let user know how system indicate the department.

10/27/2010 2:46:26 PM Alex
- change zoom_dept() function by replace the value of f1.department_id

1/27/2011 6:36:33 PM Alex
- add filter show by

6/29/2011 9:47:21 AM Justin
- Added new JS function to hide/show filter option for GRR Document Type.

6/26/2012 4:42 PM Andy
- Add allow to show "Itemize" grn if choose "All Department".

7/24/2012 10:35 AM Andy
- Add print and export excel function.

4/25/2018 10:50 AM Justin
- Enhanced to show foreign currency.

8/26/2019 2:46 PM Andy
- Fixed when show by all department and using itemise, don't filter doc_type.
- Fixed HQ license popup issue.

06/24/2020 4:04 PM Sheila
- Updated button css
*}
{include file=header.tpl}

{if !$no_header_footer}
{literal}
<script type="text/javascript">
function zoom_dept(dept_id){
    document.f1.department_id.value=dept_id;
    document.f1['doc_type'].value='';
	//document.location = '/goods_receiving_note.summary.php?'+Form.serialize(document.f1);
	submit_form();
}

function do_print_preview(id,bid){
	window.open('goods_receiving_note.php?id='+id+'&branch_id='+bid+'&a=print&print_grn_perform_report=1&noprint=1','','width=800,height=600,scrollbars=yes,resizable=yes');

}

function hide_doc_type(obj){
	if(obj.value == "") $("span_doc_type").style.display = "none";
	else $("span_doc_type").style.display = "";
}

function dept_changed(sel){
	hide_doc_type(sel);
		
	if(sel.value == "") $("span_itemize").show();
	else $("span_itemize").hide();
	

}
</script>
{/literal}
<h1>GRN Summary</h1>

<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="border:1px solid #eee;padding:5px;white-space:nowrap;">
<input type="hidden" name="export_excel" />
<input type="hidden" name="a" value="show" />

<p>
<b>GRR Receive From</b>
<input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; 
<b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

function submit_form(type){
	document.f1['export_excel'].value = 0;
	
	if(type=='excel'){
		document.f1['export_excel'].value = 1;
	}
	
	document.f1.submit();
}
</script>
{/literal}
&nbsp;
<!--b>By user</b>
<select name=user_id>
<option value=0>-- All --</option>
{section name=i loop=$user}
<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
{/section}
</select-->
</p>
<p>
<!--input type=hidden name=a value="list"-->
{if $BRANCH_CODE eq 'HQ'}
<b>Filter by Branch</b>
<select name="branch_id">
<option value="">-- All --</option>
{section name=i loop=$branch}
<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
{/section}
</select>
&nbsp;
{/if}
<b>Department</b>
<select name="department_id" onchange="dept_changed(this);">
<option value="">-- All --</option>
{section name=i loop=$dept}
<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
{/section}
</select>
<span id="span_itemize" style="{if $smarty.request.department_id}display:none;{/if}">
	<input type="checkbox" name="itemize" value="1" {if $smarty.request.itemize}checked {/if} /> <b>Itemize</b>
</span>

&nbsp;
<span id="span_doc_type" {if !$smarty.request.department_id}style="display:none;"{/if}>
<b>Document Type</b>
<select name="doc_type">
<option value="">-- All --</option>
{section name=i loop=$doc_type}
<option value="{$doc_type[i]}" {if $smarty.request.doc_type eq $doc_type[i]}selected{assign var=_dt value=`$doc_type[i]`}{/if}>{$doc_type[i]}</option>
{/section}
</select>
</span>
<!--b>Status</b>
<select name=status>
<option value=0 {if $smarty.request.status == 0}selected{/if}>All</option>
<option value=1 {if $smarty.request.status == 1}selected{/if}>Draft</option>
<option value=2 {if $smarty.request.status == 2}selected{/if}>Proforma</option>
<option value=3 {if $smarty.request.status == 3}selected{/if}>Actual PO</option>
</select-->
</p>
<p>
<b>Vendor</b>
<select name=vendor_id>
<option value="">-- All --</option>
{section name=i loop=$vendor}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/section}
</select>
&nbsp;
<b>Show by</b>
<select name="show_by">
	<option value="all" {if $smarty.request.show_by eq 'all'}selected {/if}>-- All --</option>
	<option value="ibt" {if $smarty.request.show_by eq 'ibt'}selected {/if}>IBT only</option>
	<option value="not_ibt" {if $smarty.request.show_by eq 'not_ibt'}selected {/if}>Not IBT</option>
</select>
&nbsp;
<input class="btn btn-primary" type="button" onClick="submit_form();" value="Refresh" />
<input class="btn btn-primary" type="button" value="Print" onClick="window.print();" />
{if $sessioninfo.privilege.EXPORT_EXCEL}
	<button class="btn btn-primary" onClick="submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
{/if}
</p>
{/if}

<p>Note:<br />
* Department is base on item Department.<br />
{if $config.foreign_currency}* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}{/if}
</p>
</form>

{if $by_department}
	{include file="goods_receiving_note.summary.detail.tpl"}
{else}
	{include file="goods_receiving_note.summary.top.tpl"}
{/if}
{*
{if $smarty.request.from ne ''}
{php}
show_report();
{/php}
{/if}
*}

{include file=footer.tpl}
