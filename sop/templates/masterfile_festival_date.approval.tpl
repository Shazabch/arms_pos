{include file='header.tpl'}

{literal}
<style>

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var FESTIVAL_SHEET_APPROVAL_MAIN_MODULE = {
    ajax_loading_approval: undefined,    // ajax object
    current_selected_year: 0,
    festival_sheet_dropdown_ele: undefined,
	initialize: function(){
        // user select festival sheet from dropdown
	    this.festival_sheet_dropdown_ele = $('#div_festival_sheet_dropdown_selection');
		$(this.festival_sheet_dropdown_ele).find('li[id^="li_festival_sheet-"]').live('click', function(){
		    var year = $(this).attr('id').split('-')[1]; // getfestival sheet year
			FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.load_festival_sheet(year); // call function to load data
		});
		
		this.pick_next_festival_sheet();    // automatically select the next
		
		// user click on approve
		$('#btn_approve_festival_sheet').live('click', function(){
            FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.submit_approval('approve');   // call function
		});

		// user click on reject
		$('#btn_reject_festival_sheet').live('click', function(){
            FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.submit_approval('reject');   // call function
		});
	},
	// function to load all festival date list
	load_festival_sheet: function(year){
        if(!year)  return false;   // no year given

        // copy the row sample into span
        $('#span_selected_name').html($('#li_festival_sheet-'+year).html());

        // construct params
        var params = {
			a: 'load_festival_date_list',
			year: year
		};

		// show loading icon
		$('#div_approval_data').html(_loading_);

        if(this.ajax_loading_approval){ // if got another ajax ongoing
			try{
				this.ajax_loading_approval.abort(); // try to abort the previous ajax
			}catch(ex){

			}
		}

		// call ajax to show data
		this.ajax_loading_approval = $.ajax({
			url: phpself,
			data: params,
			success: function(data){
                $('#div_approval_data').html(data);
                FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.festival_date_list_load_success(year);
			},
			completed: function(xhr){
                FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.ajax_loading_approval = undefined;   // clear the ajax object
                // call function when load finish (no matter success or failed)
                FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.current_selected_year = 0; // clear the selected year
			}
		});
	},
	// function when approval list success load
	festival_date_list_load_success: function(year){
        this.current_selected_year = year;    // store current selection year
		$('#div_approval_data p.btn_area').buttonset(); // generate button
	},
	// function to auto select next festival sheet
	pick_next_festival_sheet: function(){
        // get the current selected li
		var selected_li = $('#li_festival_sheet-'+this.current_selected_year).get(0);
		var next_li = undefined;

		if(selected_li){    // got current selected li
            next_li = $(selected_li).next('li[id^="li_festival_sheet-"]').get(0); // get the next li
		}

		$('#div_approval_data').html('');   // clear the page
		$('#span_selected_name').html('-');

		if(next_li){	// if got next <id>
            var year = $(next_li).attr('id').split('-')[1]; // get year
			FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.load_festival_sheet(year); // call function to load data
			return;
		}else{
			// load the first
			var first_li = $(this.festival_sheet_dropdown_ele).find('li[id^="li_festival_sheet-"]:first').get(0);
			if(first_li){
                var year = $(first_li).attr('id').split('-')[1]; // get year
                if(year!=this.current_selected_year){
                    FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.load_festival_sheet(year); // call function to load data
                    return;
				}
			}
		}

		// if no more approval, show dialog and return
		custom_alert.info('You have finish all the approval job.', 'No Approval Found', function(){
			window.location = '/sop/';
		});
	},
	submit_approval: function(action){
		if(!action){
			custom_alert.alert('Invalid Action.');
			return false;
		}
		
		// get current selected year
		var year = this.current_selected_year;  

		if(!year)  return false;   // no year is selected

		var comment = '';
		if(action=='reject' || action=='terminate'){
			comment = $.trim(prompt('Please enter reason:'));

			if(!comment)    return false;
		}

		if(!confirm('Are you sure?'))   return false;

		// construct params
		var params = {
			'a': 'save_festival_sheet_approval',
			year: year,
			action: action,
			comment: comment
		};

		// prompt process in progress popup
        custom_alert.prompt_in_progress(LOADING+' In progress, please wait...');

        $.post(phpself, params, function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok']){ // got ok return mean save success
                    custom_alert.close();  // close the notify dialog
                    FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.pick_next_festival_sheet(); // auto select the next
                    // remove the current selected <li>
                    $('#li_festival_sheet-'+year).remove();
	                return;
				}else{  // save failed
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

{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>

<div style="float:left;padding:4px;"><b>Select one to approve</b></div>

<div style="float:left" class="div_dropdown_selection" id="div_festival_sheet_dropdown_selection">
    <span id="span_selected_name">-</span>
		<ul>
		{foreach from=$festival_sheet_list item=r}
			{strip}
				<li id="li_festival_sheet-{$r.year}">
				 	Year: {$r.year} (Created: {$r.user_name})
			 	</li>
			{/strip}
		{/foreach}
		</ul>
	</span>
</div>

<br />
<br style="clear:both">

<hr size="2" noshade="">
<div id="div_approval_data"></div>

{include file='footer.tpl'}

<script>
{literal}
	$(function(){
        FESTIVAL_SHEET_APPROVAL_MAIN_MODULE.initialize();   // initial the module
	});
{/literal}
</script>
