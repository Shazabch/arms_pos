{include file='header.tpl'}

<script>
var YMP_HQ_EDIT = '{$YMP_HQ_EDIT}';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MARKETING_PLAN = {
	tabnum: 1,
 	pagenum: 1,
	main_tabs: undefined,
	marketing_plan_dialog: undefined,
	initialize: function(){
	    // initial the tab screen and tab button events
		this.main_tabs = $('#div_tabs').tabs({
			select: function(event, ui) {
				MARKETING_PLAN.tabnum = $(ui.tab).attr('tabnum');  // assign the selected tabnum
			    MARKETING_PLAN.pagenum = 1; // reset to first page
				MARKETING_PLAN.refresh_tab();   // triggle the refresh tab function
            }
		});
		if(this.get_tab_index() == 0)    this.refresh_tab();    // if already selecting first tab, load the content
		else    this.main_tabs.tabs('select', 0);   // else, select the first tab, then it will auto reload the content
		
		MARKETING_PLAN_DIALOG_MODULE.initialize($('#div_marketing_plan_dialog'));   // initialize marketing plan dialog
		// initialize the click event for all open marketing plan links
		$('a.open_marketing_plan').live('click', function(){
		    MARKETING_PLAN_DIALOG_MODULE.open($(this).attr('marketing_plan_id'));
			return false;
		});
		
		// initialize the click event for all delete marketing plan links
		$('a.delete_marketing_plan').live('click', function(){
            MARKETING_PLAN.delete_plan($(this).attr('marketing_plan_id'));
            return false;
		});
		
		// page change event
		$('select.sel_page_dropdown').live('change', function(){  // this = <select>
            MARKETING_PLAN.refresh_tab();   // refresh the container
		});
	},
	get_tab_index: function(){
		return int(this.main_tabs.tabs('option', 'selected'));  // return the select tab index. e.g: 0, 1, 2
	},
    refresh_tab: function(callback){    // events to handle when user change tab or change page
        var page_sel = $('#div_tabs-'+this.tabnum+' select.sel_page_dropdown').get(0);  // check whether got page selection
        if(page_sel){
            this.pagenum = $(page_sel).val(); // update the pagenum
		}else   this.pagenum = 1;   // if no page selection, always select first page

        var params = {
            a: 'load_marketing_plan_list',
            t: this.tabnum,
            p: this.pagenum
		};
		
		//$('#div_loading_marketing_plan_list').show();    // show loading icon
		$('#div_tabs-'+this.tabnum).load(phpself, params, function(){   // call ajax to reload the content
            $('#div_loading_marketing_plan_list').hide();    // hide loading icon
            $(this).find('button').button();  // customize button
            
            // additional callback
            if(callback)	callback();
		});  
	},
	delete_plan: function(id){
		if(!confirm('Are you sure?'))  return false;
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Deleting in progress, please wait...');
		// get the row element
		var tr_marketing_plan = $('#tr_marketing_plan-'+id).get(0);
        
        $.post(phpself+'?a=delete_marketing_plan&id='+id, {}, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                custom_alert.close();  // close the notify dialog
	                
	                // animate the row to show delete success
	                if(tr_marketing_plan){
			            $(tr_marketing_plan).animate({'color':'red'}, 'slow', function(){   // show red color
							$(this).animate({'color':'black'}, 'slow', function(){  // show black color
								$(this).remove();   // remove the row
								MARKETING_PLAN.refresh_tab();   // refresh the container
							});
						});
					}
	                return;
				}else{  // save failed
                    err_msg = ret['failed_reason'];
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
}

var MARKETING_PLAN_DIALOG_MODULE = {
	dialog: undefined,
	default_title: 'Marketing Plan',
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 600, // set the width
			width:700,
			minHeight: 300,    // set the height
			height:400,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Save": function() {
                    MARKETING_PLAN_DIALOG_MODULE.btn_save_clicked();
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			},
			open: function(event, ui) {
			    //MARKETING_PLAN_DIALOG_MODULE.reset();   // reset promo module
			},
			beforeClose: function(event, ui){   // triggle when popup is attemping to close
	            // nothing to do?
			}
		});
		return this;
	},
	reset: function(){
        $(this.dialog).dialog('option', 'title', this.default_title+' - New')   // reset title
	},
	open: function(id){
	    this.reset();
	    if(id)  $(this.dialog).dialog('option', 'title', this.default_title+' - Edit'); // change title to "edit", if it is existing data
	    
		$(this.dialog).html(_loading_)  // show loading icon
		            .load(phpself+'?a=open_marketing_plan&id='+id, this.dialog_load_finish)  // call ajax to load data
					.dialog('open');    // show dialog
	},
	dialog_load_finish: function(){    // initialize event when dialog load finish (this = ajax)
	    if(!document.f_marketing_plan){
            custom_alert.alert('Marketing module cannot be load.', 'Error occur');
            return false;
		}
        $(document.f_marketing_plan['date_from']).datepicker();
		$(document.f_marketing_plan['date_to']).datepicker();
	},
	check_form: function(){ // function to validate form data
	    if(!document.f_marketing_plan){ // <form> element not exists
			custom_alert.alert('Un-expected error occur. form element does not exists.');
			return false;
		}
		
		// check all required input field
		if(!check_required_input(document.f_marketing_plan))	return false;

		return true;
	},
	btn_save_clicked: function(){
		if(!this.check_form())  return false;   // validate form
		
		if(!confirm('Are you sure?'))   return false;
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Saving in progress, please wait...');
        
        // construct params
        var params = $(document.f_marketing_plan).serialize();
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['id']){ // got id return mean save success
                    /*if(ret['html']){    // got html return
						MARKETING_PLAN.replace_marketing_plan_row(ret['id'], ret['html']);   // update the marketing plan row
					}*/
					//if(MARKETING_PLAN.pagenum==1){  // only refresh if currently selecting first page
					    // reload the tab container
                        MARKETING_PLAN.refresh_tab(function(){
							var marketing_plan_row = $('#tr_marketing_plan-'+ret['id']).get(0);
							if(marketing_plan_row){
								$(marketing_plan_row).animate({'color':'red'}, 'slow', function(){
									$(this).animate({'color':'black'}, 'slow');
								});
							}
						});   
					//}
	                custom_alert.close();  // close the notify dialog
	                $(MARKETING_PLAN_DIALOG_MODULE.dialog).dialog('close');  // close the marketing plan dialog
	                return;
				}else{  // save failed
                    err_msg = ret['failed_reason'];
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
}
{/literal}
</script>

