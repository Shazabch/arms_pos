{*
REVISION HISTORY
================

2/20/2008 11:35:12 AM gary
- amend the title of printing cheque butt.

7/2/2010 4:11:39 PM Alex
- fix search bugs

4/5/2011 4:08:31 PM Justin
- Added the checking of whether want to print cheque base on config['payment_voucher_print_cheque_choice']

10/18/2012 2:07:00 PM Fithri
- payment voucher "Print & Re-Print Cheque By Log Sheet" add search log sheet

11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field

2/16/2017 1:51 PM Zhi Kai
- Change wording of 'Vouches' in 'Print Vouches by Voucer no' and 'Print Vouches by Selection' to 'Voucher'.
*}

{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
div.calendar{
	z-index: 10001;
}
td.due {
	background:#f00;
	color:#fff;
	font-weight:bold;
}
</style>

<script>
function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added1",
		align          :    "Bl",
		singleClick    :    true,
	}); 
	Calendar.setup({
		inputField     :    "added2",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added2",
		align          :    "Bl",
		singleClick    :    true,
	}); 
	Calendar.setup({
		inputField     :    "added3",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added3",
		align          :    "Bl",
		singleClick    :    true,
	}); 
	Calendar.setup({
		inputField     :    "added4",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added4",
		align          :    "Bl",
		singleClick    :    true,
	}); 
	/* 
	Calendar.setup({
		inputField     :    "added5",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added5",
		align          :    "Bl",
		singleClick    :    true,
	}); 
	Calendar.setup({
		inputField     :    "added6",
		ifFormat       :    "%Y-%m-%d",
		button         :    "t_added6",
		align          :    "Bl",
		singleClick    :    true,
	});  
	*/   
}

