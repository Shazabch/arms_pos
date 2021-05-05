{include file=header.tpl}
<script>
var g_status="{$approve|default:$status}";
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
	background-color:yellow;
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
</style>

<script>
function do_confirm(){
	document.f_m_r.a.value='confirm';
	//document.f_m_r.submit();
	if(check_a()) document.f_m_r.submit();
}

function do_approve(){
	document.f_m_r.a.value='approve';
	//document.f_m_r.submit();
	if(check_a()) document.f_m_r.submit();
}

function do_search(){
	document.f_m_r.a.value='search';
	document.f_m_r.submit();
}

function check_a(){

    var total_val=new Array();
    
	var e = $('table_annual').getElementsByClassName('total');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^line_contribute_');
	 	if (temp_1.test(e[i].id)){
	 		var line =e[i].title.split(",");
			if(line[1]>0 && line[1]<13){
		        if(!total_val[line[1]]){
					total_val[line[1]]=0;
				}
				total_val[line[1]]+=float(e[i].innerHTML);
			}
		}
	}
	for(var j=1;j<13;j++){
		if(float(total_val[j])!='100'){
			alert('You have one or more month(s) line contribution did not equal 100%');
			return false;
		}
	}
	return true;
}

var last_obj;
var g_field;
var g_month;
var g_line;

function set_value(el,value){
	var cmpvalue = float(value)
	//alert(el.className);
	el.className=el.className.replace(/(negative|zero|positive)/g,'');
	if(cmpvalue<0)
		el.addClassName('negative');
	else if (cmpvalue==0)
		el.addClassName('zero');
	else
		el.addClassName('positive');

	el.innerHTML=value;
}

function do_edit(obj){
    last_obj = obj;
	var line = obj.title.split(",");
	if(line[0]=='t_f')
		g_field='total_forecast';
  	else if(line[0]=='t_t')
		g_field='total_target';
   	else if(line[0]=='l_c')
		g_field='line_contribute';
    else if(line[0]=='adjust')
		g_field='adjustment';

	g_month=line[1];
	g_line=line[2];
	g_year=line[3];
	
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}

function save(){
	Element.hide('edit_popup');
	if(g_field=='line_contribute'){
	    var total_value=0;
		var e = $('table_annual').getElementsByClassName('total');
		for(var i=0;i<e.length;i++)	{
	 		var temp_1 =new RegExp('^line_contribute_');
		 	if (temp_1.test(e[i].id)){
		 		var line =e[i].title.split(",");
				if(line[1]==g_month && line[2]!=g_line){
					total_value+=float(e[i].innerHTML);
				}
			}
		}
		total_value=total_value+float($('edit_text').value);
		//alert(total_value);
		if(total_value>'100'){
			$('Msg').innerHTML='';
			alert('Selected month lines contribution over 100%');
			return;
		}
		/*else if(total_value<100 && g_status){
			$('Msg').innerHTML='';
			alert('Selected month lines contribution must equal to 100%');
			return;
		}*/
		else if(total_value<100){
			$('Msg').innerHTML =  'Month '+g_month+'/'+g_year+' lines contribution is '+total_value+'% less than 100%';
		}
		else {
			$('Msg').innerHTML='';
		}
		//alert(total_value);
	}
	if(float(last_obj.innerHTML)!=float($('edit_text').value)){
		last_obj.innerHTML = 'Saving..';
		var newp = last_obj;
		new Ajax.Updater(newp,'mkt_annual.php?field='+g_field+'&line_id='+g_line+'&month='+g_month+'&value='+float($('edit_text').value)+'&'+Form.serialize(document.f_m_r)+'&a=save_edit&year='+g_year,{onComplete:function(){update_table(newp)}});
	}
}

