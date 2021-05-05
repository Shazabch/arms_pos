{*
5/24/2013 11:56 AM Justin
- Enhanced to activate save function while press enter on date, location or shelf.

2/24/2014 4:24 PM Andy
- Fix the variable bug. (sometime "loc" sometime "location").

1/24/2015 3:58 PM Justin
- Bug fixed on system will allow user to save whitespacing date, location or shelf. 

9/22/2020 10:56 AM William
- Enhanced to show error message.

11/03/2020 5:15 PM Sheila
- Fixed title, table and form css

11/05/2020 11:48 AM Sheila
- Fixed breadcrumbs

11/09/2020 10:48 AM Sheila
- Removed size=10 on date

*}

{include file="header.tpl"}

<script type="text/javascript">
var do_type = '{$do_type}';
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
function submit_form(){
	document.f_a['date_t'].value = document.f_a['date_t'].value.trim();
	document.f_a['location'].value = document.f_a['location'].value.trim();
	document.f_a['shelf'].value = document.f_a['shelf'].value.trim();
	if(document.f_a['date_t'].value==''){
		alert('Please enter Date.');
		document.f_a['date_t'].focus();
		return false;
	}else if(document.f_a['location'].value=='')
	{
      alert('Please key in Location');
      document.f_a['location'].focus();
  		return false;
  }else if(document.f_a['shelf'].value=='')
	{
      alert('Please key in Shelf');
      document.f_a['shelf'].focus();
  		return false;
  }
  
  var result =  validateTimestamp(document.f_a['date_t'].value);
  if(!result)
  {
      alert("Invalid Date Format");
      return;
  }
  document.f_a.submit();
  //chk_date();
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
function back_home()
{
  window.location="home.php";
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

<h1>
Stock Take
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=custom">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>
{if $errm}
	<ul style="color:red;">
	    {foreach from=$errm item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<input type="hidden" name="do_type" value="{$do_type}" />
<table cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr>
	    <th align="left">Date</th>
	    <td>
			<input type="text" id="inp_do_date" name="date_t" value="{$form.date_t|default:$smarty.now|date_format:'%Y-%m-%d'}"  onkeypress="form_keypress(event);" /> <span class="small"> (YYYY-MM-DD) </span>
		</td>
	</tr>
	<tr>
	    <th align="left">Location</th>
	    <td>
			<input name="location" class="txt-width" {if $location}value="{$location}"{/if} onkeypress="form_keypress(event); "/>
		</td>
	</tr>
	
  <tr>
      <th align="left" valign="top">Shelf</th>
 		<td>
 		  <input name="shelf" class="txt-width" {if $shelf}value="{$shelf}"{/if} onkeypress="form_keypress(event);" />
 		</td>
 	</tr>
 	
</table>
<div align="center">
	<input type="button" class="btn btn-primary" value="Save" onClick="submit_form();" /> 
</div>
</form>
</div>
{literal}
<script>
document.f_a['date_t'].focus();
</script>
{/literal}

{include file="footer.tpl"}
