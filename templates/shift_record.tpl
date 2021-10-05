{*
added to permit user to login in wherever to modify the permitted branch -070507

1/12/2012 11:54:43 AM Justin
- Fixed the account checkbox is not checkable when at printing option.

4/17/2012 10:53:21 AM Andy
- Change "Year" dropdown to start from year 2008.

5/28/2013 3:04 PM Justin
- Modified to add new note "BS - Visit Bagan Serai".

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call
*}

{include file=header.tpl}
<script>
var count_i={$i};
</script>
{literal}
<script>
var count=0;
var dept_obj=null;
var verify=0;

function do_refresh_branch(){
	if (check_login()) {
		if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
			return;
		}
		document.f_s_r_s.department.value = '%%';
		document.f_s_r_s.a.value = 'refresh';
		document.f_s_r_s.target = '';
		document.f_s_r_s.submit();
	}
}

function chk_save_day(){
	if (check_login()) {
		document.f_s_r.a.value ='update';
		document.f_s_r.submit();
		alert('You data have been successfull saved!');
	}
}


function do_edit_user(no) {
	Element.hide('user_popup');
	curtain(false);
	ajax_request('shift_record.php',
	{
		parameters: 'ajax=3&no='+no+'&'+Form.serialize(document.f_edit_user)+'&a=ajax_edit_item',
		evalScripts: true,
		onComplete: function(m) {
		    var lines = m.responseText.split("\n");
		    var i;
			for(i=0;lines[i]=='';i++) ;
		    $('TR0_'+no).innerHTML = lines[i];
		    $('TR1_'+no).innerHTML = lines[i+1];
		}
	});
}

function call_user(id,m,y,br,d,n){
	curtain(true);
	Element.show('user_popup');
	//Position.clone($('id'), $('dept_popup'), {setHeight: false, setWidth:false});
	$('user_popup').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('user_popup', 'shift_record.php',
	{
		parameters: 'a=ajax_call_user&ajax=edit_user&id='+escape(id)+'&month='+m+'&year='+y+'&branch='+br+'&department='+escape(d)+'&no='+n,
		evalScripts: true
	});
}


function do_edit_day(day) {
	if (check_login()) {
		count=0;
		if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
			return;
		}
		document.f_s_r.update.value=day;
		document.f_s_r.a.value ='edit_day';
		document.f_s_r.submit();
	}
}

function do_delete(id,m,y,br,n)
{
	if(!confirm('Are you sure want to delete?')){
        return;
	}
	Element.remove('TR0_'+n);
	Element.remove('TR1_'+n);
	currlist[n]=null;
	//new Ajax.Request( 'shift_record.php?a=ajax_del_item&ajax=1&id='+escape(id)+'&month='+m+'&year='+y+'&branch='+br+'&no='+n, {onComplete:function(m){alert(m.responseText);}});
	ajax_request( 'shift_record.php?a=ajax_del_item&ajax=1&id='+escape(id)+'&month='+m+'&year='+y+'&branch='+br+'&no='+n );
}

function do_add(dept)
{
	//alert(document.f_s_r.name.value);
	if (empty(document.f_s_r.name, 'Please enter Employee Name'))
	{
	    return false;
	}
	if (empty(document.f_s_r.id, 'Please enter Employee ID'))
	{
	    return false;
	}
	if(dept=='%%'){
        if (empty(document.f_s_r.array_department, 'Please enter Department')){
	    	return false;
		}
	}
	if(dept=='Promoter (Hard line)' || dept=='Promoter (Soft line)' || dept=='Promoter (Supermarket line)'){
        if (empty(document.f_s_r.brand, 'Please enter Brand of Promoter')){
	    	return false;
		}
	}
	new Ajax.Updater('div_sr', 'shift_record.php',
	{
		parameters: 'a=ajax_add_item&ajax=1&'+Form.serialize(document.f_s_r),
		evalScripts: true
	});
	clear_autocomplete();
}


function do_cancel(){
	curtain(false);
	Element.hide('user_popup');
	Element.hide('print_popup');
}


function check_brand(obj,n){
	if(obj.value=='Promoter (Hard line)' || obj.value=='Promoter (Soft line)' || obj.value=='Promoter (Supermarket line)'){
		$('brand'+n).type='text';
	}
	else{
		$('brand'+n).type='hidden';
	}
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
	new Ajax.Updater('div_sr', 'shift_record.php',
	{
		parameters: 'ajax=1&'+Form.serialize(document.f_s_r)+'&a=ajax_refresh_items',
		evalScripts: true
	});
}


