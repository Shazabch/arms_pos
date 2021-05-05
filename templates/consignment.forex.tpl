{*
9/4/2012 4:15:10PM Drkoay
- add Currency Table
- add js function add_row(), remove_row(), save(), sort(), validate()

9/6/2012 9:21 AM Drkoay
- config masterfile_update_sku_items_price_on_approve change to consignment_new_sku_use_currency_table

4/27/2015 5:30 PM Justin
- Enhanced the currency search can now search with description.
*}
{include file="header.tpl"}
{literal}
<style>
.sold {
	color:#f00;
}

#serial_no_items tr:nth-child(even){
	background-color:#dddddd;
}

#serial_no_items tr:nth-child(even){
	background-color:#eeeeee;
}

/* standard style for report table */
.serial_no_tbl {
	border-top:1px solid #000;
	border-right:1px solid #000;
	white-space:no-wrap;
}

.serial_no_tbl tr.header td, .serial_no_tbl tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.serial_no_tbl tr.sn_dtl:hover{
	background:#ffffcc !important;
}

.serial_no_tbl textarea {
	background-color:#fff;
}

#currency_details tr:nth-child(odd),#currency_table tr:nth-child(odd){
	background-color: #eeeeee;
}

.hidden{
	display: none;
}

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function search(){
	uc(document.f_a.elements['search_code']);
	var search_code = document.f_a.elements['search_code'].value;
	var hidden = 0;
	var visible = 0;
	//if(!code) return false;
	
	var e = $('currency_details').getElementsByClassName('currency_code');
	var total_row=e.length;
	
	$('span_code_searching').style.display = "";
	
	$A(e).each(
		function (r,idx){
			if(r.name.indexOf("currency_code[")==0){
				var code = r.value;
				//var myregexp = /\[([A-Z]+)\]/g;
				re = RegExp(search_code);
				var currency_code = $(r).readAttribute('currency_code');
				var currency_description = (document.f_a['currency_description['+currency_code+']'].value).toUpperCase();
				//var match = re.exec(code);

				if(search_code && !re.test(code) && !re.test(currency_description)){
					$('currency_code_'+r.value).style.display = "none";
					r.disabled = true;
					hidden++;
				}else{
					$('currency_code_'+r.value).style.display = "";
					r.disabled = false;
					visible++;
				}
			}
		}
	);
	if(hidden == total_row){
		$("save_area").style.display = "none";
		$("no_data").style.display = "";
	}else{
		$("save_area").style.display = "";
		$("no_data").style.display = "none";
	}
	$('span_code_searching').style.display = "none";
}

function get_currency_history(currency_code){
	if (currency_code==undefined || currency_code==""){
		alert("Invalid Currency Code!");
		return false;
	}
	//change pop up title
	$('currency_title').update("Currency History for Code: "+currency_code);

	// loading
	$('currency_history_content').innerHTML = _loading_;
	new Ajax.Updater('currency_history_content',phpself+"?a=history&curr_code="+currency_code,{evalScripts:true});
	showdiv('currency_history');
	$('currency_history').style.zIndex = 10000;
	curtain(true);
}

function show_more_result(){
	var row=0;

	$$("#history_id .hidden").each(function(ele,index){
		if (!ele.hasClassName("hidden"))   return false;

		var child = ele;
		while (child && child.hasClassName("hidden")){
			child.removeClassName('hidden');
			child = child.next();
		}
	});
	if ($$('#history_id .hidden') == "")   $('show_result_id').hide();
}

function curtain_clicked(){
	hidediv('currency_history');
	$('currency_history').style.zIndex = "";
	curtain(false);
}

function disableEnterKey(e){
    var key;

    if(window.event) key = window.event.keyCode;	//IE
    else key = e.which;	//firefox

    if(key == 13){
		search();
		return false;
	}else return true;
}

function add_row(){
	$('currency_table').cleanWhitespace();	
	var element=$('currency_table').childNodes[0].cloneNode(true);
		
	$(element).getElementsBySelector("input").each(function(node){		
		node.value="";		
	});
	
	$('currency_table').appendChild(element); 	
}

function remove_row(obj){
	if(confirm('Are you sure?')){
		var numrows = $$('#currency_table tr').length;
		if(numrows<=1){
			$(obj.parentNode.parentNode).getElementsBySelector("input").each(function(node){		
				node.value="";		
			});
		}
		else{
			obj.parentNode.parentNode.remove();
		}
	}
}

function save(){
	sort();
	
	return validate();
}

function sort(){
	var arr=$$('#currency_table tr');	
	for(var i=0; i<arr.length; i++){
		for(var j=1; j<(arr.length-i); j++){			
			var from1=$(arr[j-1]).getElementsBySelector('[name="data[from][]"]')[0].value;
			var from2=$(arr[j]).getElementsBySelector('[name="data[from][]"]')[0].value;
			
			if(parseFloat(from1)==parseFloat(from2)){
				var to1=$(arr[j-1]).getElementsBySelector('[name="data[to][]"]')[0].value;
				var to2=$(arr[j]).getElementsBySelector('[name="data[to][]"]')[0].value;
				
				if(parseFloat(to1)>parseFloat(to2)){
					$('currency_table').insertBefore($(arr[j]), $(arr[j-1]));
					t=arr[j-1];
					arr[j-1]=arr[j]
					arr[j]=t;				
				}				
			}			
			else if(parseFloat(from1)>parseFloat(from2)){
				$('currency_table').insertBefore($(arr[j]), $(arr[j-1]));
				t=arr[j-1];
				arr[j-1]=arr[j]
				arr[j]=t;				
			}
		}
	}
}

