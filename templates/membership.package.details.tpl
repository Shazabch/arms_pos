{*
10/25/2019 10:26 AM Andy
- Rename to word from "Entry" to "Credit".

11/12/2019 2:38 PM William
- Add checking config "membership_mobile_settings" for "Profile Photo".

11/14/2019 5:17 PM William
- Enhanced to add new remark.

1/18/2021 11:55 AM William
- Enhanced to show "Patient Medical Record" when config "membership_pmr" is active.

1/26/2020 10:24 AM William
- Enhanced to use $config.membership_pmr_name as label name of membership_pmr.
*}

{include file='header.tpl'}
{include file='star_rating.include.tpl'}

<style>
{literal}
#div_profile_image{
	border: 3px outset black;
	float: right;
	background-color: #fff;
	padding: 3px;
}

#upload_image_popup {
	border:2px solid #000;
	background:#fff;
	width:300px;
	height:120px;
	padding:10px;
	position:absolute;
	text-align:center;
	z-index:10000;
}

{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MEMBER_PACKAGE_DETAILS = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		PACKAGE_ITEMS_DIALOG.initialize();
	},
	// function when users click on profile image
	profile_image_clicked: function(){
		var profile_image_url = $('inp_profile_image_url').value;
		
		// No image
		if(!profile_image_url)	return;
		show_sku_image_div(profile_image_url);
	},
	list_sel: function(tab_type){
		// Remove all tab highlight
		var all_tab = $$('#div_tab .a_tab');
		for(var i=0;i<all_tab.length;i++){
			$(all_tab[i]).removeClassName('active');
		}
		// highlight the selected tab
		$('lst-'+tab_type).addClassName('active');
		
		// hide all div
		$$('#package_list div.div_package_list').invoke('hide');
		// show the selected div
		$('package_list-'+tab_type).show();
	},
	// function when users click on to select a package
	package_clicked: function(mpp_guid){
		if(!mpp_guid)	return;
		
		PACKAGE_ITEMS_DIALOG.open(mpp_guid);
	}
}

var PACKAGE_ITEMS_DIALOG = {
	f: undefined,
	initialize: function(){
		this.f = document.f_mpp_items;
	},
	open: function(mpp_guid){
		$('div_f_mpp_items').update(_loading_);
		
		// Show Dialog
		curtain(true, 'curtain2');
		center_div($('div_mpp_items_dialog').show());
				
		var THIS = this;
		var params = {
			a: 'ajax_show_purchased_items',
			nric: document.f_a['nric'].value,
			mpp_guid: mpp_guid
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update HTML
						$('div_f_mpp_items').update(ret['html']);
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
	close: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	},
	// function when users click on button redeem
	redeem_clicked: function(item_guid){
		if(!item_guid)	return;
		this.f['redeem_item_guid'].value = item_guid;
		
		// Clone Information
		$('td_confirmation_title').update($$('#tr_f_mpp_items-'+item_guid+' td.td_title')[0].innerHTML);
		$('td_confirmation_description').update($$('#tr_f_mpp_items-'+item_guid+' td.td_description')[0].innerHTML);
		$('td_confirmation_remark').update($$('#tr_f_mpp_items-'+item_guid+' td.td_remark')[0].innerHTML);
		$('td_confirmation_entry_need').update($$('#tr_f_mpp_items-'+item_guid+' td.td_entry_need')[0].innerHTML);
		
		Effect.SlideUp('div_f_mpp_items_info_list',{
			afterFinish: function() { 
				Effect.SlideDown('div_f_mpp_items_confirmation');
			}
		});
	},
	// function when users click on back redeem
	redeem_cancel_clicked: function(){
		Effect.SlideUp('div_f_mpp_items_confirmation',{
			afterFinish: function() { 
				Effect.SlideDown('div_f_mpp_items_info_list');
			}
		});
	},
	// function when user click on button confirm redeem
	redeem_confirm_clicked: function(){
		if(!this.f['redeem_item_guid'].value){
			alert('Invalid Item to Redeem');
		}
		
		if(!confirm('Confirm Redemtion?'))	return false;
		
		var THIS = this;
		var params = $(this.f).serialize();
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		$('div_mpp_items_dialog').hide();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Redeem Succesfully');
						// Reload current page
						location.reload(true);
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
				$('div_mpp_items_dialog').show();
			}
		});
	}
}
{/literal}
</script>

