{include file=header.tpl}
<script>
/*var count_i={$i};
var currlist = new Array();
{foreach name=j item="curr_list" from=$list}
currlist[{$smarty.foreach.j.iteration}]="{$curr_list.employee_id}";
{/foreach}*/
</script>

{literal}
<script>
var count=0;
var brand_obj=null;
var dept_obj=null;
var verify=0;

function do_cancel(){
	Element.hide('brand_popup');
	Element.hide('dept_popup');
}


function close_popup(){
	Element.hide('brand_popup');
	brand_obj.value=$('selected_brand').value;
}

function choose_brand(obj){
	brand_obj = obj;
	$('selected_brand').selectedIndex=0;
	for(var i=0;i<$('selected_brand').options.length;i++)
	{
		if ($('selected_brand').options[i].value == obj.value){
			$('selected_brand').selectedIndex = i;
	    	break;
		}
	}
	//$('selected_brand').options[$('selected_brand').selectedIndex].scrollIntoView(true);
	Element.show('brand_popup');
	Position.clone(obj, $('brand_popup'), {setHeight: false, setWidth:false});
}

function close_popup_1(){
	Element.hide('dept_popup');
	dept_obj.value=$('selected_dept').value;
	document.f_s_r.submit();
}

function choose_dept(obj){
	dept_obj = obj;
	$('selected_dept').selectedIndex=0;
	for(var i=0;i<$('selected_dept').options.length;i++)
	{
       	if ($('selected_dept').options[i].value == obj.value){
			$('selected_dept').selectedIndex = i;
	    	break;
		}
	}
	//$('selected_brand').options[$('selected_brand').selectedIndex].scrollIntoView(true);
	Element.show('dept_popup');
	Position.clone(obj, $('dept_popup'), {setHeight: false, setWidth:false});
}

function do_sort(strSort){
  var form = document.f_s_r;
  
  if(strSort == form.s_field.value){
    if(form.s_arrow.value == 'ASC'){
     form.s_arrow.value = 'DESC';
    }
    else{
      form.s_arrow.value = 'ASC';
    }
  }
  else{
    form.s_arrow.value = 'ASC';
    form.s_field.value = strSort;
  }
  form.submit();
}


function do_delete(){
	if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
        return;
	}
	if (confirm('Delete selected records?')){
    			document.f_s_r.a.value ='delete';
				document.f_s_r_s.target = '';
				document.f_s_r.submit();
	}
}

function do_refresh(){
	if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
        return;
	}
	
	document.f_s_r_s.a.value = 'refresh';
	document.f_s_r_s.target = '';
	document.f_s_r_s.submit();
}

function do_copy(b){
	if(!confirm('Copy existing name list from last month?')){
        return;
	}
	document.f_s_r_s.branch.value=b;
	document.f_s_r_s.department.value='%%';

	document.f_s_r_s.a.value = 'copy';
	document.f_s_r_s.target = '';
	document.f_s_r_s.submit();
}

function chk_id(obj){
    verify++;
	count_chk=0;
	row=currlist.length;
	status_id=1;
	for(var i=1;i<=row;i++){
		if(obj.value==currlist[i]){
			status_id=0;
			count_chk=2;
			break;
		}
	}
	for (var i=1;i<(row+count);i++){
    	if(obj.value==trim(document.f_s_r.elements['id['+i+']'].value)){
    	        count_chk=count_chk+1;
            	status_id=0;
        }
    }
    if((status_id==0)&&(count_chk>1)){
    	alert("Existing id or duplicate id, please try again.");
    	obj.value="";
		obj.focus();
		return false;
	}
}