<div id="div_marketing_plan_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE}</h1>

<ul>
	{if $YMP_HQ_EDIT}
		<li><img src="/ui/new.png" align="absmiddle" /> <a href="?a=open_marketing_plan" class="open_marketing_plan">Add New Marketing Plan</a></li>
	{/if}
</ul>

<div id="div_tabs">
	<ul class="ul_tab_row">
		<li><a href="#div_tabs-1" tabnum="1">Saved</a></li>
		<li><a href="#div_tabs-2" tabnum="2">Waiting for Approval </a></li>
		<li><a href="#div_tabs-3" tabnum="3">Rejected</a></li>
		<li><a href="#div_tabs-4" tabnum="4">Terminated</a></li>
		<li><a href="#div_tabs-5" tabnum="5">Approved</a></li>
		<li><div id="div_loading_marketing_plan_list" class="loading_notice" style="display:none;"><img src="/ui/clock.gif" align="absbottom" /> Loading...</div></li>
	</ul>
	<div id="div_tabs-1"></div>
	<div id="div_tabs-2"></div>
	<div id="div_tabs-3"></div>
	<div id="div_tabs-4"></div>
	<div id="div_tabs-5"></div>
</div>

<script>
{literal}
	$(function(){
		MARKETING_PLAN.initialize();   // intial the module
	});
{/literal}
</script>
{include file='footer.tpl'}
