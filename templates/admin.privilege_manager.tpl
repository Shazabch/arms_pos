{*
9/5/2011 4:10:16 PM Andy
- Fix hover color does not appear for privilege under 'OTHERS'.

9/14/2011 9:57:30 AM Andy
- Add "preset setting" for privilege manager.

3/19/2012 2:44:37 PM Andy
- Remove "POS" and "CC" from group.

4/5/2012 10:53:10 AM aLEX
- Add "STOCK_TAKE" for default tick YES

10/5/2012 3:58 PM Andy
- Add remark "Skip Update" to privilege manager for those privilege has been skip by config.

11/20/2012 3:48 PM Andy
- Add the module can preset single privilege instead of only group privilege.
- Add privilege "CATEGORY_DISCOUNT_EDIT" and "MEMBER_POINT_REWARD_EDIT" to default turn on for ARMS PREMIUM.

8/5/2015 11:43 AM Andy
- Add default module 'DN' for premium and go.

10/19/2015 9:47 AM Andy
- Add default module 'CN' for premium and go.

10/27/2016 11:23 AM Andy
- Fixed javascript error when change preset settings.
- Added 'ALLOW_IMPORT_SKU','ALLOW_IMPORT_VENDOR','ALLOW_IMPORT_BRAND' and 'ALLOW_IMPORT_UOM' into premium & go preset settings.

11/9/2016 3:33 PM Andy
- Enhanced to add 'ADMIN' into premium, go & consign preset settings.

10/30/2017 4:54 PM Justin
- Enhanced to default choose "Yes" for Counter Management when choosing ARMS GO or PREMIUM.
*}

{include file='header.tpl'}

<style>
{literal}
.privilege_skipped{
	color: red;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var privilege = {
	'premium': ['ADJ','DO','GRA','GRN','MEMBERSHIP','PROMOTION','PO','FRONTEND','USERS','MASTERFILE_COMMON','MASTERFILE_RETAIL','PIVOT','REPORTS','FM','STOCK_TAKE','DN','CN','ADMIN','COUNTER'],
	'go': ['ADJ','DO','GRA','GRN','PROMOTION','PO','FRONTEND','USERS','MASTERFILE_COMMON','MASTERFILE_RETAIL','PIVOT','REPORTS','FM','STOCK_TAKE','DN','CN','ADMIN','COUNTER'],
	'consign': ['ADJ','CON','DO','GRA','GRN','PO','USERS','MASTERFILE_COMMON','MASTERFILE_CONSIGN','PIVOT','REPORTS','STOCK_TAKE','ADMIN']
};

var other_single_privilege = {
	'premium': ['ALLOW_IMPORT_SKU','ALLOW_IMPORT_VENDOR','ALLOW_IMPORT_BRAND','ALLOW_IMPORT_UOM'],
	'go': ['ALLOW_IMPORT_SKU','ALLOW_IMPORT_VENDOR','ALLOW_IMPORT_BRAND','ALLOW_IMPORT_UOM']
}

function submit_save(){
	if(!confirm('Are you sure?'))   return false;

	// change button to prevent user click it again
	var btn_save = $('btn_save');
	btn_save.value = 'Saving...';
	btn_save.disabled = true;

	// construct params
	var params = $(document.f_a).serialize();

	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
		    try{
		        // try to decode json
                eval('var json = '+e.responseText);
                if(json['ok']){
					alert('Save successfully.');
					// reload window
					//window.location.reload(0);
				}else if(json['error']){  // got error
					var err = json['error'];
					for(var i=0; i<err.length; i++){
						var config_name = err[i]['config_name'];
						var error_msg = err[i]['error_msg'];
						$('div_setting_error-'+config_name).update(error_msg);
						$('tr_config_row-'+config_name).addClassName('error_row');
					}
					alert('Save Failed! Please correct the error and save again.')
				}
			}catch(ex){
			    // failed to decode json
				alert(e.responseText);
			}

			// enable back the button
			btn_save.disabled = false;
			btn_save.value = 'Save';
		}
	})
}

function show_pv_details(grp_code){
	if(!grp_code)   return;
	
	curtain(true);
	center_div($('div_pv_details-'+grp_code).show());
}

