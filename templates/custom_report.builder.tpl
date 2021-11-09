{*
4/10/2020 10:10 AM William
- Enhanced to add activate and deactivate to custom report.

6/17/2020 9:15 AM William
- Enhanced to added "Age Group Settings".
*}
{include file="header.tpl"}
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>

<style>
{literal}
#div_age_group_setting{
	border: 1px solid #ccc;
	border-radius: 5px;
	background: #fff;
	width: 480px;
	padding: 11px;
	position: absolute;
	z-index: 100000;
	max-height:600px;
	overflow-y: auto;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var JQ= {};
JQ = jQuery.noConflict(true);

var REPORT_BUILDER = {
	active_clicked: function(id, active){
		if(active) var status_desc = "activate";
		else var status_desc = "deactivate";
	
		if(!confirm("Are you sure want to "+status_desc+" the selected report?")) return;
		
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		var params_str = {
			'a': 'ajax_active_changed',
			'id': id,
			'active': active
		};
		JQ.post(phpself, params_str, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					location.reload(true);
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			Element.hide('wait_popup');
			curtain(false,'curtain2');
			if(err_msg) alert(err_msg);
		});
	},
	edit_age_group_clicked: function(){
		var params = {
			'a': 'load_age_group'
		}
		
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_age_group_setting').update(ret['html']);
						center_div('div_age_group_setting');
						curtain(true);
						Element.show('div_age_group_setting');
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
			}
		});
	},
	update_age_group_clicked: function(){
		var duplicate_count = {};
		var age_group_range_age_list = document.f_r['age_group[range][age][]'];
		var age_group_range_desc_list = document.f_r['age_group[range][desc][]'];
		if(typeof(age_group_range_age_list) !='undefined' && typeof(age_group_range_desc_list) !='undefined'){
			for(var i=0; i< age_group_range_age_list.length;i++){
				var age_val = age_group_range_age_list[i].value;
				if(!duplicate_count[age_val]) duplicate_count[age_val] = 0;
				if(age_val == '' || age_val == 0){
					alert('Invalid age range.');
					return false;
				}else{
					duplicate_count[age_val] += 1;
					if(duplicate_count[age_val] > 1){
						alert('Age "'+age_val+'" already exists.');
						return false;
					}
				}
				
				var desc_val = age_group_range_desc_list[i].value;
				if(!duplicate_count[desc_val]) duplicate_count[desc_val] = 0;
				if(desc_val == ''){
					alert('Please fill in the Description field.');
					return false;
				}else{
					duplicate_count[desc_val] += 1;
					if(duplicate_count[desc_val] > 1){
						alert('Description "'+desc_val+'" already exists.');
						return false;
					}
				}
			}
		}
		var tmp_params = {
			'a': 'ajax_update_age_group',
		};
		var params_str = JQ(document.f_r).serialize()+'&'+JQ.param(tmp_params);
		
		JQ('#btn_save').attr('disabled', true);
		center_div('wait_popup');
		curtain(true,'curtain2');
		Element.show('wait_popup');
		
		JQ.post(phpself, params_str, function(data){
		    var ret = {};
		    var err_msg = '';

		    try{
                ret = JQ.parseJSON(data); // try decode json object
                if(ret['ok']){ // got 'ok' return mean save success
					alert("Update Success");
					curtain(false);
					Element.hide('div_age_group_setting');
				}else{  // failed
                    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
                    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}
			
		    // prompt the error
			Element.hide('wait_popup');
			curtain(false,'curtain2');
			JQ('#btn_save').attr('disabled', false);
			if(err_msg) alert(err_msg);
		});
	},
	add_new_range: function(){
		JQ("#div_age_range_list").append('<div style="margin-bottom: 3px;">Less than or equal (<=) <input name="age_group[range][age][]"  onChange="this.value=int(round(this.value, 3))" type="text" maxlength="3" size="3" /> Description <input name="age_group[range][desc][]" type="text" /> <a href="#" onClick="REPORT_BUILDER.remove_range(this.parentNode)"><img src="ui/icons/cancel.png" /></a></div>');
	},
	remove_range: function(obj){
		obj.remove();
	}
};
function curtain_clicked(){
	curtain(false);
	Element.hide('div_age_group_setting');
}
{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if isset($smarty.request.t)}
	<div class="card mx-3">
		<div class="card-body">
			{if $smarty.request.t eq 'save'}
		<img src="/ui/approved.png" align="absmiddle"> Report ID#{$smarty.request.id} saved.<br>
	{elseif $smarty.request.t eq 'delete'}
		<img src="/ui/cancel.png" align="absmiddle"> Report ID#{$smarty.request.id} was deleted.<br>
	{/if}
	<br />
		</div>
	</div>
{/if}

<br/>
<div class="card mx-3">
	<div class="card-body">
		<p><a href="?a=open"><img src="ui/icons/page_add.png" align="absmiddle" border="0" /> Create New Report</a></p>
<p><a href="#" onclick="REPORT_BUILDER.edit_age_group_clicked();"><img src="ui/ed.png" align="absmiddle" border="0" /> Age Group Settings</a><p>

	</div>
</div>
<div id="wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
<p align="center">
	Please wait..
	<br /><br />
	<img src="ui/clock.gif" border="0" />
</p>
</div>

<div id="div_age_group_setting" style="display:none;">
{include file="custom_report.age_group_settings.tpl"}
</div>
<div id="div_report_table">
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100" style="height: 25px;">
					<tr class="header">
						<th width="40">&nbsp;&nbsp;</th>
						<th>Report Title</th>
						<th>Group</th>
						<th>Owner</th>
						<th>Added</th>
						<th>Last Update</th>
					</tr>
				</thead>
				
				{foreach from=$report_list item=r}
					<tbody class="fs-08">
						<tr>
							<td>
								{if $r.control_type eq '2'}
									<a href="?a=open&id={$r.id}"><img src="ui/ed.png" border="0" title="Edit" /></a>
									{if $r.active}
										<a href="javascript:void(REPORT_BUILDER.active_clicked({$r.id}, 0));"><img src="ui/deact.png" title="Deactivate this Report" border="0"></a>
									{else}
										<a href="javascript:void(REPORT_BUILDER.active_clicked({$r.id}, 1));"><img src="ui/act.png" title="Activate this Report" border="0"></a>
										<br /><span class="small">(inactive)</span>
									{/if}
								{else}
									<a href="?a=view&id={$r.id}"><img src="ui/view.png" border="0" title="View" /></a>
								{/if}
							</td>
							<td>{$r.report_title|default:'-'}</td>
							<td>{$r.report_group|default:'-'}</td>
							<td>{$r.username|default:'-'}</td>
							<td align="center">{$r.added}</td>
							<td align="center">{$r.last_update}</td>
						</tr>
					</tbody>
				{foreachelse}
					<tr>
						<td colspan="6">* No Data *</td>
					</tr>
				{/foreach}
			</table>
		</div>
	</div>
</div>
</div>
{include file="footer.tpl"}