// add new row
function add_row(obj,n,t,b,n_row){
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

	var new_row = $('div_sr').insertRow(-1);
	var new_row2 = $('div_sr').insertRow(-1);

	if (((n+n_row)%2)==1){new_row.bgColor="#dddddd";new_row2.bgColor="#eeeeee";}
	else { new_row.bgColor="#ff9999";new_row2.bgColor="#ffcccc";}


	new_row.innerHTML='<th>&nbsp;</th><td><input onchange=uc(this) name=name['+n+'] size=30 maxlength=50 value="" class=name onblur="add_row(this,'+(n+1)+',\''+t+'\',\''+b+'\','+n_row+')"></td><td><input onchange="uc(this);chk_id(this,\'\')" class=name name=id['+n+'] size=20 maxlength=15 value=""></td>'+inhtml+'';

	if(t=='%%'){
			new_row2.innerHTML='<th>&nbsp;</th><td nowrap>{/literal}<select id=selected_dept name=array_department['+n+'] onchange="check_brand(this,'+n+')">{if $branch=="1"}<option >Finance</option><option>MIS</option><option>HR & Office</option><option>Internal Audit</option><option>Marketing</option><option>Merchandising</option></select>{else}<option>Management</option><option>Account</option><option>MIS</option><option>HR & Office</option><option >Security</option><option >Maintenance</option><option>A & P</option><option>Merchandising</option><option >Store</option><option >Sales (Supermarket line)</option><option >Sales (Soft line)</option><option >Sales (Hard line)</option><option>Promoter (Supermarket line)</option><option>Promoter (Soft line)</option><option>Promoter (Hard line)</option><option>Cashiering</option><option>Public Relations</option>{/if}</select>{literal}</td><td nowrap><input class=brand id=brand'+n+' onchange="uc(this);" type=hidden size=20 value="" name="brand['+n+']">&nbsp;</td>'+inhtml2+'';
	}
	else if(t=='Promoter (Supermarket line)' || t=='Promoter (Soft line)' || t=='Promoter (Hard line)'){
		new_row2.innerHTML='<th>&nbsp;</th><td align=center style="font-size:4px;color:#aaa;">Actual Attendance</td><td nowrap><input onchange="uc(this);" class=brand size=20 type=text value="" name="brand['+n+']"></td>'+inhtml2+'';
	}
	else{
		new_row2.innerHTML='<th>&nbsp;</th><td colspan=2 align=center style="font-size:4px;color:#aaa;">Actual Attendance</td>'+inhtml2+'';
	}
}

function chk_save(dept){
	if (check_login()) {
		chkstatus2=1;
		//alert(count);
		for (var i=1;i<(count);i++){
			if(trim(document.f_s_r.elements['name['+i+']'].value)=="" || trim(document.f_s_r.elements['id['+i+']'].value)=="")
			{
				chkstatus2=0;
				break;
			}
		}
		for (var i=1;i<count;i++){
			if(dept=='%%'){
				if (trim(document.f_s_r.elements['brand['+i+']'].value)=="" || trim(document.f_s_r.elements['array_department['+i+']'].value)=="" || trim(document.f_s_r.elements['name['+i+']'].value)=="" || trim(document.f_s_r.elements['id['+i+']'].value)==""){
					chkstatus2=0;
					break;
				}
			}
			if(dept=='Promoter (Hard line)' || dept=='Promoter (Soft line)' || dept=='Promoter (Supermarket line)'){
				if (trim(document.f_s_r.elements['brand['+i+']'].value)=="" || trim(document.f_s_r.elements['name['+i+']'].value)=="" || trim(document.f_s_r.elements['id['+i+']'].value)==""){
					chkstatus2=0;
					break;
				}
			}
		}
		if(chkstatus2==1){
			document.f_s_r.a.value ='save';
			document.f_s_r.submit();
			alert('You data have been successfull saved!');
		}
		else{
			alert("Please complete the details (Must Provide Name and ID).");
		}
	}
}



function do_refresh(){
	if (check_login()) {
		if(verify>0 && !confirm('You have unsaved changes, proceed without saving?')){
			return;
		}
		document.f_s_r_s.a.value = 'refresh';
		document.f_s_r_s.target = '';
		document.f_s_r_s.submit();
	}
}

