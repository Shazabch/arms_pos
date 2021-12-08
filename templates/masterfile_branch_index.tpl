{*
6/9/2008 12:34:58 PM yinsee
- allow add branch for user-id=1
- add report prefix

8/4/2009 1:00:33 PM Andy
- add "Allow edit selling price in consignment invoice" checkbox

4/22/2010 6:13:28 PM Andy
- Add print envelope

4/30/2010 12:32:29 PM Andy
- Make system admin able to add branch if using consignment modules

11/11/2010 2:43:16 PM Alex
- add print branches

12/30/2010 4:11:07 PM Andy
- Add counter limit at branch masterfile, only can change by wsatp.

4/11/2011 3:32:05 PM Justin
- Added new field "Region" from config.

5/18/2011 5:34:12 PM Andy
- Change region array structure and its related module.

5/27/2011 5:04:11 PM Justin
- Added new field "deliver_to".

5/31/2011 4:14:50 PM Andy
- Make HQ cannot belongs to any region.
- Add update all sku selling price if branch change region.

6/22/2011 2:40:36 PM Andy
- Add show red color for those in-active branch in resort branch sequence.
- Branch sequences printing add can skip in-active branch.
- Fix printing branch sequence cannot follow the position changed by user.

7/8/2011 3:30:35 PM Andy
- Change branch master file 'address' and 'deliver to' placement.
- Add can select transporter and branch master file.

9/7/2011 4:27:03 PM Alex
- remove branch receipt header n footer

3/26/2012 3:02:59 PM Andy
- Reconstruct module structure to use ajax update instead of IRS.

3/29/2012 9:58:38 AM Andy
- Fix popup location base on user scroll.

5/16/2012 4:00:34 PM Justin
- Added to pickup calendar setup while in consignment mode.
- Added new option "Transaction End Date" and appear while in consignment mode.

6/15/2012 3:26:34 PM Justin
- Added new JS function to hide/show Debtor option field.

8/30/2012 4:02 PM Andy
- Fix div centralize problem.

9/25/2012 5:28 PM Andy
- Fix edit popup position.

5/21/2013 11:55 AM Justin
- Enhanced to have new icon that represents Copy Settings by Branch.
- Enhanced to allow only Admin to have this function.

10/1/2013 6:01 PM Fithri
- make all email field not compulsary
- when sending email, check to trigger send function only when email is set

8/8/2014 12:08 PM Justin
- Enhanced to have GST Registration & Start Date.

1/17/2015 9:57 AM  Andy
- Add GST Interbranch Settings.

3/5/2015 3:51 PM Andy
- Fix popup header cannot see and drag when there is too many branches.

4/13/2015 3:48 PM Andy
- Fix if no key in GST Registration Number, the GST start date will always empty.

5/25/2015 4:03 PM Justin
- Enhanced to allow user to control if wants to change price when change region.

6/5/2015 9:45 AM Justin
- Bug fixed on GST start date cannot choose while click on add branch.

7/24/2015 11:02 AM Joo Chia
- Enhance to allow admin to copy branch setting for Trade Discount, Approval Flow, and Block GRN.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

3/24/2017 10:45 AM Justin
- Enhanced system can only accepts JPG/JPEG for logo upload.

3/30/2018 4:13 PM HockLee
- Added new input Integration Code.

6/7/2019 9:44 AM Andy
- Remove Integration Code alpha numeric code checking.

11/22/2019 5:54 PM William
- Add new branch outlet photo to branch.
*}
{include file=header.tpl}

{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/literal}

{literal}
<style>
#tbl_vvc input { 
	font: 10px "MS Sans Serif" normal;
}

.branch_inactive{
	color: red;
}

.td_same_branch{
	background: #cfcfcf;
}
</style>
{/literal}
<script type="text/javascript">
var lastn = '';
var phpself = '{$smarty.server.PHP_SELF}';
var con_modules = '{$config.consignment_modules|default:0}';
var allow_secondary_discount = '{$config.allow_secondary_discount}';
var masterfile_branch_region = '{$config.masterfile_branch_region}';
var sku_use_region_price = '{$config.sku_use_region_price}';
var current_time = '{$smarty.now}';

{literal}
function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	document.f_b.changed_fields.value = '';
	document.f_b.code.focus();
}

