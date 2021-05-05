{*

*}

{include file=header.tpl}
{if !$no_header_footer}
{literal}
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
{/literal}
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function check_form(type){

	//branch and date cannot same
	if ($('from_branch_id').value == $('to_branch_id').value && $('from_date').value == $('to_date').value){
	    alert("Invalid transfer data on same branch and same date");
		return;
	}


	var to_sel = $('to_branch_id');
	var to_selected_index = to_sel.selectedIndex;
	var to_selected_option = to_sel.options[to_selected_index];

	var to_branch_code=$(to_selected_option).getAttribute('code');

	var to_date= $('to_date').value;


	if (type== 'copy'){
	    document.f_a['a'].value="copy_stock";
		var msg=confirm("This will delete "+to_date+" data of "+to_branch_code+" before transfering. Are you sure?");

	}else if (type== 'reset'){
        document.f_a['a'].value="reset_stock";
		var msg=confirm("This will delete "+to_date+" data of "+to_branch_code+". Are you sure?");
	}

	if (msg){
		$$('.btn').each(function(obj){
			obj.update(_loading_);
		});

		document.f_a.submit();
	}
}

function init_calendar(){
    Calendar.setup({
        inputField     :    "from_date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_from_date",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "to_date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_to_date",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
}
</script>
<style>

.suc{
	color: #090;
}

.big{

	font-size:16px;
}

</style>
{/literal}
{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if $success}
<ul class=suc>
{foreach from=$success item=s}
<li> {$s}
{/foreach}
</ul>
{/if}
	<form name="f_a" method=post class="form" >
		<input type="hidden" name="ajax" value="1">
		<input type="hidden" name="a" >
		
		<table>
		    <tr>
				<th>From:</th>
				<td>Branch</td>
                <td>
					<select name="from_branch_id" id="from_branch_id">
					    {foreach from=$branches item=b}
					        <option value="{$b.id}" {if $smarty.request.from_branch_id eq $b.id}selected {/if}>{$b.code}</option>
					    {/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td>Date</td>
				<td>
					<input type="text" name="from_date" value="{$smarty.request.from_date}" id="from_date" readonly="1" size=12 />
					<img align="absmiddle" src="ui/calendar.gif" id="img_from_date" style="cursor: pointer;" title="Select Date"/> &nbsp;
				</td>
			</tr>
		    <tr>
		        <th>To:</th>
		        <td>Branch</td>
				<td>
					<select name="to_branch_id" id="to_branch_id">
					    {foreach from=$branches item=b}
					        <option value="{$b.id}" code="{$b.code}" {if $smarty.request.to_branch_id eq $b.id}selected {/if}>{$b.code}</option>
					    {/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
				</td>
				<td class="r">Date</td>
		        <td>
					<input type="text" name="to_date" value="{$smarty.request.to_date}" id="to_date" readonly="1" size=12 />
					<img align="absmiddle" src="ui/calendar.gif" id="img_to_date" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
				</td>
		</table>

		<span class="btn"><input type=button onclick="check_form('copy');" value="Copy Stock"></span>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<span class="btn"><input type=button onclick="check_form('reset')" value="Reset Stock"></span>
	</form>



<script>
init_calendar();
</script>

{if !$no_header_footer}
{include file=footer.tpl}
{/if}