function do_copy(b){
	if (check_login()) {
		if(!confirm('Copy existing name list from last month?')){
			return;
		}
		document.f_s_r_s.branch.value=b;
		document.f_s_r_s.department.value='%%';

		document.f_s_r_s.a.value = 'copy';
		document.f_s_r_s.target = '';
		document.f_s_r_s.submit();
	}
}

function chk_id(obj,x_val){
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
    if((status_id==0)&&(count_chk>1)){
    	alert("Existing id or duplicate id, please try again.");
		obj.value=x_val;
		obj.focus();
		return false;
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
		alert("Only R/M/A/F/H/X allowed for this column!");
		obj.value="";
		obj.focus();
		return false;
	}
}

function chk2ndrow(obj){
	verify++;
	if(obj.value=='AL' || obj.value=='PH' || obj.value=='NPL' || obj.value=='CL' || obj.value=='ABT'|| obj.value=='SPL' || obj.value=='MC' || obj.value=='CR' || obj.value=='ML'|| obj.value=='RPL'|| obj.value=='HQ' || obj.value=='BL' || obj.value=='GR' || obj.value=='KG'|| obj.value=='DG' || obj.value=='TM' || obj.value=='JT' || obj.value=='R' || obj.value=='M' || obj.value=='A' || obj.value=='F' || obj.value=='H'|| obj.value=='X'|| obj.value=='BS'){
	    return true;
	}
	else {
		alert("Only the legend character above allowed for this column!");
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

function do_submit_print(){
	if (check_login()) {
		curtain(false);
		Element.hide('print_popup');
		document.f_print.a.value = 'print_submit';
		document.f_print.target = 'ifprint';
		document.f_print.submit();
	}
}

function do_print(val){
	curtain(true);
	center_div('print_popup');
	Element.show('print_popup');
	//Position.clone($('user_popup'), $('print_popup'), {setHeight: false, setWidth:false});
}

function do_print_selected(val){
	if (check_login()) {
		document.f_s_r.a.value = 'print_selected';
		document.f_s_r.target = 'ifprint';
		document.f_s_r.submit();
	}
}

function check_all(v){
	var obj = $('print_popup').getElementsByTagName('input');
	for(j=0;j<obj.length;j++){
		if (obj[j].type=='checkbox'){
			obj[j].checked = v;
   		}
	}
}

function active_print(){
	var obj = $('tbl_sr').getElementsByClassName('print_select');
	for(j=0;j<obj.length;j++){
		if (obj[j].checked){
			$('print_selected').disabled=false;
			return;
   		}
   		else{
			$('print_selected').disabled=true;	   
		}
	}
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
	font:normal 11px arial;
}
#tbl_sr input.brand
{
	border:1px solid;
	font:normal 11px Arial;
}
#tbl_sr input.name_edit
{
	border:1px solid;
	font:normal 11px Arial;
	FONT-WEIGHT: bold;
}
#tbl_sr input.normal_s{
	color:black;
	border:1px solid;
	font:normal 12px Arial;

}
#tbl_sr input.cr_s{
	color:red;
	border:1px solid;
	font:normal 11px Arial;

}
#tbl_sr input.cb_s{
	color:blue;
	border:1px solid;
	font:normal 11px Arial;

}
#tbl_sr input.dept_brand_s
{
	font:normal 11px arial;
	border:1px solid;
}
</style>
{/literal}
{assign var=b value=$branch|default:$smarty.request.branch}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div id=print_popup {if $branch=='1'}style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:5px;width:230;height:190"{else}style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:5px;width:350;height:320"{/if}>

		<form name=f_print>
			<input type=hidden name=month value="{$month}">
			<input type=hidden name=year value="{$year}">
			<input type=hidden name=branch value="{$branch}">
			<input type=hidden name=department value="{$department}">
			<input type=hidden name=a value="print_submit">
			<p align=center><b>
			</b>
			<p align=left>
			
			<table>
			<tr>
			<th colspan=2 align=left><input type=checkbox onclick="check_all(this.checked)">Select/Unselect All<b></th>
			</tr>
			{if $branch=='1'}
			<tr>
			<td><input type=checkbox name=print_select[] value='Finance'>Finance</td>
			<td><input type=checkbox name=print_select[] value='MIS'>MIS</td>
			</tr>
			<tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='HR & Office'>HR & Office</td>
			<td><input type=checkbox name=print_select[] value='Internal Audit'>Internal Audit</td>
			</tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='Marketing'>Marketing</td>
			<td><input type=checkbox name=print_select[] value='Merchandising'>Merchandising</td>
			</tr>
			{else}
			<tr>
			<td><input type=checkbox name=print_select[] value='Management' {if $department eq 'Management'}checked{/if}>Management</td>
			<td><input type=checkbox name=print_select[] value='Account' {if $department eq 'Account'}checked{/if}>Account<td>
			</tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='MIS' {if $department eq 'MIS'}checked{/if}>MIS</td>
			<td><input type=checkbox name=print_select[] value='HR & Office' {if $department eq 'HR & Office'}checked{/if}>HR & Office</td>
			</tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='Security' {if $department eq 'Security'}checked{/if}>Security</td>
			<td><input type=checkbox name=print_select[] value='Maintenance' {if $department eq 'Maintenance'}checked{/if}>Maintenance</td>
			</tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='A & P' {if $department eq 'A & P'}checked{/if}>A & P</td>
			<td><input type=checkbox name=print_select[] value='Merchandising' {if $department eq 'Merchandising'}checked{/if}>Merchandising</td>
			</tr>
			<tr>
			<td><input type=checkbox name=print_select[] value='Store' {if $department eq 'Store'}checked{/if}>Store</td>
			<td nowrap><input type=checkbox name=print_select[] value='Sales (Supermarket line)' {if $department eq 'Sales (Supermarket line)'}checked{/if}>Sales (Supermarket line)</td>
			</tr>
			<tr>
			<td nowrap><input type=checkbox name=print_select[] value='Sales (Soft line)' {if $department eq 'Sales (Soft line)'}checked{/if}>Sales (Soft line)</td>
			<td nowrap><input type=checkbox name=print_select[] value='Sales (Hard line)' {if $department eq 'Sales (Hard line)'}checked{/if}>Sales (Hard line)</td>
			</tr>
			<tr>
			<td nowrap><input type=checkbox name=print_select[] value='Promoter (Supermarket line)'  {if $department eq 'Promoter (Supermarket line)'}checked{/if}>Promoter (Supermarket line)</td>
			<td nowrap><input type=checkbox name=print_select[] value='Promoter (Soft line)' {if $department eq 'Promoter (Soft line)'}checked{/if}>Promoter (Soft line)</td>
			</tr>
			<tr>
			<td nowrap><input type=checkbox name=print_select[] value='Promoter (Hard line)' {if $department eq 'Promoter (Hard line)'}checked{/if}>Promoter (Hard line)</td>
			<td><input type=checkbox name=print_select[] value='Cashiering' {if $department eq 'Cashiering'}checked{/if}>Cashiering</td>
			</tr>
			<tr>
			<td colspan=2><input type=checkbox name=print_select[] value='Public Relations' {if $department eq 'Public Relations'}checked{/if}>Public Relations</td>
			</tr>
			{/if}
			<tr>
			<td colspan=2>
			&nbsp;
			</td>
			</tr>
			<tr align=center>
			<td colspan=2>
			<input type=button value="OK" onclick="do_submit_print();">&nbsp;&nbsp;&nbsp;<input type=button value="Cancel" onclick="do_cancel();">
			</td>
			</tr>
			</table>
			</form>

	</div>