function ed(n)
{
	// got use region
	/*if(masterfile_branch_region){
		if(n==1){
			$('tr_region_row').hide();	// HQ cannot have region
		}else{
			$('tr_region_row').show();
		}
		
	}
	
	document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	showdiv('div_branch');
	document.f_b.reset();
	document.f_b.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;

	document.f_b.a.value = 'u';
	document.f_b.code.focus();*/
	
	$('div_branch_content').update(_loading_);
	new Ajax.Updater('div_branch_content', phpself+'?a=ajax_edit_branch', {
		parameters:{
			bid: n
		},
		evalScripts: true,
		onComplete: function(){
			center_div($('div_branch'));	// reposition to center	

			if(int($('div_branch').style.top)<0){
				$('div_branch').style.top = '30px';
			}
		}
	});
	$('div_branch').show();
	$('div_branch').style.top = my + document.body.scrollTop;
}
{/literal}
{if $allow_add_branch}
{literal}
function add()
{
	// got use region
	/*if(masterfile_branch_region){
		$('tr_region_row').show();
	}
	
	showdiv('div_branch');
	document.getElementById('abtn').style.display = '';
	document.getElementById('ebtn').style.display = 'none';
	document.getElementById('bmsg').innerHTML = 'Enter the following and click ADD';
	document.f_b.reset();
	document.f_b.id.value = 0;
	document.f_b.a.value = 'a';
	document.f_b.code.focus();*/
	
	$('div_branch_content').update(_loading_);
	new Ajax.Updater('div_branch_content', phpself+'?a=ajax_add_branch', {
		evalScripts: true,
		onComplete: function(){
			center_div($('div_branch'));
			
			if(int($('div_branch').style.top)<0){
				$('div_branch').style.top = '30px';
			}
		}
	});
	center_div($('div_branch').show());
}
{/literal}
{/if}
{literal}
function act(n, s)
{
	//_irs.document.location = '?a=v&id='+n+'&v='+s;
	new Ajax.Request(phpself, {
		parameters:{
			a:'ajax_activate',
			bid:n,
			v:s
		},
		evalScripts:true,
		onComplete: function(e){
			load_table();	
		}
	});
}

function update_branch(){
	if (check_login()) {
        if(!check_b())	return;

        $$('#div_branch_btn_area input').each(function(inp){
            inp.disabled = true;
        });

        $('div_branch_processing_msg').update(_loading_);
        document.f_b.submit();
    }
	/*
	new Ajax.Request(phpself, {
		method:'post',
		parameters: $(document.f_b).serialize(),
		onComplete: function(e){
			var str = e.responseText.trim();
			
			if(str == 'OK'){
				load_table();
				alert('Update Branch Successfully');
				hidediv('div_branch');
			}else{
				alert(str);
				$$('#div_branch_btn_area input').each(function(inp){
					inp.disabled = false;
				});
				$('div_branch_processing_msg').update('');
			}
		}
	});
	*/
}

function iframe_callback(r)
{
	if (r === true)
	{
		load_table();
		alert('Update Branch Successfully');
		hidediv('div_branch');
	}
	else
	{
		alert(r);
		$$('#div_branch_btn_area input').each(function(inp){
			inp.disabled = false;
		});
		$('div_branch_processing_msg').update('');
	}
}

function check_b()
{
	if (empty(document.f_b.code, 'You must enter Code'))
	{
		return false;
	}
	if (empty(document.f_b.report_prefix, 'You must enter Report Prefix'))
	{
		return false;
	}
	if (empty(document.f_b.description, 'You must enter Description'))
	{
		return false;
	}
	if (empty(document.f_b.company_no, 'You must enter Registration No'))
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
	if(con_modules==1){
        if (empty(document.f_b.con_dept_name, 'You must enter Department Name'))
		{
			return false;
		}
		if (empty(document.f_b.con_terms, 'You must enter Terms'))
		{
			return false;
		}
	}
	
	if(masterfile_branch_region){	// got use region
		if(sku_use_region_price && document.f_b['region']){	// got use region price
			// got edit region
			if(document.f_b['id'].value>0 && document.f_b['region'].value!='' && document.f_b['old_region'].value != document.f_b['region'].value && document.f_b['force_update_rprice'].checked == true){
				if(!confirm('You have change region, all SKU selling price in this branch will update based on new region you choose.')){
					return false;
				
				}
			}
		}
	}

	if(document.f_b['integration_code']){
			var str = document.f_b['integration_code'].value.trim();
			if(str){	// got integration code
				if(str.length > 10){	// min 10 char
					alert('Integration code cannot more than 10 characters');
					document.f_b['integration_code'].focus();
					return false;
				}
				
				/*if(!str.match(/^[a-z0-9]+$/i)){
					alert('Integration code only allow alphabet and number.');
					document.f_b['integration_code'].focus();
					return false;
				}*/
			}
		}

	return true;
}


