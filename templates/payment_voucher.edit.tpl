{*
	REVISION HISTORY
	=================
	- 11/28/2007 12:51:53 PM gary
	- add validate issue_name in changing voucher type.
	
	2/21/2008 6:18:42 PM gary
	- amend the title of cheque butt.
	- display the cancellation reason.
	
	2/27/2008 2:48:38 PM gary
	- fix cancellation reason show in create new (bug).
	
	11/22/2010 4:16:48 PM Alex
	- fix cancellation status show in create new (bug)
	
	12/9/2010 11:22:05 AM Alex
	- fix calculation bugs
	- change text to readonly instead of hidden
	
	4/19/2017 11:17 AM Khausalya
	-Enhanced changes from RM to use config setting. 
	
	6/23/2020 03:24 PM Sheila
	- Updated button css
	
	*}
	
	{include file=header.tpl}
	<script>
	
	{if $form.id || $form.bank}
		
		var g_type="{$form.voucher_type}";
	{else}
		var g_type="1";
	{/if}
	
	{literal}
	
	var no_add=0;
	var count;
	var no_rows;
	var fix_rows;
	
	function reset_row(status){
		if(status){
			no_rows--;
		}
		//set only the last row can allow add_row when onblur.
		var e = $('item_list').getElementsByClassName('doc_no');
		var last=float(e.length-1);
		//alert('row='+e.length+'//last='+last);
		for(var i=0;i<e.length;i++)	{
			 var temp_1 =new RegExp('^doc_no_');
			 if (temp_1.test(e[i].id)){	
				 if(i!=last){
					 e[i].alt="used"; 	
				}
				else{
					 e[i].alt='';
				}
			}
		}
		//reset the no of each row.
		var e = $('item_list').getElementsByClassName('no');
		//alert(no_rows);
		for(var i=0;i<e.length;i++)	{
			 var temp_1 =new RegExp('^no_');
			 if (temp_1.test(e[i].id)){
				 if((i==no_rows) && e[i].title==''){
					 e[i].innerHTML=i+1; 	
				 }
				 else{
					 if(e[i].title==''){
						td_1='<img src=/ui/remove16.png title="Delete" onclick="if(confirm(\'Are you sure?\')) Element.remove(this.parentNode.parentNode);calcalate_total();reset_row(0);" align=absmiddle border=0>'+(i+1)+'.';			 
					 }
					 else{
						td_1='<img src=/ui/remove16.png title="Delete" onclick="if(confirm(\'Are you sure?\')) Element.remove(this.parentNode.parentNode);calcalate_total();reset_row(1);" align=absmiddle border=0>'+(i+1)+'.';				 
					 }
					 e[i].innerHTML=td_1;
				 }
			}
		}
		
		//reset the cycle color of each row.
		var e = $('item_list').getElementsByClassName('cls_tr');
		for(var i=0;i<e.length;i++)	{
			 var temp_1 =new RegExp('^tr_');
			 if (temp_1.test(e[i].id)){	
				if (((i+1+no_rows)%2)==1){e[i].bgColor="#ff9999";}
				else { e[i].bgColor="#dddddd"}
			}
		}			
	}
	
	function add_row(obj,no_row,remove,amt){
		if (obj!=undefined){
			if(obj.value=='' && amt.value>0){
				alert('Please enter the doc_no too before you can go to next row.');
				return;
			}
			if (obj.alt != '' || obj.value=='' || obj.value=='0')return;
			obj.alt='used';
		}
		no_add++;
		
		if(no_rows==undefined){
			no_rows=0;
		}
		count=float(fix_rows)+float(no_add);
		var new_row = $('item_list').insertRow(-1);
		new_row.id='tr_'+count;
		new_row.className='cls_tr';
	
		if (((count)%2)==1){new_row.bgColor="#dddddd";}
		else { new_row.bgColor="#ff9999"}
	
		if(remove=='1'){
			td_1='<img src=/ui/remove16.png title="Delete" onclick="if(confirm(\'Are you sure?\')) Element.remove(this.parentNode.parentNode);calcalate_total();reset_row(0);" align=absmiddle border=0>'+(count)+'.';
		}
		else{
			td_1=count+".";
		}
		
		if($('doc_type_'+(count-1))){
			last_doc_type=$('doc_type_'+(count-1)).value;	
		}
		
		new_row.innerHTML='<th align=center class="no" id=no_'+count+'>'+td_1+'</th><td align=center><select class="form-control" id=doc_type_'+count+' name="doc_type[]" onchange="check_type(this,'+count+');chk_doc_no($(\'doc_no_'+count+'\'),this,this.value);">{/literal}{foreach key=key item=item from=$doc_type}<option value={$key} {if $list.$n.doc_type==$key}selected{/if}>{$item}</option>{/foreach}{literal}</select></td><td align=center><input class="form-control" id=doc_date_'+count+' name="doc_date[]" size="10" maxlength=10 onchange="check_date(this);" onclick="if(this.value)this.select();"></td><td><input class="form-control" onchange="uc(this);chk_doc_no(this,$(\'doc_type_'+count+'\'),$(\'doc_type_'+count+'\').value);" name="doc_no[]" size="58" id=doc_no_'+count+' class="doc_no"></td><td class="r"><input class="form-control" id=credit_'+count+' name="credit[]" size="20" class="r" onchange="mf(this);calcalate_total();" onclick="if(this.value)this.select();" onblur="if(check_both(this,$(\'debit_'+count+'\'),$(\'doc_type_'+count+'\')))add_row($(\'doc_no_'+count+'\'),'+no_row+',1,this);">&nbsp;</td><td class="r"><input class="form-control" id=debit_'+count+' name="debit[]" size="20" class="r" onchange="mf(this);calcalate_total();" type="text" readonly onclick="if(this.value)this.select();" onblur="if(check_both($(\'credit_'+count+'\'),this,$(\'doc_type_'+count+'\')))add_row($(\'doc_no_'+count+'\'),'+no_row+',1,this);">&nbsp;</td>';
		
		$('doc_type_'+count).focus();
		if($('doc_type_'+(count-1))){
			$('doc_type_'+count).value=last_doc_type;
			check_type($('doc_type_'+count),count);	
		}
		reset_row(0);
	}
	
	function check_both(obj_credit,obj_debit,obj_doc_type){
		credit=obj_credit.value;
		debit=obj_debit.value;
		doc_type=obj_doc_type.value;
	
		if(doc_type=='O'){
			if(credit!='' && credit>0){
				obj_debit.readOnly=true;
				obj_debit.value=null;
				obj_credit.readOnly=false;
			}
			else if(debit!='' && debit>0){
				obj_credit.readOnly=true;
				obj_credit.value=null;
				obj_debit.readOnly=false;
			}
			else if(credit<1 && debit<1){
				obj_debit.readOnly=false;
				obj_credit.readOnly=false;
			}
			return true;	
		}
		else if(doc_type=='VI' || doc_type=='VD' || doc_type=='C'){
			if(credit!=''){
				if(credit==0){
					alert('Amount value cannot be zero or negative.');
					return false;
				}
				else{
					return true;
				}	
			}
		}
		else if(doc_type=='VC' || doc_type=='D'){
			if(debit!=''){
				if(debit==0){
					alert('Amount value cannot be zero or negative.');
					return false;
				}
				else{
					return true;
				}			
			}
		}
	}
	
	function chk_doc_no(obj,type_obj,org_type){
		var value=obj.value.toUpperCase();
		var d_type=type_obj.value;
		
		var doc_no= document.f_a.elements['doc_no[]'];
		var doc_type= document.f_a.elements['doc_type[]'];
		
		if(doc_no.length && value!=''){
			j=0;
			for(var i=0;i<doc_no.length;i++){
				if(trim(doc_no[i].value)==value && trim(doc_type[i].value)==d_type){
					j++;
				}
			} 
			if(j>1){
				alert('Doc No already Exist in this payment Voucher');
				if(obj.readOnly==true){
					type_obj.value=org_type;	
				}
				else{
					obj.value='';			
				}
				return false;
			}
		}
	}
	
	function isNumeric(value) {
	  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/)) 
		  return false;
	  return true;
	}
	
	
	function check_date(obj){	
		text=obj.value;
		if(text && isNumeric(text) && text.length=='6'){
			day=text.slice(0,2);
			month=text.slice(2,4);
			year=text.slice(4,6);
			year='20'+year;
	
			if(day<32 && month<13 && day>0 && month>0){
				obj.value=day+'/'+month+'/'+year;
			}
			else{
				alert('Invalid day/month format.');
				obj.value='';
				obj.focus();
			}
		}
		else{
			alert('Please provide the valid format.');
			obj.value='';
			obj.focus();
		}	
	}
	
	function do_save(){
		document.f_a.a.value='save';
		if(check_a())
		document.f_a.submit();
	}
	
	function check_a(){
		if($('vendor_id')){
			var vendor_id=$('vendor_id').value;	
		}
	
		var total=float($('t_debit').innerHTML);
		
		if(g_type=='4'){
			//issue name cant be blank (requested by ah lee)
			if (empty($('issue_name'), "You must keyin the cheque issue name.")){
				return false;
			}	
			//let acct_code cant be blank requested by ah lee
			//if (empty($('acct_code'), "You must have Account Code for the Bank, please refer to HQ Finance Department.")){
			   // return false;
			//}
		}
		else{
			if(!vendor_id){
				alert('Please choose a valid Vendor to create the voucher.');
				return false;
			}
			if (empty(document.f_a.acct_code, "You must have Account Code for the Bank, please refer to HQ Finance Department.")){
				return false;
			}
		}
	
		if($('bank')){
			if (empty($('bank'), "You must select a Bank.")){
				return false;
			}
			/*
			if (empty($('bank_code'), "You must have Bank Code for the Bank, please refer to HQ Finance Department.")){
				return false;
			}*/
			if (empty(document.f_a.banker_code, "You must have Banker Code for the Bank, please refer to HQ Finance Department.")){
				return false;
			}
		}
		else{
			alert('Empty banker informations, please refer to HQ Finance Department for the banker informations.');
			return false;
		}
		
		if (empty($('payment_date'), "You must enter the Payment Date")){
			return false;
		}
		
		 if(total>0){
			return true;
		}
		else{
			alert('Please make sure the amount is in POSITIVE');
			return false;		
		}	
	}
	
	function calcalate_total(){
		var t_credit=0;
		var t_debit=0;
		var e = $('item_list').getElementsByClassName('r');
		for(var i=0;i<e.length;i++)	{
			 if (/^credit_/.test(e[i].id)){
				t_credit+=float(e[i].value);
			}
			 if (/^debit_/.test(e[i].id)){
				t_debit+=float(e[i].value);
			}				
		}
		$('t_debit').innerHTML=round(t_credit-t_debit,2);
			
	}
	
	
	function check_type(obj,row){
		val=obj.value;
		if(val=='VI' || val=='VD' || val=='C'){
			$('debit_'+row).readOnly=true;
			$('debit_'+row).value='';
			$('credit_'+row).readOnly=false;
		}
		else if(val=='VC' || val=='D'){
			$('credit_'+row).readOnly=true;
			$('credit_'+row).value='';
			$('debit_'+row).readOnly=false;
		}
		else if(val=='O'){
			$('debit_'+row).readOnly=false;
			$('credit_'+row).readOnly=false;
		}
		
		check_both($('credit_'+row),$('debit_'+row),$('doc_type_'+row));
		calcalate_total();
	}
	
	function show_div(){
		//Element.hide('show_last');
		$('show_last').style.display='none';
		if(g_type=='4'){
			var v_b_i=$('voucher_branch_id').value;
			if($('acct_code')){
				a_c=$('acct_code').value;		
			}
			else{
				a_c='';
			}
			Element.show('bank_div');
			new Ajax.Updater('bank_div', 'payment_voucher.php', {
				parameters: 'a=ajax_load_vendor_detail&vendor_id='+'0'+'&voucher_branch_id='+v_b_i+'&acct_code='+a_c+'&voucher_type='+g_type,
				evalScripts: true
			});	
		}
		else{
			var vendor_id=$('vendor_id').value
			var v_b_i=$('voucher_branch_id').value
			if(vendor_id>0){
				Element.show('bank_div');	
				new Ajax.Updater('bank_div', 'payment_voucher.php', {
					parameters: 'a=ajax_load_vendor_detail&vendor_id='+vendor_id+'&voucher_branch_id='+v_b_i+'&voucher_type='+g_type,
					evalScripts: true
				});	
			}
			else{
				//alert('Please choose a vendor');
				return false;	
			}	
		}
		/*var acct_code=$('acct_code').value;
		alert(acct_code);
		if(acct_code=='' || !acct_code){
			alert('You must have Account Code for the Bank, please refer to HQ Finance Department.');
			$('vendor_id').value='';
			$('autocomplete_vendor').value='';
			$('autocomplete_vendor_choices').value='';
		}
		*/
	}
	
	function refresh_bank(obj){
		bank=obj.value;
		if($('vendor_id')){
			vendor_id=$('vendor_id').value;	
		}
		else{
			vendor_id='';	
		}
		if($('acct_code_1')){
			acct_code=$('acct_code_1').value;	
		}
		else{
			acct_code='';
		}
		v_b_i=$('voucher_branch_id').value;
		new Ajax.Updater('bank_div', 'payment_voucher.php', {
			parameters: 'a=refresh_bank&vendor_id='+vendor_id+'&bank='+bank+'&voucher_branch_id='+v_b_i+'&voucher_type='+g_type+'&acct_code='+acct_code,
			evalScripts: true
		});		
	}
	
	
	function do_cancel(){
		document.f_b.reason.value = '';
		var p = prompt('Enter reason to Cancel:');
		if (p.trim()=='' || p==null) return;
		document.f_b.reason.value = p;
		if (confirm('Press OK to Cancel this Voucher.')){
			document.f_b.a.value = "cancel";
			document.f_b.submit();
		}
	}
	
	function change_type(obj){
		type=obj.value;
		g_type=type;
		if(type=='1'){
			$('urgent').style.disabled=false;
			$('td_urgent').style.display='';
			$('td_urgent_2').style.display='';	
		}
		else{
			$('urgent').style.disabled=true;
			$('td_urgent').style.display='none';
			$('td_urgent_2').style.display='none';	
		}
		
		if(type=='3'){
			$('issue_row').style.display='';
			$('vendor_row').style.display='';
		}
		else if(type=='4'){
			$('vendor_id').value='';	
			$('autocomplete_vendor').value='';
			$('autocomplete_vendor_choices').value='';
			$('issue_row').style.display='';
			$('vendor_row').style.display='none';	
	
		}
		else if(type=='1' || type=='2' || type=='5'){
			$('issue_name').value='';
			$('issue_row').style.display='none';
			$('vendor_row').style.display='';
		}
	}
	
	/*
	function check_length(obj){
		txt=obj.value;
		if(txt.length>51){
			obj.value='';
			obj.focus();
			alert('Issue Name is not allowed more than 51 chars, just leave blank if more than 51 chars.');
			return false;
		}
	}
	*/
	
	function show_issue_option(name){
		if($('div_issue_list').style.display=='none'){
			Element.show('div_issue_list');	
			Position.clone($('issue_name_2'), $('div_issue_list'), {setHeight: false, setWidth:false});
			chklabel = $('issue_name').value;
			$$('#div_issue_list li').each(function (obj,idx){
				if (obj.innerHTML == chklabel){
					obj.className = 'current';
					obj.scrollToPosition;
				}
				else{
					obj.className = '';		
				}
			});	
		}
		else{
			Element.hide('div_issue_list');		
		}	
	}
	
	function do_select_issue_name(obj){
		var name =obj.title;	
		$('issue_name').value=name;	
		Element.hide('div_issue_list');		
	}
	
	</script>
	<style>
	#div_issue_list ul li {
		cursor:pointer;
		display:block;
		margin:0;padding:4px;
	}
	#div_issue_list ul li:hover {
		background:#ff9;
	}
	
	#div_issue_list ul li.current {
		background:#9ff;
	}
	
	#div_issue_list:hover ul {
		visibility:visible;
	}
	
	.keyin{
		background:yellow;
	}
	</style>
	{/literal}
	
	<div id=div_issue_list style="display:none;position:absolute;z-index:100;background:#fff;border:1px solid maroon;height:200px;width:515px;overflow:auto;">
	<ul class="list-group list-group-flush" id=tab>
	{foreach item="issue_item" from=$issue_list}
	<li class="list-group-item list-group-item-action" onclick="do_select_issue_name(this);" id="selected_issue_item" title="{$issue_item.name|upper}">{$issue_item.name|upper}</li>
	{/foreach}
	</ul>
	</div>
	
	
	<div id=show_last>
	{if $smarty.request.v_no}
	<font size=4>Last PV Reference no. :<b> {$smarty.request.v_no}{if $smarty.request.v_type eq '5'}(BA){/if}</b></font>
	{/if}
	</div>
	
	<div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE} {if $form.id}({$form.voucher_no}{if $form.voucher_type eq '5'}(BA){/if}){else}(New){/if}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
            </div>
        </div>
    </div>
    
    <h3>
    
    {if $form.status == '3'}
        Status: Completed
    {elseif $form.status == '2'}
        Status: Printed Voucher
    {elseif $form.status == '1'}
        Status: Saved Voucher
    {elseif $form.status == '0'}
        Status: Vourcher Cancelled
    {/if}
    
    </h3>
	{if !$form.status && $form.id}
	<div class="stdframe" style="background:#fff">
	<h4>Cancellation Reason</h4>
	<p>
	<img src=ui/terminated.png width=16 height=16  border=0 align=absmiddle> {$form.cancelled_reason} by {$form.cancel_user}
	</p>
	</div>
	<br>
	{/if}
	
	{if $errm.top}
	<div id=err><div class=errmsg><ul>
	{foreach from=$errm.top item=e}
	<div class="p-2 alert alert-danger rounded"><li> {$e}</li></div>
	{/foreach}
	</ul></div></div>
	{/if}
	
	<form name=f_a method=post>
	<input type=hidden name=a>
	<input type=hidden name=bank>
	<input type=hidden name=reason>
	<input type=hidden name=log_sheet_status value="{$form.log_sheet_status}">
	<input type=hidden name=status value="{$form.status}">
	<input type=hidden name=voucher_type value="{$form.voucher_type}">
	<input type=hidden name=id value="{$form.id}">
	<input type=hidden name=voucher_no value="{$form.voucher_no}">
	<input type=hidden name=branch_id value="{$form.branch_id}">
	
	<div class="card mx-3">
		<div class="card-body">
			<table>
    
                <tr>
                <b class="form-label mt-2">Voucher Type</b>
                {if ($form.voucher_type eq '1') || !$form.voucher_type}
                
                <td><input type=radio name="voucher_type" value=1 {if $form.voucher_type eq '1' || !$form.voucher_type}checked{/if} {if $form.id}disabled{else}onClick="change_type(this);"{/if}><span class="fs-09"> Normal</span></td>
                <td>
                    &nbsp;&nbsp;	<span id=td_urgent><span class="text-dark fs-09"><b> Urgent </b></span>
                <input align=absbottom type=checkbox name=urgent id=urgent {if $form.urgent eq '1'}checked{/if}></td></span>
                    
            </td>
                {/if}
                
                <td>&nbsp;&nbsp;<input type=radio name="voucher_type" value=2 {if $form.voucher_type eq '2'}checked{/if} {if $form.id}disabled{else}onClick="change_type(this);"{/if}> Fast Payment</td>
                
                <td><input type=radio name="voucher_type" value=3 {if $form.voucher_type eq '3'}checked{/if} {if $form.id}disabled{else}onClick="change_type(this);"{/if}> Different Cheque Issue Name</td>
                
                <td><input type=radio name="voucher_type" value=4 {if $form.voucher_type eq '4'}checked {/if} {if $form.id}disabled{else}onClick="change_type(this);show_div();"{/if}> Blank Sheet</td> 
                
                {if $BRANCH_CODE eq 'HQ'}
                <td><input type=radio name="voucher_type" value=5 {if $form.voucher_type eq '5'}checked {/if} {if $form.id}disabled{else}onClick="change_type(this);show_div();"{/if}> BA</td>
                {/if}
                </tr>
                
            </table>
	
	
	<div>
	

		<div class="row">
			
				<div class="col-md-6">
					<tr>
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label mt-2">To Branch</b>
						<select class="form-control" id="voucher_branch_id" name="voucher_branch_id" onchange="show_div();" {if $form.id}disabled{/if}>
						{foreach item="curr_Branch" from=$branches}
						<option value={$curr_Branch.id} {if $curr_Branch.id==$form.voucher_branch_id || $curr_Branch.id==$smarty.request.vbid}selected{/if}>{$curr_Branch.code}</option>
						{/foreach}
						</select>
						
						{/if}
					</tr>
				</div>
				<div class="col-md-6">
					<tr>
						<b class="form-label mt-2">Cheque Collect At</b>
						<select class="form-control" id="cheque_branch_id" name="cheque_branch_id"  {*if $form.id}disabled{/if*}>
						{foreach item="curr_Branch" from=$branches}
						<option value={$curr_Branch.id} {if $curr_Branch.id==$form.cheque_branch_id || $curr_Branch.id==$smarty.request.cbid}selected{/if}>{$curr_Branch.code}</option>
						{/foreach}
						</select>
					</tr>
				</div>
				
				
				{if $BRANCH_CODE ne 'HQ'}
					<div class="col-md-6"><input class="form-control" type=hidden id="voucher_branch_id" value="{$sessioninfo.branch_id}"></div>
				{/if}
				
				{if $form.voucher_type ne '4'}
				<div class="col-md-6">
					<tr id="vendor_row">
						<b class="form-label mt-2">To Vendor <span class="text-danger"> *</span></b>
					
						<div class="form-inline">
							<input class="form-control" id="vendor_id" size="6" name="vendor_id"  value="{$form.vendor_id}" readonly>
							&nbsp;<input class="form-control" size=53 id="autocomplete_vendor" name="vendor" value="{$form.vendor}" >
							<div id="autocomplete_vendor_choices" class="autocomplete"></div>
						</div>
						
					</tr>
				</div>
				{/if}
				
				
				<div class="col-md-6">
					<tr id=issue_row {if $form.voucher_type eq '4' || $form.voucher_type eq '3'}{else}style="display:none;"{/if}>
						<b class="form-label mt-2">Issue Name</b>
						<div class="form-inline">
							<input class="form-control" id="issue_name" name="issue_name" size="63" onchange="uc(this);" value="{$form.issue_name}" maxlength="50">
							&nbsp;&nbsp;<img src=/ui/option_button.jpg style="border:1px solid #bad3fc;padding:1px;" align=top onclick="show_issue_option($('issue_name').value);">
						</div>
						<small class="text-danger">(MAX 51 Chars)</small> 
							
							<div id="issue_name_2"></div>
						</td>
					</tr>
				</div>
				
				<div class="col-md-6">
					<tr>
						<b class="form-label mt-2">Payment Date</b>
							<input class="form-control" type="text" id="payment_date" name="selected_date" size=10 maxlength=10 value="{$form.payment_date|default:$smarty.request.d|date_format:$config.dat_format}" onchange="check_date(this);" onclick="if(this.value)this.select();">  
							<small>(ddmmyy)</small>
						</td>
					</tr>
				</div>
				
				<div class="col-md-6">
					<tr>
						<b class="form-label mt-2">Being Payment For</b>
						<textarea class="form-control" rows="2" cols="68" name=voucher_remark onchange="uc(this);">{$form.voucher_remark}</textarea>
						</td>
					</tr>
				</div>
			
		</div>
	</div>

	<br style="clear: both;">

			<table id="bank_div" style={if !$form.id}"display:none;"{/if}  border=0 cellspacing=0 cellpadding=4>
				{include file=payment_voucher.edit.vendor_detail.tpl}
				</table>

	<ul style="list-style-type: none;">
		<li>
		 <div class="alert alert-danger rounded mt-2" style="max-width: 350px;">Rows with empty '<font color=red>Doc No</font>' column will not be saved.<br></div>
		</ul>
		
	</div>
