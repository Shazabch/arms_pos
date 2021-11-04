{*
REVISION HISTORY
================

1/8/2008 3:00:13 PM gary
- add open vendor's grn summary.

1/8/2008 4:17:08 PM gary
- add term column in branch_vendor.

6/17/2010 3:09:38 PM alex
- add vendor block branch

9/20/2010 5:31:43 PM Andy
- Add link to view vendor discont table.

10/19/2010 11:01:49 AM Justin
- Added a new option field to allow/disallow vendor accept item without PO.

5/23/2011 5:05:22 PM Justin 
- Added export as CSV format feature (all vendors with no filter).

9/5/2011 5:21:49 PM Andy
- Add "Enable Stock Reorder Notify" checkbox at vendor masterfile.

12/13/2011 12:59:32 PM Andy
- Add checkbox to allow force update to all SKU cost when change discount rate.

12/16/2011 3:30:54 PM Justin
- Added sort by header feature when reload table.

4/30/2012 4:59:02 PM Andy
- Add can filter by "All".
- Add show loading process icon when reload vendor list.

7/5/2012 1:33 PM Andy
- Add can generate vendor portal key for vendor at vendor master file.

7/16/2012 5:09 PM Andy
- Enlarge Vendor Portal Popup size.

7/17/2012 12:08 PM Justin
- Added new field "Account ID" and available when config "enable_vendor_account_id" is on.

9/11/2012 3:24 PM Andy
- Enhance vendor login ticket,email, link to debtor to saved by branch.

2/7/2013 4:02 PM Justin
- Changed the name "GRN without PO" into "GRN items without PO".

4/12/2013 10:22 AM Justin
- Added Allow GRN items qty not allow PO qty field.

5/8/2013 4:03 PM Justin
- Enhanced to adjust the sequence of GRN items qty not allow PO qty drop down list to prevent user save it as "Yes" as default while create new vendor.

5/22/2013 11:51 AM Justin
- Added Allow PO without checkout GRA field.

6/7/2013 3:58 PM Justin
- Added the missing checking for Branch.
- Added * mark for all compulsory fields.

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

12/23/2013 5:02 PM Justin
- Bug fixed on field changes capture for log is not working properly while having more than 1 form.

8/21/2014 4:03 PM Justin
- Enhanced to have GST Type, Registration Number & Start Date.

10/9/2014 4:36 PM Justin
- Enhanced to change the "-" for GST Type into "No GST".

10/28/2014 1:40 PM Justin
- Enhanced to have validation for GST Type, Registration Number & Start Date (exceipt tax code "NR").

10/30/2014 10:45 AM Justin
- Enhanced to take out GST Type and replace with "GST Registered" checkbox.

12/16/2014 12:08 PM Justin
- Bug fixed on GST info did not check with config.

1/24/2015 1:05 PM Justin
- Enhanced the "GST Registered" into drop down list.

3/4/2015 2:51 PM Andy
- Remove "Account Receivable Code" and "Account Receivable Name".

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

22/9/2016 1:44 PM Kee Kee
- change "Account Payable" to "Purchase"

27/9/2016 4:55 PM Kee Kee
- change "Purchase" to "Purchase Account"

12/1/2016 11:46 AM Andy
- Hide GST Reg No and start date when add new vendor.
- Add Internal Code. (Use for GRR for IBT DO Checking)

3/28/2017 10:54 AM Justin
- Enhanced to add new feature "Import Payment Voucher Code by CSV".

9:53 AM 3/30/2017 justin
- Added config checking "payment_voucher_vendor_maintenance" for "Import Payment Voucher Code by CSV".

4/14/2017 9:17 AM Justin
- Modified the "Import Payment Voucher Code by CSV" become "Import / Export Payment Voucher Code by CSV".

4/14/2017 3:36 PM Justin
- Bug fixed on the link "Import / Export Payment Voucher Code by CSV" suppose can only be accessed from HQ.

8/27/2018 3:05 PM Andy
- Add SST feature.

12/17/2019 4:02 PM William
- Enhanced to change the max length of "company No" to 30.

06/26/20 3:50 PM Sheila
- Updated button css

01/5/2021 4:44 PM Rayleen
- Add new field "Delivery Type"
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
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
{/literal}

<script type="text/javascript">
var lastn = '';
var phpself = '{$smarty.server.PHP_SELF}';
var enable_gst = '{$config.enable_gst}';
var default_tax_percent = float('{$config.arms_tax_settings.percent}');

{literal}
function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	changesel(document.f_b.vendortype_code, document.f_b._vendortype_code.value);
	document.f_b.changed_fields.value = '';
	document.f_b.code.focus();
	check_tax_registered();
}

