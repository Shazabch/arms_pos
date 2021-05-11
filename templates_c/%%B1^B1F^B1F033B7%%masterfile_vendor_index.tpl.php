<?php /* Smarty version 2.6.18, created on 2021-05-10 17:41:10
         compiled from masterfile_vendor_index.tpl */ ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<?php echo '
<style>
#tbl_vvc input { 
	font: 10px "MS Sans Serif" normal;
}

.border{
	background-color:black;
}

.calendar, .calendar table {
	z-index:100000;
}

</style>
'; ?>


<script type="text/javascript">
var lastn = '';
var phpself = '<?php echo $_SERVER['PHP_SELF']; ?>
';
var enable_gst = '<?php echo $this->_tpl_vars['config']['enable_gst']; ?>
';
var default_tax_percent = float('<?php echo $this->_tpl_vars['config']['arms_tax_settings']['percent']; ?>
');

<?php echo '
function loaded()
{
	document.getElementById(\'bmsg\').innerHTML = \'Click Update to save changes\';
	changesel(document.f_b.vendortype_code, document.f_b._vendortype_code.value);
	document.f_b.changed_fields.value = \'\';
	document.f_b.code.focus();
	check_tax_registered();
}

function ed(n)
{
	document.getElementById(\'abtn\').style.display = \'none\';
	document.getElementById(\'ebtn\').style.display = \'\';
	document.getElementById(\'bmsg\').innerHTML = \'<img src=ui/clock.gif align=absmiddle> Loading...\';
	document.f_b[\'enable_stock_reoder_notify\'].checked = false;
	if(enable_gst){
		document.f_b[\'gst_register\'].value = 0;
		$(\'tr_gst_info\').style.display = "none";
	}
	
	showdiv(\'ndiv\');
	showdiv(\'tr_alt\');
	document.f_b.id.value = n;
	_irs.document.location = \'?a=e&id=\'+n;
	lastn = n;
	document.f_b.a.value = \'u\';
	document.f_b.code.focus();
	changed_input_tracker = document.f_b[\'changed_fields\'];
}

function add()
{
	showdiv(\'ndiv\');
	hidediv(\'tr_alt\');
	document.getElementById(\'abtn\').style.display = \'\';
	document.getElementById(\'ebtn\').style.display = \'none\';
	document.getElementById(\'bmsg\').innerHTML = \'Enter the following and click ADD\';
	document.f_b.reset();
	document.f_b.id.value = 0;
	document.f_b.a.value = \'a\';
	document.f_b.code.focus();
	if(enable_gst){
		$(\'tr_gst_info\').style.display = "none";
	}
	check_tax_registered();
}

function showtd(id,n)
{
	var did = document.f_d.department_id.value;
	document.getElementById(\'tmsg\').innerHTML = \'<img src=ui/clock.gif align=absmiddle> Loading...\';
	showdiv(\'ddiv\')
	if (n != undefined) $(\'td_name\').innerHTML = n;
	document.f_d.reset();
	_irs.document.location = \'?a=load_td&id=\'+id+\'&department_id=\'+did;
}
var g_id;
function show_vvc(id,n)
{
	g_id=id;
	//alert(g_id);
	$(\'vendor_id\').value=id;
	$(\'vendor\').value=n;
	showdiv(\'vvc_div\');	
	
	new Ajax.Updater("vvc_div", "masterfile_vendor.php",{
	    parameters:  \'a=load_vvc&vendor_id=\'+id,
	    evalScripts: true
	});
}

function vvc_keyin(){
	hidediv(\'vvc_div\');
	document.f_u.vendor_id.value=g_id;
	document.f_u.submit();
}

function show_vbb(id,n)
{
	g_id=id;
	//alert(g_id);
	$(\'vendor_id\').value=id;
	$(\'vendor\').value=n;
	showdiv(\'vbb_div\');

	new Ajax.Updater("vbb_div", "masterfile_vendor.php",{
	    parameters:  \'a=load_vbb&vendor_id=\'+id,
	    evalScripts: true
	});
}

function vbb_keyin(){
	hidediv(\'vbb_div\');
	document.f_v.vendor_id.value=g_id;
	document.f_v.submit();
}

function tdloaded()
{
	document.getElementById(\'tmsg\').innerHTML = \'\';
}

function showalt()
{
	if (document.f_b.id.value!=\'\' && document.f_b.id.value>0)
	{
		changed_input_tracker = document.f_t[\'changed_fields\'];
		document.f_t.vendor_id.value = document.f_b.id.value;
		document.f_t.branch_id.options[0].selected = true;
		showdiv(\'adiv\');
	}
}

function vloaded()
{
	document.f_t.changed_fields.value = \'\';
	if (document.f_t._branch_id.value == \'\')
	{
		document.f_t.vendor_id.value = document.f_b.id.value;
		document.getElementById(\'vmsg\').innerHTML = \'This Branch currently does not have an alternate vendor contact\';
	}
	document.f_t.branch_id.focus();
}

function loadalt(id)
{
	vid = document.f_t.vendor_id.value;
	document.f_t.reset();
	document.f_t.vendor_id.value = vid;
	document.getElementById(\'vmsg\').innerHTML = \'\';
	if (id == 0) return;

	changesel(document.f_t.branch_id, id);
	_irs.document.location = \'?a=lv&vid=\'+vid+\'&id=\'+id;
}

function act(n, s)
{
	_irs.document.location = \'?a=v&id=\'+n+\'&v=\'+s;
}

function check_b()
{
	if (check_login()) {
        if (empty(document.f_b.code, \'You must enter Code\'))
        {
            return false;
        }
        if (empty(document.f_b.description, \'You must enter Description\'))
        {
            return false;
        }
        if (empty(document.f_b.company_no, \'You must enter Company No\'))
        {
            return false;
        }
        if (empty(document.f_b.term, \'You must enter Term\'))
        {
            return false;
        }
        if (empty(document.f_b.credit_limit, \'You must enter Credit Limit\'))
        {
            return false;
        }
        if (empty(document.f_b.bank_account, \'You must enter Bank Account\'))
        {
            return false;
        }
        if (empty(document.f_b.address, \'You must enter Address\'))
        {
            return false;
        }
        if (empty(document.f_b.phone_1, \'You must enter at least Phone #1\'))
        {
            return false;
        }
        if (empty(document.f_b.contact_person, \'You must enter Contact Person\'))
        {
            return false;
        }

        /*
        if (empty(document.f_b.contact_email, \'You must enter Contact Email\'))
        {
            return false;
        }
        */

        return true;
    }
    return false;
}

function check_t()
{
	changed_input_tracker = document.f_t[\'changed_fields\'];
	if (document.f_t.branch_id.value == 0)
	{
		alert(\'You select a Branch\');
		return false;
	}
	if (empty(document.f_t.credit_limit, \'You must enter Credit Limit\'))
	{
		return false;
	}
	if (empty(document.f_t.bank_account, \'You must enter Bank Account\'))
	{
		return false;
	}
	if (empty(document.f_t.address, \'You must enter Address\'))
	{
		return false;
	}
	if (empty(document.f_t.phone_1, \'You must enter at least Phone #1\'))
	{
		return false;
	}
	if (empty(document.f_t.contact_person, \'You must enter Contact Person\'))
	{
		return false;
	}
	if (empty(document.f_t.contact_email, \'You must enter Contact Email\'))
	{
		return false;
	}
	return true;
}

function reload_table(load_page)
{
	if (load_page) lp = \'&pg=\'+$(\'pg\').value;
	else lp = \'\';
	params = \'a=ajax_reload_table&\'+Form.serialize($(\'search_form\'))+lp;
	//alert(params);return;
	$(\'span_loading_vendor_list\').show();
	new Ajax.Updater("udiv", "masterfile_vendor.php",{
	    parameters: params,
	    evalScripts: true,
		onComplete: function(m){
			ts_makeSortable($(\'vendor_tbl\'));
		}
	});
	return false;
}

function do_find(f,obj)
{
	uc(obj);
	var v = new String(obj.value);
	if (v==\'\')
	{
		alert(\'Search field is empty\');
		obj.focus();
		return;
	}
	new Ajax.Updater("udiv", "masterfile_vendor.php",{
	    parameters: \'a=ajax_reload_table&search=\'+f+\'&\'+Form.Element.serialize(obj),
	    evalScripts: true
	});
}

function open_grn(vendor_id){
	window.open(\'goods_receiving_note.summary.php?vendor_id=\'+vendor_id);
}


var VENDOR_PORTAL_POPUP = {
	f_vp: undefined,
	is_updating: false,
	initialize: function(){
		new Draggable(\'div_vendor_portal\',{ handle: \'div_vendor_portal_header\'});	
	},
	// function to close popup
	close: function(){
		if(this.is_updating){
			alert(\'Updating is running, please wait for it to finish.\');
			return false;
		}
		
		curtain(false, \'curtain2\');
		default_curtain_clicked();
	},
	// function to show popup with vendor portal information
	open: function(vid){
		var THIS = this;
		
		$(\'div_vendor_portal_content\').update(_loading_);
		
		curtain(true, \'curtain2\');
		center_div($(\'div_vendor_portal\').show());
		
		var params = {
			vid: vid
		};
		new Ajax.Request(phpself+\'?a=ajax_load_vendor_portal_info\',{
			parameters: params,
			method:\'post\',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\'] && ret[\'html\']){ // success
						$(\'div_vendor_portal_content\').update(ret[\'html\']);
						THIS.initial_f_vp();
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			    $(\'div_vendor_portal_content\').update(\'\');
			}
		});
	},
	// the first thing to do after popup is show, initial event for popup form
	initial_f_vp: function(){
		this.f_vp = document.f_vp;
		
		/*Calendar.setup({
		    inputField     :    "inp_expire_date",     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_expire_date",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});*/
		// setup calendar
		var inp_expire_date_list = $(this.f_vp).getElementsBySelector("input.inp_expire_date");
		for(var i=0; i<inp_expire_date_list.length; i++){
			var bid = inp_expire_date_list[i].id.split("-")[1];
			
			Calendar.setup({
			    inputField     :    "inp_expire_date-"+bid,     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_expire_date-"+bid,  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true
			});
		}
		
		this.is_updating = false;
	},
	// function when user toggle allow all branches checkbox
	toggle_all_allowed_branches: function(){
		var c = $(\'inp_toggle_all_allowed_branches\').checked;
		
		$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
			inp.checked = c;
		});	
	},
	// function when user toggle no expire checkbox
	toggle_no_expire: function(bid){
		var c = $(\'inp_no_expire-\'+bid).checked;
		
		if(c){
			$(\'span_expire_date-\'+bid).hide();
		}else{
			$(\'span_expire_date-\'+bid).show();
		}
	},
	// function when user change active status
	active_vendor_portal_changed: function(){
		var active = int(getRadioValue(this.f_vp[\'active_vendor_portal\']));
		
		var span_active_vendor_portal_msg = $(\'span_active_vendor_portal_msg\');
		if(active){
			$(span_active_vendor_portal_msg).hide();
		}else{
			$(span_active_vendor_portal_msg).show();
		}
	},
	// function when user click generate ticket
	generate_ticket_clicked: function(bid){
		var ticket = this.f_vp[\'login_ticket[\'+bid+\']\'].value;
		
		if(ticket){
			this.clear_ticket(bid);
		}else{
			this.generate_ticket(bid);
		}
	},
	// function to clear ticket
	clear_ticket: function(bid){
		this.f_vp[\'login_ticket[\'+bid+\']\'].value = \'\';
		$(\'btn_generate_ticket-\'+bid).value = \'Generate\';
		
		$(\'span_clone_ticket-\'+bid).hide();
	},
	// function to generate new ticket
	generate_ticket: function(bid, use_this_ticket){
		var alpha_list = [\'a\',\'b\',\'c\',\'d\',\'e\',\'f\',\'g\',\'h\',\'i\',\'j\',\'k\',\'l\',\'m\',\'n\',\'o\',\'p\',\'q\',\'r\',\'s\',\'t\',\'u\',\'v\',\'w\',\'x\',\'y\',\'z\'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var ticket_length = 10;
		var ticket = \'\';
		
		if(!use_this_ticket){
			// generate new ticket
			for(var i=0; i<ticket_length; i++){
				var j = Math.floor(Math.random()*(rand_char.length));
				ticket += rand_char[j];
			}
		}else{
			// use the given ticket
			ticket = use_this_ticket;
		}
		
		
		// assign
		this.f_vp[\'login_ticket[\'+bid+\']\'].value = ticket;
		$(\'btn_generate_ticket-\'+bid).value = \'Clear\';
		
		$(\'span_clone_ticket-\'+bid).show();
	},
	// function to validate form 
	check_form: function(){
		/*if(this.f_vp[\'login_ticket\'].value != \'\'){	// got ticket
			var checked_count = 0;
			$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
				if(inp.checked)	checked_count++;
			});
			if(checked_count<=0){
				alert(\'Please tick at least 1 allowed branch.\');
				return false;
			}
		}*/
		
		// get branches checkbox
		var inp_allowed_branches_list = $(this.f_vp).getElementsBySelector("input.inp_allowed_branches");
		
		// loop for each branches checkbox
		for(var i=0; i<inp_allowed_branches_list.length; i++){
			// found got tick
			if(inp_allowed_branches_list[i].checked){
				var bid = inp_allowed_branches_list[i].id.split("-")[1];	// get branch id
				
				// sku group
				if(!this.f_vp[\'sku_group_info[\'+bid+\']\'].value){
					alert(\'Please select SKU Group for all ticked branches.\');
					return false;
				}
				
				// login ticket
				if(!this.f_vp[\'login_ticket[\'+bid+\']\'].value){	// found no give login ticket
					alert(\'Please generate ticket for all ticked branches.\');
					return false;
				}
			}
		}
		
		return true;
	},
	// function when user click update
	update_clicked: function(){
		var THIS = this;
		
		// validate form
		if(!this.check_form())	return;
		
		// update button status
		$$(\'#p_action_button input\').invoke(\'disable\');
		$(\'btn_update_vendor_portal\').value = \'Updating…\';
		
		this.is_updating = true;
		
		var params = $(this.f_vp).serialize();
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = \'\';
			    THIS.is_updating = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret[\'ok\']){ // success
						alert(\'Update Successfully\');
						THIS.close();
		                return;
					}else{  // save failed
						if(ret[\'failed_reason\'])	err_msg = ret[\'failed_reason\'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = \'No Respond from server.\';
			    // prompt the error
			    alert(err_msg);
			    $$(\'#p_action_button input\').invoke(\'enable\');
			    $(\'btn_update_vendor_portal\').value = \'Update\';
			}
		});
	},
	// function to clone ticket for all branches
	clone_ticket: function(bid){
		var ticket = this.f_vp[\'login_ticket[\'+bid+\']\'].value.trim();
		if(!ticket)	return false;	// no ticket to clone
		
		if(!confirm(\'Are you sure to clone this ticket to all allowed branches?\'))	return false;
		
		// get branches checkbox
		var inp_allowed_branches_list = $(this.f_vp).getElementsBySelector("input.inp_allowed_branches");
		
		// loop for each branches checkbox
		for(var i=0; i<inp_allowed_branches_list.length; i++){
			// found got tick
			if(inp_allowed_branches_list[i].checked){
				var bid = inp_allowed_branches_list[i].id.split("-")[1];	// get branch id
				
				this.generate_ticket(bid, ticket);	// clone ticket
			}
		}
	}
}

function gst_info_onload(){
	if(document.f_b[\'gst_register\'] != undefined){
		// means this vendor is using GST and info is required
		if(document.f_b[\'gst_register\'].value == -1) $(\'tr_gst_info\').show();
		else $(\'tr_gst_info\').hide();
	}
}

function tax_registered_changed(){
	var tax_register = int(document.f_b[\'tax_register\'].value);
	if(tax_register>0){	// when change from No to Yes, put default tax percent if the percent is 0
		var tax_percent = float(document.f_b[\'tax_percent\'].value);
		if(tax_percent<=0){
			document.f_b[\'tax_percent\'].value = default_tax_percent;
		}
	}
	check_tax_registered();
}

function check_tax_registered(){
	var tax_register = int(document.f_b[\'tax_register\'].value);
	
	$$("td.td_tax_details").each(function(inp){
		if(tax_register>0){
			$(inp).show();
		}else{
			$(inp).hide();
		}
	});
}

function tax_percent_changed(){
	var inp = document.f_b[\'tax_percent\'];
	inp.value = float(inp.value);
	if(inp.value<=0)	inp.value = 0;
}

</script>
'; ?>


<!-- VENDOR PORTAL KEY DIALOG -->
<div id="div_vendor_portal" class="curtain_popup" style="position:absolute;z-index:10000;width:900px;height:550px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_vendor_portal_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_choose_item_type_dialog_header">Vendor Portal Information</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="VENDOR_PORTAL_POPUP.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_vendor_portal_content" style="padding:2px;height:500px;overflow:auto;">

	</div>
</div>
<!-- End of VENDOR PORTAL KEY DIALOG -->

<h1>Vendor Master File</h1>

<ul>
	<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>
		<li><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Vendor</a> (Alt+A)</li>
	<?php endif; ?>
	<li>
	    <a href="report.vendor_discount_table.php" target="_blank">
			<img src="ui/new.png" title="Enter Report" align="absmiddle" border="0" />
			Vendor Discount Table
		</a>
	</li>
	<li>
		<form name="f_a">
			<input type="hidden" name="a" value="do_export">
			<a href="javascript:void(document.f_a.submit())">
				<img src="ui/new.png" title="Enter Report" align="absmiddle" border="0" />
				Export As CSV (SQL Accounting)
			</a>
		</form>
	</li>
	<?php if ($this->_tpl_vars['config']['payment_voucher_vendor_maintenance'] && BRANCH_CODE == 'HQ'): ?>
		<li>
			<a href="masterfile_vendor.import_pymt_vch.php" target="_blank">
				<img src="ui/new.png" title="Enter Report" align="absmiddle" border="0" />
				Import / Export Payment Voucher Code by CSV
			</a>
		</li>
	<?php endif; ?>
</ul>

<form name="search_form" id="search_form" onsubmit="return reload_table()">
<p>
<b>Description</b> :&nbsp;
	<input type="text" name="desc" size="15" />

&nbsp;&nbsp;&nbsp;
	
<b>Status</b> :&nbsp;
	<select name="status">
		<option value="">All</option>
		<option value="1">Active</option>
		<option value="0">Inactive</option>
	</select>

&nbsp;&nbsp;&nbsp;

<b>Starts With</b> :&nbsp;
	<select name="starts_with">
		<option value="">All</option>
		<option value="A">A</option>
		<option value="B">B</option>
		<option value="C">C</option>
		<option value="D">D</option>
		<option value="E">E</option>
		<option value="F">F</option>
		<option value="G">G</option>
		<option value="H">H</option>
		<option value="I">I</option>
		<option value="J">J</option>
		<option value="K">K</option>
		<option value="L">L</option>
		<option value="M">M</option>
		<option value="N">N</option>
		<option value="O">O</option>
		<option value="P">P</option>
		<option value="Q">Q</option>
		<option value="R">R</option>
		<option value="S">S</option>
		<option value="T">T</option>
		<option value="U">U</option>
		<option value="V">V</option>
		<option value="W">W</option>
		<option value="X">X</option>
		<option value="Y">Y</option>
		<option value="Z">Z</option>
		<option value="others">Others</option>
	</select>
<input type=button value="Search" onclick="reload_table()" />
</p>
</form>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor_table.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<br>

<!-- start vendor -->
<div class="ndiv" id="ndiv" style="position:absolute;left:150;top:100;display:none;">
<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<form method=post name=f_b target=_irs onSubmit="return check_b()">
<div id=bmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="a">
<input type=hidden name=id value="">
<input type=hidden name=_vendortype_code value="">
<table id="tb"  border=0>
<tr>
<td><b>Vendor Code<font color="red" size="+1">*</font></b></td>
<td <?php if (! $this->_tpl_vars['config']['ci_auto_gen_artno']): ?> colspan=3 <?php endif; ?> ><input onBlur="uc(this)" name=code size=10 maxlength=10></td>
<?php if ($this->_tpl_vars['config']['ci_auto_gen_artno']): ?>
	<td><b>Prefix Code</b></td>
	<td><input onBlur="uc(this)" name=prefix_code size=2 maxlength=1></td>
<?php endif; ?>
</tr><tr>
<tr>
	<td><b>Internal Code [<a href="javascript:void(alert('Currently use for\n- Auto search by GRR for IBT DO.'));">?</a>]</b></td>
	<td><input onBlur="uc(this)" name="internal_code" size="15" maxlength="15" /></td>
</tr>
<td><b>Vendor Name<font color="red" size="+1">*</font></b></td>
<td colspan=3><input onBlur="uc(this)" name=description size=50></td>
</tr><tr>
<td><b>Company No.<font color="red" size="+1">*</font></b></td>
<td><input name=company_no size=20 maxlength=30></td>
<td><b>Vendor Type</b></td>
<td><select name=vendortype_code>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['vendortype']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<option value="<?php echo $this->_tpl_vars['vendortype'][$this->_sections['i']['index']]['code']; ?>
"><?php echo $this->_tpl_vars['vendortype'][$this->_sections['i']['index']]['code']; ?>
 - <?php echo $this->_tpl_vars['vendortype'][$this->_sections['i']['index']]['description']; ?>
</option>
<?php endfor; endif; ?>
</select></td>
</tr><tr>
<td><b>GRR without PO</b></td>
<td><select name=allow_grr_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
<?php if ($this->_tpl_vars['config']['use_grn_future']): ?>
<td><b>GRN items without PO</b></td>
<td><select name=allow_grn_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
</tr>
<?php else: ?>
	<td colspan="2">&nbsp;</td>
	</tr>
<?php endif; ?>
<tr>
<td><b>PO without Checkout GRA</b></td>
<td><select name=allow_po_without_checkout_gra><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
<?php if ($this->_tpl_vars['config']['use_grn_future']): ?>
<td><b>GRN items qty not allow <br />to over PO qty</b></td>
<td><select name=grn_qty_no_over_po_qty><option value=0>No</option><option value=1>Yes</option></select></td>
<?php else: ?>
	<td colspan="2">&nbsp;</td>
	</tr>
<?php endif; ?>
</tr><tr>
<td><b>Term<font color="red" size="+1">*</font></b></td>
<td><input name=term size=4></td>
<td><b>Grace Period</b></td>
<td><input name=grace_period size=4></td>
</tr><tr>
<td><b>Prompt Payment</b></td>
<td colspan=3>Term <input name=prompt_payment_term size=4> Discount (%) <input name=prompt_payment_discount size=4></td>
</tr><tr>
<td><b>Fast Payment</b></td>
<td colspan=3>Term <input name=fast_payment_term size=4> Discount (%) <input name=fast_payment_discount size=4></td>
</tr><tr id="tr_alt" style="display:none">
<td colspan=4><br>
Below is the default contact information for this vendor.<br>
To create alternate contact for branch, <a href="javascript:void(showalt())">click here &#187;</a>.

</tr><tr>
<td><b>Credit Limit<font color="red" size="+1">*</font></b></td>
<td><input name=credit_limit size=10></td>
<td><b>Bank Account<font color="red" size="+1">*</font></b></td>
<td><input name=bank_account size=20></td>
</tr><tr>
<td ><b>Address<font color="red" size="+1">*</font></b></td>
<td colspan=3><textarea name=address rows=5 cols=50></textarea></td>
</tr><tr>
<td><b>Phone #1<font color="red" size="+1">*</font></b></td>
<td><input name=phone_1 size=20></td>
<td><b>Phone #2</b></td>
<td><input name=phone_2 size=20></td>
</tr><tr>
<td><b>Fax No.</b></td>
<td colspan=3><input name=phone_3 size=20></td>
</tr><tr>
<td><b>Contact Person<font color="red" size="+1">*</font></b></td>
<td><input onBlur="uc(this)" name=contact_person size=20></td>
<td><b>Contact Email</b></td>
<td><input onBlur="lc(this)" name=contact_email size=20></td>
</tr><tr>
	<td><b>Enable Stock Reorder Notify</b></td>
	<td><input type="checkbox" name="enable_stock_reoder_notify" value="1" />
</tr>
<?php if ($this->_tpl_vars['config']['enable_vendor_account_id']): ?>
	<tr>
	<td><b>Account ID</b></td>
	<td colspan="3"><input name=account_id size=20></td>
	</tr>
<?php endif; ?>
<!--tr>
	<td valign=top><b>GST Type</b></td>
	<td colspan="3">
		<select name="gst_type" style="width:400;">
			<option value="">No GST</option>
			<?php $_from = $this->_tpl_vars['gst_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
				<option value="<?php echo $this->_tpl_vars['r']['id']; ?>
"><?php echo $this->_tpl_vars['r']['code']; ?>
 - <?php echo $this->_tpl_vars['r']['description']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
		</select>
	</td>
</tr-->

<?php if ($this->_tpl_vars['config']['enable_gst']): ?>
	<tr>
		<td><b>GST Registered</b></td>
		<!--td><input type="checkbox" name="gst_register" value="1" /></td-->
		<td colspan="3">
			<select name="gst_register" onchange="gst_info_onload();">
				<option value="0">No</option>
				<option value="-1">Yes</option>
				<?php $_from = $this->_tpl_vars['gst_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dummy'] => $this->_tpl_vars['r']):
?>
					<option value="<?php echo $this->_tpl_vars['r']['id']; ?>
"><?php echo $this->_tpl_vars['r']['code']; ?>
 - <?php echo $this->_tpl_vars['r']['description']; ?>
</option>
				<?php endforeach; endif; unset($_from); ?>
			</select>
		</td>
	</tr>

	<tr id="tr_gst_info">
		<td><b>GST Registration Number<font color="red" size="+1">*</font></b></td>
		<td><input name="gst_register_no" size="20" value="" /></td>
		<td><b>GST Start Date<font color="red" size="+1">*</font></b></td>
		<td>
			<input size="10" type="text" name="gst_start_date" value="" id="gst_start_date">
			<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select GST Start Date">
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<fieldset>
				<legend><b>GL Code</b></legend>
				<table>
					<tr>
						<td><b>Purchase Account Code</b></td>
						<td><input type="text" name="account_payable_code" value=""/></td>
						<td><b>Purchase Account Name</b></td>
						<td><input type="text" name="account_payable_name" value=""/></td>
					</tr>
				</table>

			</fieldset>
		</td>
	</tr>
<?php endif; ?>

<tr>
	<td><b><?php echo $this->_tpl_vars['config']['arms_tax_settings']['name']; ?>
 Tax Registered</b></td>
	<td>
		<select name="tax_register" onchange="tax_registered_changed();">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
	</td>
	<td class="td_tax_details"><b>Tax Percentage</b></td>
	<td class="td_tax_details">
		<input type="text" name="tax_percent" onChange="tax_percent_changed();" />
	</td>
</tr>
<tr>
	<td><b>Delivery Type</b></td>
	<td colspan="3">
		<select name="delivery_type">
			<option value="">Warehouse</option>
			<option value="D">Direct</option>
		</select>
	</td>
</tr>
<tr>
	<td align="center" colspan="4">
		<br>
		<div id="abtn" style="display:none;">
			<input class="btn btn-warning" type="submit" value="Add"> <input type="button" value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
		</div>
		<div id="ebtn" style="display:none;">
			<input class="btn btn-primary" type="submit" value="Update"> <input class="btn btn-warning "type="button" value="Restore" onclick="ed(lastn)"> <input class="btn btn-error" type="button" value="Close" onclick="f_b.reset(); hidediv('ndiv');">
		</div>
	</td>
</tr>
</table>

</form>
</div></div></div>
</div>
<!-- end vendor -->


<!-- start payment vocher maintenance div -->
<div class="ndiv" id="vvc_div" style="position:absolute;left:250;top:100;display:none;">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor_index.vvc.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
<!-- end of div -->

<!-- start vendor block branch -->
<div class="ndiv" id="vbb_div" style="position:absolute;left:250;top:100;display:none;">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor_index.vbb.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>
<!-- end vendor block branch-->


<!-- start vendor TRADE_DISCOUNT table -->
<div class="ndiv" id="ddiv" style="position:absolute;left:180;top:150;display:none;">
<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ddiv'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_d target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="ad">
<input type=hidden name=vendor_id value="">
<b>Trade Discount Table for <span id=td_name>#</span></b><br>
<br>
<b>Select Department</b> <select name=department_id onchange="showtd(vendor_id.value)">
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['department']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<option value=<?php echo $this->_tpl_vars['department'][$this->_sections['i']['index']]['id']; ?>
><?php echo $this->_tpl_vars['department'][$this->_sections['i']['index']]['description']; ?>
</option>
<?php endfor; endif; ?>
</select>
<br>

<?php unset($this->_sections['b']);
$this->_sections['b']['name'] = 'b';
$this->_sections['b']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['b']['show'] = true;
$this->_sections['b']['max'] = $this->_sections['b']['loop'];
$this->_sections['b']['step'] = 1;
$this->_sections['b']['start'] = $this->_sections['b']['step'] > 0 ? 0 : $this->_sections['b']['loop']-1;
if ($this->_sections['b']['show']) {
    $this->_sections['b']['total'] = $this->_sections['b']['loop'];
    if ($this->_sections['b']['total'] == 0)
        $this->_sections['b']['show'] = false;
} else
    $this->_sections['b']['total'] = 0;
if ($this->_sections['b']['show']):

            for ($this->_sections['b']['index'] = $this->_sections['b']['start'], $this->_sections['b']['iteration'] = 1;
                 $this->_sections['b']['iteration'] <= $this->_sections['b']['total'];
                 $this->_sections['b']['index'] += $this->_sections['b']['step'], $this->_sections['b']['iteration']++):
$this->_sections['b']['rownum'] = $this->_sections['b']['iteration'];
$this->_sections['b']['index_prev'] = $this->_sections['b']['index'] - $this->_sections['b']['step'];
$this->_sections['b']['index_next'] = $this->_sections['b']['index'] + $this->_sections['b']['step'];
$this->_sections['b']['first']      = ($this->_sections['b']['iteration'] == 1);
$this->_sections['b']['last']       = ($this->_sections['b']['iteration'] == $this->_sections['b']['total']);
?>
<h4><?php echo $this->_tpl_vars['branches'][$this->_sections['b']['index']]['code']; ?>
</h4>
<table class=small>
<tr>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['skutype']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<td><?php echo $this->_tpl_vars['skutype'][$this->_sections['i']['index']]['code']; ?>
</td>
<?php endfor; endif; ?>
</tr>
<tr>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['skutype']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<td><input size=5 name="commission[<?php echo $this->_tpl_vars['skutype'][$this->_sections['i']['index']]['code']; ?>
][<?php echo $this->_tpl_vars['branches'][$this->_sections['b']['index']]['id']; ?>
]" <?php if (! $this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>disabled<?php endif; ?>></td>
<?php endfor; endif; ?>
</tr>
</table>
<?php endfor; endif; ?>

<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_BRAND'] && $this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
<p>
	<input type="checkbox" name="force_update" value="1" /> <b>Force update SKU cost.</b><br />
</p>
<?php endif; ?>

<p align=center>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>
<input type=submit value="Save">
<?php endif; ?>
<input type=button value="Close" onclick="f_d.reset(); hidediv('ddiv');">
</p>

</form>
</div></div></div>
</div>

<!-- start alt-contact -->
<div class="ndiv" id="adiv" style="position:absolute;left:180;top:150;display:none;">
<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('adiv'))" accesskey="X"><img src=ui/closewin.png border=0 align=absmiddle></a><br>Close (Alt+X)</div>

<form method=post name=f_t target=_irs onSubmit="return check_t()">
<div id=vmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="av">
<input type=hidden name=vendor_id value="">
<input type=hidden name=_branch_id value="">
<table id="tb"  border=0>
<tr>
<td><b>Branch<font color="red" size="+1">*</font></b></td>
<td colspan=3>
<select name=branch_id onchange="loadalt(this.value)">
<option value=0 selected>Select Branch...</option>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<option value="<?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['id']; ?>
"><?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['code']; ?>
 - <?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['description']; ?>
</option>
<?php endfor; endif; ?>
</select>
</tr><tr>
<td><b>GRR without PO</b></td>
<td><select name=allow_grr_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
<?php if ($this->_tpl_vars['config']['use_grn_future']): ?>
<td><b>GRN items without PO</b></td>
<td><select name=allow_grn_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
<?php endif; ?>
<tr>
<td><b>Term</b></td>
<td><input name=term size=4></td>
</tr>

<td><b>Credit Limit<font color="red" size="+1">*</font></b></td>
<td><input name=credit_limit size=10></td>
<td><b>Bank Account<font color="red" size="+1">*</font></b></td>
<td><input name=bank_account size=20></td>
</tr><tr>
<td valign=top><b>Address<font color="red" size="+1">*</font></b></td>
<td colspan=3><textarea name=address rows=5 cols=50></textarea></td>
</tr><tr>
<td valign=top><b>Phone #1<font color="red" size="+1">*</font></b></td>
<td colspan=3><input name=phone_1 size=20></td>
</tr><tr>
<td valign=top><b>Phone #2</b></td>
<td colspan=3><input name=phone_2 size=20></td>
</tr><tr>
<td valign=top><b>Fax</b></td>
<td colspan=3><input name=phone_3 size=20></td>
</tr><tr>
<td valign=top><b>Contact Person<font color="red" size="+1">*</font></b></td>
<td colspan=3><input onBlur="uc(this)" name=contact_person size=20></td>
</tr><tr>
<td valign=top><b>Contact Email<font color="red" size="+1">*</font></b></td>
<td colspan=3><input onBlur="lc(this)" name=contact_email size=20></td>
</tr>
<?php if ($this->_tpl_vars['config']['enable_vendor_account_id']): ?>
	<tr>
	<td valign=top><b>Account ID</b></td>
	<td colspan=3><input name=account_id size=20></td>
	</tr>
<?php endif; ?>
<tr>
<td colspan=4 align=center>
<br>
<input type=submit value="Save"> <input type=button value="Close" onclick="f_t.reset(); hidediv('adiv'); changed_input_tracker = document.f_b.changed_fields;">
</td></tr>
</table>
</form>
</div></div></div>
</div>
<!-- end alt-contact -->

<div style="display:none;"><iframe name=_irs width=500 height=400 frameborder=1 onload="gst_info_onload();"></iframe></div>

<script>
init_chg(document.f_b);
dont_track = '|branch_id|';
init_chg(document.f_t);
new Draggable('ndiv');
new Draggable('adiv');
new Draggable('ddiv');
new Draggable('vvc_div');
new Draggable('vbb_div');
</script>

<script type="text/javascript">
	VENDOR_PORTAL_POPUP.initialize();
	
	
	<?php if ($this->_tpl_vars['config']['enable_gst']): ?>
		<?php echo '
			Calendar.setup({
				inputField     :    "gst_start_date",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "t_added2",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true
			});
		'; ?>

	<?php endif; ?>
</script>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>