function list_sel(n,s){
	var i;
	for(i=0;i<=5;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('voucher_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	if (n==0) pg +='&search='+ $('search').value ;
	
	if(n==5){
		new Ajax.Updater('voucher_list', 'payment_voucher.php', {
		parameters: 'a=ajax_load_ls_list&t='+n+'&'+pg,
		evalScripts: true
		});	
	}
	else{
		new Ajax.Updater('voucher_list', 'payment_voucher.php', {
		parameters: 'a=ajax_load_voucher_list&t='+n+'&'+pg,
		evalScripts: true
		});	
	}

}

function do_print(id,b,vb){
	document.f_a.a.value = 'print';
	document.f_a.id.value = id;
	document.f_a.branch_id.value =b;
	document.f_a.voucher_branch_id.value =vb;	
	curtain(true);
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	$('print_dialog').style.display = 'none';
	document.f_a.target = "ifprint";
	document.f_a.submit();
	curtain(false);
}

function do_print_cheque(id,b,vb,bank,action,v_no,status){
	var type='single_cheque';
	Element.show('keyin_popup');
	curtain(true);
	center_div('keyin_popup');
	
	$('keyin_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('keyin_popup', 'payment_voucher.php', {
	parameters: 'a=ajax_load_keyin_list&b='+b+'&id='+id+'&vb='+vb+'&bank='+bank+'&type='+type+'&action='+action,
	evalScripts: true
	});
}

function print_single_cheque(){
	if($('p')){
		u_val=$('u').value;
		if(!u_val){
			alert('Please provide Username to Re-print cheque.');
			return;
		}
		
		p_val=$('p').value;
		if(!p_val){
			alert('Please provide Password to Re-print cheque.');
			return;
		}
		document.f_keyin.p.value = p_val;
		document.f_keyin.u.value = u_val;
	}
	cheque_no=document.f_keyin.cheque_no.value;
	if(!cheque_no){
		alert('Please keyin the cheque No.');
		return;	
	}
	Element.hide('keyin_popup');
	curtain_clicked();
	document.f_keyin.a.value = 'print_cheque';
	if(document.f_keyin.print_cheque == undefined || document.f_keyin.print_cheque.checked == true) document.f_keyin.target = 'ifprint';

	document.f_keyin.submit();
	list_sel(4);
}

var g_type;
var bid;

function load_banker_select_row(bid){
	if(g_type=='cheque'){
		display_div='banker_select_row';	
	}
	else if(g_type=='cheque_date'){
		display_div='banker_select_row_date';
	}	
	else if(g_type=='cheque_butt'){
		display_div='banker_select_row_butt';	
	}
	else if(g_type=='damage_cheque'){
		display_div='banker_select_row_damage';	
	}
	new Ajax.Updater(display_div, 'payment_voucher.php', {
		parameters: 'a=ajax_load_banker_selection&bid='+bid,
		evalScripts: true
	});	
}

function select_print(type){

	//bank stil keep the last value.
	//$('bank').value=null;
	//g_bank='';
	//alert($('bank').value+'//'+g_bank);
	g_type=type;
	
	if(g_type=='cheque'){
		Element.show('banker_select_row');
		if(!bid){
			bid=1;
			$('voucher_branch_id').value=1;
			$('voucher_branch_id_date').value=1;
		}
		load_banker_select_row(bid);		
	}
	else{
		if($('banker_select_row')){
			Element.hide('banker_select_row');		
		}	
	}
	if(g_type=='summary'){
		$('include_printed').checked=false;		
		Element.hide('select_printed_row');			
	}
	else{	
		Element.show('select_printed_row');		
	}
	Element.hide('keyin_popup');
	Element.show('cheque_popup');
	curtain(true);
	center_div('cheque_popup');
	init_calendar();
}

function select_print_date(type){
	g_type=type;
	if($('voucher_popup')){
		Element.hide('voucher_popup');			
	}
	if(g_type=='cheque_date'){
		Element.show('banker_select_row_date');
		if(!bid){
			bid=1;
			$('voucher_branch_id').value=1;
			$('voucher_branch_id_date').value=1;
		}
		load_banker_select_row(bid);		
	}
	else{
		if($('banker_select_row_date')){
			Element.hide('banker_select_row_date');			
		}
	}
		
	Element.hide('keyin_popup');
	Element.show('cheque_popup_date');
	curtain(true);
	center_div('cheque_popup_date');
	init_calendar();
}

function get_cheque_by_date_list(){
	Element.hide('cheque_popup_date');	
	g_b=$('voucher_branch_id_date').value;
	
	if($('include_printed_date').checked){
		g_printed=$('include_printed_date').value;
	}
	else{
		g_printed=0;
	}
	g_from_d=$('added3').value;
	g_to_d=$('added4').value;
	if(g_type=='cheque_date'){
		g_bank=$('bank').value;
	}
	else{
		g_bank='';
	}	
	ajax_load_cheque_list();
}

function curtain_clicked(){	
	Element.hide('cheque_popup');
	Element.hide('cheque_popup_date');
	Element.hide('keyin_popup');
	Element.hide('keyin_popup_date');
	Element.hide('print_dialog');
	Element.hide('print_butt');
	Element.hide('damage_cheque');
	Element.hide('print_cheques_by_ls');
	Element.hide('div_reprint_ls');
	Element.hide('print_ls_dialog');
	Element.hide('div_reprint_c_by_ls');
	curtain(false);
}

var g_b, g_from_d, g_to_d, g_from_no, g_to_no, g_printed, g_bank;

function get_print_cheque_list(){
	Element.hide('cheque_popup');	
	g_b=$('voucher_branch_id').value;
	if($('include_printed').checked){
		g_printed=$('include_printed').value;
	}
	else{
		g_printed=0;
	}
	g_from_d=$('added1').value;
	g_to_d=$('added2').value;	
	g_from_no=$('from_v_no').value;
	g_to_no=$('to_v_no').value;
	if(g_type=='cheque'){
		g_bank=$('bank').value;
	}
	else{
		g_bank='';
	}		
	ajax_load_cheque_list();
}

function ajax_load_cheque_list(){
	Element.show('keyin_popup');
	curtain(true);
	center_div('keyin_popup');
	$('keyin_popup').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('keyin_popup', 'payment_voucher.php', {
	parameters: 'a=ajax_load_keyin_list&branch_id='+g_b+'&from_date='+g_from_d+'&to_date='+g_to_d+'&from_no='+g_from_no+'&to_no='+g_to_no+'&type='+g_type+'&include_printed='+g_printed+'&bank='+g_bank,
	//+'&bank='+g_bank
	evalScripts: true
	});
}

function submit_print_by_date(){
	if($('p')){
		u_val=$('u').value;
		if(!u_val){
			alert('Please provide Username to Re-print cheque.');
			return;
		}
		
		p_val=$('p').value;
		if(!p_val){
			alert('Please provide Password to Re-print cheque.');
			return;
		}
		document.f_keyin.p.value = p_val;
		document.f_keyin.u.value = u_val;
	}
	
	if(g_type=='voucher_date'){
		b_copy=$('branch_copy').checked;
		v_copy=$('vendor_copy').checked;
	
		if(!b_copy && !v_copy){
			alert('You must select at least 1 copy to print.');
			return;
		}
		curtain_clicked();
	}
	
	document.f_keyin.a.value = 'print_list';
	document.f_keyin.target = 'ifprint';
	document.f_keyin.submit();
	list_sel(1);
}

function submit_print_list(){
	if($('p')){
		u_val=$('u').value;
		if(!u_val){
			alert('Please provide Username to Re-print cheque.');
			return;
		}
		
		p_val=$('p').value;
		if(!p_val){
			alert('Please provide Password to Re-print cheque.');
			return;
		}
		document.f_keyin.p.value = p_val;
		document.f_keyin.u.value = u_val;
	}
	if(g_type=='voucher'){
		b_copy=$('branch_copy').checked;
		v_copy=$('vendor_copy').checked;	

		if(!b_copy && !v_copy){
			alert('You must select at least 1 copy to print.');
			return;
		}
	}
	if(g_type=='voucher' || g_type=='summary'){
		curtain_clicked();
	}
	curtain_clicked();
	document.f_keyin.a.value = 'print_list';
	document.f_keyin.target = 'ifprint';
	document.f_keyin.submit();

	list_sel(1);
	if(g_type == 'cheque'){
		g_type='keyin_cheque';
		ajax_load_cheque_list();
	}
}


function submit_keyin(){
	//alert('sdgdfg0');
	if($('p')){
		u_val=$('u').value;
		if(!u_val){
			alert('Please provide Username to Re-print cheque.');
			return;
		}
		
		p_val=$('p').value;
		if(!p_val){
			alert('Please provide Password to Re-print cheque.');
			return;
		}
		document.f_keyin.p.value = p_val;
		document.f_keyin.u.value = u_val;
	}
	item_val=$('item_1').value;
	if(item_val){
		curtain_clicked();
		document.f_keyin.a.value = 'keyin_cheque';
		document.f_keyin.submit();
	}
	else{
		alert('Please keyin the first cheque #');
		return;
	}	
}

function submit_keyin_by_date(){
	item_val=$('item_1').value;
	if(item_val){
		curtain_clicked();
		new Ajax.Request( 'payment_voucher.php?'+Form.serialize(document.f_keyin_1)+'&a=keyin_cheque');
	}
	else{
		alert('Please keyin the first cheque #');
		return;
	}	
}

function click_it(obj){
	if (obj.checked){
		obj.value='1';
	}
	else{
		obj.value='0';
	}
}

function do_cancel(id,b_id){
	document.f_m_h.reason.value = '';
	var p = prompt('Enter reason to Cancel:');
	if (p.trim()=='' || p==null) return;
	document.f_m_h.reason.value = p;
	if (confirm('Press OK to Cancel this Voucher.')){
		document.f_m_h.id.value = id;
		document.f_m_h.branch_id.value = b_id;
		document.f_m_h.a.value = "cancel";
		document.f_m_h.submit();
	}
	//list_sel(3);
}

function print_cheque_butt(){
	g_type='cheque_butt';
	//alert(bid);
	if(!bid){
		bid=1;
		$('butt_branch_id').value=1;
	}
	//alert(bid);
	Element.show('print_butt');
	load_banker_select_row(bid)
	curtain(true);	
	center_div('print_butt');
	init_calendar();
}

function print_butt_ok(){
	t_val=$('to_c_no').value;
	f_val=$('from_c_no').value;
	if(!t_val || !f_val){
		alert('Please provide Cheque No. to print cheque butt.');
		return;
	}
	$('print_butt').style.display = 'none';
	document.f_butt.target = "ifprint1";
	document.f_butt.submit();
	curtain(false);
}

function keyin_damage_cheque(){
	document.f_damage.cheque_no.value='';
	$('damage_remarks').value='';
	g_type='damage_cheque';
	if(!bid){
		bid=1;
		$('damage_branch_id').value=1;
	}
	Element.show('damage_cheque');
	load_banker_select_row(bid);
	curtain(true);	
	center_div('damage_cheque');
}

function submit_damage(){
	if(check_damage()){
		$('damage_cheque').style.display = 'none';
		document.f_damage.submit();
		curtain(false);	
	}
}

function check_damage(){

	if (empty(document.f_damage.cheque_no, "You must enter Cheque No. to record")){
	    return false;
	}
	if (empty($('damage_remarks'), "You must enter Remarks to record")){
	    return false;
	}
	return true;
}

function get_banker(obj){
	if(obj){
		bid=obj.value;
		load_banker_select_row(bid);	
	}
}

function load_ls_banker(no,load_type){
	if(load_type=='print'){
		load_div='banker_select_row_ls';
	}
	else if(load_type=='reprint'){
		load_div='reprint_c_banker_select_row_ls';
	}
	new Ajax.Updater(load_div, 'payment_voucher.php', {
		parameters: 'a=ajax_load_banker_selection&ls_no='+no,
		evalScripts: true
	});	
}

function reset_autocomplete_field(inputname) {
	//alert($(inputname).value);
	$(inputname).value = '';
}

function print_cheques(){
	Element.show('print_cheques_by_ls');
	
	new Ajax.Updater('ls_row', 'payment_voucher.php', {
		parameters: 'a=ajax_load_log_sheet&from=cheque',
		evalScripts: true,
		onComplete:function(){
			ls_no=document.f_ls.log_sheet_no.value;	
			load_ls_banker(ls_no,'print');		
			var autocompleter_ins = new Ajax.Autocompleter(
				"cheque_autocomplete_log_sheet_no",
				"cheque_autocomplete_log_sheet_no_choices",
				"payment_voucher.php?a=ajax_load_log_sheet&from=cheque_autocomplete",
				{
					afterUpdateElement: function (obj, li) {
						$('cheque_autocomplete_opt_log_sheet_no_'+li.title).selected = true;
						load_ls_banker(li.title);
					}
				}
			);
		} 
	});
	curtain(true);	
	center_div('print_cheques_by_ls');	
}

function go_select_cheques(){
	Element.hide('print_cheques_by_ls');
	ls_no=document.f_ls.log_sheet_no.value;
	b=document.f_ls.bank.value;

	curtain(true);	
	center_div('keyin_popup');	
	
	Element.show('keyin_popup');
	new Ajax.Updater('keyin_popup', 'payment_voucher.php', {
		parameters: 'a=ajax_load_ls_items&type=cheques_by_ls&ls_no='+ls_no+'&bank='+b,
		evalScripts: true
	});
}


function print_selected_cheques(){
	if($('p')){
		u_val=$('u').value;
		if(!u_val){
			alert('Please provide Username to Re-print cheque.');
			return;
		}
		
		p_val=$('p').value;
		if(!p_val){
			alert('Please provide Password to Re-print cheque.');
			return;
		}
		document.f_keyin.p.value = p_val;
		document.f_keyin.u.value = u_val;
	}
	curtain_clicked();
	document.f_keyin.a.value = 'print_list';
	document.f_keyin.target = 'ifprint';
	document.f_keyin.submit();
}

function do_print_ls(ls_no){
	document.f_reprint_ls.log_sheet_no.value=ls_no;
	curtain(true);
	show_print_ls_dialog();		
}

function show_print_ls_dialog(){
	center_div('print_ls_dialog');
	$('print_ls_dialog').style.display = '';
	$('print_ls_dialog').style.zIndex = 10000;
}

function print_ls_ok(){
	$('print_ls_dialog').style.display = 'none';
	document.f_reprint_ls.target = 'ifprint';
	document.f_reprint_ls.submit();
	curtain(false);
}


function reprint_ls(){
	Element.show('div_reprint_ls');
	curtain(true);
	center_div('div_reprint_ls');
	new Ajax.Updater('reprint_ls_row', 'payment_voucher.php', {
		parameters: 'a=ajax_load_log_sheet&from=reprint_ls',
		evalScripts: true
	});	
}

function reprint_ls_submit(){
	document.f_reprint_ls.target = 'ifprint';
	document.f_reprint_ls.submit();	
	curtain_clicked();
}

function reprint_cheques(){
	Element.show('div_reprint_c_by_ls');

	new Ajax.Updater('reprint_c_ls_row', 'payment_voucher.php', {
		parameters: 'a=ajax_load_log_sheet&from=reprint_cheque',
		evalScripts: true,
		onComplete:function(){
			ls_no=document.f_reprint_c_ls.log_sheet_no.value;	
			load_ls_banker(ls_no,'reprint');		
			var autocompleter_ins = new Ajax.Autocompleter(
				"reprint_cheque_autocomplete_log_sheet_no",
				"reprint_cheque_autocomplete_log_sheet_no_choices",
				"payment_voucher.php?a=ajax_load_log_sheet&from=reprint_cheque_autocomplete",
				{
					afterUpdateElement: function (obj, li) {
						$('reprint_cheque_autocomplete_opt_log_sheet_no_'+li.title).selected = true;
						load_ls_banker(li.title);
					}
				}
			);
		}
	});
	curtain(true);
	center_div('div_reprint_c_by_ls');
}

function go_select_reprint_cheques(){
	Element.hide('div_reprint_c_by_ls');
	ls_no=document.f_reprint_c_ls.log_sheet_no.value;
	b=document.f_reprint_c_ls.bank.value;

	curtain(true);	
	center_div('keyin_popup');	
	
	Element.show('keyin_popup');
	new Ajax.Updater('keyin_popup', 'payment_voucher.php', {
		parameters: 'a=ajax_load_ls_items&type=cheques_by_ls&printed=1&ls_no='+ls_no+'&bank='+b,
		evalScripts: true
	});
}

</script>
{/literal}

<!-- Start PRINT CHEQUES BY LOG SHEET NO-->
<div id=div_reprint_c_by_ls style="background:#fff;border:3px solid #000;width:350px;height:180px;position:absolute; padding:10px; display:none;z-index:10000;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<form name=f_reprint_c_ls method=post>
<input type=hidden name=a value="print">

<h3 align=center>Select Log Sheet To Re-Print Cheques</h3>


<table align=center>
<tr id=reprint_c_ls_row align=left>
<th width=100>Log Sheet</th>
<td>	
<select name="log_sheet_no"  onchange="reprint_c_load_ls_banker(this.value);">
{foreach key=key item=item from=$ls_list}
<option value={$item.log_sheet_no} {if $selected==$key}selected{/if}>{$item.log_sheet_no}</option>
{/foreach}
</select>
</td>
</tr>

<tr id=reprint_c_banker_select_row_ls align=left>
<th>Banker</th>
<td>	
<select name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select>
</td>
</tr>
</table>

<br>
<p align=center>
<input type=button value="Next" onclick="go_select_reprint_cheques()"> 
</p>
</form>
</div>
<!--End PRINT CHEQUES BY LOG SHEET NO-->



<!-- Start REPRINT LS-->
<div id=div_reprint_ls style="background:#fff;border:3px solid #000;width:350px;height:140px;position:absolute; padding:10px; display:none;z-index:10000;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<form name=f_reprint_ls method=post>
<input type=hidden name=a value="reprint_ls">
<input type=hidden name=log_sheet_no value="">

<h3 align=center>Select Log Sheet To Re-Print</h3>


<table align=center>
<tr id=reprint_ls_row align=left>
<th width=100>Log Sheet</th>

<td>	
<select name="reprint_log_sheet_no">
{foreach key=key item=item from=$ls_list}
<option value={$item.log_sheet_no}>{$item.log_sheet_no}</option>
{/foreach}
</select>
</td>
</tr>

<tr>
<th colspan=2 align=center>
(Format : A5 Portrait)
</th>
</tr>
</table>

<br>
<p align=center>
<input type=button value="Print" onclick="reprint_ls_submit();"> 
</p>
</form>
</div>
<!--End REPRINT LS-->


<!-- Start print dialog -->
<div id=print_ls_dialog style="background:#fff;border:3px solid #000;width:260px;height:80px;position:absolute; padding:10px; display:none;">
<p align=center>
This Payment Voucher Log Sheet will Print with <br>
<b>A5 Portrait</b> Format.
<br><br>
<input type=button value="Print" onclick="print_ls_ok()"> 
<input type=button value="Cancel" onclick="curtain_clicked();">
</p>
</div>
<!--End print dialog -->


<!-- Start PRINT CHEQUES BY LOG SHEET NO-->
<div id=print_cheques_by_ls style="background:#fff;border:3px solid #000;width:350px;height:180px;position:absolute; padding:10px; display:none;z-index:10000;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<form name=f_ls method=post>
<input type=hidden name=a value="print">

<h3 align=center>Select Log Sheet To Print Cheques</h3>


<table align=center>
<tr id=ls_row align=left>
<th width=100>Log Sheet</th>
<td>	
<select name="log_sheet_no"  onchange="load_ls_banker(this.value);">
{foreach key=key item=item from=$ls_list}
<option value={$item.log_sheet_no} {if $selected==$key}selected{/if}>{$item.log_sheet_no}</option>
{/foreach}
</select>
</td>
</tr>

<tr id=banker_select_row_ls align=left>
<th>Banker</th>
<td>	
<select name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select>
</td>
</tr>
</table>

<br>
<p align=center>
<input type=button value="Next" onclick="go_select_cheques()"> 
</p>
</form>
</div>
<!--End PRINT CHEQUES BY LOG SHEET NO-->



<!-- Start KEYIN DAMAGE CHEQUE-->
<div id=damage_cheque style="background:#fff;border:3px solid #000;width:350px;height:240px;position:absolute; padding:10px; display:none;z-index:10000;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<form name=f_damage method=post>
<input type=hidden name=a value="keyin_damage_cheque">

<h3 align=center>Keyin Damage Cheque</h3>

<table>
<tr align=left>
<th width=100>Outlet</th>
<td>	
<select id="damage_branch_id" name="damage_branch_id" onchange="get_banker(this);">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$form.voucher_branch_id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
</select> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
</tr>

<tr id=banker_select_row_damage align=left>
<th width=100>Banker</th>
<td>	
<select id=bank_damage name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
</tr>

<tr align=left>
<th width=100>Cheque No.</th>
<td><input id=cheque_no name=cheque_no size=20> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr>

<tr align=left>
<th valign=top width=100>Remarks</th>
<td>	
<textarea rows="2" cols="25" name=damage_remarks id=damage_remarks onchange="uc(this);"></textarea> <img src="ui/rq.gif" align="absbottom" title="Required Field">
</td>
</tr>
</table>

<p align=center>
<input type=button value="Save Damage Cheque" onclick="submit_damage()"> 
</p>
</form>
</div>
<!--End KEYIN DAMAGE CHEQUE-->


<!-- Start print cheque butt-->
<div id=print_butt style="background:#fff;border:3px solid #000;width:500px;height:160px;position:absolute; padding:10px; display:none;z-index:10000;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<form name=f_butt method=get>
<input type=hidden name=a value="print_butt">
<img src=ui/print64.png hspace=10 align=left> 
<h3>Printing Selection Of Cheque Butt</h3>

<table>
<tr align=left>
<th>Outlet</th>
<td>	
<select id="butt_branch_id" name="butt_branch_id" onchange="get_banker(this);">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$form.voucher_branch_id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
</select>
</td>
</tr>

<tr id=banker_select_row_butt align=left>
<th>Banker</th>
<td>	
<select id=bank_date name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select>
</td>
</tr>

<tr  align=left>
<th>Cheque # From</th>
<th nowrap>
<input type="text" name="from_c_no" id="from_c_no" size=12> 
</th>

<th>To</th>
<td>
<input type="text" name="to_c_no" id="to_c_no" size=12> 
</td>
</tr>

<!--tr align=left nowrap>
<th>Payment Date From</th>
<th nowrap>
<input type="text" name="from_date" id="added5" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added5" style="cursor: pointer;" title="Select Date">
</th>

<th>To</th>
<td>
<input type="text" name="to_date" id="added6" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added6" style="cursor: pointer;" title="Select Date">
</td>
</tr-->

</table>
<p align=center>
<input type=button value="Print Cheque Butt" onclick="print_butt_ok()"> 
</p>
</form>
</div>
<!--End print cheque butt -->


<!-- Start print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:140px;position:absolute; padding:10px; display:none;">
<form name=f_a method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a>
<input type=hidden name=tpl>
<input type=hidden name=id>
<input type=hidden name=reason>
<input type=hidden name=branch_id>
<input type=hidden name=voucher_branch_id>
<input type=hidden name=cheque_no>

<table>
<tr>
<td><input type=checkbox name="print_vendor_copy" checked> Vendor's Copy</td>
</tr>
<tr>
<td><input type=checkbox name="print_branch_copy" checked> Branch's Copy (Internal)</td>
</tr>

<tr>
<th>
(Format : A5 Landscape)
</th>
</tr>
</table>

<p align=center>
<input type=button value="Print" onclick="print_ok()"> 
<input type=button value="Cancel" onclick="curtain_clicked();">
</p>
</form>
</div>
<!--End print dialog -->


<!--START OF PRITING SELECTION WITH DATE-->
<div id="cheque_popup_date" style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:2px;width:370;height:180;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<h3 align=center>Printing Selection 1</h3>

<table>
{if $BRANCH_CODE eq 'HQ'}
<tr align=left>
<th>Outlet</th>
<td>	
<select id="voucher_branch_id_date" name="voucher_branch_id" onchange="get_banker(this);">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$form.voucher_branch_id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
</select>
</td>
</tr>

<tr id=banker_select_row_date style="display:none;" align=left>
<th>Banker</th>
<td>	
<select id=bank_date name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select>
</td>
</tr>

{else}
<input type=hidden name="voucher_branch_id" id="voucher_branch_id_date" value="{$sessioninfo.branch_id}">
{/if}

<tr align=left nowrap>
<th>Payment Date From</th>
<th nowrap>
<input type="text" name="from_date" id="added3" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added3" style="cursor: pointer;" title="Select Date">
</th>

<th>To</th>
<td>
<input type="text" name="to_date" id="added4" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added4" style="cursor: pointer;" title="Select Date">
</td>
</tr>

<tr align=left>
<th nowrap>Include Printed</th>
<td>
<input type=checkbox name="include_printed" id="include_printed_date" onchange="click_it(this);">
</td>
</tr>
</table>

<p align=center>
<input type=button value="Next" onclick="get_cheque_by_date_list();">
</p>
</div>
<!--END OF DIV PRINTING SELECTION WITH DATE-->


<!--START OF PRITING SELECTION WITH VOUCHER_NO-->
<div id="cheque_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:2px;width:370;height:210;">
<div class=small style="position:absolute; right:20; text-align:right;float:right;">
<img src=ui/closewin.png border=0 align=absmiddle onclick=curtain_clicked();>
</div>
<h3 align=center>Printing Selection 2</h3>
<table>
{if $BRANCH_CODE eq 'HQ'}
<tr align=left>
<th>Outlet</th>
<td>	
<select id="voucher_branch_id" name="voucher_branch_id" onchange="get_banker(this);">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$form.voucher_branch_id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
</select>
</td>
</tr>

<tr id=banker_select_row style="display:none;" align=left>
<th>Banker</th>
<td>	
<select id=bank name="bank">
{foreach key=key item=item from=$vvc.bank.bank_name}
<option value={$key} {if $selected==$key}selected{/if}>{$item}</option>
{/foreach}
</select>
</td>
</tr>
{else}
<input type=hidden name="voucher_branch_id" id="voucher_branch_id" value="{$sessioninfo.branch_id}">
{/if}

<tr align=left nowrap>
<th>Payment Date From</th>
<th nowrap>
<input type="text" name="from_date" id="added1" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
</th>

<th>To</th>
<td>
<input type="text" name="to_date" id="added2" size=8 value="{$smarty.now|date_format:"%Y-%m-%d"}"> 
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
</td>
</tr>

<tr  align=left>
<th>Voucher # From</th>
<th nowrap><input type="text" name="from_v_no" id="from_v_no" size=12 value="1"> 
</th>

<th>To</th>
<td>
<input type="text" name="to_v_no" id="to_v_no" size=12 value="99999"> 
</td>
</tr>

<tr align=left id=select_printed_row>
<th nowrap>	Include Printed</th>

<td>
<input type=checkbox name="include_printed" id="include_printed" onchange="click_it(this);">
</td>
</tr>
</table>

<p align=center>
<input type=button value="Next" onclick="get_print_cheque_list();">
</p>
</div>
<!--END OF DIV PRINTING SELECTION  WITH VOUCHER_NO-->

<!--START OF CONFIRM PRINT ITEMS TEMPLATE-->
<form name=f_keyin method=post>
<input type=hidden name=a>
<input type=hidden name=p>
<input type=hidden name=u>
<input type=hidden name=print_items>
<input type=hidden name=tpl>
<input type=hidden name=branch_id>
<input type=hidden name=id>
<input type=hidden name=bank>
<!--input type=hidden name=action-->
<div id="keyin_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:2px;width:350px;height:450px;">
{include file=payment_voucher.home.print.items.tpl}
</div>
</form>
<!--END OF CONFIRM PRINT ITEMS TEMPLATE-->

<!--START OF CONFIRM PRINT ITEMS TEMPLATE-->
<form name=f_keyin_1 method=post>
<input type=hidden name=a>
<input type=hidden name=p>
<input type=hidden name=u>
<div id="keyin_popup_date" style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:2px;width:350px;height:450px;">
{include file=payment_voucher.home.print.items.tpl}
</div>
</form>
<!--END OF CONFIRM PRINT ITEMS TEMPLATE-->


<!--START OF MAIN FORM-->
<form onsubmit="list_sel(0,0);return false;" name=f_m_h method=post>
<input type=hidden name=a>
<input type=hidden name=tpl>
<input type=hidden name=id>
<input type=hidden name=reason>
<input type=hidden name=branch_id>
<input type=hidden name=voucher_branch_id>
<input type=hidden name=cheque_no>

<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>

<ul>
<li><img src=ui/new.png align=absmiddle>
<a href=payment_voucher.php?a=new> Create New Payment Voucher </a>
<li>
<table>
<tr>
<td width=200>
<a href="javascript:void(select_print('voucher'))">
<img src=/ui/print.png border=0 title="Print Voucher"> Print Voucher by Voucher No</a>
<td>
<td>
<a href="javascript:void(select_print_date('voucher_date'))"> 
<img src=/ui/print.png border=0 title="Print Voucher">  Print Voucher by Selection</a>
</td>
</tr>
</table>


<li>
<table>
<tr>
<td width=202>
<a href="javascript:void(select_print('summary'))"> 
<img src=/ui/print.png border=0 title="Print Voucher Log Sheet"> Print PV Log Sheet</a>
</td>


<td width=200>
<a href="javascript:void(reprint_ls())"> 
<img src=/ui/print.png border=0 title="Re-Print Voucher Log Sheet"> Re-Print PV Log Sheet</a>
</td>
</tr>
</table>

{if BRANCH_CODE eq 'HQ'}
<li>
<table>
<tr>
<td width=200>
<a href="javascript:void(print_cheques())"> 
<img src=/ui/icons/script.png border=0 title="Print Cheques"> Print Cheques By Log Sheet</a>
</td>

<td width=200>
<a href="javascript:void(reprint_cheques())"> 
<img src=/ui/icons/script.png border=0 title="Print Cheque Butt"> Re-Print Cheques By Log Sheet</a>
</td>
</tr>
</table>

<li>
<table>
<tr>
<td width=200>
<a href="javascript:void(keyin_damage_cheque())"> 
<img src=/ui/report_edit.png border=0 title="Keyin Damage Cheque"> Keyin Damage Cheque</a>
</td>

<td width=200>
<a href="javascript:void(print_cheque_butt())"> 
<img src=/ui/print.png border=0 title="Print Cheque Butt"> Print Cheque Butt</a>
</td>
</tr>
</table>
{/if}


</ul> 
<br>

<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved Voucher</a>
<a href="javascript:list_sel(2)" id=lst2 class=active>Printed Voucher</a>
<a href="javascript:list_sel(3)" id=lst3 class=active>Cancelled</a>
<a href="javascript:list_sel(4)" id=lst4 class=active>Completed</a>
<a href="javascript:list_sel(5)" id=lst5 class=active>Log Sheet Status</a>
<a id=lst0>Find <input id=search name=find> <input type=submit value="Go"></a>
</div>
</form>
<div id=voucher_list style="border:1px solid #000">
</div>

<!--END OF MAIN FORM-->
{include file=footer.tpl}
<script>
list_sel(1);
</script>