function update_total_target(){
	var amt=new Array();
	var accumulate=new Array();
	var count_t_target=0;
	var total_by_month=0;
	var e = $('table_annual').getElementsByClassName('total');

	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^total_target_'+g_month);
		if (temp_1.test(e[i].id)){
 		    var total_target_val=float(e[i].innerHTML);
		}
		
 		var temp_2 =new RegExp('^adjustment_'+g_month);
		if (temp_2.test(e[i].id)){
 		    var adjustment=float(e[i].innerHTML);
		}
		
 		var temp_3 =new RegExp('^sales_'+g_month);
		if (temp_3.test(e[i].id)){
 		    var sales=float(e[i].innerHTML);
		}
		
 		var temp_4 =new RegExp('^by_month_'+g_month);
		if (temp_4.test(e[i].id)){
 		    var by_month=(total_target_val+adjustment)-sales;
 		    set_value(e[i],round(by_month,2));
		}
	
 		if (/^line_contribute_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(line[1]==g_month){
				var line_contribute_val=float(e[i].innerHTML);
			}
		}
 		if (/^line_amt_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(line[1]==g_month){
				var line_amt_val=total_target_val*line_contribute_val/100;
				set_value(e[i],round(line_amt_val,2));
			}
    		if(!amt[line[2]]){
				amt[line[2]]=0;
			}
			amt[line[2]]+=float(e[i].innerHTML);
		}
  		if (/^t_amt_/.test(e[i].id)){
			var line =e[i].title.split(",");
			set_value(e[i],round(amt[line[2]],2));
		}

 	  	if (/^total_target_/.test(e[i].id)){
			count_t_target+=float(e[i].innerHTML);
		}
 	  	if (/^by_month_/.test(e[i].id)){
 	  		var line =e[i].title.split(",");
			total_by_month+=float(e[i].innerHTML);
			if(!accumulate[line[1]]){
				accumulate[line[1]]=0;
			}
			accumulate[line[1]]=total_by_month;
		}
 	  	if (/^accumulate_/.test(e[i].id)){
 	  		var line =e[i].title.split(",");
			set_value(e[i],round(accumulate[line[1]],2));			
		}

	}
	set_value($('t_target'),round(count_t_target,2));
	set_value($('t_by_month'),round(total_by_month,2));
}


function update_line_contribute(){
	var l_c=0;
	var l_a=0;
	var e = $('table_annual').getElementsByClassName('total');
	for(var i=0;i<e.length;i++)	{
 		var temp_1 =new RegExp('^total_target_'+g_month);
		if (temp_1.test(e[i].id)){
 		    var total_target_val=float(e[i].innerHTML);
		}
 		var temp_2 =new RegExp('^line_contribute_'+g_line+'_'+g_month);
	 	if (temp_2.test(e[i].id)){
			var line_contribute_val=float(e[i].innerHTML);
			e[i].innerHTML=round(line_contribute_val,2)+'%';
		}
 		var temp_3 =new RegExp('^line_amt_'+g_line+'_'+g_month);
	 	if (temp_3.test(e[i].id)){
			var line_amt_val=total_target_val*line_contribute_val/100;
			set_value(e[i],round(line_amt_val,2));
		}
 		var temp_4 =new RegExp('^line_contribute_'+g_line+'_');
	 	if (temp_4.test(e[i].id)){
			l_c+=float(e[i].innerHTML);
		}
  		var temp_5 =new RegExp('^line_amt_'+g_line+'_');
	 	if (temp_5.test(e[i].id)){
			l_a+=float(e[i].innerHTML);
		}
	}
	set_value($('t_pct_'+g_line),round(l_c,2)+'%');
	set_value($('t_amt_'+g_line),round(l_a,2));
}

function update_forecast(){
	var total_f=0;
	var e = $('table_annual').getElementsByClassName('forecast');
	for(var i=0;i<e.length;i++)	{
 	  	if (/^total_forecast_/.test(e[i].id)){
			total_f+=float(e[i].innerHTML);
		}
	}
	set_value($('t_forecast'),round(total_f,2));
}