function ed(n)
{
	document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	document.f_b['enable_stock_reoder_notify'].checked = false;
	if(enable_gst){
		document.f_b['gst_register'].value = 0;
		$('tr_gst_info').style.display = "none";
	}
	
	showdiv('ndiv');
	showdiv('tr_alt');
	document.f_b.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;
	document.f_b.a.value = 'u';
	document.f_b.code.focus();
	changed_input_tracker = document.f_b['changed_fields'];
}

function add()
{
	showdiv('ndiv');
	hidediv('tr_alt');
	document.getElementById('abtn').style.display = '';
	document.getElementById('ebtn').style.display = 'none';
	document.getElementById('bmsg').innerHTML = 'Enter the following and click ADD';
	document.f_b.reset();
	document.f_b.id.value = 0;
	document.f_b.a.value = 'a';
	document.f_b.code.focus();
	if(enable_gst){
		$('tr_gst_info').style.display = "none";
	}
	check_tax_registered();
}

function showtd(id,n)
{
	var did = document.f_d.department_id.value;
	document.getElementById('tmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('ddiv')
	if (n != undefined) $('td_name').innerHTML = n;
	document.f_d.reset();
	_irs.document.location = '?a=load_td&id='+id+'&department_id='+did;
}
var g_id;
function show_vvc(id,n)
{
	g_id=id;
	//alert(g_id);
	$('vendor_id').value=id;
	$('vendor').value=n;
	showdiv('vvc_div');	
	
	new Ajax.Updater("vvc_div", "masterfile_vendor.php",{
	    parameters:  'a=load_vvc&vendor_id='+id,
	    evalScripts: true
	});
}

function vvc_keyin(){
	hidediv('vvc_div');
	document.f_u.vendor_id.value=g_id;
	document.f_u.submit();
}

function show_vbb(id,n)
{
	g_id=id;
	//alert(g_id);
	$('vendor_id').value=id;
	$('vendor').value=n;
	showdiv('vbb_div');

	new Ajax.Updater("vbb_div", "masterfile_vendor.php",{
	    parameters:  'a=load_vbb&vendor_id='+id,
	    evalScripts: true
	});
}

function vbb_keyin(){
	hidediv('vbb_div');
	document.f_v.vendor_id.value=g_id;
	document.f_v.submit();
}

function tdloaded()
{
	document.getElementById('tmsg').innerHTML = '';
}

function showalt()
{
	if (document.f_b.id.value!='' && document.f_b.id.value>0)
	{
		changed_input_tracker = document.f_t['changed_fields'];
		document.f_t.vendor_id.value = document.f_b.id.value;
		document.f_t.branch_id.options[0].selected = true;
		showdiv('adiv');
	}
}

function vloaded()
{
	document.f_t.changed_fields.value = '';
	if (document.f_t._branch_id.value == '')
	{
		document.f_t.vendor_id.value = document.f_b.id.value;
		document.getElementById('vmsg').innerHTML = 'This Branch currently does not have an alternate vendor contact';
	}
	document.f_t.branch_id.focus();
}

function loadalt(id)
{
	vid = document.f_t.vendor_id.value;
	document.f_t.reset();
	document.f_t.vendor_id.value = vid;
	document.getElementById('vmsg').innerHTML = '';
	if (id == 0) return;

	changesel(document.f_t.branch_id, id);
	_irs.document.location = '?a=lv&vid='+vid+'&id='+id;
}

function act(n, s)
{
	_irs.document.location = '?a=v&id='+n+'&v='+s;
}

function check_b()
{
	if (check_login()) {
        if (empty(document.f_b.code, 'You must enter Code'))
        {
            return false;
        }
        if (empty(document.f_b.description, 'You must enter Description'))
        {
            return false;
        }
        if (empty(document.f_b.company_no, 'You must enter Company No'))
        {
            return false;
        }
        if (empty(document.f_b.term, 'You must enter Term'))
        {
            return false;
        }
        if (empty(document.f_b.credit_limit, 'You must enter Credit Limit'))
        {
            return false;
        }
        if (empty(document.f_b.bank_account, 'You must enter Bank Account'))
        {
            return false;
        }
        if (empty(document.f_b.address, 'You must enter Address'))
        {
            return false;
        }
        if (empty(document.f_b.phone_1, 'You must enter at least Phone #1'))
        {
            return false;
        }
        if (empty(document.f_b.contact_person, 'You must enter Contact Person'))
        {
            return false;
        }

        /*
        if (empty(document.f_b.contact_email, 'You must enter Contact Email'))
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
	changed_input_tracker = document.f_t['changed_fields'];
	if (document.f_t.branch_id.value == 0)
	{
		alert('You select a Branch');
		return false;
	}
	if (empty(document.f_t.credit_limit, 'You must enter Credit Limit'))
	{
		return false;
	}
	if (empty(document.f_t.bank_account, 'You must enter Bank Account'))
	{
		return false;
	}
	if (empty(document.f_t.address, 'You must enter Address'))
	{
		return false;
	}
	if (empty(document.f_t.phone_1, 'You must enter at least Phone #1'))
	{
		return false;
	}
	if (empty(document.f_t.contact_person, 'You must enter Contact Person'))
	{
		return false;
	}
	if (empty(document.f_t.contact_email, 'You must enter Contact Email'))
	{
		return false;
	}
	return true;
}

function reload_table(load_page)
{
	if (load_page) lp = '&pg='+$('pg').value;
	else lp = '';
	params = 'a=ajax_reload_table&'+Form.serialize($('search_form'))+lp;
	//alert(params);return;
	$('span_loading_vendor_list').show();
	new Ajax.Updater("udiv", "masterfile_vendor.php",{
	    parameters: params,
	    evalScripts: true,
		onComplete: function(m){
			ts_makeSortable($('vendor_tbl'));
		}
	});
	return false;
}

function do_find(f,obj)
{
	uc(obj);
	var v = new String(obj.value);
	if (v=='')
	{
		alert('Search field is empty');
		obj.focus();
		return;
	}
	new Ajax.Updater("udiv", "masterfile_vendor.php",{
	    parameters: 'a=ajax_reload_table&search='+f+'&'+Form.Element.serialize(obj),
	    evalScripts: true
	});
}

function open_grn(vendor_id){
	window.open('goods_receiving_note.summary.php?vendor_id='+vendor_id);
}


var VENDOR_PORTAL_POPUP = {
	f_vp: undefined,
	is_updating: false,
	initialize: function(){
		new Draggable('div_vendor_portal',{ handle: 'div_vendor_portal_header'});	
	},
	// function to close popup
	close: function(){
		if(this.is_updating){
			alert('Updating is running, please wait for it to finish.');
			return false;
		}
		
		curtain(false, 'curtain2');
		default_curtain_clicked();
	},
	// function to show popup with vendor portal information
	open: function(vid){
		var THIS = this;
		
		$('div_vendor_portal_content').update(_loading_);
		
		curtain(true, 'curtain2');
		center_div($('div_vendor_portal').show());
		
		var params = {
			vid: vid
		};
		new Ajax.Request(phpself+'?a=ajax_load_vendor_portal_info',{
			parameters: params,
			method:'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_vendor_portal_content').update(ret['html']);
						THIS.initial_f_vp();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			    $('div_vendor_portal_content').update('');
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
		var c = $('inp_toggle_all_allowed_branches').checked;
		
		$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
			inp.checked = c;
		});	
	},
	// function when user toggle no expire checkbox
	toggle_no_expire: function(bid){
		var c = $('inp_no_expire-'+bid).checked;
		
		if(c){
			$('span_expire_date-'+bid).hide();
		}else{
			$('span_expire_date-'+bid).show();
		}
	},
	// function when user change active status
	active_vendor_portal_changed: function(){
		var active = int(getRadioValue(this.f_vp['active_vendor_portal']));
		
		var span_active_vendor_portal_msg = $('span_active_vendor_portal_msg');
		if(active){
			$(span_active_vendor_portal_msg).hide();
		}else{
			$(span_active_vendor_portal_msg).show();
		}
	},
	// function when user click generate ticket
	generate_ticket_clicked: function(bid){
		var ticket = this.f_vp['login_ticket['+bid+']'].value;
		
		if(ticket){
			this.clear_ticket(bid);
		}else{
			this.generate_ticket(bid);
		}
	},
	// function to clear ticket
	clear_ticket: function(bid){
		this.f_vp['login_ticket['+bid+']'].value = '';
		$('btn_generate_ticket-'+bid).value = 'Generate';
		
		$('span_clone_ticket-'+bid).hide();
	},
	// function to generate new ticket
	generate_ticket: function(bid, use_this_ticket){
		var alpha_list = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
		var num_list = [0,1,2,3,4,5,6,7,8,9];
		var rand_char = alpha_list.concat(num_list, num_list, num_list);
		var ticket_length = 10;
		var ticket = '';
		
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
		this.f_vp['login_ticket['+bid+']'].value = ticket;
		$('btn_generate_ticket-'+bid).value = 'Clear';
		
		$('span_clone_ticket-'+bid).show();
	},
	// function to validate form 
	check_form: function(){
		/*if(this.f_vp['login_ticket'].value != ''){	// got ticket
			var checked_count = 0;
			$(this.f_vp).getElementsBySelector("input.inp_allowed_branches").each(function(inp){
				if(inp.checked)	checked_count++;
			});
			if(checked_count<=0){
				alert('Please tick at least 1 allowed branch.');
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
				if(!this.f_vp['sku_group_info['+bid+']'].value){
					alert('Please select SKU Group for all ticked branches.');
					return false;
				}
				
				// login ticket
				if(!this.f_vp['login_ticket['+bid+']'].value){	// found no give login ticket
					alert('Please generate ticket for all ticked branches.');
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
		$$('#p_action_button input').invoke('disable');
		$('btn_update_vendor_portal').value = 'Updating…';
		
		this.is_updating = true;
		
		var params = $(this.f_vp).serialize();
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    THIS.is_updating = false;
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Update Successfully');
						THIS.close();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			    $$('#p_action_button input').invoke('enable');
			    $('btn_update_vendor_portal').value = 'Update';
			}
		});
	},
	// function to clone ticket for all branches
	clone_ticket: function(bid){
		var ticket = this.f_vp['login_ticket['+bid+']'].value.trim();
		if(!ticket)	return false;	// no ticket to clone
		
		if(!confirm('Are you sure to clone this ticket to all allowed branches?'))	return false;
		
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
	if(document.f_b['gst_register'] != undefined){
		// means this vendor is using GST and info is required
		if(document.f_b['gst_register'].value == -1) $('tr_gst_info').show();
		else $('tr_gst_info').hide();
	}
}

function tax_registered_changed(){
	var tax_register = int(document.f_b['tax_register'].value);
	if(tax_register>0){	// when change from No to Yes, put default tax percent if the percent is 0
		var tax_percent = float(document.f_b['tax_percent'].value);
		if(tax_percent<=0){
			document.f_b['tax_percent'].value = default_tax_percent;
		}
	}
	check_tax_registered();
}

function check_tax_registered(){
	var tax_register = int(document.f_b['tax_register'].value);
	
	$$("td.td_tax_details").each(function(inp){
		if(tax_register>0){
			$(inp).show();
		}else{
			$(inp).hide();
		}
	});
}

function tax_percent_changed(){
	var inp = document.f_b['tax_percent'];
	inp.value = float(inp.value);
	if(inp.value<=0)	inp.value = 0;
}

</script>
{/literal}

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
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Vendor Master File</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			{if $sessioninfo.privilege.MST_VENDOR}
				<li><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Vendor</a> (Alt+A)</li>
			{/if}
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
			{if $config.payment_voucher_vendor_maintenance && BRANCH_CODE eq "HQ"}
				<li>
					<a href="masterfile_vendor.import_pymt_vch.php" target="_blank">
						<img src="ui/new.png" title="Enter Report" align="absmiddle" border="0" />
						Import / Export Payment Voucher Code by CSV
					</a>
				</li>
			{/if}
		</ul>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="search_form" id="search_form" onsubmit="return reload_table()">
			<p>
			<div class="row">
				
			<div class="col">
				<b class="form-label">Description :</b>
				<input class="form-control" type="text" name="desc" size="15" />
			</div>
				
			<div class="col">
				<b class="form-label">Status :</b>
				<select class="form-control" name="status">
					<option value="">All</option>
					<option value="1">Active</option>
					<option value="0">Inactive</option>
				</select>
			</div>
			
			<div class="col">
				<b class="form-label">Starts With :</b>
				<select class="form-control" name="starts_with">
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
			</div>
			<div class="col">
				<input type="button" class="btn btn-primary mt-4" value="Search" onclick="reload_table()" />
			</div>
			</div>
			</p>
			</form>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		{include file=masterfile_vendor_table.tpl}
	</div>
</div>

<br>

<!-- start vendor -->
<div class="ndiv" id="ndiv" style="background-color: white;  position:absolute;left:350;top:150;display:none;">
<div class="blur"><div class="shadow"><div class="content" style="margin: 20px;">

<div class="small mt-2 ml-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<form method=post name=f_b target=_irs onSubmit="return check_b()">
<div id=bmsg class="mt-2 mr-2" style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="a">
<input type=hidden name=id value="">
<input type=hidden name=_vendortype_code value="">
<table id="tb"  border=0>
<tr>
<td><b class="form-label">Vendor Code<font color="red" size="+1">*</font></b></td>
<td {if !$config.ci_auto_gen_artno} colspan=3 {/if} >
	<input class="form-control" onBlur="uc(this)" name=code size=10 maxlength=10></td>
{if $config.ci_auto_gen_artno}
	<td><b class="form-label">Prefix Code</b></td>
	<td><input class="form-control" onBlur="uc(this)" name=prefix_code size=2 maxlength=1></td>
{/if}
</tr><tr>
<tr>
	<td><b class="form-label">Internal Code [<a href="javascript:void(alert('Currently use for\n- Auto search by GRR for IBT DO.'));">?</a>]</b></td>
	<td><input class="form-control" onBlur="uc(this)" name="internal_code"  /></td>
</tr>
<td><b class="form-label">Vendor Name<font color="red" size="+1">*</font></b></td>
<td colspan=3><input class="form-control" onBlur="uc(this)" name=description size=50></td>
</tr><tr>
<td><b class="form-label">Company No.<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" name=company_no size=20 maxlength=30></td>
<td><b class="form-label">Vendor Type</b></td>
<td><select class="form-control" name=vendortype_code>
{section name=i loop=$vendortype}
<option value="{$vendortype[i].code}">{$vendortype[i].code} - {$vendortype[i].description}</option>
{/section}
</select></td>
</tr><tr>
<td><b class="form-label">GRR without PO</b></td>
<td><select class="form-control" name=allow_grr_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
{if $config.use_grn_future}
<td><b class="form-label">GRN items without PO</b></td>
<td><select class="form-control" name=allow_grn_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
</tr>
{else}
	<td colspan="2">&nbsp;</td>
	</tr>
{/if}
<tr>
<td><b class="form-label">PO without Checkout GRA</b></td>
<td><select class="form-control" name=allow_po_without_checkout_gra><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
{if $config.use_grn_future}
<td><b class="form-label">GRN items qty not allow <br />to over PO qty</b></td>
<td><select class="form-control" name=grn_qty_no_over_po_qty><option value=0>No</option><option value=1>Yes</option></select></td>
{else}
	<td colspan="2">&nbsp;</td>
	</tr>
{/if}
</tr><tr>
<td><b class="form-label">Term<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" name=term size=4></td>
<td><b class="form-label">Grace Period</b></td>
<td><input class="form-control" name=grace_period size=4></td>
</tr><tr>
<td><b class="form-label">Prompt Payment</b></td>
	<td colspan=3>
		<div class="form-inline">
			Term &nbsp;
		<input class="form-control" name=prompt_payment_term >&nbsp; Discount (%) &nbsp; 
		<input class="form-control" name=prompt_payment_discount >
		</div>
	</td>
</tr><tr>
<td><b class="form-label">Fast Payment</b></td>
<td colspan=3>
	<div class="form-inline">
		Term &nbsp;
	<input class="form-control" name=fast_payment_term >&nbsp; Discount (%) &nbsp; 
	<input class="form-control" name=fast_payment_discount >
	</div>
</td>
</tr><tr id="tr_alt" style="display:none">
<td colspan=4><br>
Below is the default contact information for this vendor.<br>
To create alternate contact for branch, <a href="javascript:void(showalt())">click here &#187;</a>.

</tr><tr>
<td><b class="form-label">Credit Limit<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" name=credit_limit size=10></td>
<td><b class="form-label">Bank Account<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" name=bank_account size=20></td>
</tr><tr>
<td ><b class="form-label">Address<font color="red" size="+1">*</font></b></td>
<td colspan=3><textarea class="form-control" name=address rows=5 cols=50></textarea></td>
</tr><tr>
<td><b class="form-label">Phone #1<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" name=phone_1 size=20></td>
<td><b class="form-label">Phone #2</b></td>
<td><input class="form-control" name=phone_2 size=20></td>
</tr><tr>
<td><b class="form-label">Fax No.</b></td>
<td colspan=3><input class="form-control" name=phone_3 size=20></td>
</tr><tr>
<td><b class="form-label">Contact Person<font color="red" size="+1">*</font></b></td>
<td><input class="form-control" onBlur="uc(this)" name=contact_person size=20></td>
<td><b class="form-label">Contact Email{*<font color="red" size="+1">*</font>*}</b></td>
<td><input class="form-control" onBlur="lc(this)" name=contact_email size=20></td>
</tr><tr>
	<td><b class="form-label">Enable Stock Reorder Notify</b></td>
	<td><input type="checkbox" name="enable_stock_reoder_notify" value="1" />
</tr>
{if $config.enable_vendor_account_id}
	<tr>
	<td><b class="form-label">Account ID</b></td>
	<td colspan="3"><input class="form-control" name=account_id size=20></td>
	</tr>
{/if}
<!--tr>
	<td valign=top><b>GST Type</b></td>
	<td colspan="3">
		<select name="gst_type" style="width:400;">
			<option value="">No GST</option>
			{foreach from=$gst_list item=r}
				<option value="{$r.id}">{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr-->

{if $config.enable_gst}
	<tr>
		<td><b class="form-label">GST Registered</b></td>
		<!--td><input type="checkbox" name="gst_register" value="1" /></td-->
		<td colspan="3">
			<select class="form-control" name="gst_register" onchange="gst_info_onload();">
				<option value="0">No</option>
				<option value="-1">Yes</option>
				{foreach from=$gst_list key=dummy item=r}
					<option value="{$r.id}">{$r.code} - {$r.description}</option>
				{/foreach}
			</select>
		</td>
	</tr>

	<tr id="tr_gst_info">
		<td><b class="form-label">GST Registration Number<font color="red" size="+1">*</font></b></td>
		<td><input class="form-control" name="gst_register_no" size="20" value="" /></td>
		<td><b class="form-label">GST Start Date<font color="red" size="+1">*</font></b></td>
		<td>
			<div class="form-inline">
				<input class="form-control" size="10" type="text" name="gst_start_date" value="" id="gst_start_date">
			<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select GST Start Date">
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<fieldset>
				<legend><b>GL Code</b></legend>
				<table>
					<tr>
						<td><b class="form-label">Purchase Account Code</b></td>
						<td><input class="form-control" type="text" name="account_payable_code" value=""/></td>
						<td><b class="form-label">Purchase Account Name</b></td>
						<td><input class="form-control" type="text" name="account_payable_name" value=""/></td>
					</tr>
				</table>

			</fieldset>
		</td>
	</tr>
{/if}

<tr>
	<td><b class="form-label">{$config.arms_tax_settings.name} Tax Registered</b></td>
	<td>
		<select class="form-control" name="tax_register" onchange="tax_registered_changed();">
			<option value="0">No</option>
			<option value="1">Yes</option>
		</select>
	</td>
	<td class="td_tax_details"><b class="form-label">Tax Percentage</b></td>
	<td class="td_tax_details">
		<input class="form-control" type="text" name="tax_percent" onChange="tax_percent_changed();" />
	</td>
</tr>
<tr>
	<td><b class="form-label">Delivery Type</b></td>
	<td colspan="3">
		<select class="form-control" name="delivery_type">
			<option value="">Warehouse</option>
			<option value="D">Direct</option>
		</select>
	</td>
</tr>
<tr>
	<td align="center" colspan="4">
		<br>
		<div id="abtn" style="display:none;">
			<input class="btn btn-warning mb-2" type="submit" value="Add">
			 <input type="button" class="btn btn-danger mb-2" value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
		</div>
		<div id="ebtn" style="display:none;">
			<input class="btn btn-primary mb-2" type="submit" value="Update">
			 <input class="btn btn-warning mb-2 "type="button" value="Restore" onclick="ed(lastn)">
			 <input class="btn btn-danger mb-2" type="button" value="Close" onclick="f_b.reset(); hidediv('ndiv');">
		</div>
	</td>
</tr>
</table>

</form>
</div></div></div>
</div>
<!-- end vendor -->


<!-- start payment vocher maintenance div -->
<div class="ndiv" id="vvc_div" style="position:absolute;left:540;top:100;display:none;background-color: white;">
{include file=masterfile_vendor_index.vvc.tpl}
</div>
<!-- end of div -->

<!-- start vendor block branch -->
<div class="ndiv" id="vbb_div" style="position:absolute;left:540;top:100;display:none;background-color: white;">
{include file=masterfile_vendor_index.vbb.tpl}
</div>
<!-- end vendor block branch-->


<!-- start vendor TRADE_DISCOUNT table -->
<div class="ndiv" id="ddiv" style="position:absolute;left:480;top:350px;;display:none;background-color: white;">
<div class="blur"><div class="shadow"><div class="content" style="margin: 20px;">

<div class="small mt-2 mr-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ddiv'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_d target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="ad">
<input type=hidden name=vendor_id value="">
<b>Trade Discount Table for <span id=td_name>#</span></b><br>
<br>
<b class="form-label">Select Department</b> <select class="form-control" name=department_id onchange="showtd(vendor_id.value)">
{section name=i loop=$department}
<option value={$department[i].id}>{$department[i].description}</option>
{/section}
</select>
<br>

{section name=b loop=$branches}
<h4>{$branches[b].code}</h4>
<table class=small>
<tr>
{section name=i loop=$skutype}
<td>{$skutype[i].code}</td>
{/section}
</tr>
<tr>
{section name=i loop=$skutype}
<td><input class="form-control" size=5 name="commission[{$skutype[i].code}][{$branches[b].id}]" {if !$sessioninfo.privilege.MST_VENDOR}disabled{/if}></td>
{/section}
</tr>
</table>
{/section}

{if $sessioninfo.privilege.MST_BRAND and $sessioninfo.level>=9999}
<p>
	<input type="checkbox" name="force_update" value="1" /> <b>Force update SKU cost.</b><br />
</p>
{/if}

<p align=center>
{if $sessioninfo.privilege.MST_VENDOR}
<input type=submit class="btn btn-primary mb-2" value="Save">
{/if}
<input type=button class="btn btn-danger mb-2" value="Close" onclick="f_d.reset(); hidediv('ddiv');">
</p>

</form>
</div></div></div>
</div>

<!-- start alt-contact -->
<div class="ndiv" id="adiv" style="position:absolute;left:180;top:150;display:none;background-color: white;">
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
{section name=i loop=$branches}
<option value="{$branches[i].id}">{$branches[i].code} - {$branches[i].description}</option>
{/section}
</select>
</tr><tr>
<td><b>GRR without PO</b></td>
<td><select name=allow_grr_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
{if $config.use_grn_future}
<td><b>GRN items without PO</b></td>
<td><select name=allow_grn_without_po><option value=1>Allowed</option><option value=0>Not Allowed</option></select></td>
{/if}
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
{if $config.enable_vendor_account_id}
	<tr>
	<td valign=top><b>Account ID</b></td>
	<td colspan=3><input name=account_id size=20></td>
	</tr>
{/if}
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
	
	
	{if $config.enable_gst}
		{literal}
			Calendar.setup({
				inputField     :    "gst_start_date",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "t_added2",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true
			});
		{/literal}
	{/if}
</script>
{include file=footer.tpl}