{* Member Purchased Package Items Dialog *}
<div id="div_mpp_items_dialog" class="curtain_popup" style="position:absolute;z-index:10005;width:600px;height:500px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_mpp_items_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Select Item to Redeem</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="PACKAGE_ITEMS_DIALOG.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_mpp_items_dialog_content" style="padding:2px;overflow-y:auto;height:460px;">
		<form name="f_mpp_items" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_redeem_package" />
			<input type="hidden" name="nric" value="{$member.nric|escape:'html'}" />
			
			<div id="div_f_mpp_items"></div>
		</form>
	</div>
</div>

<h1>{$PAGE_TITLE}</h1>

<div class="stdframe" style="background-color: white !important;">
	{* Profile Image *}
	{if $config.membership_mobile_settings}
	<div id="div_profile_image">
		<div align="center">Profile Photo</div>
		<div align="center">
			<input type="hidden" id="inp_profile_image_url" name="profile_image_url" value="{$member.profile_image_url}" />
			<img {if $member.profile_image_url}src="thumb.php?img={$member.profile_image_url|urlencode}&h=100&w=100"{/if} onClick="MEMBER_PACKAGE_DETAILS.profile_image_clicked();" height="100" width="100" />
		</div>
	</div>
	{/if}
	
	<form name="f_a" onSubmit="return false;">
		<input type="hidden" name="nric" value="{$member.nric|escape:'html'}" />
		
		{* Profile Info *}
		<table class="body">
			<tr>
				<td width="100"><b>NRIC<b></td>
				<td>{$member.nric}</td>
			</tr>
			
			<tr>
				<td><b>Card No<b></td>
				<td>{$member.card_no}</td>
			</tr>
			
			<tr>
				<td><b>Name<b></td>
				<td>{$member.name}</td>
			</tr>
			<tr>
				<td valign="top"><b>Remark<b></td>
				<td>{$member.remark|escape:'html'|nl2br}</td>
			</tr>
			{if $config.membership_pmr}
			<tr>
				<td valign="top"><b>{$config.membership_pmr_name}<b></td>
				<td style="{if $member.pmr}border: 1px solid #ccc;{/if}"><span style="max-height: 100px; width: 100%; display: block; overflow: auto;">{$member.pmr|escape:'html'|nl2br}</span></td>
			</tr>
			{/if}
		</table>
	</form>
	
	<br style="clear:both;" />
</div>

<br />

<div id="div_tab" class="tab" style="height:20px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	{foreach from=$package_tab_list key=tab_type item=tab_name name=ftab}
		<a href="javascript:void(MEMBER_PACKAGE_DETAILS.list_sel('{$tab_type}'))" id="lst-{$tab_type}" class="{if $smarty.foreach.ftab.first}active{/if} a_tab">{$tab_name}</a>
	{/foreach}
	<a href="javascript:void(MEMBER_PACKAGE_DETAILS.list_sel('history'))" id="lst-history" class="a_tab">Redeem History</a>
	<a href="javascript:void(MEMBER_PACKAGE_DETAILS.list_sel('log'))" id="lst-log" class="a_tab">Log</a>
	
</div>

