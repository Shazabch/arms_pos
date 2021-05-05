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
		
		curtain(true);
		center_div($('div_quota_history_dialog').show());
		
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

<h1>{$PAGE_TITLE}</h1>

<!-- Quota History DIALOG -->
<div id="div_quota_history_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:400px;height:300px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_quota_history_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;" id="span_mnm_choose_item_type_dialog_header">Quota History</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_quota_history_dialog_content" style="padding:2px;height:270px;overflow:auto;">

	</div>
</div>
<!-- End of Quota History DIALOG -->
	
<ul>
	<li> Please note after you update monthly quota, you will need to wait the cron to auto update those related member quota.</li>
</ul>

<form name="f_a" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="a" value="ajax_update_staff_quota" />
	
	<table class="report_table" style="background-color:#fff;">
		<tr class="header">
			<th>Staff Type</th>
			<th>Monthly Quota</th>
		</tr>
		{foreach from=$config.membership_staff_type key=v item=mem_label}
			<tr>
				<td>{$mem_label}</td>
				<td align="center">
					<input type="text" size="5" name="quota_value[{$v}]" value="{$data.data.$v.quota_value}" style="text-align:right;" />
					<img src="/ui/icons/zoom.png" title="View History" align="absmdiddle" class="clickable" onClick="STAFF_SETUP_QUOTA.view_history_clicked('{$v}');" />
				</td>
			</tr>
		{/foreach}
	</table>
	
	<p id="p_action_button">
		<input type="button" value="Update" onClick="STAFF_SETUP_QUOTA.update_clicked();" id="btn_update_form" />
		<span id="span_update_form_loading" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Processing...</span>
	</p>
</form>

<script type="text/javascript">
	STAFF_SETUP_QUOTA.initialize();
</script>
{include file="footer.tpl"}
