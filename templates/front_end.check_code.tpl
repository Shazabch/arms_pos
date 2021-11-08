{*
8/13/2010 10:58:15 AM Andy
- Add Replacement Item Group popup in front end check code.

5/4/2011 3:15:11 PM Justin
- Added checkbox that allow to show child sku items.
- Amended the label from "Scan or Enter the code" become "Scan or Enter the code, Batch or Serial No".

2/29/2012 2:26:43 PM Justin
- Added config check to show/hide Batch or Serial No label.
*}

{include file=header.tpl}
{include file=front_end.tpl}
{literal}
<style>
#result {
	width:700px;
	margin:0 auto;
	border:1px solid #ccc;
	margin-top:1px;
	padding:10px;
}
#result .item {
	font-size:11px;
	padding-bottom:10px;
	border-top:1px solid #999;
}
#result .item em {
	color:#00f;
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
<script>
var newdiv;
var i=0;

function fsubmit()
{
	document.f1.code.focus();
	if (document.f1.code.value == '') return false;
	
	$('result').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	
	new Ajax.Updater(
		'result','front_end.check_code.php', {
		    parameters:Form.serialize(document.f1),
		    evalScripts:true
		}
	)
	document.f1.code.value = '';
	if($('result').style.display == 'none') $('result').style.display = '';
	return false;
}

function show_replacement_items(sid){
    var params = {
		'sid': sid,
		'can_click_item_row': 1
	}
	replacement_item.show_popup(params);
}

function replacement_item_code_clicked(params){
	var sku_item_code = params['sku_item_code'];

	if(!sku_item_code)  return;

	document.f1['code'].value = sku_item_code;
	fsubmit();
	default_curtain_clicked();
}
</script>
{/literal}

<!-- Replacement Iten Popup -->
{if $config.enable_replacement_items}{include file='replacement_items_popup.tpl'}{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Check Code</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="alert alert-primary mx-3 rounded" style="max-width: 350px;">
	- Use this module for SKU lookup.
</div>
<div class="card mx-3">
	<div class="card-body">
		<div align="center">
			<h2 align="center" class="form-label">Scan or Enter the code{if $config.enable_sn_bn}, Batch or Serial No{/if} to Search</h2>
			
			<form name="f1" class="biginput" onsubmit="return fsubmit()">
			<input type="hidden" name="a" value="find">
				<table>
					<tr>
						<td><input class="form-control" size="20" name="code"></td>
						<td>&nbsp;<input class="btn btn-primary" type="submit" value="Find"></td>
					</tr>
					<tr>
						<td colspan="2"><input type="checkbox" name="show_child" value="1"> Show child items</td>
					</tr>
				</table>
			</div>
	</div>
</div>
<br>
<div id="result" style="display:none;">
</div>
{include file=footer.tpl}