<div id="package_list">
	{foreach from=$package_tab_list key=tab_type item=tab_name name=ftab}
		<div id="package_list-{$tab_type}" class="div_package_list" style="border:1px solid #000;padding:3px;{if !$smarty.foreach.ftab.first}display:none;{/if}">
			<table class="report_table" width="100%">
				<tr class="header">
					{if $tab_type eq 'available'}
						<th width="100">&nbsp;</th>
					{/if}
					<th>Package Ref No.</th>
					<th>POS Ref No.</th>
					<th>Package Doc No.</th>
					<th>Package Title</th>
					<th>Purchase Date</th>
					<th>Purchase Branch</th>
					<th>Purchase Qty</th>
					<th>Used Credit</th>
					<th>Remaining Credit</th>
					<th>Added</th>
					<th>Last Update</th>
				</tr>
				
				{foreach from=$mpp_list.$tab_type key=mpp_guid item=mpp}
					<tr>
						{if $tab_type eq 'available'}
							<td align="center">
								<input type="button" value="Select" onClick="MEMBER_PACKAGE_DETAILS.package_clicked('{$mpp_guid}');" />
							</td>
						{/if}
						<td align="center">{$mpp.ref_no|default:'-'}</td>
						<td align="center">{$mpp.pos_receipt_ref_no|default:'-'}</td>
						<td align="center">{$mpp.doc_no}</td>
						<td>{$mpp.title}</td>
						<td align="center">{$mpp.date}</td>
						<td align="center">{$mpp.pos_bcode}</td>
						<td align="right">{$mpp.qty}</td>
						<td align="right">{$mpp.used_entry}</td>
						<td align="right">{$mpp.remaining_entry}</td>
						<td align="center">{$mpp.added}</td>
						<td align="center" nowrap>{$mpp.last_update}</td>
					</tr>
				{foreachelse}
					<tr>
						{assign var=cols value=9}
						{if $tab_type eq 'available'}
							{assign var=cols value=$cols+1}
						{/if}
						<td colspan="{$cols}">No Data</td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/foreach}
	
	{* History *}
	<div id="package_list-history" class="div_package_list" style="border:1px solid #000;padding:3px;display:none;">
		<table class="report_table" width="100%">
				<tr class="header">
					<th>Redeem Date</th>
					<th>Redeem Branch</th>
					<th>User</th>
					<th>Package Ref No.</th>
					<th>Package Doc No.</th>
					<th>Package Title</th>
					<th>Redeem Item Title</th>
					<th>Added</th>
					
					{if $config.masterfile_enable_sa}
						<th>Sales Agent</th>
					{/if}
					<th>Services Rating</th>
					<th>Overall Rating</th>
				</tr>
				{foreach from=$redeem_his_list item=r}
					<tr>
						<td align="center">{$r.date}</td>
						<td align="center">{$r.bcode}</td>
						<td>{$r.user_u}</td>
						<td align="center">{$r.ref_no}</td>
						<td align="center">{$r.doc_no}</td>
						<td>{$r.package_title}</td>
						<td>{$r.item_title}</td>
						<td align="center">{$r.added}</td>
						
						{if $config.masterfile_enable_sa}
							{* Sales Agent *}
							<td nowrap>
								{foreach from=$r.sa_info.sa_list key=sa_id item=sa}
									{if $sa.rate}
										{include file='star_rating.tpl' rate=$sa.rate}
									{/if}
									{$sa.sa_info.code} - {$sa.sa_info.name}
									<br />
								{foreachelse}
									-
								{/foreach}						
							</td>
							
						{/if}
						<td nowrap align="center">
							{if $r.overall_rating}
								{include file='star_rating.tpl' rate=$r.service_rating}
							{/if}
						</td>
						<td nowrap align="center">
							{include file='star_rating.tpl' rate=$r.overall_rating}
						</td>
					</tr>
				{/foreach}
		</table>
	</div>
	
	{* Log *}
	<div id="package_list-log" class="div_package_list" style="border:1px solid #000;padding:3px;display:none;">
		<table class="report_table" width="100%">
				<tr class="header">
					<th>Branch</th>
					<th>User</th>
					<th>Timestamp</th>
					<th>Log</th>
				</tr>
				{foreach from=$log_list item=r}
					<tr>
						<td align="center">{$r.bcode}</td>
						<td>{$r.user_u}</td>
						<td align="center">{$r.added}</td>
						<td>{$r.log}</td>						
					</tr>
				{/foreach}
		</table>
	</div>
</div>


<script>MEMBER_PACKAGE_DETAILS.initialize();</script>
{include file='footer.tpl'}