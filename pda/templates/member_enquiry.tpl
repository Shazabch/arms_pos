{*
1/29/2021 12:45 PM William
- Enhanced to add Member no, Phone to search result table
*}

{include file=header.tpl}
{literal}
<style>
#result {
	width:100%;
	margin:0 auto;
	margin-top:1px;
	align: left;
}
</style>
{/literal}
<script>
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
function fsubmit()
{
	var scan_code = document.f_a.member_no.value;
	if(scan_code == '') return false;

	document.getElementById("result").style.display = "";
	document.getElementById("result").innerHTML = "Loading... Please wait";
	
	document.f_a.a.value = "check_member";
	document.f_a.submit();
}

function check_member(event){
    if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		fsubmit();
	}else{
	    return false;
	}
}

</script>
{/literal}

<!-- Replacement Iten Popup -->
<h1>
Member Enquiry
</h1>

<span class="breadcrumbs"><a href="home.php"> < Dashboard</a></span>
<div style="margin-bottom: 10px"></div>

<div class="stdframe" style="background:#fff">
	<h2>Scan or Enter member no/member name/nric/phone</h2>
	<br/>
	<form name="f_a" method="post" onSubmit="return false;">
		<input type="hidden" name="a" />
		<table width="100%">
			<tr>
				<td><input class="txt-width" name="member_no" value="{$form.member_no}" onKeyPress="check_member(event);"></td>
				<td><input type="button" value="Find" onclick="fsubmit();"></td>
			</tr>
		</table>
		<br />
		<span style="color:red;">
			{if $err}
				<ul>
				{foreach from=$err item=e}
					<li>{$e}</li>
				{/foreach}
				</ul>
			{/if}
		</span>
	</form>
	<br />

	<div id="result">
	{if $member_list}
		<table width="100%" border="1" cellspacing="0" cellpadding="4">
			<tr>
				<th>&nbsp;</th>
				<th>Member No/NRIC/Name</th>
				<th>Phone</th>
			</tr>
			{foreach from=$member_list item=r}
				<tr>
					<td width="20">
						<a href="member_enquiry.php?a=get_member_info&nric={$r.nric}"><img src="/ui/view.png" border="0" title="View" /></a>
					</td>
					<td>
						{if $r.card_no}<span>{$r.card_no}</span></br>{/if}
						{if $r.nric}<span>{$r.nric}</span></br>{/if}
						{if $r.name}<span>{$r.name}</span>{/if}
					</td>
					<td>{$r.phone_3}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
	</div>
</div>

<script>
	document.f_a.member_no.focus();
</script>
{include file="footer.tpl"}