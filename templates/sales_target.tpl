{*
1/31/2012 10:41:32 AM Justin
- Fixed the wrong display of current branch code when filter branch from HQ.

10/23/2018 5:05 PM Justin
- Enhanced the module to compatible with new SKU Type.
*}
{include file=header.tpl}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = '{$smarty.request.branch}';
</script>

{literal}
<style>
#table_sheet { font: 10px "MS Sans Serif" normal;}
td, th { white-space:nowrap; }
.border{
	background-color:black;
}

.display{
	background-color:white;
	text-align:right;
}
.keyin{
	//background-color:yellow;
	text-align:right;
}
.optional{
	background-color:#ffc;
	text-align:right;
}
.weekly{
	background-color:#e4efff;
	text-align:right;
	FONT-WEIGHT: bold;
}
.monthly{
	background-color:#ccc;
	text-align:right;
	FONT-WEIGHT: bold;
}
.positive{
	color:blue;
	text-align:right;
}
.negative{
	color:red;
	text-align:right;
}
.zero{
	color:green;
	text-align:right;
}

.CONSIGN{
	background: #eee;
}

.OUTRIGHT{
	background: #fff;
}

.con_sunday{
	color:red;
	background: #ffa;
}

.out_sunday{
    color:red;
    background: #ffd;
}
</style>

<script>
var last_obj;

function do_edit(obj){
    last_obj = obj;
    
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}

function save(){
	Element.hide('edit_popup');
	
	if(float(last_obj.innerHTML)!=float($('edit_text').value)){
	    var data = last_obj.id.split('_');
	    date = data[0];
		category_id = data[1];
		sku_type = data[2];
	    
		last_obj.innerHTML = 'Saving..';
		//last_obj.style.backgroundColor = '#f0f0f0';
		
		var newp = last_obj;
		new Ajax.Updater(newp, phpself,
		{
		    method: 'post',
		    parameters:
		    {
		        a: 'save',
				date: date,
				category_id: category_id,
				sku_type: sku_type,
				branch_id: branch_id,
				target: $('edit_text').value
			},
			onComplete:function(){
				//last_obj.style.backgroundColor = 'white';
				changeNext(last_obj,category_id,sku_type,date);
			}
		});
	}
}

function changeNext(el,category_id,sku_type,change_at_date){
	var current_tr = el.parentNode;
	//var childs = current_tr.childNodes;
	//var childs = current_tr.cells;
	var childs = current_tr.getElementsByTagName('td');
	
	var start_change = false;
	var updated = false;
	var j = 0;
	var dateArray = new Array();
	
	dateArray[j] = change_at_date;
	j++;
	
	//alert(el.id);
	//alert(childs[5].tagName);
	//alert(childs.length);
	start_date = '';
	end_date = '';
	
	for(var i=0; i<childs.length;i++){
		if(childs[i].tagName=='TD'){
		    if(start_change){
		    
				if(trim(childs[i].innerHTML)==''||childs[i].innerHTML==0||trim(childs[i].innerHTML)=='&nbsp;')	{
				    if(start_date==''){
                        var data = childs[i].id.split('_');
	    				start_date = data[0];
	    				dateArray[j] = start_date;
	    				j++;
	    				//alert(start_date);
					}
					
                    //childs[i].innerHTML = el.innerHTML;
                    childs[i].update(el.innerHTML);
                    
                    if(start_date!=''){
                        var data = childs[i].id.split('_');
	    				end_date = data[0];
	    				dateArray[j] = end_date;
	    				j++;
						//changeTD(start_date,end_date,el.innerHTML,category_id,sku_type);
					}
				}else{
				    if(start_date!=''){
                        //var data = childs[i].id.split('_');
	    				//end_date = data[0];
	    				changeTD(start_date,end_date,el.innerHTML,category_id,sku_type);
	    				updated = true;
					}
					break;
				}
			}
			

		    if(childs[i].id==el.id){
                start_change = true;
			}
		}
	}
	
	if(start_date!=''&&end_date!=''&&!updated){
        changeTD(start_date,end_date,el.innerHTML,category_id,sku_type);
	}
	
	
	reCalculate(category_id,sku_type,dateArray);
}

function reCalculate(category_id,sku_type,dateArray){
	var all_tr = $('table_sales').getElementsByTagName('tr');
	start_date = dateArray[0];
	
	// count row first
	current_tr = $(category_id+'_'+sku_type);
	var childs = current_tr.getElementsByTagName('td');
	var total = 0;
	
	for(var i=0; i<childs.length; i++){
		if(childs[i].className.indexOf('keyin')>=0){
			total += int(childs[i].innerHTML);
		}
	}
	
	$('total_'+category_id+'_'+sku_type).update(addCommas(total));
	
	// count column
	
	for(var j=0; j<dateArray.length; j++){
	    total = 0;
	    
        for(var i=0; i<all_tr.length; i++){
            if(all_tr[i].id.indexOf(sku_type)>=0&&all_tr[i].id.indexOf('total')==-1){
                total += int($(dateArray[j]+'_'+all_tr[i].id).innerHTML);
			}
		}
		
		$(dateArray[j]+'_total_'+sku_type).update(addCommas(total));
	}
	
	// count row and column total
	
	var row_total = $('total_'+sku_type).getElementsByTagName('th');
	total = 0;

	for(var i=0; i<row_total.length; i++){
		if(row_total[i].title=='total'){
			total += int(row_total[i].innerHTML);
		}
	}
	
	$('total_total_'+sku_type).update(addCommas(total));
}

