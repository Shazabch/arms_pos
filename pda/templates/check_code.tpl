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
	document.getElementById("result").innerHTML = "{/literal}{$LNG.LOADING_MSG}{literal}";

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



<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.CHECK_CODE}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

<!-- Search form -->
<div class="row mt-2 ">
	<div class="col-lg-12 col-md-12">
		<div class="card col-md-8">
			<form name="f1" class="input" onsubmit="return false;">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<label>{$LNG.SCAN_OR_ENTER_CODE}{if $config.enable_sn_bn}, {$LNG.BATCH_OR_SERIAL_NO}{/if} {$LNG.TO_SEARCH}</label>
						<div class="row row-xs">
							<input type="hidden" name="a" value="">
							<div class="col-md-10 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text"  name="code" onKeyPress="checkKey(event);">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="{$LNG.FIND}" onclick="fsubmit();">
							</div>
						</div>
					</div>
					<div class="checkbox">
						<div class="custom-checkbox custom-control">
							<input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" name="show_child" value="1" id="checkbox-2">
							<label for="checkbox-2" class="custom-control-label mt-1" name="show_child" value="1">{$LNG.SHOW_CHILD_ITEMS}</label>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->

<div id="result" {if !$is_find}style="display:none;"{/if}>
	{if is_array($items)}{include file="check_code.items.tpl"}{/if}
</div>

<script>
	document.f1.code.focus();
</script>
{include file="footer.tpl"}