function chk_save(row){
    chkstatus=1;
    chkstatus2=1;

    if((trim(document.f_s_r.elements['name['+(row+count)+']'].value)=="") && (trim(document.f_s_r.elements['id['+(row+count)+']'].value)=="")){
    	for(var j=1;j<count_i;j++){
        	if(trim(document.f_s_r.elements['Eday['+(row+count)+']['+j+']'].value)!="" || trim(document.f_s_r.elements['day['+(row+count)+']['+j+']'].value)!=""){
            	chkstatus2=0;
                break;
			}
		}
	}
	else if((trim(document.f_s_r.elements['name['+(row+count)+']'].value)!="") && (trim(document.f_s_r.elements['id['+(row+count)+']'].value)=="")){
	    chkstatus2=0;
	}
	else if((trim(document.f_s_r.elements['id['+(row+count)+']'].value)!="") && (trim(document.f_s_r.elements['name['+(row+count)+']'].value)=="")){
	    chkstatus2=0;
	}
	
    for (var i=1;i<(row+count);i++){
        if(trim(document.f_s_r.elements['name['+i+']'].value)=="" || trim(document.f_s_r.elements['id['+i+']'].value)=="")
		{
            chkstatus2=0;
            break;
        }
    }
    /*if(chkstatus==1){
        for (var i=1;i<=(row+count);i++){
            for(var j=1;j<count_i;j++){
                if(trim(document.f_s_r.elements['Eday['+i+']['+j+']'].value)==""){
                    chkstatus2=0;
                    break;
                }
            }
        }
    }
    else{
        chkstatus2=0;
    }*/

    if(chkstatus2==1){
    	document.f_s_r_s.a.value ='save';
        document.f_s_r.submit();
    }
    else{
        alert("Please complete the details.");
    }
}



function changered(obj) {
	verify++;
	if(obj.value=='R' || obj.value=='M' || obj.value=='A' || obj.value=='F' || obj.value=='H'|| obj.value=='X'){
		if (obj.value=='R'){
			obj.style.color='red';
		}
		else {
            obj.style.color='black';
		}
	}
	else {
		alert("Only R/M/A/F/H/X allowed for this coulmn!");
		obj.value="";
		obj.focus();
		return false;
	}
}

function chk2ndrow(obj){
	verify++;
	if(obj.value=='AL' || obj.value=='PH' || obj.value=='NPL' || obj.value=='CL' || obj.value=='ABT'|| obj.value=='SPL' || obj.value=='MC' || obj.value=='CR' || obj.value=='ML'|| obj.value=='RPL'|| obj.value=='HQ' || obj.value=='BL' || obj.value=='GR' || obj.value=='KG'|| obj.value=='DG' || obj.value=='TM' || obj.value=='JT' || obj.value=='R' || obj.value=='M' || obj.value=='A' || obj.value=='F' || obj.value=='H'|| obj.value=='X'){
	    return true;
	}
	else {
		alert("Only the legend character above allowed for this coulmn!");
		obj.value="";
		obj.focus();
		return false;
	}
}

function changeblue(obj,a) {
    verify++;
	if (obj.value==a){
	    if (obj.value=='R')
			obj.style.color='red';
		else
			obj.style.color='black';
	}
	else {
        obj.style.color='blue';
	}
}

// add new row
function add_row(obj,n,t,b){
	if (obj!=undefined)
	{
		if (obj.alt != '' || obj.value=='') return;
		obj.alt='used';
		verify++;
	}
	var i,inhtml="", inhtml2="";
	count++;

	for (i=1; i<count_i; i++) {
		inhtml=inhtml+'<td><input onchange="uc(this);changered(this);changeblue(form.elements[\'day['+n+']['+i+']\'],this.value)" {if $curr_list.$arrayname eq R}class=cr{/if} class=normal name=Eday['+n+']['+i+'] size=1 maxlength=1 value=""></td>';
		
		inhtml2=inhtml2+'<td><input onchange="uc(this);chk2ndrow(this);changeblue(this,form.elements[\'Eday['+n+']['+i+']\'].value)" {if $curr_list.$arrayname eq $curr_list.$arrayEday}class=cb{/if} name=day['+n+']['+i+'] size=1 maxlength=3 value=""></td>';
	}

	var new_row = $('tbd_sr').insertRow(-1);
	var new_row2 = $('tbd_sr').insertRow(-1);
	
	if ((n%2)==1){new_row.bgColor="#dddddd";new_row2.bgColor="#eeeeee";}
	else { new_row.bgColor="#ff9999";new_row2.bgColor="#ffcccc";}
	
	new_row.innerHTML='<th>'+n+'</th><td><input onchange=uc(this) name=name['+n+'] size=30 maxlength=50 value="" class=name onblur="add_row(this,'+(n+1)+',\''+t+'\',\''+b+'\')"></td><td><input onchange="uc(this);chk_id(this)" class=name name=id['+n+'] size=20 maxlength=15 value=""></td>'+inhtml+'';
	
	
	if(t=='%%'){
			new_row2.innerHTML='<th>&nbsp;</th><td nowrap><img src="ui/expand.gif" onclick="choose_dept(document.f_s_r.elements[\'array_department['+n+']\']);" style="cursor: pointer;" title="Select Department"><input class=dept_brand size=30 type=text value="" name="array_department['+n+']"></td><th>&nbsp;</th>'+inhtml2+'';

	}
	else if(t=='Promoter (Supermarket line)' || t=='Promoter (Soft line)' || t=='Promoter (Hard line)'){
		new_row2.innerHTML='<th>&nbsp;</th><td align=center style="font-size:4px;color:#aaa;">Actual Attendance</td><td nowrap><input class=brand size=20 type=text value="" name="brand['+n+']"></td>'+inhtml2+'';
	}
	else{
		new_row2.innerHTML='<th>&nbsp;</th><td colspan=2 align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>'+inhtml2+'';
	}
}