function clearRow(category_id,sku_type){
	if(!confirm('Are you sure to clear this row target ?')){
		return;
	}
	
	var childs = $(category_id+'_'+sku_type).getElementsByTagName('td');
	
	var j = 0;
	var dateArray = new Array();
	
	for(var i=0; i<childs.length; i++){
        if(childs[i].className.indexOf('keyin')>=0){
			childs[i].update('&nbsp;');
			
			var data = childs[i].id.split('_');
	    	end_date = data[0];
	    	dateArray[j] = end_date;
	    	j++;
		}
	}

	changeTD(dateArray[0],dateArray[j-1],0,category_id,sku_type);
	
	reCalculate(category_id,sku_type,dateArray);
}

function changeTD(start_date,end_date,target,category_id,sku_type){
    new Ajax.Request(phpself,
	{
		method: 'post',
		parameters:
		{
		    a: 'changeTD',
			start_date: start_date,
			end_date: end_date,
			category_id: category_id,
			sku_type: sku_type,
			branch_id: branch_id,
			target: target
		},
		onComplete:function(e){
			if(e.responseText.indexOf('Error:') >= 0){
				alert(e.responseText);
				return;
			}
		}
	});
}

function checkKey(event){
    if (event == undefined) event = window.event;
    
	if(event.keyCode==13){
		save();
	}else if(event.keyCode==27){
	    $('edit_text').value = float(last_obj.innerHTML);
		document.f_sales_target.h.focus();
	}
}

function trim(str){
 return str.replace(/(^\s*)|(\s*$)/g, "");
}

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function show_auto_fill(){
	curtain(true);
	$('div_auto_fill_in').show();
	center_div('div_auto_fill_in');
}

function curtain_clicked(){
	$('div_auto_fill_in').hide();
}