var g_id;
function show_vvc(id,n){

	g_id=id;
	//alert(g_id);
	$('branch_id').value=id;
	$('branch').value=n;
	showdiv('vvc_div');	
	
	new Ajax.Updater("vvc_div", "masterfile_branch.php",{
	    parameters:  'a=load_vvc&branch_id='+id,
	    evalScripts: true
	});
}

function vvc_keyin(){
	//alert(g_id);
	hidediv('vvc_div');
	document.f_u.branch_id.value=g_id;
	document.f_u.submit();
}

function show_trade_discount(branch_id){
	curtain(true);
	
    $('div_trade_discount').update(_loading_);
	$('div_trade_discount').show();
	center_div('div_trade_discount');
	new Ajax.Updater('div_trade_discount',phpself,{
		parameters: {
			a: 'load_trade_discount',
			branch_id: branch_id
		},
		onComplete: function(e){
            center_div('div_trade_discount');
		},
		evalScripts: true
	});
}

function curtain_clicked(){
	$('div_trade_discount').hide();
	$('div_sort_sequence').hide();
	$('div_cs').hide();
	curtain(false);
}

function sort_sequence(col){
	curtain(true);
	$('div_sort_sequence').show();
	center_div('div_sort_sequence');
	
	var params = {
			a: 'sort_sequence',
			sort_by: col
		};
	if(document.f_sequence){
		params['skip_inactive'] = document.f_sequence['skip_inactive'].checked ? 1 : 0;
	}
	params = $H(params).toQueryString();
	
	$('div_sort_sequence').update(_loading_);
	new Ajax.Updater('div_sort_sequence',phpself,{
		method : 'post',
		parameters: params,
		evalScripts: true,
		onComplete: function(e){
		    if(col)
				document.f_sequence['sort_by'].value=col;
		}
	});
}

function print_discount_table(){
	document.fprint['a'].value = 'print_branch_discount_table';
	document.fprint.submit();
}

function print_envelope(bid){
    document.fprint['a'].value = 'print_envelope';
    document.fprint['selected_bid'].value = bid;
	document.fprint.submit();
}

function check_lost_inv_disc(ele){
    var discount = ele.value.trim();
    if(allow_secondary_discount){
        var discount_format = /^\d+(\.\d+){0,1}(\+\d+(\.\d+){0,1}){0,1}$/;
		if(!discount_format.test(discount)){
	        ele.value = '';
	        discount = '';
		}
	}else   ele.value = float(ele.value);
    
}

function print_branch_sequence(){
	
	toggle_select_all_opt(document.f_sequence['sel_branch'], true);
	document.f_sequence.target='_blank';
	document.f_sequence['a'].value = 'print_branch_sequence';
    document.f_sequence.submit();
	toggle_select_all_opt(document.f_sequence['sel_branch'], false);
}

function load_table(){
	new Ajax.Updater('udiv',phpself+'?a=load_table', {
		evalScripts: true
	})
}

function check_branch_type(obj){
	if(obj.value == "franchise"){
		$('debtor').show();
		document.f_b.debtor_id.disabled = false;
	}else{
		$('debtor').hide();
		document.f_b.debtor_id.disabled = true;
	}
}
function copy_settings_dialog(bid){
	$('div_cs').show();
	center_div('div_cs');
	$("cbid").value = bid;
	curtain(true);
}