function do_print(){
	if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
        return;
	}
	document.f_s_r_s.a.value = 'print';
	document.f_s_r_s.target = 'ifprint';
	document.f_s_r_s.submit();
}
</script>

<style>
#tbl_sr th{
	padding:2px;
}
.sort{
    FONT-WEIGHT: bold;
    text-align: center;
	CURSOR: pointer;
}
.normal{
	color:black;
}
.cr{
	color:red;
}
.cb{
	color:blue;
}
#tbl_sr td, #tbl_sr th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
#tbl_sr td.shaded {
	background:#eee;
	color:#aaa;
	font-size:9px;
	text-align:center;
}
#tbl_sr input[size="1"]
{
	width:20px;
	text-align:center;
}
#tbl_sr input
{
	padding:0; margin: 0; border:none;
	background:transparent;
	font:normal 9px Arial;
}
#tbl_sr input.name
{
	font:normal 11px Arial;
	FONT-WEIGHT: bold;
}
#tbl_sr input.dept_brand
{
	font:normal 12px arial;
}
#tbl_sr input.brand
{
	border:1px solid;
	font:normal 12px Arial;
}
</style>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $errm}<div id=err><div class=errmsg><ul>{foreach from=$errm item=e}<li>{$e}{/foreach}</ul></div></div>{/if}

<div id=brand_popup ondblclick=close_popup(); >
<select id=selected_brand style="font-size:15px;" size=12>
{foreach item="curr_Brand" from=$BrandArray}
<option>{$curr_Brand.description}</option>
{/foreach}
</select><br>
<input type=button value="OK" onclick="close_popup();">&nbsp;&nbsp;<input type=button value="Cancel" onclick="do_cancel();">
</div>


<div id=dept_popup ondblclick=close_popup_1(); style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;padding:5px;">
<select id=selected_dept style="font-size:15px;" size=12 onchange="close_popup_1();">
{if $branch=='1'}
<option>Finance</option>
<option>MIS</option>
<option>HR & Office</option>
<option>Internal Audit</option>
<option>Marketing</option>
<option>Merchandising</option>
</select>
{else}
<option>Management</option>
<option>Account</option>
<option>MIS</option>
<option>HR & Office</option>
<option>Security</option>
<option>Maintenance</option>
<option>A & P</option>
<option>Merchandising</option>
<option>Store</option>
<option>Sales (Supermarket line)</option>
<option>Sales (Soft line)</option>
<option>Sales (Hard line)</option>
<option>Promoter (Supermarket line)</option>
<option>Promoter (Soft line)</option>
<option>Promoter (Hard line)</option>
<option>Cashiering</option>
<option>Public Relations</option>
{/if}
</select>
<br>
<input type=button value="OK" onclick="close_popup_1();">&nbsp;&nbsp;<input type=button value="Cancel" onclick="do_cancel();">
</div>

<form action=shift_record.php method=post name=f_s_r_s>
<input type=hidden name=a value="refresh">
<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>

<table cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr>
<th>
By Date : <select name="month" onchange="do_refresh();">
<option value=1  {if $month == 1}selected{/if}>January</option>
<option value=2  {if $month == 2}selected{/if}>February</option>
<option value=3  {if $month == 3}selected{/if}>March</option>
<option value=4  {if $month == 4}selected{/if}>April</option>
<option value=5  {if $month == 5}selected{/if}>May</option>
<option value=6  {if $month == 6}selected{/if}>June</option>
<option value=7  {if $month == 7}selected{/if}>July</option>
<option value=8  {if $month == 8}selected{/if}>August</option>
<option value=9  {if $month == 9}selected{/if}>September</option>
<option value=10 {if $month == 10}selected{/if}>October</option>
<option value=11 {if $month == 11}selected{/if}>November</option>
<option value=12 {if $month == 12}selected{/if}>December</option>
</select></td>

