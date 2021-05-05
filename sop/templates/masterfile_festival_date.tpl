{include file='header.tpl'}
{literal}
<style>

</style>
{/literal}


<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var FESTIVAL_DATE_MAIN_MODULE = {
    tabnum: 1,
    festival_tabs: undefined,
	initialize: function(){
	    // initial tabs
	    this.festival_tabs = $('#div_festival_list').tabs({
            select: function(event, ui) {
				FESTIVAL_DATE_MAIN_MODULE.tabnum = $(ui.tab).attr('href').split('-')[1];  // assign the selected tabnum
			    FESTIVAL_DATE_MAIN_MODULE.pagenum = 1; // reset to first page
				FESTIVAL_DATE_MAIN_MODULE.refresh_tab();   // triggle the refresh tab function
            }
		});
		
		if(this.get_tab_index() == 0)    this.refresh_tab();    // if already selecting first tab, load the content
		else    this.festival_tabs.tabs('select', 0);   // else, select the first tab, then it will auto reload the content
	    
	    // initial add festival dialog module
	    FESTIVAL_DATE_ADD_DIALOG_MODULE.initialize($('#div_add_festival_sheet'));
	    
	    // event when user click add new festival sheet
		$('#a_add_new_festival_sheet').live('click', function(){
            FESTIVAL_DATE_MAIN_MODULE.choose_new_festival_sheet();
		});
		
		// event when user click delete festival
		$('#div_festival_list a.delete_festival').live('click', function(){
			var year = FESTIVAL_DATE_MAIN_MODULE.get_year_by_ele(this);
			FESTIVAL_DATE_MAIN_MODULE.delete_festival(year);
		});
	},
	// function to handle when user click add new festival sheet
	choose_new_festival_sheet: function(){
        FESTIVAL_DATE_ADD_DIALOG_MODULE.open();
	},
	// function to get current selected tab
	get_tab_index: function(){
		return int(this.festival_tabs.tabs('option', 'selected'));  // return the select tab index. e.g: 0, 1, 2
	},
	// function to refresh festival list
	refresh_tab: function(callback){
        var params = {
            a: 'load_festival_list',
            t: this.tabnum
		};

		//$('#div_loading_marketing_plan_list').show();    // show loading icon
		$('#div_tabs-'+this.tabnum).load(phpself, params, function(){   // call ajax to reload the content

            // additional callback
            if(callback)	callback();
		});
	},
	get_year_by_ele: function(ele){
        if(!ele)    return 0;
		var parent_ele = $(ele).get(0);

		while(parent_ele){    // loop parebt until it found the row
		    if($.trim(parent_ele.tagName).toLowerCase()=='tr'){
                if($(parent_ele).hasClass('is_year_row')){    // found the row
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(!parent_ele) return 0;

		var year = int($(parent_ele).attr('id').split('-')[1]);
		return year;
	},
	delete_festival: function(year){
		if(!year)   return false;
		
		if(!confirm('Are you sure?'))   return false;
		
		// construct params
		var params = {
			a: 'delete_festival',
			year: year
		};
		
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Deleting in progress, please wait...');
		// get the row element
		var tr_festival_row = $('#tr_festival_row-'+year).get(0);
		
		$.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
	                custom_alert.close();  // close the notify dialog

	                // animate the row to show delete success
	                if(tr_festival_row){
					    blink_element(tr_festival_row, '', function(){
                            $(tr_festival_row).remove();   // remove the row
							FESTIVAL_DATE_MAIN_MODULE.refresh_tab();   // refresh the container
						});    // blink color
					}
	                return;
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur');
		});
	}
};

var FESTIVAL_DATE_ADD_DIALOG_MODULE = {
    default_title: 'Add New Festival Sheet',
	dialog: undefined,
	form_element: undefined,
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 300, // set the width
			width:400,
			minHeight: 150,    // set the height
			height:150,
			closeOnEscape: false,    // whether user press escape can close
			hide: 'fade',   // the effect when hide, can be slide or others
			show: 'fade',   // same as hide effect
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true,   // disable the popup from resize
			stack: true,
			title: this.default_title,
			buttons: {  // create a set of buttons under button areas
				"Add": function() {
                    FESTIVAL_DATE_ADD_DIALOG_MODULE.btn_save_clicked();
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
		
		// assign form
		this.form_element = document.f_add_festival;
		if(!this.form_element){
			custom_alert.alert('Festival Dialog cannot be load.');
			return false;
		}
		
		$(this.form_element['year']).keydown(function(event){
            FESTIVAL_DATE_ADD_DIALOG_MODULE.check_year_keydown(event);
		});
		return this;
	},
	close: function(){
        $(this.dialog).dialog("close");
	},
	open: function(){
		$(this.dialog).dialog('open');
	},
	// function to check form
	check_form: function(){
	    // min year 2010
	    var year_ele = $(this.form_element['year']).get(0);
	    miz(year_ele);
		if($(year_ele).val() < 2010){
			custom_alert.alert('Year cannot less than 2010.','Save Error',function(){
                $(year_ele).select();
			});
			return false;
		}
		
		// max year 2099
		if($(year_ele).val() > 2099){
			custom_alert.alert('Year must start with 20xx.','Save Error',function(){
                $(year_ele).select();
			});
			return false;
		}
		return true;
	},
	// function when user click save
	btn_save_clicked: function(){
	    // validate form
		if(!this.check_form())  return false;
		
		if(!confirm('Are you sure?'))   return false;
		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' Saving in progress, please wait...');

        // construct params
        var year_ele = $(this.form_element['year']);
        var year = year_ele.val();
        var params = $(this.form_element).serialize();
        
        $.post(phpself, params, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object
                if(ret['ok']){ // got id return mean save success
                    FESTIVAL_DATE_MAIN_MODULE.refresh_tab(function(){
						var tr_festival_row = $('#tr_festival_row-'+year).get(0);
						if(tr_festival_row){
						    blink_element(tr_festival_row);    // blink color
						}
					});
	                custom_alert.close();  // close the notify dialog
	                FESTIVAL_DATE_ADD_DIALOG_MODULE.close();  // close the dialog
	                return;
				}else{  // save failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    custom_alert.alert(err_msg, 'Error occur', function(){
                year_ele.select();
			});
		});
	},
	// function to handle when user press key
	check_year_keydown: function(event){
		var kc = event.which;
		
		if(kc==13){ // enter
			this.btn_save_clicked();    // trigger save event
		}
	}
};
{/literal}
</script>

