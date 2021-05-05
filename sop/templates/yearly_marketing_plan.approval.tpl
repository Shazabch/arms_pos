{include file='header.tpl'}

{literal}
<style>

</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE = {
    ajax_load_marketing_plan: undefined,    // ajax object
    current_selected_marketing_plan_id: 0,
	initialize: function(){
	    // user select marketing plan from dropdown
	    this.marketing_plan_dropdown = $('#div_marketing_plan_dropdown_selection');
		$(this.marketing_plan_dropdown).find('li[id^="li_marketing_plan_title-"]').live('click', function(){
		    var marketing_plan_id = $(this).attr('id').split('-')[1]; // get marketing plan id
			SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.load_marketing_plan(marketing_plan_id); // call function to load data
		});
		
		this.pick_next_marketing_plan();    // automatically select the next
		
		// user click on approve
		$('#btn_approve_marketing_plan').live('click', function(){
            SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.submit_marketing_plan('approve');   // call function
		});
		
		// user click on reject
		$('#btn_reject_marketing_plan').live('click', function(){
            SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.submit_marketing_plan('reject');   // call function
		});
		
		// user click on terminate
		$('#btn_terminate_marketing_plan').live('click', function(){
            SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.submit_marketing_plan('terminate');   // call function
		});
	},
	pick_next_marketing_plan: function(){   // function to auto pick next
	    // get the current selected li
		var selected_li = $('#li_marketing_plan_title-'+this.current_selected_marketing_plan_id).get(0);
		var next_li = undefined;
		
		if(selected_li){    // get current selected li
            next_li = $(selected_li).next('li[id^="li_marketing_plan_title-"]').get(0); // get the next li
		}
		
		$('#div_approval_data').html('');   // clear the page
		$('#span_selected_name').html('-');
		
		if(next_li){	// if got next <id>
            var marketing_plan_id = $(next_li).attr('id').split('-')[1]; // get marketing plan id
			SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.load_marketing_plan(marketing_plan_id); // call function to load data
			return;
		}else{
			// load the first
			var first_li = $(this.marketing_plan_dropdown).find('li[id^="li_marketing_plan_title-"]:first').get(0);
			if(first_li){
                var marketing_plan_id = $(first_li).attr('id').split('-')[1]; // get marketing plan id
                if(marketing_plan_id!=this.current_selected_marketing_plan_id){
                    SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.load_marketing_plan(marketing_plan_id); // call function to load data
                    return;
				}
			}
		}
		
		// if no more approval, show dialog and return
		custom_alert.info('You have finish all the approval job.', 'No Approval Found', function(){
			window.location = '/sop/';
		});
	},
    load_marketing_plan: function(marketing_plan_id){   // function to load approval data
        if(!marketing_plan_id)  return false;   // no ID
        
        // copy the row sample into span
        $('#span_selected_name').html($('#li_marketing_plan_title-'+marketing_plan_id).html());
        
        // construct params
        var params = {
			a: 'load_marketing_plan_details',
			marketing_plan_id: marketing_plan_id
		};
		
		// show loading icon
		$('#div_approval_data').html(_loading_);

        if(this.ajax_load_marketing_plan){ // if got another ajax ongoing
			try{
				this.ajax_load_marketing_plan.abort(); // try to abort the previous ajax
			}catch(ex){

			}
		}
		
		// call ajax to show data
		this.ajax_load_marketing_plan = $.ajax({
			url: phpself,
			data: params,
			success: function(data){
                $('#div_approval_data').html(data);
                SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.marketing_plan_details_load_success(marketing_plan_id);
			},
			completed: function(xhr){
                SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.ajax_load_marketing_plan = undefined;   // clear the ajax object
                // call function when load finish (no matter success or failed)
                SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.current_selected_marketing_plan_id = 0; // clear the selected id
			}
		});
	},
	marketing_plan_details_load_success: function(marketing_plan_id){
		this.current_selected_marketing_plan_id = marketing_plan_id;    // store current selection ID
		$('#div_approval_data p.btn_area').buttonset(); // generate button

	},
	submit_marketing_plan: function(action){
		if(!action){
			custom_alert.alert('Invalid Action.');
			return false;
		}
		var marketing_plan_id = this.current_selected_marketing_plan_id;
		
		if(!marketing_plan_id)  return false;   // no ID
		
		var comment = '';
		if(action=='reject' || action=='terminate'){
			comment = $.trim(prompt('Please enter reason:'));

			if(!comment)    return false;
		}
		
		if(!confirm('Are you sure?'))   return false;
		
		// construct params
		var params = {
			'a': 'save_marketing_plan',
			marketing_plan_id: marketing_plan_id,
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
                    SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.pick_next_marketing_plan(); // auto select the next
                    $('#li_marketing_plan_title-'+marketing_plan_id).remove();
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

<div style="float:left" class="div_dropdown_selection" id="div_marketing_plan_dropdown_selection">
    <span id="span_selected_name">-</span>
		<ul>
		{foreach from=$marketing_plan_list item=r}
			{strip}
				<li title="{$r.title}" id="li_marketing_plan_title-{$r.id}">
				 &nbsp;{$r.title} &nbsp;(Date: {$r.date_from} to {$r.date_to}, Created: {$r.user_name})
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
        SOP_MARKETING_PLAN_APPROVAL_MAIN_MODULE.initialize();   // initial the module
	});
{/literal}
</script>