<td>
<select name="year" onchange="do_refresh();">
<option value={$year-1} {if $year == ($year-1)}selected{/if}>{$year-1}</option>
<option value={$year} 	{if $year == $year}selected{/if}>{$year}</option>
<option value={$year+1} {if $year == ($year+1)}selected{/if}>{$year+1}</option>
</select></td>

<td>
<th>
Branch :
<select name="branch" onchange="do_refresh();">
{foreach item="curr_Branch" from=$BranchArray}
<option value={$curr_Branch.id} {if $curr_Branch.id==$branch}selected{/if}>{$curr_Branch.code}</option>
{/foreach}
</select>
</td>
<td>

<th>
Department :
<select name="department" onchange="do_refresh();">
<option value="%%"> --- All --- </option>
{if $branch=='1'}
<option {if $department eq 'Finance'}selected{/if}>Finance</option>
<option {if $department eq 'MIS'}selected{/if}>MIS</option>
<option {if $department eq 'HR & Office'}selected{/if}>HR & Office</option>
<option {if $department eq 'Internal Audit'}selected{/if}>Internal Audit</option>
<option {if $department eq 'Marketing'}selected{/if}>Marketing</option>
<option {if $department eq 'Merchandising'}selected{/if}>Merchandising</option>
{else}
<option {if $department eq 'Management'}selected{/if}>Management</option>
<option {if $department eq 'Account'}selected{/if}>Account</option>
<option {if $department eq 'MIS'}selected{/if}>MIS</option>
<option {if $department eq 'HR & Office'}selected{/if}>HR & Office</option>
<option {if $department eq 'Security'}selected{/if}>Security</option>
<option {if $department eq 'Maintenance'}selected{/if}>Maintenance</option>
<option {if $department eq 'A & P'}selected{/if}>A & P</option>
<option {if $department eq 'Merchandising'}selected{/if}>Merchandising</option>
<option {if $department eq 'Store'}selected{/if}>Store</option>
<option {if $department eq 'Sales (Supermarket line)'}selected{/if}>Sales (Supermarket line)</option>
<option {if $department eq 'Sales (Soft line)'}selected{/if}>Sales (Soft line)</option>
<option {if $department eq 'Sales (Hard line)'}selected{/if}>Sales (Hard line)</option>
<option {if $department eq 'Promoter (Supermarket line)'}selected{/if}>Promoter (Supermarket line)</option>
<option {if $department eq 'Promoter (Soft line)'}selected{/if}>Promoter (Soft line)</option>
<option {if $department eq 'Promoter (Hard line)'}selected{/if}>Promoter (Hard line)</option>
<option {if $department eq 'Cashiering'}selected{/if}>Cashiering</option>
<option {if $department eq 'Public Relations'}selected{/if}>Public Relations</option>
{/if}
</select>
</td>

<td>
<p align=center>
<input type=button value="Refresh" onclick="do_refresh();">
<input type=button value="Print" onclick="do_print()">
{if $num_row==0 && $sessioninfo.privilege.SHIFT_RECORD_EDIT && $num_row_last>0}
<input type=button onclick="do_copy({$branch})" value="Copy Name List" >
{/if}
</form>
</p>
</td>
</tr>
</table>

<form action=shift_record.php method=post name=f_s_r>
<input type=hidden name=a value="save">
<input type=hidden name=s_field value="{$s_field}">
<input type=hidden name=s_arrow value="{$s_arrow}">
<input type=hidden name=month value="{$month}">
<input type=hidden name=year value="{$year}">
<input type=hidden name=branch value="{$branch}">
<input type=hidden name=department value="{$department}">

<table cellpadding=1 cellspacing=0 border=0 id=tbl_sr style="border:1px solid #000;padding:0px;">
<tr bgcolor=#ffee99>
<th><b>No</th>
<td class=sort onclick="do_sort('employee_name')"><b> Employee Name</td>
<td class=sort onclick="do_sort('employee_id')"><b>Employee ID</td>

{section name=loopi loop=$i start=1 step=1}
<th><b>{$smarty.section.loopi.iteration}</b><div class=small>{$showday[loopi]}</div></th>
{/section}
<td width=16><img src=/ui/pixel.gif width=16></td>
</tr>