<div id="div_add_festival_sheet" style="display:none;">
	<form name="f_add_festival" onSubmit="return false;">
	    <input type="hidden" name="a" value="add_new_festival_sheet" />
		<br />
	    <b>Please enter New Festival Year: </b>&nbsp;
	    <input type="text" name="year" size="5" onChange="miz(this);" maxlength="4" onFocus="this.select();" />
	</form>
</div>

<h1>{$PAGE_TITLE}</h1>

<ul>
	<li><img src="/ui/new.png" align="absmiddle" /> <a href="javascript:void(0);" id="a_add_new_festival_sheet">Add New Festival Sheet</a></li>
</ul>

<div id="div_festival_list">
	<ul class="ul_tab_row">
		<li><a href="#div_tabs-1">Saved</a></li>
		<li><a href="#div_tabs-2">Waiting for Approval </a></li>
		<li><a href="#div_tabs-3">Rejected</a></li>
		<li><a href="#div_tabs-4">Approved</a></li>
	</ul>
	<div id="div_tabs-1"></div>
	<div id="div_tabs-2"></div>
	<div id="div_tabs-3"></div>
	<div id="div_tabs-4"></div>
</div>

{include file='footer.tpl'}

<script>
	{literal}
	$(function(){
        FESTIVAL_DATE_MAIN_MODULE.initialize(); // initiali module
	});
	{/literal}
</script>
