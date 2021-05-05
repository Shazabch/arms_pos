{*
11/23/2011 10:21:43 AM Justin

11/03/2020 5:15 PM Sheila
- Fixed title, table and form css, breadcrumbs

11/05/2020 11:48 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file=header.tpl}
{literal}
<style>
#result {
	width:100%;
	margin:0 auto;
	border:1px solid #ccc;
	margin-top:1px;
	align: left;
}
#result .item {
	
	padding-bottom:10px;
	border-top:1px solid #999;
	padding: 10px;
}
#result .item em {
	color:#0066CC;
	padding-left:5px;
	font-style: normal;
}
#result .block
{
	float:left;
}
#result .bignumber
{
	float:left;
	font-size:12px;
}
#result .br
{
	color:#fff;
	background:#060;
	padding:0 3px;
	font-weight: bold;
}
</style>
{/literal}
<script>
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
var newdiv;
var i=0;

function fsubmit()
{
	var scan_code = document.f1.code.value;
	if(document.f1.show_child.checked == true) var showchild = 1;
	else var showchild = 0;

	if(scan_code == '') return false;

	document.getElementById("result").style.display = "";
	document.getElementById("result").innerHTML = "Loading... Please wait";

	// due to windows phone x.x cannot be use '$'
    /*$.ajax({
		url: php_self, 
		type: "POST", 
		data: {a: "find", code: scan_code, show_child: showchild},
		success: function(msg){
			if($.trim(msg) != ""){
				$('#result').html(msg).show();
			}else{   
			  alert("Nothing to search!");
			  return;
			}
		}
	});*/

	document.f1.a.value = "find";
	document.f1.submit();
	//document.f1.code.value = '';
	//document.f1.code.focus();
}

function checkKey(event){
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
Check Code
</h1>

<span class="breadcrumbs"><a href="home.php"> < Dashboard</a></span>
<div style="margin-bottom: 10px"></div>


<div class="stdframe" style="background:#fff">
<h2>Scan or Enter the code{if $config.enable_sn_bn}, <br />Batch or Serial No{/if} to Search</h2>
<br/>
<form name="f1" class="input" onsubmit="return false;">
	<input type="hidden" name="a" value="">
	<table width="100%">
		<tr>
			<td><input class="txt-width" name="code" onKeyPress="checkKey(event);"></td>
			<td><input type="button" value="Find" onclick="fsubmit();"></td>
		</tr>
		<tr>
			<td colspan="2"><input type="checkbox" name="show_child" value="1"> Show child items</td>
		</tr>
	</table>
	</div>
<br>
<div id="result" {if !$is_find}style="display:none;"{/if}>
	{if is_array($items)}{include file="check_code.items.tpl"}{/if}
</div>

<script>
	document.f1.code.focus();
</script>
{include file="footer.tpl"}