<div class="modal" id="print_popup">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" >
					<h6 class="modal-title">Please Select Department to Print</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
				</div>
			<div class="modal-body tx-center">
				
			</div>
		</div>
	</div>
</div>



<form name=f_edit_user method=post>
<input type=hidden name=month value="{$month}">
<input type=hidden name=year value="{$year}">
<input type=hidden name=branch value="{$branch}">
<input type=hidden name=department value="{$department}">
<div id=user_popup style="z-index:10000;background:#fff;border:2px solid #000;padding:5px;display:none;position:absolute;">
</div>
</form>

<div class="card mx-3">
	<div class="card-body">
		<form action=shift_record.php method=post name=f_s_r_s>
			<input type=hidden name=a value="refresh">
			<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
			
		
			<b class="form-label mt-2">By Date :</b>
			<select class="form-control" name="month" onchange="do_refresh();">
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
			</select>
			
			
				<select class="form-control mt-2" name="year" onchange="do_refresh();">
					{assign var=start_year value=2007}
					{assign var=max_year value=$smarty.now|date_format:"%Y"}
					{section start=$start_year loop=$max_year+1 name=s}
						{assign var=start_year value=$start_year+1}
						<option value="{$start_year}" {if $year == $start_year}selected {/if}>{$start_year}</option>
					{/section}
				</select>
		
			
			
			<b class="form-label mt-2">Branch :</b>
			<select class="form-control" name="branch" onchange="do_refresh_branch();">
			{foreach item="curr_Branch" from=$BranchArray}
			<option value={$curr_Branch.id} {if $curr_Branch.id==$branch}selected{/if}>{$curr_Branch.code}</option>
			{/foreach}
			</select>
		
		
			<b class="form-label mt-2">Department :</b>
			<select class="form-control" name="department" onchange="do_refresh();">
			<option value="%%"> --- All --- </option>
			{if $branch=='1'}
			<option value='Finance' {if $department eq 'Finance'}selected{/if}>Finance</option>
			<option value='MIS' {if $department eq 'MIS'}selected{/if}>MIS</option>
			<option value='HR & Office' {if $department eq 'HR & Office'}selected{/if}>HR & Office</option>
			<option value='Internal Audit' {if $department eq 'Internal Audit'}selected{/if}>Internal Audit</option>
			<option value='Marketing' {if $department eq 'Marketing'}selected{/if}>Marketing</option>
			<option value='Merchandising' {if $department eq 'Merchandising'}selected{/if}>Merchandising</option>
			{else}
			<option value='Management' {if $department eq 'Management'}selected{/if}>Management</option>
			<option value='Account' {if $department eq 'Account'}selected{/if}>Account</option>
			<option value='MIS' {if $department eq 'MIS'}selected{/if}>MIS</option>
			<option value='HR & Office' {if $department eq 'HR & Office'}selected{/if}>HR & Office</option>
			<option value='Security' {if $department eq 'Security'}selected{/if}>Security</option>
			<option value='Maintenance' {if $department eq 'Maintenance'}selected{/if}>Maintenance</option>
			<option value='A & P' {if $department eq 'A & P'}selected{/if}>A & P</option>
			<option value='Merchandising' {if $department eq 'Merchandising'}selected{/if}>Merchandising</option>
			<option value='Store' {if $department eq 'Store'}selected{/if}>Store</option>
			<option value='Sales (Supermarket line)' {if $department eq 'Sales (Supermarket line)'}selected{/if}>Sales (Supermarket line)</option>
			<option value='Sales (Soft line)' {if $department eq 'Sales (Soft line)'}selected{/if}>Sales (Soft line)</option>
			<option value='Sales (Hard line)' {if $department eq 'Sales (Hard line)'}selected{/if}>Sales (Hard line)</option>
			<option value='Promoter (Supermarket line)' {if $department eq 'Promoter (Supermarket line)'}selected{/if}>Promoter (Supermarket line)</option>
			<option value='Promoter (Soft line)' {if $department eq 'Promoter (Soft line)'}selected{/if}>Promoter (Soft line)</option>
			<option value='Promoter (Hard line)' {if $department eq 'Promoter (Hard line)'}selected{/if}>Promoter (Hard line)</option>
			<option value='Cashiering' {if $department eq 'Cashiering'}selected{/if}>Cashiering</option>
			<option value='Public Relations' {if $department eq 'Public Relations'}selected{/if}>Public Relations</option>
			{/if}
			</select>
		
			
			<td>
			<p align=center class="mt-3">
			<input type=button class="btn btn-primary" value="Refresh" onclick="do_refresh();">
			{if $shift_record_privilege.SHIFT_RECORD_EDIT.$b}
			<input type=button class="btn btn-info" value="Print" onclick="do_print('{$department}')">
			
			<input id=print_selected disabled type=button class="btn btn-primary" value="Print Selected" onclick="do_print_selected('{$department}')">
			{/if}
			{if $num_row==0 && $shift_record_privilege.SHIFT_RECORD_EDIT.$branch && $num_row_last>0}
			<input type=button class="btn btn-primary" onclick="do_copy({$branch})" value="Copy Name List" >
			{/if}
			</p>
			
			</table>
			</form>
	</div>