function use_preset_privilege(){
	var v = document.f_a['preset_setting'].value.trim();
	if(!v)	return false;
	
	if(v){
		if(!confirm('Are you sure to change all privilege?')){
			document.f_a['preset_setting'].value = '';
			return false;
		}
	}
	
	var pri = privilege[v];
	
	// make add default as 'No'
	$$('#tbl_config input.inp_priv_no').each(function(inp){
		inp.checked = true;
	});
	
	// turn on privilege
	for(var i=0; i<pri.length; i++){
		var p = pri[i];
		$('priv_group-1-'+p).checked = true;
	}
	
	var other_pri = other_single_privilege[v];
	// turn on single privilege
	if(other_pri){
		for(var i=0; i<other_pri.length; i++){
			$('single_priv-1-'+other_pri[i]).checked = true;
		}
	}
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

{foreach from=$pv_list key=grp_code item=grp}
    {if $grp_code ne 'others'}  <!-- exclude others -->
	
				<div id="div_pv_details-{$grp_code}" style="display:none;position:absolute;z-index:10000;width:650px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0 !important;" class="curtain_popup">
					<div id="div_pv_details-{$grp_code}-header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
						<span style="float:left;">Privileges Details</span>
						<span style="float:right;">
							<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
						</span>
						<div style="clear:both;"></div>
					</div>
					<div id="div_pv_details-{$grp_code}-content" style="padding:2px;height:410px;overflow:auto;">
						<div class="table-responsive">
							<table width="100%">
								<thead class="bg-gray-100">
									<tr>
										<th>Code</th>
										<th>Description</th>
										<th width="80">HQ Only</th>
										<th width="80">Branch Only</th>
									</tr>
								</thead>
								{foreach from=$grp key=pv_code item=pv}
									<tbody class="fs-08">
										<tr class="thover">
											<td>{$pv_code}
												{if $config.skip_update_privilege_list and is_array($config.skip_update_privilege_list) and in_array($pv_code, $config.skip_update_privilege_list)}
													<sup class="privilege_skipped">(Skip Update)</sup>
												{/if}
											</td>
											<td>{$pv.desc|default:'-'}</td>
											<td align="center">{if $pv.hq_only}Yes{else}-{/if}</td>
											<td align="center">{if $pv.branch_only}Yes{else}-{/if}</td>
										</tr>
									</tbody>
								{/foreach}
							</table>
						</div>
					</div>
				</div>
				<script>
					var div_name = 'div_pv_details-{$grp_code}';
					{literal}
					new Draggable(div_name,{ handle: div_name+'-header'});
					{/literal}
				</script>
			

    {/if}
{/foreach}

<form name="f_a" method="post">
<div class="card mx-3">
	<div class="card-body">
		<input type="hidden" name="a" value="save_privilege" onSubmit="return false;" />

		<div style="">
		<b class="form-label">
			Use preset settings:
		</b>
		<select class="form-control" name="preset_setting" onChange="use_preset_privilege();">
			<option value="">--</option>
			<option value="premium">ARMS Premium</option>
			<option value="go">ARMS Go</option>
			<option value="consign">ARMS Consignment</option>
		</select>
		
	</div>
</div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover" id="tbl_config">
			<thead class="bg-gray-100 py-3 	fs-09">
				<tr>
					<th colspan="4">Module</th>
					<th width="200">Active</th>
				</tr>
			</thead>
			{foreach from=$pv_list key=grp_code item=grp}
				{if $grp_code ne 'others'}
					<tbody class="fs-08">
						<tr >
							<td colspan="4">
								<img src="/ui/icons/application.png" align="absmiddle" title="View Privileges details" class="clickable" onClick="javascript:void(show_pv_details('{$grp_code}'));" />
								{$privilege_groupname.$grp_code}
								<sup style="color:blue;">({count var=$grp})</sup>
							</td>
			
							<!-- Active -->
							<td align="center">
								<input type="radio" id="priv_group-1-{$grp_code}" name="active[{$grp_code}]" value="1" {if $privilege_master.$grp_code.active}checked {/if} /> Yes
								<input type="radio" class="inp_priv_no" name="active[{$grp_code}]" value="0" {if !$privilege_master.$grp_code.active}checked {/if} /> No
							</td>
						</tr>
					</tbody>
				{/if}
			{/foreach}
			{if $pv_list.others}
				<thead class="bg-gray-100">
					<tr >
						<th>Others</th>
						<th>Description</th>
						<th width="80">HQ Only</th>
						<th width="80">Branch Only</th>
						<th>Active</th>
					</tr>
				</thead>
				{foreach from=$pv_list.others key=pv_code item=pv}
					<tbody class="fs-08">
						<tr class="thover">
							<td>{$pv_code}</td>
							<td>{$pv.desc|default:'-'}</td>
							<td align="center">{if $pv.hq_only}Yes{else}-{/if}</td>
							<td align="center">{if $pv.branch_only}Yes{else}-{/if}</td>
							<!-- Active -->
							<td align="center">
								<input type="radio" name="others[{$pv_code}]" value="1" id="single_priv-1-{$pv_code}" {if $privileges.$pv_code}checked {/if} /> Yes
								<input type="radio" name="others[{$pv_code}]" value="0" {if !$privileges.$pv_code}checked {/if} /> No
							</td>
						</tr>
					</tbody>
				{/foreach}
			{/if}
		</table>
	</div>
</div>
</div>
</form>

<div style="position:fixed;bottom:0;background:rgb(189, 202, 231);width:100%;text-align:center;left:0;padding:3px;opacity:0.8;">
		<input type="button" id="btn_save"class="btn btn-primary fs-06"  value="Save" onClick="submit_save();" />
</div>
{include file='footer.tpl'}