function validate(){
	var arr=$$('#currency_table input');
	
	for(var i=0; i<arr.length; i++){
		var v=parseFloat(arr[i].value);
		if(isNaN(v)){
			alert('Please don\'t let the field blank.');
			arr[i].focus();
			return false;
		}
	}
		
	var arr=$$('#currency_table tr');
	
	for(var i=0; i<arr.length; i++){
		var to=parseFloat($(arr[i]).getElementsBySelector('[name="data[to][]"]')[0].value);
		var from=parseFloat($(arr[i]).getElementsBySelector('[name="data[from][]"]')[0].value);
		
		if(isNaN(to)) to=0;
		if(isNaN(from)) from=0;
		
		if(to<from){
			alert('Invalid price range.\n" To " price must heigher than " From " price.');
			$(arr[i]).getElementsBySelector('[name="data[to][]"]')[0].focus();
			return false;
		}
		
		if(arr[i-1]!=undefined){
			var pto=parseFloat($(arr[i-1]).getElementsBySelector('[name="data[to][]"]')[0].value); //previous row to
					
			if(isNaN(pto)) pto=0;			
				
			if(parseFloat(from) <= parseFloat(pto)){
				alert('Price range overlapped.');
				$(arr[i]).getElementsBySelector('[name="data[from][]"]')[0].focus();
				return false;
			}
		}
	}
	return true;
}

{/literal}
</script>

<iframe width=1 height=1 style="visibility:hidden" id=test></iframe>

<div id="currency_history" style="padding:0px;border:1px solid #000;overflow:hidden;width:300px;height:320px;position:absolute;background:#fff;display:none;">
	<div id="currency_history_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span id="currency_title" style="float:left;"></span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="Element.hide('currency_history'); curtain(false);" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
<div id="currency_history_content" style="height:290px;overflow:auto;"></div>
</div>

<h1>{$PAGE_TITLE}</h1>
<div class=stdframe style="background:#fff;">
<div id=history_popup style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>

{if $log}
Status:
<div><ul>
{foreach from=$log item=msg}
<li> {$msg}
{/foreach}
</ul></div>
{/if}

{if $errm.top}
<div id="err"><div class="errmsg"><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name="f_a" method="post">
<input type="hidden" name="a" value="save">
<table>
	<tr>
		<th>Search Currency Code / Description:</th>
		<td><input id="code" name="search_code" onclick="this.select()" onkeyup="if(!this.value) search();" style="font-size:14px;width:150px;" onkeypress="return disableEnterKey(event);"></td>
		<td><input type="button" value="Search" onclick="search();" class="clickable">&nbsp;
			<span id="span_code_searching" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Searching... Please wait</span>
		</td>
	</tr>
</table>
<br />
<div id="currency_item_div" style="width:500px;">
	{include file="consignment.forex.items.tpl"}
</div>
</form>

{if $config.consignment_new_sku_use_currency_table}

<br/><br/>
<form name="f_b" method="post" onsubmit="return save();">
	<input type="hidden" name="a" value="save_currency">
<fieldset style="width:60%;">
	<legend><b><font size="2">Currency Table</font></b></legend>
	- For those price not in this table will not be connect
	<table style="border:1px solid #999; padding:1px;" class="input_no_border body" border=0 cellspacing=1 cellpadding=1>
		<tr bgcolor="#ffee99">
			<th style="background: #97cbff;"></th>
			<th style="background: #97cbff;">From (RM)</th>
			<th style="background: #97cbff;">To (RM)</th>
			<th width="30">&nbsp;</th>
			{foreach from=$currency_list item=curr_type key=curr_id name=currency}
			<th>{$curr_id}</th>
			{/foreach}
		</tr>
		<tbody id="currency_table">
		{foreach from=$consignment_currency_table key=k item=i}
		<tr>
			<td><a href="javascript:void(0);" onclick="remove_row(this);"><img src="ui/cancel.png" alt="Remove"/></a></td>
			<td><input class="r" type="text" name="data[from][]" value="{$i.from|string_format:"%.2f"|default:""}" onchange="this.value=round2(this.value);"/></td>
			<td><input class="r" type="text" name="data[to][]" value="{$i.to|string_format:"%.2f"|default:""}" onchange="this.value=round2(this.value);"/></td>
			<td>&nbsp;</td>
			{foreach from=$currency_list item=curr_type key=curr_id name=currency}
			<td><input class="r" type="text" name="data[currency][{$curr_id}][]" value="{$i.currency.$curr_id|default:""}" onchange="this.value=float(this.value);"/></td>
			{/foreach}
		</tr>
		{foreachelse}
		<tr>
			<td><a href="javascript:void(0);" onclick="remove_row(this);"><img src="ui/cancel.png" alt="Remove"/></a></td>
			<td><input class="r" type="text" name="data[from][]" value="" onchange="this.value=round2(this.value);"/></td>
			<td><input class="r" type="text" name="data[to][]" value="" onchange="this.value=round2(this.value);"/></td>
			<td>&nbsp;</td>
			{foreach from=$currency_list item=curr_type key=curr_id name=currency}
			<td><input class="r" type="text" name="data[currency][{$curr_id}][]" value="" onchange="this.value=float(this.value);"/></td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
	</table>
	<center>
	<button type="button" onclick="javascript:add_row();">Add New Row</button>
	<button type="submit">Save</button>
	</center>
</fieldset>
<form>
{/if}
<br/><br/>
{include file="footer.tpl"}
{literal}
<script>
new Draggable('currency_history');
</script>
{/literal}
