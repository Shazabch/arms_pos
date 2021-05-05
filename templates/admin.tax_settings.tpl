{**}
{include file=header.tpl}

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
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var TAX_SETTINGS_MODULE = {
	f_a: undefined,
	initialize : function(){
		this.f_a = document.f_a;
	},
	toggle_activation : function(){
		if($('is_enable_tax').checked == false){
			if(!confirm("Are you sure want to away from Tax registered?")){
				$('is_enable_tax').checked = true;
				return;
			}
			this.f_a.active.value = 0;
		}else{
			this.f_a.active.value = 1;
		}
	},
	check_form : function(){
		if(!confirm('Are you sure save the tax settings.'))	return false;
		document.f_a.submit();
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{if $err}
<div id="err"><div class="errmsg"><ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{if $smarty.request.save}<img src="ui/approved.png" title="Saved Tax information" border="0"> <b>Saved Tax information.</b><br /><br />{/if}

<div class="stdframe">
<label><input type="checkbox" onchange="TAX_SETTINGS_MODULE.toggle_activation();" id="is_enable_tax" value="1" {if $form.active}checked{/if} {if !$sessioninfo.privilege.ADMIN_TAX_EDIT}disabled{/if} /> <b>This company is under Tax registered</b></label>

<form method="post" name="f_a" onSubmit="return TAX_SETTINGS_MODULE.check_form();" >
	<input type="hidden" name="a" value="update">
	<input type="hidden" name="active" value="{$form.active}">
	
	<table id="tax_settings">
		<tr>
			<td><b>Tax Start Date</b>&nbsp;&nbsp;</td>
			<td>
				<input size="10" type="text" name="tax_start_date" value="{$form.tax_start_date}" id="date" {if !$sessioninfo.privilege.ADMIN_TAX_EDIT}disabled{/if}>
				{if $sessioninfo.privilege.ADMIN_TAX_EDIT}<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">{/if}
			</td>
		</tr>
	</table>
	
	{if $sessioninfo.privilege.ADMIN_TAX_EDIT}
	<p align="center">
		<input type="submit" value="Save" id="save_btn"> 
	</p>
	{/if}
</form>
</div>

{literal}
<script type="text/javascript">
	TAX_SETTINGS_MODULE.initialize();

    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}

{include file=footer.tpl}
