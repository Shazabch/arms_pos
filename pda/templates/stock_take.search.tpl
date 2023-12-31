{*
9/6/2012 6:10 PM Justin
- Added to check single server mode while show out the branch list for HQ.

5/24/2013 11:56 AM Justin
- Enhanced to activate save function while press enter on date, location or shelf.

2/24/2014 4:24 PM Andy
- This tpl no longer use.

04/11/2020 3:24PM Rayleen
- Modified page style/layout. 
*}

{* include file='header.tpl'}

<script type="text/javascript">
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
function submit_form(){
	if(document.f_a['date'].value==''){
		notify('error','{/literal}{$LNG.ENTER_DATE_ERR}{literal}','center')
		document.f_a['date'].focus();
		return false;
	}else if(document.f_a['location'].value==''){
		notify('error','{/literal}{$LNG.ENTER_LOCATION_ERR}{literal}','center')
		document.f_a['location'].focus();
		return false;
	}else if(document.f_a['shelf'].value==''){
		notify('error','{/literal}{$LNG.ENTER_SHELF_ERR}{literal}','center')
		document.f_a['shelf'].focus();
		return false;
	}
  
	var result =  validateTimestamp(document.f_a['date'].value);
	if(!result){
		notify('error','{/literal}{$LNG.INVALID_DATE_FORMAT_ERR}{literal}','center')
		return;
	}

	document.f_a.submit();
}

function validateTimestamp(timestamp){
	if (!/\d{4}\-\d{1,2}\-\d{1,2}/.test(timestamp)) {
        return false;
    }

    var temp = timestamp.split(/[^\d]+/);

    var year = parseFloat(temp[0]);
    var month = parseFloat(temp[1]);
    var day = parseFloat(temp[2]);
   
	if(month==4||month==6||month==9||month==11){
		if(day>30)	return false;
	}
	if(month==2){
		if(day>28&&year%4!=0)	return false;
	}

    return (month<13 && month>0) && (day<32 && day>0);

}

// function when user press enter
function form_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		this.pagenum = 1;
		
		submit_form();
	}
}

{/literal}
</script>

<span class="breadcrumbs"><a href="home.php">{$LNG.DASHBOARD}</a> > <a href="home.php?a=menu&id=custom">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="view_items" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<table class="small">
	{if $BRANCH_CODE eq "HQ" && $config.single_server_mode}
		<tr>
			<td valign="top"><b>{$LNG.BRANCH}</b></td>
			<td>
				<select name="branch_id" size="7">
					{foreach from=$branches item=r}
						<option value="{$r.id}" {if $form.branch_id eq $r.id || $BRANCH_CODE eq $r.code}selected{/if}>{$r.code}</option>
					{/foreach}
				</select>
			</td>
		</tr>
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	<tr>
		<td valign="top"><b>{$LNG.DATE}</b></td>
		<td>
			<input type="text" name="date" size="10" value="{$form.date|default:$smarty.now|date_format:'%Y-%m-%d'}"  onkeypress="form_keypress(event);" /> (YYYY-MM-DD)
		</td>
	</tr>
	<tr>
		<td><b>{$LNG.LOCATION}</b></td>
		<td>
			<input name="location" value="{$form.location}" onkeypress="form_keypress(event);" />
		</td>
	</tr>
	<tr>
		<td><b>{$LNG.SHELF}</b></td>
		<td>
			<input name="shelf" value="{$form.shelf}" onkeypress="form_keypress(event);" />
		</td>
	</tr>
</table>
<div align=center>
	<input type="button" value="{$LNG.SEARCH}" onClick="submit_form();" /> 
</div>
</form>
</div>
{literal}
<script>
document.f_a['date'].focus();
</script>
{/literal}

{include file='footer.tpl' *}
