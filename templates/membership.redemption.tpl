{*
9/24/2010 4:35:37 PM Justin
- Added a prompt out window to show the scanned IC image whenever found matched with Card No and NRIC.
- Allowed user to proceed to next page or remain on the current page.

9/29/2010 3:50:26 PM Justin
- Added a prompt out window to show the scanned IC image whenever found matched with Card No and NRIC.
- Allowed user to proceed to next page or remain on the current page.

10/8/2010 5:32:29 PM Justin
- Add div to maintain the buttons in center.

06/29/2020 05:56 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

<script>
{literal}
function check_f(){
	if(document.f_a['card_no'].value.trim()==''){
		alert('Please enter Card No.');
		document.f_a['card_no'].focus();
		return false;
	}
	if(document.f_a['nric'].value.trim()==''){
		alert('Please enter NRIC.');
		document.f_a['nric'].focus();
		return false;
	}
	document.f_a.submit();
}
function toggle_img_div(id){
	if (document.getElementById(id).style.display == 'none'){
		curtain(true);
		show_div(id);
	}else{
		curtain(false);
		hide_div(id);
	}

	return (document.getElementById(id).style.display == 'none');
}

function show_div(id)
{
	var div = document.getElementById(id);
	if (div.style.display=='none') div.style.display='';

	if (div.style.position == 'absolute')
	    div.style.top = (parseInt(document.body.scrollTop)+50)+'px';

	curtain(true);
}

function hide_div(id){
	
	if (document.getElementById(id).style.display!='none'){
	    document.getElementById(id).style.display='none';
	    curtain(false);
	}
}

function curtain_clicked(){
    $('ic_org').hide();
    curtain(false);
}
{/literal}
</script>

<div id="ic_org" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:150px; left:150px;z-index:20000;">
	<div class=small style="position:absolute; right:10px;">
		<a href="javascript:void(hide_div('ic_org'))"><img src=ui/closewin.png border=0 align=absmiddle></a>
	</div>
	<img src="{$ic_path}"><br /><br />
	<div align="center">
		<input type=button value="Continue" onclick="hide_div('ic_org'); document.f_a.proceed.value=1; document.f_a.submit();">
		<input type=button value="Back" onclick="hide_div('ic_org'); document.f_a.card_no.value = ''; document.f_a.nric.value = '';">
	</div>
</div>

<div align="center">
<h1 class="text-primary mt-3">{$PAGE_TITLE}</h1>
<form name="f_a" class="biginput" onSubmit="return check_f();" method="post">

<div class="card mx-3">
	<div class="card-body">
		<input type="hidden" name="a" value="check_and_show_items" />
	<table>
	    <tr>
	        <th><h3 class="form-label">Card No : </h3></th>
	        <td><input class="form-control" type="text" name="card_no" value="{$card_no}" /></td>
	    </tr>
	    <tr>
	        <th><h3 class="form-label">NRIC : </h3></th>
	        <td><input class="form-control" type="text" name="nric" value="{$nric}" /></td>
	    </tr>
	</table>
	<div align="center">
		<input type="hidden" name="proceed" />&nbsp;&nbsp;
		<input class="btn btn-primary  mt-2" style="font-size: 13px" type=button value="Enter" onclick="check_f()">
	</div>
	</div>
</div>
</form>

{if $err}
<ul style="color:red;">
	{foreach from=$err item=e}
	    <li>{$e}</li>
	{/foreach}
</ul>
{/if}

</div>
<script>
var ic_path = "{$ic_path}";

{literal}
if(ic_path){
	toggle_img_div('ic_org');
}
new Draggable('ic_org');
{/literal}
</script>
{include file='footer.tpl'}