<tbody id=tbd_sr style="height:410px;overflow:auto">

{if $num_row>0}
{foreach name=j item="curr_list" from=$list}
{assign var=n value=$smarty.foreach.j.iteration}
{if (($n%2)==1)}<tr bgcolor=#dddddd>{else}<tr bgcolor=#ff9999>{/if}
<th>{$smarty.foreach.j.iteration}</th>
<td><input class=name onchange=uc(this) name=name[{$smarty.foreach.j.iteration}] size=30 maxlength=50 value="{$curr_list.employee_name}"></td>
<td><input class=name onchange="uc(this);chk_id(this)" name=id[{$smarty.foreach.j.iteration}] size=20 maxlength=15 value="{$curr_list.employee_id}"></td>

{section name=loopi loop=$i start=1 step=1}
<td align=center>
{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=estimate_day_record_$ii}

<input onchange="uc(this);changered(this);changeblue(form.elements['day[{$smarty.foreach.j.iteration}][{$smarty.section.loopi.iteration}]'],this.value)" {if $curr_list.$arrayname eq 'R'}class='cr'{/if} class='normal' name=Eday[{$smarty.foreach.j.iteration}][{$smarty.section.loopi.iteration}] size=1 maxlength=1 value="{$curr_list.$arrayname}"></td>
{/section}
</tr>

{if (($n%2)==1)}<tr bgcolor=#eeeeee>{else}<tr bgcolor=#ffcccc>{/if}
<th><input type="checkbox" name="{$curr_list.employee_id}" id="delete_id[{$smarty.foreach.j.iteration}]" value="{$curr_list.employee_id}"></th>
{if $department eq '%%'}
<td nowrap>
<img src="ui/expand.gif" onclick="choose_dept(document.f_s_r.elements['array_department[{$smarty.foreach.j.iteration}]']);" style="cursor: pointer;" title="Select Department">
<input class=dept_brand type=text size=30 value="{$curr_list.department}" name="array_department[{$smarty.foreach.j.iteration}]">
</td>

{if $curr_list.department eq 'Promoter (Hard line)' || $curr_list.department eq 'Promoter (Soft line)' || $curr_list.department eq 'Promoter (Supermarket line)'}
<td nowrap>
<input class=brand type=text size=20 value="{$curr_list.brand}" name="brand[{$smarty.foreach.j.iteration}]">
</td>
{else}
<td>
&nbsp;<input type=hidden name="brand[{$smarty.foreach.j.iteration}]" value="{$curr_list.brand}">
</td>
{/if}

{else}
{if $department eq 'Promoter (Hard line)' || $department eq 'Promoter (Soft line)' || $department eq 'Promoter (Supermarket line)'}
<td align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>
<td nowrap>
<input class=brand type=text size=20 value="{$curr_list.brand}" name="brand[{$smarty.foreach.j.iteration}]">
</td>
{else}
<td colspan=2 align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>
{/if}
{/if}

{section name=loopi loop=$i start=1 step=1}
<td align=center>
{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=day_record_$ii} {assign var=arrayEday value=estimate_day_record_$ii}

<input onchange="uc(this);chk2ndrow(this);changeblue(this,form.elements['Eday[{$smarty.foreach.j.iteration}][{$smarty.section.loopi.iteration}]'].value)" {if $curr_list.$arrayname eq $curr_list.$arrayEday && $curr_list.$arrayname eq 'R'}class=cr {elseif $curr_list.$arrayname eq $curr_list.$arrayEday} class=normal {else} class='cb'{/if}  name=day[{$smarty.foreach.j.iteration}][{$smarty.section.loopi.iteration}] size=1 maxlength=3 value="{$curr_list.$arrayname}"></td>
{/section}
</tr>
{/foreach}
{/if}
</tbody>
</table>
</form>

<p align=center>
{if $sessioninfo.privilege.SHIFT_RECORD_EDIT && $num_row>0}
<input type=button onclick="do_delete()" value="Delete Selected" style="font:bold 20px Arial; background-color:#900; color:#fff;">{/if}&nbsp;&nbsp;
{if $sessioninfo.privilege.SHIFT_RECORD_EDIT}
<input id=submits_r name=submits_r type=button onclick="chk_save({$num_row})" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
{else}
<input type=button onclick="document.location='/'" value="Close" style="font:bold 20px Arial; background-color:#09f; color:#fff;">
{/if}
</p>