function copy_settings_clicked(obj){
	if(!$("copy_type").value){
		alert("Please select Copy Type");
		return false;
	}else if(!$("copy_from_bid").value){
		alert("Please select Branch Copy From");
		return false;
	}else if($("copy_from_bid").value == $("cbid").value){
		alert("Cannot copy from same branch");
		return false;
	}
	
	if(!confirm("Are you sure want to copy? \n\nNote: This action cannot be UNDO.")) return false;
	
	$("div_copying").show();
	obj.disabled = true;
	$("cancel_btn").disabled = true;

	var params = $(document.cs_f).serialize();
	
	new Ajax.Request(phpself, {
		parameters: params,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';
					
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					alert("Copied Successfully.");
					$("div_copying").hide();
					obj.disabled = false;
					$("cancel_btn").disabled = false;
					return;
				}else{  // save failed
					if(ret['failed_reason']) err_msg = ret['failed_reason'];
					else err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			if(!err_msg)	err_msg = 'No Respond from server.';
			// prompt the error
			alert(err_msg);
			$("div_copying").hide();
			obj.disabled = false;
			$("cancel_btn").disabled = false;
		}
	});

}

function show_full_logo()
{
	center_div('logo_full');
	$('logo_full').toggle();
}

function show_gst_interbranch(){
	var params = {
		'a': 'show_gst_interbranch'
	};
	$('div_gst_interbranch_content').update(_loading_);
	center_div($('div_gst_interbranch').show());
	
	new Ajax.Request(phpself, {
		parameters: params,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';
					
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['html']){ // success
					$('div_gst_interbranch_content').update(ret['html']);
					//center_div($('div_gst_interbranch'));
					return;
				}else{  // save failed
					if(ret['failed_reason']) err_msg = ret['failed_reason'];
					else err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			if(!err_msg)	err_msg = 'No Respond from server.';
			// prompt the error
			//alert(err_msg);
			$('div_gst_interbranch_content').update(err_msg);
		}
	});
}

function save_gst_interbranch(){
	if(!document.f_gst_interbranch){
		alert('Form object not found');
		return false;
	}
	
	$('btn_save_gst_interbranch').disable().value = "Saving...";
	
	var params = $(document.f_gst_interbranch).serialize();
	new Ajax.Request(phpself, {
		parameters: params,
		method: 'post',
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';
					
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					alert('Save successfully');
					$('div_gst_interbranch').hide();
					return;
				}else{  // save failed
					if(ret['failed_reason']) err_msg = ret['failed_reason'];
					else err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			if(!err_msg)	err_msg = 'No Respond from server.';
			// prompt the error
			alert(err_msg);
			$('btn_save_gst_interbranch').enable().value = "Save";
		}
	});
}

function toggle_gst_interbranch(obj, bid1, bid2){
	var c = obj.checked;
	if(bid1){
		$(document.f_gst_interbranch).getElementsBySelector('.chx_interbranch-'+bid1).each(function(chx){
			chx.checked = c;
		});
	}
	if(bid2){
		$(document.f_gst_interbranch).getElementsBySelector('.chx_interbranch2-'+bid2).each(function(chx){
			chx.checked = c;
		});
	}
}

function gst_reg_no_changed(){
	check_gst_allow_edit();
}

function gst_reg_date_changed(){
	check_gst_allow_edit();
}

function check_gst_allow_edit(){
	if(document.f_b['gst_register_no'].value.trim()==''){
		document.f_b['gst_start_date'].value = '';
	}
}

function upload_check()
{
	if (!/\.jpg|\.jpeg/i.test(document.f_b['logo'].value))
	{
		alert("Selected file must be a valid JPG/JPEG image");
		document.f_b['logo'].value = "";
		return false;
	}
	
	var oFile = document.f_b['logo'].files[0];
	if (oFile.size > 5242880) // 5 mb for bytes.
	{
		alert("Branch logo is exceeded maximum file size (maximum 5MB only).");
		document.f_b['logo'].value = "";
		return false;
	}
	
	return true;
}