function update_adjustment(){
	var amt=new Array();
	var accumulate=new Array();
	var count_t_target=0;
	var total_by_month=0;
	var total_f=0;
	var e = $('table_annual').getElementsByClassName('adjust');
	for(var i=0;i<e.length;i++)	{
 	  	if (/^adjustment_/.test(e[i].id)){
			total_f+=float(e[i].innerHTML);
		}
 		var temp_1 =new RegExp('^total_target_'+g_month);
		if (temp_1.test(e[i].id)){
 		    var total_target_val=float(e[i].innerHTML);
		}
		
 		var temp_2 =new RegExp('^adjustment_'+g_month);
		if (temp_2.test(e[i].id)){
 		    var adjustment=float(e[i].innerHTML);
		}
		
 		var temp_3 =new RegExp('^sales_'+g_month);
		if (temp_3.test(e[i].id)){
 		    var sales=float(e[i].innerHTML);
		}
		
 		var temp_4 =new RegExp('^by_month_'+g_month);
		if (temp_4.test(e[i].id)){
 		    var by_month=(total_target_val+adjustment)-sales;
 		    set_value(e[i],round(by_month,2));
		}
 	  	if (/^by_month_/.test(e[i].id)){
 	  		var line =e[i].title.split(",");
			total_by_month+=float(e[i].innerHTML);
			if(!accumulate[line[1]]){
				accumulate[line[1]]=0;
			}
			accumulate[line[1]]=total_by_month;
		}
 	  	if (/^accumulate_/.test(e[i].id)){
 	  		var line =e[i].title.split(",");
			set_value(e[i],round(accumulate[line[1]],2));			
		}
	}
	set_value($('t_adjust'),round(total_f,2));
	set_value($('t_by_month'),round(total_by_month,2));
}

function update_table(cell){
	new Effect.Highlight(cell);
	if (g_field=='total_target'){
		update_total_target();
	}
	else if (g_field=='total_forecast'){
		update_forecast();
	}
	else if (g_field=='line_contribute'){
	    update_line_contribute();
	}
	else if (g_field=='adjustment'){
	    update_adjustment();
	}
}

</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>
<ul>
<li> Click on a month to view daily detail for selected month
</ul>
{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id=edit_text size=5 onblur=save()>
</div>


<form name=f_m_r method=post>
<input type=hidden name=a value=''>

<div class=stdframe style="background:#fff;">

	{if $BRANCH_CODE eq 'HQ'}
	<b>Branch :</b> <select name="branch_id" onchange="do_search();">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
	</select>
	{/if}
	&nbsp;&nbsp;
	<b>Year</b> <select name="year" onchange="do_search()">
	<option value={$year-1} {if $year == ($year-1)}selected{/if}>{$year-1}</option>
	<option value={$year} 	{if $year == $year}selected{/if}>{$year}</option>
	<option value={$year+1} {if $year == ($year+1)}selected{/if}>{$year+1}</option>
	</select>
	&nbsp;&nbsp;
	<input id=submits_r name=submits_r type=button onclick="do_search()" value="Refresh">
	
</div>

<br>
<div>
<span id="Msg" title="Message" class="negative"></span>
</div>
<br>
{if $branch_id>1}
<div id=category_list>
{include file=mkt_annual.sheet.tpl}
</div>

<p align=center>
{if !$approve and $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}
{if !$status }
<input id=submits_r name=submits_r type=button onclick="do_confirm();" value="Confirm" style="font:bold 20px Arial; background-color:#f90; color:#fff;">

{elseif $mkt_annual_privilege.MKT_ANNUAL_APPROVAL.$branch_id}
<input id=submits_r name=submits_r type=button onclick="do_approve();" value="Approve" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
{/if}
{/if}

</p>
{/if}

</form>

{include file=footer.tpl}
<script>
/*list_sel(1,1);
{if $smarty.request.a eq 'view' || $BRANCH_CODE eq 'HQ'}
Form.disable(document.f_m_r);
{/if}
_init_enter_to_skip(document.f_m_r);*/
</script>