</div>
	{if $errm.voucher}
	<div id=err><div class=errmsg><ul>
	{foreach from=$errm.voucher item=e}
	<li> {$e}
	{/foreach}
	</ul></div></div>
	{/if}
	
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table id="tbl_voucher"  class="tb report_table table mb-0 text-md-nowrap  table-hover">
	
				<thead class="bg-gray-100">
					<tr>
				
						<th width=20>No.</th>
						<th width=20>Doc Type</th>
						<th width=50 nowrap>Doc Date (ddmmyy)</th>
						<th width=80>Doc No</th>
						<th width=150> + Amount ({$config.arms_currency.symbol}) </th>
						<th width=150> - Amount ({$config.arms_currency.symbol}) </th>
						</tr>
						
				</thead>
				<tbody id="item_list" class="fs-08">
				{assign var=m value=0}
				{section name=i loop=$list}
				{assign var=n value=$smarty.section.i.iteration-1}
				{assign var=m value=$smarty.section.i.iteration}
				<tr bgcolor={cycle values="#dddddd,#ff9999"} id="tr_{$n}" class="cls_tr">
				
				<th align=center nowrap id="no_{$n+1}" class="no" title="{$n+1}">
				
				{if ($form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ') || ($form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id )|| (!$form.status && $form.log_sheet_status<3 && $form.cancelled_by) || (!$form.active)}
				
				<img src=/ui/remove16.png title="Delete" onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode);calcalate_total();reset_row(1);" align=absmiddle border=0>
				{/if}
				{$n+1}.
				</th>
				
				<td align=center>
				<select class="form-control" id=doc_type_{$n} name="doc_type[]" onchange="check_type(this,{$n});chk_doc_no($('doc_no_{$n}'),this,'{$list.$n.doc_type}');">
				{foreach key=key item=item from=$doc_type}
				<option value={$key} {if $list.$n.doc_type==$key}selected{/if}>
				{$item}
				</option>
				{/foreach}
				</select>
				</td>
				
				<td align=center>
				<input class="form-control" id=doc_date_{$n} name="doc_date[]" size="10" maxlength=10 value="{$list.$n.doc_date|date_format:$config.dat_format}" onchange="check_date(this);" onclick="if(this.value)this.select();">
				</td>
				
				<td>
				<input class="form-control" id=doc_no_{$n} name="doc_no[]" size="58" value="{$list.$n.doc_no}" onchange="uc(this);chk_doc_no(this,$('doc_type_{$n}'),$('doc_type_{$n}').value);">
				</td>
				
				<td class="r">
				<input class="form-control" id=credit_{$n} name="credit[]" size="20" value="{$list.$n.credit|default:0|number_format:2:'.':''|ifzero:''}" class="r" onchange="mf(this);calcalate_total();check_both(this,$('debit_{$n}'),$('doc_type_{$n}'));" onclick="if(this.value)this.select();" type="text" {if ($list.$n.doc_type=='VC' || $list.$n.doc_type=='D')}readonly {/if}>&nbsp;
				</td>
				
				<td class="r">
				<input class="form-control" id=debit_{$n} name="debit[]" size="20" value="{$list.$n.debit|default:0|number_format:2:'.':''|ifzero:''}" class="r" onchange="mf(this);calcalate_total();check_both($('credit_{$n}'),this,$('doc_type_{$n}'));" onclick="if(this.value)this.select();" type="text" {if ($list.$n.doc_type=='VI' || $list.$n.doc_type=='VD' || $list.$n.doc_type=='C')}readonly {/if}>&nbsp;
				</td>
				
				{if ($form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ') || ($form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id )|| (!$form.status && $form.log_sheet_status<3 && $form.cancelled_by) || (!$form.active)}
				{assign var=val_zero value=0}
				<script>
				fix_rows={$m|default:$val_zero};
				no_rows={$m|default:$val_zero};
				check_both($('credit_{$n}'),$('debit_{$n}'),$('doc_type_{$n}'));
				</script>
				{/if}
				</tr>
				{/section}
				</tbody>
				
				<tbody class="fs-09">
					<tr>
						<th align=right colspan=5>Total ({$config.arms_currency.symbol})</th>
						<th align=right id=t_debit>{$form.total|number_format:2:".":""}</th>
						</tr>	
				</tbody>
				</table>
		</div>
	</div>
</div>
	
	{if ($form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ') || ($form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id )|| (!$form.status && $form.log_sheet_status<3 && $form.cancelled_by) || (!$form.active)}
	<script>add_row(undefined,{$m},'0',undefined);</script>
	{/if}
	</form>

	
	{if $form.status eq '1' || $form.status eq '2'}
	<form name=f_b method=post>
	<input type=hidden name=a>
	<input type=hidden name=bank>
	<input type=hidden name=reason>
	<input type=hidden name=id value="{$form.id}">
	<input type=hidden name=branch_id value="{$form.branch_id}">
	{/if}
	
	<p align=center>
	<input type=button class="btn btn-danger" value="Close" onclick="document.location='/payment_voucher.php'">
	
	{if ($form.status && $form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id) || ($form.status && $form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ')}
	<input type=button class="btn btn-info" value="Cancel" onclick="do_cancel()">
	{/if}
	
	{if ($form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ') || ($form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id )|| (!$form.status && $form.log_sheet_status<3 && $form.cancelled_by) || (!$form.active)}
	<input name=bsubmit type=button class="btn btn-success" value="Save" onclick="do_save()">
	{/if}
	</p>
	
	{include file=footer.tpl}
	
	<script>
	{if $smarty.request.bank}
	show_div();
	{/if}
	{if $form.voucher_type ne '4'}
	{literal}
	new Ajax.Autocompleter("autocomplete_vendor", "autocomplete_vendor_choices", "ajax_autocomplete.php?a=ajax_search_vendor&type=All", { afterUpdateElement: function (obj, li) { document.f_a.vendor_id.value = li.title; show_div(); }});
	//after complete, only can call the javascript function
	{/literal}
	{/if}
	{if ($form.log_sheet_status<3 && $sessioninfo.privilege.PAYMENT_VOUCHER_EDIT && BRANCH_CODE eq 'HQ') || ($form.log_sheet_status<3 && $form.user_id eq $sessioninfo.id )|| (!$form.status && $form.log_sheet_status<3 && $form.cancelled_by) || (!$form.active)}
	{else}
	Form.disable(document.f_a);
	{/if}
	</script>
	