var OUTLET_PHOTO_DIALOG = {
	open: function(branch_id){
		$('div_outlet_photo_dialog_content').update(_loading_);
		
		//show dialog
	//	curtain(true);
	//	center_div($('div_outlet_photo').show());
	jQuery('#div_outlet_photo').modal('show');		
		var THIS = this;
		var params = {
			a: 'ajax_show_outlet_photo',
			branch_id: branch_id
		}
			
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			asynchronous: false,
			onComplete: function(msg){			    
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
		
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] && ret['html']){ // success
						// Redirect to main page
						$('div_outlet_photo_dialog_content').update(ret['html']);
						center_div('div_outlet_photo');
						return;
					}else{
						err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				// prompt the error
				alert(err_msg);
				THIS.close();
			}
		});
	},
	
	upload_outlet_photo: function(){
		if(document.f_outlet_photo['outlet_photo'].value==''){
			alert('Select a file to upload');
			return false;
		}else if (!/\.jpg|\.jpeg|\.png|\.gif/i.test(document.f_outlet_photo['outlet_photo'].value)){
			alert("Selected file must be a valid JPG/JPEG/PNG/GIF image");
			return false;
		}
		
		var oFile = document.f_outlet_photo['outlet_photo'].files[0];
		if (oFile.size > 1000000 ) // 1 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 1MB only.");
			return false;
		}
		
		if(!confirm('Are you sure?')) return false;
		
		this.set_banner_uploading(true);
		document.f_outlet_photo.submit();
	},
	
	set_banner_uploading: function(is_uploading){
		if(is_uploading){
			$('btn_submit_branch_outlet_photo').disabled = true;
			$('span_loading_branch_outlet_photo').show();
		}else{
			$('btn_submit_branch_outlet_photo').disabled = false;
			$('span_loading_branch_outlet_photo').hide();
		}
	},
	
	photo_uploaded_failed: function(){
		this.set_banner_uploading(false);
	},
	
	photo_uploaded: function(filepath){
		$('img_branch_outlet_photo').src = filepath;
		document.f_outlet_photo['outlet_photo'].value='';
		this.set_banner_uploading(false);
		$('span_del_outlet_photo').show();
	},
	
	
	delete_outlet_photo: function(branch_id){
		if(!confirm('Are you sure?')) return false;
		var params = {
			a: 'delete_outlet_photo',branch_id: branch_id
		}
		
		ajax_request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				
				try{
					ret = JSON.parse(str);
					if (ret['ok']){
						$('img_branch_outlet_photo').src = "?t="+current_time;
						document.f_outlet_photo['outlet_photo'].value = '';
						$('span_del_outlet_photo').hide();
					}else{
						alert(msg.responseText);
					}
				}catch(ex){ // failed to decode json, it is plain text response
					alert(msg.responseText);
				}
			}
		});
	}
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Branch Master File</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		
{if $allow_add_branch}
<div><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Branch</a> (Alt+A)</div><br>
{/if}

{if $sessioninfo.level>=5000}
<div><a href="javascript:sort_sequence();"><img border="0" align="absmiddle" title="ReSort Branch Sequence" src="/ui/icons/table_refresh.png" /> Resort Branch Sequence</a></div><br />
{/if}

{if $config.masterfile_branch_allow_print_discount_table}
<div><a href="javascript:void(print_discount_table());"><img src="ui/icons/printer.png" title="Export Discount Table" align=absmiddle border=0> Export Discount Table</a></div><br>
{/if}

{if !$config.consignment_modules && $config.enable_gst}
	<div><a href="javascript:void(show_gst_interbranch());"><img src="ui/icons/page.png" title="GST Interbranch Settings" align="absmiddle" border="0"> GST Interbranch Settings</a></div><br>
{/if}
	</div>
</div>

<!-- printing area -->
<form name="fprint" target="ifprint">
	<input type="hidden" name="a">
	<input type="hidden" name="selected_bid" />
</form>
<iframe name="ifprint" style="width:1px;height:1px;visibility:hidden;"></iframe>
<!-- end of printing area -->

<!-- Div Sort Branch Sequence -->
<div id="div_sort_sequence" style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;width:800px;height:460px;"></div>
<!-- End of Div Sort Branch Sequence -->

<!-- start payment vocher maintenance div -->
<div class="ndiv mt-5 shadow" id="vvc_div" style="background-color: #fff;  position:absolute;left:550;top:120;display:none;z-index: 2;">
{include file=masterfile_branch_index.vvc.tpl}
</div>
<div class="ndiv" style="" >
<div class="content rounded" id="div_trade_discount" style="background-color: #fff; padding: 10px; border: 1px solid rgb(51, 48, 48); position:absolute;left:300;top:100;display:none;height:400px;width:500px;overflow-y:auto;z-index:10000;"></div>
</div>
<!-- end of div -->

