{include file=header.tpl}
{literal}
<script>
function zoom_dept(dept_id)
{
	document.location = '/goods_receiving_note.summary.php?'+Form.serialize(document.f1)+'&department_id='+dept_id;
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="border:1px solid #eee;padding:5px;white-space:nowrap;">
<p>
<b>GRN Date From</b> <input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
&nbsp;&nbsp;&nbsp; <input type=submit value="Refresh">

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

</script>
{/literal}
&nbsp;
<p>
<!--input type=hidden name=a value="list"-->
{if $BRANCH_CODE eq 'HQ'}
<b>Filter by Branch</b>
<select name=branch_id>
<option value="">-- All --</option>
{section name=i loop=$branch}
<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
{/section}
</select>
&nbsp;
{/if}
<b>Department</b>
<select name=department_id>
<option value="">-- All --</option>
{section name=i loop=$dept}
<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
{/section}
</select>
&nbsp;
<b>Vendor</b>
<select name=vendor_id>
<option value="">-- All --</option>
{section name=i loop=$vendor}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/section}
</select>
&nbsp;
<b>Status</b>
{assign var=_st value='All'}
<select name=returned>
<option value="">-- All --</option>
<option value="0" {if $smarty.request.returned eq '0'}selected{assign var=_st value='Not Returned'}{/if}>Not Returned</option>
<option value="1" {if $smarty.request.returned eq '1'}selected{assign var=_st value='Returned'}{/if}>Returned</option>
</select>
</p>

</form>
<br>
<h2>
Date: From {$smarty.request.from|default:"-"} to {$smarty.request.to|default:"-"}
&nbsp;&nbsp;
Department: {$_dp|default:"All"}
&nbsp;&nbsp;
Vendor: {$_vd|default:"All"}
&nbsp;&nbsp;
Status: {$_st|default:"All"}
</h2>
{php}
show_report();
{/php}

{include file=footer.tpl}