<table width='100%' style="border:1px solid #000;padding:0px;">
<tr>
<td colspan=18><font color=red>1st row allowed </font>:</td>
</tr>
<tr>
<td width='2%' align=right><font color=blue>A</font></td><td width='1%' align=center>:</td><td width='10%'>Afternoon Shift</td>
<td width='2%' align=right><font color=blue>F</font></td><td width='1%' align=center>:</td><td width='10%'>Full Day</td>
<td width='2%' align=right><font color=blue>H</font></td><td width='1%' align=center>:</td><td width='10%'>Half Day</td>
<td width='2%' align=right><font color=blue>M</font></td><td width='1%' align=center>:</td><td width='10%'>Morning Shift</td>
<td width='2%' align=right><font color=red>R</font></td><td width='1%' align=center>:</td><td width='10%'>Rest</td>
<td width='2%' align=right><font color=blue>X</font></td><td width='1%' align=center>:</td><td width='10%'>Resign</td>
</tr>

<tr>
<td colspan=18><font color=red>2nd row allowed </font>:</td>
</tr>
<tr>
<td width='2%' align=right><font color=blue>A</font></td><td width='1%' align=center>:</td><td width='10%'>Afternoon Shift</td>
<td width='2%' align=right><font color=blue>ABT</font></td><td width='1%' align=center>:</td><td width='10%'>Absent</td>
<td width='2%' align=right><font color=blue>AL</font></td><td width='1%' align=center>:</td><td width='10%'>Annual Leave</td>
<td width='2%' align=right><font color=blue>BL</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Baling</td>
<td width='2%' align=right><font color=blue>CL</font></td><td width='1%' align=center>:</td><td width='10%'>Company Leave</td>
<td width='2%' align=right><font color=blue>CR</font></td><td width='1%' align=center>:</td><td width='10%'>Change Rest Day</td>
</tr>

<tr>
<td width='2%' align=right><font color=blue>DG</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Dungun</td>
<td width='2%' align=right><font color=blue>F</font></td><td width='1%' align=center>:</td><td width='10%'>Full Day</td>
<td width='2%' align=right><font color=blue>GR</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Gurun</td>
<td width='2%' align=right><font color=blue>H</font></td><td width='1%' align=center>:</td><td width='10%'>Half Day</td>
<td width='2%' align=right><font color=blue>HQ</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Head Office</td>
<td width='2%' align=right><font color=blue>JT</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Jitra</td>
</tr>

<tr>
<td width='2%' align=right><font color=blue>KG</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Kangar</td>
<td width='2%' align=right><font color=blue>M</font></td><td width='1%' align=center>:</td><td width='10%'>Morning Shift</td>
<td width='2%' align=right><font color=blue>MC</font></td><td width='1%' align=center>:</td><td width='10%'>Medical Leave</td>
<td width='2%' align=right><font color=blue>ML</font></td><td width='1%' align=center>:</td><td width='10%'>Maternity Leave</td>
<td width='2%' align=right><font color=blue>NPL</font></td><td width='1%' align=center>:</td><td width='10%'>No Pay Leave</td>
<td width='2%' align=right><font color=blue>PH</font></td><td width='1%' align=center>:</td><td width='10%'>Public Holiday</td>

</tr>

<tr>
<td width='2%' align=right><font color=red>R</font></td><td width='1%' align=center>:</td><td width='10%'>Rest</td>
<td width='2%' align=right><font color=blue>RPL</font></td><td width='1%' align=center>:</td><td width='10%'>Replacement Leave</td>
<td width='2%' align=right><font color=blue>SPL</font></td><td width='1%' align=center>:</td><td width='10%'>Special Leave</td>
<td width='2%' align=right><font color=blue>TM</font></td><td width='1%' align=center>:</td><td width='10%'>Visit Tanah Merah</td>
<td width='2%' align=right><font color=blue>X</font></td><td width='1%' align=center>:</td><td width='10%'>Resign</td>
<td width='2%' align=right>&nbsp;</td><td width='1%' align=center>&nbsp;</td><td width='10%'>&nbsp;</td>
</tr>
</table>

{include file=footer.tpl}
<script>
{if $sessioninfo.privilege.SHIFT_RECORD_EDIT}
add_row(undefined,{$num_row+1},'{$department}',{$branch});
_init_enter_to_skip(tbl_sr);
{else}
Form.disable(document.f_s_r);
{/if}
</script>