<!-- branch popup -->
<div class="ndiv shadow" id="div_branch" style="z-index: 2; background-color: #fff; position:absolute;margin-left:100px; margin-top:30px;display:none; width: 850px; height: 500px; overflow: auto;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div style="height:30px;background-color:#6883C6; position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_branch_header">
					<div class="small" style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(hidediv('div_branch'))"><img class="mt-1" src="ui/closewin.png" border="0" align="absmiddle"></a></div>
					<span class="ml-2">Branch Information</span>
				</div>

				<div id="div_branch_content" style="margin-top:20px;">
					{include file='masterfile_branch.open.tpl'}
					
				</div>
			</div>
		</div>
	</div>
</div>

<!-- branch outlet photo popup-->

<div class="modal" id="div_outlet_photo">
    <div class="modal-dialog modal-lg " role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header bg-danger" id="div_sa_photo_dialog_header">
                <h6 class="modal-title text-white">Branch Outlet Photo</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
				<div style="clear:both;"></div>
			</div>
            <div class="modal-body" id="div_outlet_photo_dialog_content"  >
                
            </div>
        </div>
    </div>
</div>

<iframe name="if_outlet_photo" style="width:1px;height:1px;visibility:hidden;"></iframe>

<!-- copy settings popup -->
<form name="cs_f">
	<div class="ndiv" id="div_cs" style="position:absolute;left:150;top:150;z-index:10000;display:none;">
		<div class="blur">
			<div class="shadow">
				<div class="content" style="background-color:white; padding: 15px; border-bottom: solid 1px #aebcce ;">
					<div style="height:25px;background-color:#3d5a80;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_cs_header">
						<div class="small" style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(hidediv('div_cs'))"><img class=" mt-1" src="ui/closewin.png" border=0 align="absmiddle"></a></div>
						<b class="ml-2 ">Copy Settings</b>
					</div>

					<div id="div_cs_content" style="min-width:100px;">
						<b class="form-label mt-4">Copy</b>
						<select class="form-control" name="copy_type" id="copy_type">
							<option value="">Please Select</option>
							<option value="1">Selling Prices</option>
							<option value="2">Block PO</option>
							<option value="3">POS Settings</option>
							<option value="4">Member Discount</option>
							<option value="5">Member Points</option>
							<option value="6">Trade Discount</option>
							<option value="7">Approval Flow</option>
							<option value="8">Block GRN</option>
						</select>
						<b class="form-label mt-2">From branch</b>
						<select class="form-control" name="copy_from_bid" id="copy_from_bid">
							<option value="">Please Select</option>
							{foreach from=$branches key=r item=branch}
								<option value="{$branch.id}">{$branch.code}</option>
							{/foreach}
						</select>
					</div>
					<div style="color:red;margin-top:15px;min-height:50px;">
						<b>Note: This action will overwrite everything and cannot be UNDO.</b><br />
						<div id="div_copying" style="margin-top:5px;padding:2px;color:black;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Copying... Please wait</div>
					</div>
					<div id="div_btn" align="center">
						<input type="button" class="btn btn-primary" value="Copy" id="copy_btn" onclick="copy_settings_clicked(this);" />
						<input type="button" class="btn btn-danger" value="Cancel" id="cancel_btn" onclick="curtain_clicked();" />
						<input type="hidden" name="cbid" id="cbid" value="" />
						<input type="hidden" name="a" value="branch_copy_settings" />
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

{* GST Interbranch Settings *}
<div class="ndiv" id="div_gst_interbranch" style="position:absolute;left:150;top:150;display:none;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div style="height:20px;background-color:#6883C6;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_gst_interbranch_header">
					<div class="small" style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(hidediv('div_gst_interbranch'))"><img src="ui/closewin.png" border="0" align="absmiddle"></a></div>
					GST Interbranch Settings
				</div>

				<div id="div_gst_interbranch_content" style="margin-top:20px;min-width:400px;min-height:400px;">
					test
				</div>
			</div>
		</div>
	</div>
</div>

<div id="udiv" class="stdframe">
{include file=masterfile_branch_table.tpl}
</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
init_chg(document.f_b);
{literal}
new Draggable('div_branch', { handle: 'div_branch_header'});
new Draggable('vvc_div');
new Draggable('div_cs', { handle: 'div_cs_header'});
new Draggable('div_gst_interbranch', { handle: 'div_gst_interbranch_header'});
{/literal}
</script>

{include file=footer.tpl}