</div>


{include file=shift_record.items.tpl}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover fs-08">
				<tr>
				<td colspan=18><font color=red>1st row allowed </font>:</td>
				</tr>
				<tr>
				<td width='2' align=right><font color=blue>A</font></td><td width='1' align=center>:</td><td width='10'>Afternoon Shift</td>
				<td width='2' align=right><font color=blue>F</font></td><td width='1' align=center>:</td><td width='10'>Full Day</td>
				<td width='2' align=right><font color=blue>H</font></td><td width='1' align=center>:</td><td width='10'>Half Day</td>
				<td width='2' align=right><font color=blue>M</font></td><td width='1' align=center>:</td><td width='10'>Morning Shift</td>
				<td width='2' align=right><font color=red>R</font></td><td width='1' align=center>:</td><td width='10'>Rest</td>
				<td width='2' align=right><font color=blue>X</font></td><td width='1' align=center>:</td><td width='10'>Resign</td>
				</tr>
				
				<tr>
				<td colspan=18><font color=red>2nd row allowed </font>:</td>
				</tr>
				<tr>
				<td width='2' align=right><font color=blue>A</font></td><td width='1' align=center>:</td><td width='10'>Afternoon Shift</td>
				<td width='2' align=right><font color=blue>ABT</font></td><td width='1' align=center>:</td><td width='10'>Absent</td>
				<td width='2' align=right><font color=blue>AL</font></td><td width='1' align=center>:</td><td width='10'>Annual Leave</td>
				<td width='2' align=right><font color=blue>BL</font></td><td width='1' align=center>:</td><td width='10'>Visit Baling</td>
				<td width='2' align=right><font color=blue>CL</font></td><td width='1' align=center>:</td><td width='10'>Company Leave</td>
				<td width='2' align=right><font color=blue>CR</font></td><td width='1' align=center>:</td><td width='10'>Change Rest Day</td>
				</tr>
				
				<tr>
				<td width='2' align=right><font color=blue>DG</font></td><td width='1' align=center>:</td><td width='10'>Visit Dungun</td>
				<td width='2' align=right><font color=blue>F</font></td><td width='1' align=center>:</td><td width='10'>Full Day</td>
				<td width='2' align=right><font color=blue>GR</font></td><td width='1' align=center>:</td><td width='10'>Visit Gurun</td>
				<td width='2' align=right><font color=blue>H</font></td><td width='1' align=center>:</td><td width='10'>Half Day</td>
				<td width='2' align=right><font color=blue>HQ</font></td><td width='1' align=center>:</td><td width='10'>Visit Head Office</td>
				<td width='2' align=right><font color=blue>JT</font></td><td width='1' align=center>:</td><td width='10'>Visit Jitra</td>
				</tr>
				
				<tr>
				<td width='2' align=right><font color=blue>KG</font></td><td width='1' align=center>:</td><td width='10'>Visit Kangar</td>
				<td width='2' align=right><font color=blue>M</font></td><td width='1' align=center>:</td><td width='10'>Morning Shift</td>
				<td width='2' align=right><font color=blue>MC</font></td><td width='1' align=center>:</td><td width='10'>Medical Leave</td>
				<td width='2' align=right><font color=blue>ML</font></td><td width='1' align=center>:</td><td width='10'>Maternity Leave</td>
				<td width='2' align=right><font color=blue>NPL</font></td><td width='1' align=center>:</td><td width='10'>No Pay Leave</td>
				<td width='2' align=right><font color=blue>PH</font></td><td width='1' align=center>:</td><td width='10'>Public Holiday</td>
				
				</tr>
				
				<tr>
				<td width='2' align=right><font color=red>R</font></td><td width='1' align=center>:</td><td width='10'>Rest</td>
				<td width='2' align=right><font color=blue>RPL</font></td><td width='1' align=center>:</td><td width='10'>Replacement Leave</td>
				<td width='2' align=right><font color=blue>SPL</font></td><td width='1' align=center>:</td><td width='10'>Special Leave</td>
				<td width='2' align=right><font color=blue>TM</font></td><td width='1' align=center>:</td><td width='10'>Visit Tanah Merah</td>
				<td width='2' align=right><font color=blue>X</font></td><td width='1' align=center>:</td><td width='10'>Resign</td>
				<td width='2' align=right><font color=blue>BS</font></td><td width='1' align=center>:</td><td width='10'>Visit Bagan Serai</td>
				</tr>
				</table>
		</div>
	</div>
</div>


{include file=footer.tpl}
<script>
{if $shift_record_privilege.SHIFT_RECORD_EDIT.$b}
_init_enter_to_skip(tbl_sr);
{else}
Form.disable(document.f_s_r);
{/if}
</script>
