{*
4/15/2013 3:28 PM Justin
- Modified to show S/A Code prefix on outside of the field.

11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

2/9/2015 4:55 PM Justin
- Bug fixed on some times the searching engine doesn't appear while assigning commission.

6/30/2015 3:52 PM Justin
- Bug fixed on save commission will not save the user id and last update.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

8/18/2017 5:01 PM Andy
- Enhanced to able to filter by code, name and status.

10/22/2019 3:05 PM Andy
- Added Sales Agent Photo.

11/22/2019 5:18 PM Justin
- Enhanced to have new options "Position" and "Leader".
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}

.div_leader_container{
	border: 1px solid blue;
	min-width: 100px;
	float: left;
	padding:3px;
	background-color: #eee;
	margin-left: 5px;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function curtain_clicked(){
	if($('div_sa_table').style.display == ""){
		SALES_AGENT_MODULE.sa_table_fade();
	}else{
		hidediv('div_commission_table');
		hidediv('div_commission_items_table');
		hidediv('div_st_table');
	}
	curtain(false);
}

var SALES_AGENT_MODULE = {
	curr_id: undefined,
	form_element: undefined,
	sa_id: undefined,
	prv_bid: undefined,
	initialize : function(){
		// event when user click "add"
		$('add_btn').observe('click', function(){
            SALES_AGENT_MODULE.validate('add');
		});

		// even when user click "cancel" and "close"
		$('cancel_btn').observe('click', function(){
            SALES_AGENT_MODULE.sa_table_fade();
		});
		$('close_btn').observe('click', function(){
            SALES_AGENT_MODULE.sa_table_fade();
		});

		// even when user click "edit"
		$('edit_btn').observe('click', function(){
			SALES_AGENT_MODULE.sa_edit(0, 1);
		});

		// event when user click "update"
		$('update_btn').observe('click', function(){
            SALES_AGENT_MODULE.validate('update');
		});
		
		new Draggable('div_sa_table');
		new Draggable('div_commission_table',{ handle: 'commission_header'});
		new Draggable('div_commission_items_table');
		new Draggable('div_st_table',{ handle: 'sales_target_header'});
		center_div('div_sa_table');
		center_div('div_commission_table');
		center_div('div_commission_items_table');
		center_div('div_st_table');
	},
	sa_table_appear : function(type){
		if(type == "add"){
			$('bmsg').update("Complete below form and click Add");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
			document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 1, 1); };
			document.f_b.ticket_btn.value = "Generate";
			
			// unset all leaders
			$('span_leader_list').update('');
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
		}
		$('err_msg').update();
		hidediv('err_msg');

		showdiv('div_sa_table');
		center_div('div_sa_table');
		curtain(true);
	},
	sa_table_fade : function(){
		curtain(false);
		Effect.SlideUp('div_sa_table', {
			duration: 0.2,
			afterFinish: function() {
				$('bmsg').update();
			}
		});
	},
	validate : function(prs_type){
		if (empty(document.f_b.code, 'You must enter Code')) return false;
		if (empty(document.f_b.name, 'You must enter Name')) return false;

		if(prs_type == "add") SALES_AGENT_MODULE.sa_add();
		else SALES_AGENT_MODULE.sa_update();
	},
	sa_add : function(){
		this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();
		
		var params = {
		    a: 'add'
		};
		prm += '&'+$H(params).toQueryString();

		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("Sales Agent ["+document.f_b.name.value.trim()+"] has been added.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	sa_update : function(){
		this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'update'
		};
		prm += '&'+$H(params).toQueryString();

		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("Sales Agent ["+document.f_b.name.value.trim()+"] has been updated.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},

	sa_edit : function(id, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		else if(is_restore && id == 0) id = this.curr_id;
		document.f_b.reset();
		document.f_b.id.value = id;
		this.curr_id = id;
		
		this.curr_sa_id = id;
		
		var THIS = this;
		ajax_request(phpself, {
			parameters:{
				a: 'edit',
				sa_id: id
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['sa_info']){ // success
					if(document.f_b){
						document.f_b.id.value = ret['sa_info']['id'];
						document.f_b.code.value = ret['sa_info']['code'];
						document.f_b.name.value = ret['sa_info']['name'];
						document.f_b.company_code.value = ret['sa_info']['company_code'];
						document.f_b.company_name.value = ret['sa_info']['company_name'];
						document.f_b.address.value = ret['sa_info']['address'];
						document.f_b.email.value = ret['sa_info']['email'];
						document.f_b.phone_1.value = ret['sa_info']['phone_1'];
						if(ret['sa_info']['ticket_expired'] == 1){ // is expired
							document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 1, 1); };
							document.f_b.ticket_btn.value = "Generate";
						}else{
							document.f_b.ticket_no.value = ret['sa_info']['ticket_no'];
							document.f_b.old_ticket_no.value = ret['sa_info']['ticket_no'];
							document.f_b.ticket_valid_before.value = ret['sa_info']['ticket_valid_before'];
							document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 0, 1); };
							document.f_b.ticket_btn.value = "Deactivate";
						}
						if(ret['sa_info']['position_id'] > 0) document.f_b.position_id.value = ret['sa_info']['position_id'];
						
						// show the list of the sales agent leaders
						if(ret['sa_info']['leader_list'] != undefined){
							$('span_leader_list').update(ret['sa_info']['leader_list']);
						}else{
							$('span_leader_list').update('');
						}
						
						if(is_restore == 0) SALES_AGENT_MODULE.sa_table_appear('edit');
						return;
					}else err_msg = "Failed to load edit form!";
				}else{  // load sa info failed
					if(ret['failed_msg'])	err_msg = ret['failed_msg'];
					else err_msg = str;
				}

				alert(err_msg);
			}
		});

		document.f_b.a.value = 'update';
		document.f_b.code.focus();
	},
	sa_activation : function(id, status){
		if(status == 0 && !confirm("Are you sure want to deactivate this Sales Agent?")) return;

		var params = {
		    a: 'activation',
			sa_id: id,
			value: status
		};
		//prm += '&'+$H(params).toQueryString();

		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					document.location=phpself;
				}else{
					$('bmsg').update(msg.responseText.trim());
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	sa_ticket_activation : function(id, status, is_edit_screen){
		var alert_msg = "";
		if(status == 1) alert_msg = "Generate";// is generate ticket
		else alert_msg = "Deactivate";
		if(is_edit_screen == 0 && !confirm(alert_msg+" Access Ticket?")) return;

		if(is_edit_screen && document.f_b && status == 0){ // is deactivate from edit menu
			document.f_b.ticket_no.value = "";
			document.f_b.ticket_valid_before.value = "";
			document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 1, 1); };
			document.f_b.ticket_btn.value = "Generate";
			return;
		}
		
		var params = {
		    a: 'ticket_activation',
			sa_id: id,
			value: status,
			is_es: is_edit_screen
		};
		//prm += '&'+$H(params).toQueryString();

		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					if(is_edit_screen && document.f_b){
						document.f_b.ticket_no.value = ret['ticket_no'];
						document.f_b.ticket_valid_before.value = ret['valid_before'];
						document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 0, 1); };
						document.f_b.ticket_btn.value = "Deactivate";
					}else{
						alert("Successfully "+alert_msg+"d Access Ticket for "+$("list_sa_code_"+id).value);
						document.location=phpself;
					}
				}else{
					alert(msg.responseText.trim());
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	
	sales_target_table_appear: function(id){
		if(!id) return;

		ajax_request(phpself, {
			parameters:{
				a: 'ajax_load_sales_target',
				sa_id: id
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					$('div_st_list').update(ret['html']);
					document.f_st.sa_id.value = ret['sa_id'];

					if($('div_st_table').style.display == 'none'){
						Effect.Appear('div_st_table', {
							duration: 0.5
						});
						center_div('div_st_table');
						curtain(true);
					}
				}else{
					alert(ret['error']);
				}
				$('span_loading').update();
			}
		});

		document.f_b.a.value = 'update';
		document.f_b.code.focus();
	},
	
	save_sales_target: function(id){
		var prm = $(document.f_st).serialize();
		
		var params = {
		    a: 'ajax_save_sales_target'
		};
		prm += '&'+$H(params).toQueryString();

		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("Sales Target has been saved.");
					curtain_clicked();
				}else{
					alert(msg.responseText.trim());
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	
	load_sales_target_month_value: function(said, bid, obj){
		curr_year = obj.value;
		var params = {
			a: 'ajax_load_st_month_value',
			sa_id: said,
			branch_id: bid,
			curr_yr: curr_year
		};
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();

				eval("var json = "+msg.responseText);
				for(var mth_key in json){
					if(document.f_st.elements['mth['+bid+']['+mth_key+']'] != undefined) document.f_st.elements['mth['+bid+']['+mth_key+']'].value = json[mth_key]['mth'];
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	// function to reload sa list
	reload_sa_list: function(){
		$('inp_reload_sa').disabled = true;
		$('span_loading_sa_list').show();
		
		var params = $(document.f_a).serialize();
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_sa_list').update(ret['html']);
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}						
					}
				}catch(ex){
					alert(str);
				}
				
				$('inp_reload_sa').disabled = false;
				$('span_loading_sa_list').hide();
			}
		});
	},
	
	// function when user click on search leader
	search_leader_click: function(){
		SEARCH_LEADER_DIALOG.open();
	},
	
	// function to add Sales Agent Leader
	add_sa_leader: function(sa_id, sa_name){
		$('span_leader_loading').update(_loading_);
		
		new Ajax.Request(phpself, {
			parameters: {
				a: 'ajax_add_leader',
				sa_id: sa_id,
			},
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('span_leader_loading').update('');
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						new Insertion.Bottom($('span_leader_list'), ret['html']);
						hidediv('div_search_leader_dialog');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	
	// function when user click on delete leader
	del_leader_assign: function(sa_id){
		$('div_leader_assign-'+sa_id).remove();
	},
}

var SA_COMMISSION_MODULE = {
	form_element: undefined,
	sa_id: undefined,
	prv_bid: undefined,
	initialize : function(){
		this.form_element = document.f_sac;
		
		// event when user click "update"
		$('sac_save_btn').observe('click', function(){
            SA_COMMISSION_MODULE.save_branch_commission();
		});
		
		// event when user click "close"
		$('sac_close_btn').observe('click', function(){
            SA_COMMISSION_MODULE.commission_table_fade('div_commission_table');
		});
	},
	commission_table_appear: function(said){
		this.sa_id = said;
		$('span_loading').update(_loading_);
		var branch_id = '';
		var sch_str = '';

		if($('sac_branch_id') != undefined){
			branch_id = $('sac_branch_id').value;
			$('sac_branch_id').onchange = function(){ SA_COMMISSION_MODULE.commission_table_appear(said); };
		}
		
		var params = {
			a: 'ajax_load_commission_by_branch_list',
			sa_id: said,
			sac_branch_id: branch_id,
			search_str: sch_str
		};
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					$('div_sac_list').update(ret['html']);

					if($('div_commission_table').style.display == 'none'){
						Effect.SlideDown('div_commission_table', {
							duration: 0.5
						});
						center_div('div_commission_table');
						curtain(true);
					}
				}else{
					alert(ret['error']);
				}
				$('span_loading').update();
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	commission_table_fade : function(id){
		if(id != "div_commission_items_table" || $("div_commission_table").style.display == "none") curtain(false);
		if(id == "div_commission_table") if(!confirm("Are you sure want to close without save?")) return;
		Effect.SlideUp(id, {
			duration: 0.5
		});
	},
	commission_items_table_appear: function(sacid, bid){
		$('span_loading').update(_loading_);

		var params = {
			a: 'ajax_load_commission_items_list',
			sac_id: sacid,
			branch_id: bid
		};

		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					$('div_saci_list').update(ret['html']);
					Effect.SlideDown('div_commission_items_table', {
						duration: 0.5
					});
					center_div("div_commission_items_table");
					curtain(true);
				}else{
					alert(ret['error']);
				}
				$('span_loading').update();
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	/*toggle_commission_item_table : function(sac_id, obj){
		if($('tr_saci_items_list_'+sac_id).style.display == "none"){
			obj.src = "/ui/icons/zoom_out.png";
			showdiv('tr_saci_items_list_'+sac_id);
		}else{
			obj.src = "/ui/icons/zoom_in.png";
			hidediv('tr_saci_items_list_'+sac_id);	
		}
	},*/
	use_branch_commission : function(sac_id, bid, obj){
		if(!confirm("Are you sure want to use this commission?")) return;
		var commission_title = obj.readAttribute("commission_title");
		$('branch_sac_'+bid).value = sac_id;
		$('toggle_bsac_btn_'+bid).style.display = "";
		$('branch_commission_title_'+bid).value = commission_title;
		if(!$('branch_prv_sac_'+bid).value) $('branch_sac_active_'+bid).value = 1;
		
	},
	toggle_branch_commission_status : function(bid, obj){
		if($('branch_sac_active_'+bid).value > 0){ // is deactivate
			if(!confirm("Are you sure want to use remove commission from this agent?")) return;
			$('branch_commission_title_'+bid).value = "-- None -- ";
			if(!$('branch_prv_sac_'+bid).value){
				$('branch_sac_'+bid).value = "";
				obj.style.display = "none";
			}else{
				obj.innerHTML = "Activate";
				$('span_branch_sac_active_'+bid).style.display = "";
			}
			
			$('branch_sac_active_'+bid).value = 0;
		}else{ // is activate
			if($('branch_commission_title_'+bid).title) $('branch_commission_title_'+bid).value = $('branch_commission_title_'+bid).title;

			if(!$('branch_prv_sac_'+bid).value) obj.style.display = "";
			else{
				obj.innerHTML = "Deactivate";
				$('span_branch_sac_active_'+bid).style.display = "none";
			}

			$('branch_sac_active_'+bid).value = 1;
		}
	},
	search_branch_commission : function(bid, is_search){
		if(!is_search){
			if(this.prv_bid != undefined && this.prv_bid != bid) $('commission_list_'+this.prv_bid).style.display = "none";
			if($('commission_list_'+bid).style.display == "")
				//$('commission_list_'+bid).style.display = "none";
				Effect.Fade('commission_list_'+bid, { duration: 0.5 });
			else{
				Effect.Appear('commission_list_'+bid, { duration: 0.5 });
			}
			//if(this.prv_bid == bid) return; // return since user doing close the commission list only
			this.prv_bid = bid;
			//if($('div_branch_commission_'+bid).innerHTML.trim() != "") return; // stop to go further if found contents is already loaded
		}else{
			$('span_sac_branch_loading_'+bid).update(_loading_);
		}

		$('span_loading').update(_loading_);
		var said = this.sa_id;
		var sch_str = '';

		if($('search_commission_'+bid) != undefined){
			if($('search_commission_'+bid).value.trim() != ""){
				sch_str = $('search_commission_'+bid).value.trim();
			}
		}

		var params = {
			a: 'ajax_load_commission_list',
			sa_id: said,
			sac_branch_id: bid,
			search_str: sch_str
		};
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					$('div_branch_commission_'+bid).update(ret['html']);
				}else{
					alert(ret['error']);
				}
				$('span_loading').update();
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	save_branch_commission : function(){
		if (check_login()) {
            $('sac_save_btn').disabled = true;
			document.f_sac.sa_id.value = this.sa_id;
			document.f_sac.submit();
        }
	}
}

var SALES_AGENT_PHOTO_DIALOG = {
	initialize: function(){
		
	},
	open: function(sa_id){
		// Show Loading
		$('div_sa_photo_dialog_content').update(_loading_);
		
		// Show Dialog
		curtain(true);
		center_div($('div_sa_photo_dialog').show());
		
		var THIS = this;
		var params = {
			a: 'ajax_show_sa_photo',
			sa_id: sa_id
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
						$('div_sa_photo_dialog_content').update(ret['html']);
						center_div('div_sa_photo_dialog');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
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
	// function when users click on upload photo
	upload_sa_photo_clicked: function(){
		// Check File Extension
		if (document.f_sa_photo['sa_photo'].value == '') {
			alert('Select a file to upload');
			return false;
		}else if (!/\.jpg|\.jpeg|\.png|\.gif/i.test(document.f_sa_photo['sa_photo'].value))
		{
			alert("Selected file must be a valid JPG/JPEG/PNG/GIF image");
			return false;
		}
		
		var oFile = document.f_sa_photo['sa_photo'].files[0];
		if (oFile.size > 1000000 ) // 1 mb for bytes.
		{
			alert("Image File Size is limited to a maximum of 1MB only.");
			return false;
		}
		
		if(!confirm('Are you sure?')) return false;
		
		this.set_banner_uploading(true);
		document.f_sa_photo.submit();
	},
	set_banner_uploading: function(is_uploading){
		if(is_uploading){
			$('btn_submit_sales_agent_photo').disabled = true;
			$('span_loading_sales_agent_photo').show();
		}else{
			$('btn_submit_sales_agent_photo').disabled = false;
			$('span_loading_sales_agent_photo').hide();
		}
	},
	close: function(){
		default_curtain_clicked();
	},
	// callback function when upload failed
	photo_uploaded_failed: function(){
		this.set_banner_uploading(false);
	},
	// callback function after photo uploaded
	photo_uploaded: function(filepath){
		$('img_sales_agent_photo').src = filepath;
		this.set_banner_uploading(false);
	}
};

var SEARCH_LEADER_DIALOG = {
	f: undefined,
	initialize: function(){
		this.f = document.f_search_leader;
		
		SA_AUTOCOMPLETE.initialize({
			'callback': function(sa_id, sa_name){
				SEARCH_LEADER_DIALOG.add_leader_clicked(sa_id, sa_name);
			}
		});
	},
	open: function(){	
		// Clear Form value
		this.f.reset();
		$('inp_selected_sa_id').value = '';
		
		// Show Dialog
		curtain(true);
		center_div($('div_search_leader_dialog').show());
		
		// Focus on search input
		SA_AUTOCOMPLETE.focus_inp_search_sa_name();
	},
	close: function(){
		hidediv('div_search_leader_dialog');
	},
	// function when user click to add elader
	add_leader_clicked: function(sa_id, sa_name){
		if(!sa_id){
			alert('Please search the Sales Agent.');
			// Focus on search input
			SA_AUTOCOMPLETE.focus_inp_search_sa_name();
			return false;
		}

		SALES_AGENT_MODULE.add_sa_leader(sa_id, sa_name);
	}
}
</script>
{/literal}

{* SA Photo Dialog *}
<div id="div_sa_photo_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:550px;height:330px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sa_photo_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Sales Agent Photo</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SALES_AGENT_PHOTO_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sa_photo_dialog_content" style="padding:2px;">
	</div>
</div>
<iframe name="if_sa_photo" style="width:1px;height:1px;visibility:hidden;"></iframe>

<h1>{$PAGE_TITLE}</h1>
<div>
	<a onclick="SALES_AGENT_MODULE.sa_table_appear('add');" style="cursor:pointer;"><img src="ui/icons/user_add.png" title="Create Sales Agent" align="absmiddle" border="0"> Create New Sales Agent</a> <span id="span_loading"></span><br /><br />
</div>

<p>
	<form name="f_a" onSubmit="SALES_AGENT_MODULE.reload_sa_list();return false;">
		<input type="hidden" name="a" value="ajax_reload_sa_list" />
		<span>
			<b>Code / Name:</b>
			<input type="text" name="code_or_name" />
			&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		
		<span>
			<b>Status:</b>
			<select name="status">
				<option value="">All</option>
				<option value="1">Active</option>
				<option value="0">Inactive</option>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		
		<input id="inp_reload_sa" type="button" value="Search" onClick="SALES_AGENT_MODULE.reload_sa_list();" />
	</form>
	<span id="span_loading_sa_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
</p>

<div id="div_sa_list">
{include file="masterfile_sa.list.tpl"}
</div>
<br>

<!-- printing area -->
<form name="fprint" target="ifprint">
	<input type="hidden" name="a">
	<input type="hidden" name="selected_bid" />
</form>
<iframe name="ifprint" style="width:1px;height:1px;visibility:hidden;"></iframe>
<!-- end of printing area -->

<!--div class="curtain_popup" id="div_commission_table" style="position:absolute;z-index:10000;width:800px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="commission_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Commission List</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onclick="SALES_AGENT_MODULE.commission_table_fade('div_commission_table');" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
	</div>
	<div id="div_sac_list" style="padding:4;"></div>
</div-->

<form method="post" name="f_st" onSubmit="return false;">
	<input type="hidden" name="a" value="save_sales_target">
	<input type="hidden" name="sa_id">
	<div class="ndiv" id="div_st_table" style="position:absolute;width:950px;height:400px;display:none;z-index:10000;">
		<div class="blur"><div class="shadow"><div class="content">
		<div style="float:left;position:absolute;" class="clickable" id="sales_target_header"><h4>Sales Target List</h4></div>
		<div class="small" style="position:absolute; right:10; text-align:right;">
		<a onclick="SA_COMMISSION_MODULE.commission_table_fade('div_st_table');" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br /><u>C</u>lose (Alt+C)</div>
		<br /><br />
		<div id="div_st_list" style="height:80%;overflow-x:hidden;overflow-y:auto;"></div>
		</div></div></div>
	</div>
</form>

<form method="post" name="f_sac" onSubmit="return false;">
	<input type="hidden" name="a" value="save_commission">
	<input type="hidden" name="sa_id">
	<div class="ndiv" id="div_commission_table" style="position:absolute;width:600px;height:400px;display:none;z-index:10000;">
		<div class="blur"><div class="shadow"><div class="content">
		<div style="float:left;position:absolute;" class="clickable"  id="commission_header"><h4>Commission List</h4></div>
		<div class="small" style="position:absolute; right:10; text-align:right;">
		<a onclick="SA_COMMISSION_MODULE.commission_table_fade('div_commission_table');" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br /><u>C</u>lose (Alt+C)</div>
		<br /><br />
		<div id="div_sac_list" style="height:80%;overflow-x:hidden;overflow-y:auto;"></div>
		</div></div></div>
	</div>
</form>

<div class="ndiv" id="div_commission_items_table" style="position:absolute;width:800px;height:300px;display:none;z-index:10000;">
	<div class="blur"><div class="shadow"><div class="content">
	<div style="float:left;position:absolute;"><h4>Commission Item List</h4></div>
	<div class="small" style="position:absolute; right:10; text-align:right;">
	<a onclick="SA_COMMISSION_MODULE.commission_table_fade('div_commission_items_table');" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br /><u>C</u>lose (Alt+C)</div>
	<br /><br />
	<div id="div_saci_list"></div>
	</div></div></div>
</div>

{* Search Leader Dialog *}
<div id="div_search_leader_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:500px;height:100px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_search_leader_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Search Leader</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SEARCH_LEADER_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_search_leader_dialog_content" style="padding:2px;">
		<form name="f_search_leader">	
			<p align="center">
				<b>Search Leader: &nbsp;&nbsp;&nbsp;</b>
				{include file='sa_autocomplete.tpl' btn_add=1}
			</p>
		</form>
	</div>
</div>

<div class="ndiv" id="div_sa_table" style="position:absolute;width:670px;height:300px;display:none;z-index:10000;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small" style="position:absolute; right:10; text-align:right;"><a onclick="SALES_AGENT_MODULE.sa_table_fade();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br><u>C</u>lose (Alt+C)</div>

<form method="post" name="f_b" onSubmit="return SALES_AGENT_MODULE.validate();">
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	<div id="err_msg" style="color:#CE0000; display:none; font-weight:bold;"></div>
	<input type="hidden" name="a" value="add">
	<input type="hidden" name="id" value="">
	<table>
		<tr><td valign="top">
		<table id="tb">
			<tr>
			<td><b>Code</b></td>
			<td>
				{if $config.masterfile_sa_code_prefix}{$config.masterfile_sa_code_prefix}&nbsp;{/if}
				<input onBlur="uc(this)" name="code" size="20" maxlength="20" value=""> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr><tr>
			<td><b>Name</b></td>
			<td><input onBlur="uc(this)" name="name" size="30" maxlength="40"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
			</tr><tr>
			<td><b>Company Code</b></td>
			<td><input onBlur="uc(this)" name="company_code" size="20" maxlength="20"></td>
			</tr><tr>
			<td><b>Company Name</b></td>
			<td><input onBlur="uc(this)" name="company_name" size="30" maxlength="40"></td>
			</tr><tr>
			<td><b>Email</b></td>
			<td><input onBlur="lc(this);" name="email" size="20"></td>
			</tr><tr>
			<td valign="top"><b>Phone No</b></td>
			<td><input name="phone_1" size="12"></td>
			</tr><tr>
			<td valign="top"><b>Ticket No</b></td>
			<td>
				<input name="ticket_no" size="10" readonly>
				<input type="hidden" name="old_ticket_no">
				<input type="button" name="ticket_btn" value="Generate">
			</td>
			</tr><tr>
			<td valign="top"><b>Valid Before</b></td>
			<td><input name="ticket_valid_before" size="18" readonly></td>
			</tr>
			<tr>
				<td><b>Position</b></td>
				<td>
					<select name="position_id">
						<option value="">-- Please Select --</option>
						{foreach from=$position_list key=position_id item=r}
							<option value="{$position_id}">{$r.code} - {$r.description}</option>
						{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<td><b>Leader</b></td>
				<td>
					<img src="ui/ed.png" align="absmiddle" onClick="SALES_AGENT_MODULE.search_leader_click();" style="float:left;" />
					<span id="span_leader_list">
					</span>
					<span id="span_leader_loading"></span>
				</td>
			</tr>
		</table>
		</td><td valign="top">
		<b>Address</b><br />
		<textarea name="address" rows="5" cols="30"></textarea><br />
		</td></tr>
	</table>
	<!-- bottom -->
	<div align="center" id="abtn" style="display:none;">
		<input type="button" value="Add" id="add_btn"> 
		<input type="button" value="Cancel" id="cancel_btn">
	</div>
	<div align="center" id="ebtn" style="display:none;">
		<input type="button" value="Update" id="update_btn"> 
		<input type="button" value="Restore" id="edit_btn"> 
		<input type="button" value="Close" id="close_btn">
	</div>
</form>
</div></div></div>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
SALES_AGENT_MODULE.initialize();
SEARCH_LEADER_DIALOG.initialize();
</script>

{include file=footer.tpl}