function start_auto_fill(){
	if(!confirm('Attention: This action will overwrite all current data, click OK to confirm.'))  return false;
	document.f_sales_target.target_per.value = int($('input_sales_target').value);
	document.f_sales_target.replace_type.value = getRadioValue(document.f_auto_fill['radio_replace_type']);
	document.f_sales_target.nearest_round_up.value = document.f_auto_fill.select_round_up.value;
	document.f_sales_target.a.value = 'auto_fill_sales_target';
	document.f_sales_target.submit();
	return false;
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

<!-- Special Div -->
<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id=edit_text size=5 onblur="save()" onKeyPress="checkKey(event)">
</div>

<div id="div_auto_fill_in"  style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;width:450px;height:190px;padding:5px;">
<div style="float:right;"><img onclick="default_curtain_clicked()" src="/ui/closewin.png" /></div>
<h2>Auto Fill in base on last year sales amount.</h2>
<form name="f_auto_fill">
<b>Sales Target: </b>
<input type="text" id="input_sales_target" size="5" style="text-align:right;" onChange="this.value=round(this.value)"> %
<br /><b>Overwrite: </b>
<input type="radio" name="radio_replace_type" value="month" checked /> Current Month Only
<input type="radio" name="radio_replace_type" value="year" /> Current Year
<br />
<b>Round up to nearest: </b>
<select name="select_round_up">
	<option value="10">10</option>
	<option value="50">50</option>
	<option value="100" selected>100</option>
	<option value="500">500</option>
	<option value="1000">1000</option>
</select><br />
<input type="button" value="Start Generate" onClick="start_auto_fill();" />
</form>
<p>
Example:<br />
last year Feb 19 sales amount = 1,000.<br />
You key in 10%, this year Feb 19 sales target will be 1,100.</p>
</div>
<!-- End of Special Div-->

<form name="f_sales_target" method="post">
<input type="hidden" name="a" value="load_table">
<input type="hidden" name="h" value="">
<input type="hidden" name="target_per" />
<input type="hidden" name="replace_type" />
<input type="hidden" name="nearest_round_up" />
<div class=stdframe style="background:#fff;">
<b>Year: </b>
<select name="year" onChange="form.submit();">
	{foreach from=$year_list item=r}
	    <option value="{$r.year}" {if $smarty.request.year eq $r.year} selected {/if}>{$r.year}</option>
	{/foreach}
	<option value="{$r.year+1}" {if $smarty.request.year eq $r.year+1} selected {/if}>{$r.year+1}</option>
</select>&nbsp;

<b>Month: </b>
<select name="month" onChange="form.submit();">
    <!--<option value="0">-- All --</option>-->
	{foreach from=$months key=id item=r}
	    <option value="{$id}" {if $smarty.request.month eq $id} selected {/if}>{$r}</option>
	{/foreach}
</select>&nbsp;

{if $BRANCH_CODE eq 'HQ'}
<b>Branch: </b>
<select name="branch" onChange="form.submit();">
	{foreach from=$branch_list item=r}
	    <option value="{$r.id}" {if $smarty.request.branch eq $r.id} selected {/if}>{$r.code}</option>
	    {if $smarty.request.branch eq $r.id}
			{assign var=bcode value=$r.code}
	    {/if}
	{/foreach}
</select>&nbsp;
{/if}

<input type="submit" name="submits" value="Refresh" />
{if (isset($smarty.request.submits) or isset($smarty.request.target_per)) and $date_label}
	<input type="button" value="Auto Fill in" onClick="show_auto_fill();"/>
{/if}
</div>
</form>
<br />
{if !$date_label}
{if isset($smarty.request.submits)}-- No Data --{/if}
{else}
<h1>
Year: {$smarty.request.year} &nbsp;&nbsp;&nbsp;&nbsp;
Month: {$months[$smarty.request.month]} &nbsp;&nbsp;&nbsp;&nbsp;
Branch: {$branch_code|default:BRANCH_CODE} &nbsp;&nbsp;&nbsp;&nbsp;
</h1>
<table class=tb border=0 cellspacing=0 cellpadding=2 id="table_sales" width=100%>
	<tr height=24 bgcolor="#ffee99">
	    <th colspan="2">{$months[$smarty.request.month]} {$smarty.request.year}</th>
    {foreach from=$date_label key=did item=r}
	        <th {if $r.sun eq 'Sun'}class="sunday"{/if}>{$r.day}</th>
    {/foreach}
    <th>Total</th>
    </tr>
    
	{foreach from=$cat_list item=c}
		{assign var=close_tr value=0}
	    {foreach from=$sku_type item=sku}
	        {if $close_tr eq 2}
				</tr>
				<tr id="{$c.id}_{$sku.code}">
			{else if $close_tr eq 0}
			    <tr id="{$c.id}_{$sku.code}">
			    <th rowspan="{$sku_type|@count}" bgcolor="#ffee99">{$c.description}</th>
			    {assign var=close_tr value=1}
			{/if}
	        <th class="{$sku.code}" align="left">
			<a href="javascript:clearRow('{$c.id}','{$sku.code}')">
			<img src="ui/del.png" title="Clear this row" border="0"/>
			</a>
			{$sku.code}
			</th>
	        {foreach from=$date_label key=did item=r}
			    {assign var=cid value=$c.id}
			    {assign var=date value=$r.date}
			    {assign var=scode value=$sku.code}
				<td class="keyin {$sku.code} {if $r.sun eq 'Sun'}{if $scode eq 'CONSIGN'}con_sunday{else}out_sunday{/if}{/if}" onclick="do_edit(this);" id="{$date}_{$cid}_{$scode}">
				{$table.$cid.$scode.$date|number_format|ifzero:'&nbsp;'}
				</td>
				{assign var=row_total value=$row_total+$table.$cid.$scode.$date}
		    {/foreach}
		    <th id="total_{$cid}_{$scode}" class="r {$sku.code}">{$total.row.$cid.$scode|number_format|ifzero:'&nbsp;'}</th>
		    {assign var=close_tr value=2}
	    </tr>
	    {/foreach}
	{/foreach}
	<!-- Column Total -->
		{assign var=close_tr value=0}
	    {foreach from=$sku_type item=sku}
	        {if $close_tr eq 2}
				</tr>
				<tr id="total_{$sku.code}">
			{else if $close_tr eq 0}
			    <tr id="total_{$sku.code}">
			    <th rowspan="2" bgcolor="#ffee99">Total</th>
			    {assign var=close_tr value=1}
			{/if}
	        <th class="{$sku.code}">{$sku.code}</th>
	        {foreach from=$date_label key=did item=r}
			    {assign var=cid value=$c.id}
			    {assign var=date value=$r.date}
			    {assign var=scode value=$sku.code}
				<th class="r {$sku.code} {if $r.sun eq 'Sun'}{if $scode eq 'CONSIGN'}con_sunday{else}out_sunday{/if}{/if}" id="{$date}_total_{$scode}" title="total">
				{$total.column.$scode.$date|number_format|ifzero:'&nbsp;'}
				</th>
		    {/foreach}
		    <th id="total_total_{$scode}" class="r {$sku.code}">
			{$total.total.$scode.total|number_format|ifzero:'&nbsp;'}
			</th>
		    {assign var=close_tr value=2}
	    </tr>
	    {/foreach}
	</tr>
	<!-- End of Column Total -->
</table>
{/if}
{include file=footer.tpl}
