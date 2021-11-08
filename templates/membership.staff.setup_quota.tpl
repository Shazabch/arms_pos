{include file="header.tpl"}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var STAFF_SETUP_QUOTA = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		new Draggable('div_quota_history_dialog',{ handle: 'div_quota_history_dialog_header'});	
	},
	// function when user click update
	update_clicked: function(){
		
		var params = $(this.f).serialize();
		$$('#p_action_button input').invoke('disable');
		$('span_update_form_loading').show();
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $$('#p_action_button input').invoke('enable');
			    $('span_update_form_loading').hide();
			     				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Update Successfully');
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
			}
		});
	},
	// function when user click view history
	view_history_clicked: function(staff_type){
		if(!staff_type)	return false;
		
		var params = {
			'a': 'ajax_show_quota_history',
			'staff_type': staff_type
		};
		
		$('div_quota_history_dialog_content').update(_loading_);
		
		//curtain(true);
		//center_div($('div_quota_history_dialog').show());
		jQuery('#div_quota_history_dialog').modal('show');
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $('div_quota_history_dialog_content').update('');
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_quota_history_dialog_content').update(ret['html']);	
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
			}
		});
	}
};
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<!-- Quota History DIALOG -->
<div class="modal" id="div_quota_history_dialog">
    <div class="modal-dialog  modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header bg-danger" id="div_quota_history_dialog_header">
                <h6 class="modal-title text-center text-white" id="span_mnm_choose_item_type_dialog_header">Quota History</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
				<div style="clear:both;"></div>
			</div>
            <div class="modal-body">
                <div id="div_quota_history_dialog_content" style="padding:2px;height:270px;overflow:auto;"></div>
            </div>
        </div>
    </div>
</div>

<!-- End of Quota History DIALOG -->
	
<div class="alert alert-primary mx-3 rounded">
	<ul style="list-style-type: none;">
		<li> Please note after you update monthly quota, you will need to wait the cron to auto update those related member quota.</li>
	</ul>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" onSubmit="return false;" class="stdframe">
			<input type="hidden" name="a" value="ajax_update_staff_quota" />
			
			<table class="report_table" >
				<thead class="bg-gray-100">
					<tr class="header">
						<th>Staff Type</th>
						<th>Monthly Quota</th>
					</tr>
				</thead>
				{foreach from=$config.membership_staff_type key=v item=mem_label}
					<tboody class="fs-08">
						<tr>
							<td>{$mem_label}</td>
							<td align="center">
								<div class="form-inline">
								&nbsp;	<input type="text" class="form-control" size="5" name="quota_value[{$v}]" value="{$data.data.$v.quota_value}" style="text-align:right;" />
								&nbsp;<img src="/ui/icons/zoom.png" title="View History" align="absmdiddle" class="clickable" onClick="STAFF_SETUP_QUOTA.view_history_clicked('{$v}');" />
								</div>
							</td>
						</tr>
					</tboody>
				{/foreach}
			</table>
			
			<p id="p_action_button">
				<input type="button" class="btn btn-primary mt-2 " value="Update" onClick="STAFF_SETUP_QUOTA.update_clicked();" id="btn_update_form" />
				<span id="span_update_form_loading" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Processing...</span>
			</p>
		</form>
	</div>
</div>

<script type="text/javascript">
	STAFF_SETUP_QUOTA.initialize();
</script>
{include file="footer.tpl"}
