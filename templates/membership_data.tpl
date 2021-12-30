{*
1/14/2008 5:51:20 PM yinsee
- add NRIC duplicate check
- fix 'U' bug (put old-nric and old-name out of if-else) 

3/24/2008 3:13:52 PM gary
- add block and unblock card

5/6/2008 4:41:19 PM yinsee
- remove MEMBERSHIP_UNBLOCK privilege checking (obsolete)

12/30/2008 5:37:15 PM yinsee
- add "ADD MEMBER" mode ($add_mode = true)

12/31/2008 3:15:35 PM yinsee
- add member type 

6/22/2010 2:41:39 PM yinsee
- while disable form, replace checkbox and radio with picture

3/7/2011 3:20:04 PM Justin
- Added the customized menu that allow user to maintain different list of membership info.
- The customized menu which starts from "Choice of Newspaper" and ends with "Other VIP Card".

5/10/2011 6:08:53 PM Andy
- Add if no apply_branch_id, update membership info will update current branch as apply_branch_id. If login as HQ, will show a dropdown to let user choose apply branch.

5/11/2011 10:56:12 AM Justin
- Added checking for apply branch id in JS.
- Added drop down list for user to select apply branch when found no apply branch id and login as HQ.

6/29/2011 4:58:08 PM Justin
- Added new feature to call out the list of function to view full size of photo or upload photo.
- Added the function to hide/show the remark of "view full size" and enable/disable the view full size whenever system able/unable to find the IC image.
- Added a new function to update photo upload for IC image.
- Added a new function rename the IC image file if found member's NRIC has been changed.
- Fixed the bugs whenever upload new IC photo but still show the existing image instead of the new uploaded one.

7/26/2011 5:00:11 PM Justin
- Removed the checking feature to permanently allow user to view larger size even the member does not have any photo. Remove this special for Aneka since their photo taken from other branches.

8/19/2011 6:02:43 PM Justin
- Added new checking feature to disable/enable upload IC image function.

10/19/2011 10:38:43 AM Justin
- Added new field "Recruit By".
- Added new JS function to monitor and return user ID into hidden field when found valid username from database.

12/7/2011 10:13:43 AM Justin
- Fixed the wording "Require".

2/10/2012 5:43:23 PM Justin
- Modified to include new occupation "Teacher" and "Government Servant".
- Modified the JS checking to include these two data as well.

6/22/2012 6:01:23 PM Justin
- Added new field "Parent Card" to create inheritance between sub and principal card.
- Added new JS function to check for parent NRIC.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

10:31:12 AM 7/30/2012 Justin
- Enhanced to capture new info from extra info base on config set the fields.

8/2/2012 4:23:23 PM Justin
- Enhanced the form validation to have config checking as if found it is set.
- Added the missing label of "* Required Field".

8/28/2012 4:51:11 PM Justin
- Enhanced to store original DOB on hidden field.

9/18/2012 4:20 PM Justin
- Changed the wording "Designation" into "Title".

9/25/2012 12:39 PM Justin
- Bug fixed on NRIC and Name need to base on privilege "MEMBERSHIP_TOPEDIT" to allow add/edit.

1/16/2013 11:31 AM Andy
- Add when change staff type will also trigger to recalculate point.

2/21/2013 3:17 PM Andy
- Add checking to privilege "MEMBERSHIP_UPDATE_STAFF_TYPE", if got only can update membership staff type

8/19/2013 2:28 PM Justin
- Enhanced to have new feature to unlink principal relationship.
- Enhanced to improves principal card validation.

2:32 PM 9/12/2013 Justin
- Modified the capture from "Applying Branch to "Issue Branch".
- Removed the config cardname to prevent confusion.

9/13/2013 10:54 AM Fithri
- allow key-in issue date & expiry date when add member (if config is on)
- HQ can change issue branch when add & edit member

2:39 PM 8/22/2014 Justin
- Enhanced to have "Always Print Full Tax Invoice" and "GST Type".

8/3/2015 2:22 PM Joo Chia
- Fix apply branch id to be always required and not allow to select empty (--) branch when update.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

1/23/2017 5:22 AM Andy
- Enhance Calendar to block user to select date more than 2037-12-31.

2/7/2017 10:07 AM Andy
- Fixed card number, issue date and expiry date missing when found error and redirected back.
- Fixed button Save cannot click when redirected back from error.

2/20/2017 3:36 PM Justin
- Bug fixed on principal NRIC cannot be removed once click on unlick button (while adding new member).

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 

10/27/2017 5:31 PM Andy
- Enhanced to hide Newspaper and VIP Card when got config "membership_not_malaysian".

12/10/2018 11:03 AM Andy
- Hide "Always Print Full Tax Invoice" and "GST Type" when no config.enable_gst

10/25/2019 3:02 PM William
- Enhanced to add remark and display "Mobile Profile Photo".
- Enhanced to use config "membership_state_settings" to load the state list.

11/7/2019 5:26 PM William
- Add checking config "membership_mobile_settings" for "Mobile Profile Photo".

11/12/2019 4:56 PM William
- Fixed bug issue branch will always follow the login branch.

11/22/2019 4:24 PM William
- Fix bug when membership no apply_branch_id and not hq, auto use current branch as apply_branch_id.

12/5/2019 5:51 PM Andy
- Fixed one of the "Issue Branch" should be "Apply Branch".

06/29/2020 02:15 PM Sheila
- Updated button css.

1/18/2021 1:20 PM William
- Enhanced to show "Patient Medical Record" when config "membership_pmr" is active.

1/26/2020 10:24 AM William
- Enhanced to use $config.membership_pmr_name as label name of membership_pmr.
*}

{include file=header.tpl}

{literal}
<style>
input:disabled, select:disabled{
	background: #fcfcfc;
	color:#000;
}
</style>
{/literal}

<script type="text/javascript">
var verify="{$form.from_list}";
var add_mode = "{$add_mode}";
</script>

{literal}

<script type="text/javascript">
var _valid_ = '<img src="/ui/approved.png" style="width:18px;" align="absmiddle"  title="Valid Username">';
var _invalid_ = '<img src="/ui/deact.png" style="width:18px;" align="absmiddle" title="Invalid Username">';


function check_a()
{
	if (check_login()) {

        if(!check_required_field(document.f_a))	return false;

        /*if (empty(document.f_a.apply_branch_id, 'You must select apply branch'))
        {
            return false;
        }
        if (empty(document.f_a.name, 'You must enter name'))
        {
            return false;
        }
        if (rdcbn_empty('des', 4, 'You must select designation'))
        {
            return false;
        }
        if (rdcbn_empty('gender', 2, 'You must select gender'))
        {
            return false;
        }
        if (empty(document.f_a.dob_d, 'You must enter date of birth'))
        {
            return false;
        }
        if (empty(document.f_a.dob_m, 'You must enter date of birth'))
        {
            return false;
        }
        if (empty(document.f_a.dob_y, 'You must enter date of birth'))
        {
            return false;
        }
        if (document.f_a.dob_d.value > 31)
        {
            alert('Invalid day value in date of birth');
            document.f_a.dob_d.focus();
            return false;
        }
        if (document.f_a.dob_m.value > 12)
        {
            alert('Invalid month value in date of birth');
            document.f_a.dob_m.focus();
            return false;
        }

        if (rdcbn_empty('ms', 2, 'You must select marital status'))
        {
            return false;
        }
        if (rdcbn_empty('nt', 2, 'You must select national'))
        {
            return false;
        }
        if (rdcbn_empty('race', 4, 'You must select race'))
        {
            return false;
        }
        if (rdcbn_empty('edu', 4, 'You must select level of education'))
        {
            return false;
        }
        if (rdcbn_empty('lang', 3, 'You must select preferred language'))
        {
            return false;
        }
        if (empty(document.f_a.address, 'You must enter address'))
        {
            return false;
        }
        if (empty(document.f_a.postcode, 'You must enter post code'))
        {
            return false;
        }
        if (empty(document.f_a.city, 'You must enter city'))
        {
            return false;
        }
        if (empty(document.f_a.state, 'You must select a state'))
        {
            return false;
        }
        if (rdcbn_empty('occ', 10, 'You must select occupation'))
        {
            return false;
        }*/

        document.f_a.submit();
    }
}

function block_card()
{
	if (document.f_b.reason.selectedIndex == 0)
	{
	    alert('Please select a blocking reason.');
	    return;
	}
	if (document.f_b.reason.value == 'others' && document.f_b.reason_other.value.trim() == '')
	{
	    alert('Please enter a blocking reason.');
	    document.f_b.reason_other.select();
	    document.f_b.reason_other.focus();
	    return;
	}
	ajax_request(
		"membership.php",
		{
			parameters: 'a=ajax_block&'+Form.serialize(document.f_b),
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function(m) {
			    alert(m.responseText);
			    if(verify)
			    	setTimeout('window.close()',100);
			    else
					window.location='/membership.php?t=update';
			},
		}
	);
}

function unblock_card()
{
	if (!confirm('Click OK to un-block this member.')) return;

	ajax_request(
		"membership.php",
		{
			parameters: 'a=ajax_unblock&'+Form.serialize(document.f_b),
			onFailure: function(m) {
			    alert(m.responseText);
			},
			onSuccess: function(m) {
			    alert(m.responseText);
			    if(verify)
			    	setTimeout('window.close()',100);
			    else
					window.location='/membership.php?t=update';	
			}
		}
	);
}

function show_context_menu(obj){
	context_info = {element: obj};
	$('item_context_menu').show();

	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	
	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}

	$('ul_menu').onmousemove = function() {
		clearTimeout(context_info.timer);
	}

	return false;
}

function hide_context_menu(){
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;
	Element.hide('item_context_menu');
}

function show_photo_upload_menu(){
	hidediv('ic_org');
	showdiv('photo_upload_menu');
	$('photo_upload_menu').style.zIndex = 10000;
	curtain(true);
}

function ic_upload_check(){
	if (!/\.jpg/i.test(document.ic_pu.ic_photo.value)){
		alert("Selected file must be a valid JPEG image");
		return false;
	}

	return true;
}

function ic_upload_callback(content, img_src){
	$('div_ic_photo_area').update(content.innerHTML);
	$('full_ic_photo_display_area').update(img_src);
	
	document.ic_pu.ic_photo.value = "";
	curtain_clicked();
}

function curtain_clicked(){
	hidediv('photo_upload_menu');
	curtain(false);
}

function user_verify(obj){
	var username = obj.value.trim();
	if (username == ''){
		document.f_a['recruit_by'].value = "";
		$('rb_loading').update();
		return;
	}

	$('rb_loading').update(_loading_);
	ajax_request(
		"membership.php",
		{
			parameters: 'a=user_verify&username='+username,
			onFailure: function(m) {
			    alert(m.responseText);
			},
			onSuccess: function(m) {
				if(int(m.responseText) > 0){
					$('rb_loading').update(_valid_);
					document.f_a['recruit_by'].value = m.responseText;
				}else $('rb_loading').update(_invalid_);
			}
		}
	);
}

function principal_nric_verify(obj){
	if(document.f_a.nric.value == ""){
		alert("Please enter NRIC before add principal card.");
		return;
	}

	var principal_i = prompt("Please enter Principal NRIC/Card No:");
	
	if(principal_i == null) return;
	
	
	var principal_info = principal_i.toUpperCase().trim();
	var old_principal_nric = document.f_a.old_principal_nric.value;
	
	if(document.f_a.old_nric.value == principal_info || document.f_a.nric.value == principal_info || document.f_a.card_no.value == principal_info){
		alert("Cannot assign current member as Principal!");
		document.f_a.principal_nric.value = old_principal_nric;
		return;
	}
	
	// invalid principal checking
	if(principal_info == ""){
		$('pnric_loading').update();
		return;
	}

	$('pnric_loading').update(_loading_);
	ajax_request(
		"membership.php",
		{
			parameters: 'a=principal_nric_verify&principal_info='+principal_info+"&nric="+document.f_a.nric.value,
			onFailure: function(m) {
			    alert(m.responseText);
			},
			onSuccess: function(m) {
				var str = m.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){
					if(!confirm(ret['result'])){
						$('pnric_loading').update();
						document.f_a.principal_nric.value = old_principal_nric;
						return;	
					}
					document.f_a.principal_nric.value = ret['val'];
					obj.hide();
					$('unlink_principal').show();
				}else{
					alert(ret['result']);
				}
				$('pnric_loading').update();
			}
		}
	);
}

function salesman_perc_changed(obj){
	if(obj.value > 100) obj.value = 100;
	else if(obj.value < 0) obj.value = 0;
}

function principal_unlink(obj){
	// if it is adding new member, straight remove it without checking points balance
	if(add_mode > 0){
		$('find_principal').show();
		$('unlink_principal').hide();
		document.f_a.principal_nric.value = "";
		return;
	}
	
	$('pnric_loading').update(_loading_);
	ajax_request(
		"membership.php",
		{
			parameters: 'a=principal_nric_unlink&principal_nric='+document.f_a.principal_nric.value+'&nric='+document.f_a.old_nric.value,
			onFailure: function(m) {
			    alert(m.responseText);
			},
			onSuccess: function(m) {
				var str = m.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){
					if(!confirm("Are you sure want to unlink?")){
						$('pnric_loading').update();
						return;
					}
					$('find_principal').show();
					$('unlink_principal').hide();
					document.f_a.principal_nric.value = "";
				}else{
					alert(ret['result']);
				}
				$('pnric_loading').update();
			}
		}
	);
}
function validate_newcard(el)
{
	uc(el);
	$('save_btn').disabled = true;
	$('card_check').update('');
	if(el.value.trim()=='')	return;
	
	$('card_check').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';
	
	// check the card and return status
	var param = 'card_no='+el.value+'&a=ajax_validate_card';
	
	ajax_request('membership.php', {
		parameters: param,
		onComplete:function(m) {
			$('card_check').innerHTML = m.responseText.replace(/\\n/," "); 		
			// if status = OK, enable submit button
			if (m.responseText=='OK')
				$('save_btn').disabled = false;
			else
			{
				el.select();
				el.focus();
				alert(m.responseText.replace(/\\n/,"\n"));
			}
		}
	});
}

function profile_image_clicked(){
	var profile_image_url = $('inp_profile_image_url').value;
	
	// No image
	if(!profile_image_url)	return;
	show_sku_image_div(profile_image_url);
}
</script>
{/literal}

{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
{/literal}
{literal}
<style>
#div_profile_image{
	border: 3px outset black;
	float: right;
	background-color: #fff;
	padding: 3px;
}
</style>
{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{*$config.membership_cardname*}Membership {if $add_mode}(Add New Member){/if}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class=errmsg>
{if $errmsg}<ul>{foreach item=m from=$errmsg}<li>{$m}{/foreach}</ul>{/if}
</div>

<!-- Popup menu -->
{assign var=file_ic_path value=$form.ic_path|@substr:1}
<div id="item_context_menu" style="display:none;position:absolute;">
	<ul id="ul_menu" class="contextmenu">
		<li><a href="javascript:showdiv('ic_org');"><img src="/ui/icons/photo.png" align="absmiddle"> View Full Size</a></li>
		<li><a href="javascript:show_photo_upload_menu();"><img src="/ui/icons/vcard_add.png" align="absmiddle"> Update Photo</a></li>
	</ul>
</div>

<form name="ic_pu" onsubmit="return ic_upload_check()"; target="_ifs" enctype="multipart/form-data" method="post">
	<input type="hidden" name="a" value="add_ic_photo">
	<input type="hidden" name="nric" value="{$form.old_nric|default:$form.nric}">
	<div id="photo_upload_menu" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:200px; left:200px;">
		<div class=small style="position:absolute; right:10px;">
			<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
		</div>
		<div class="stdframe" align="center">
			<p>
				<h4>Photo Upload Menu:</h4><br>
				<input type="file" name="ic_photo" id="ic_photo">
				<br />* Must be a valid JPEG image 
			</p> 
			<p align="center" id="choices">
				<input type="submit" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Upload">
				<input type="button" style="font:bold 14px Arial; background-color:#f00; color:#fff;" value="Cancel" onclick="curtain_clicked()">
			</p>
		</div>
	</div>
</form>
<iframe name="_ifs" width="1" height="1" style="visibility:hidden"></iframe>

<form id="id_f_a" name="f_a" action="{$smarty.server.PHP_SELF}?t=update" method=post enctype="multipart/form-data" onsubmit="return false">
<input type=hidden name=a value="u">
<input type=hidden name=t value="update">
<input name=old_nric type=hidden value="{$form.old_nric|default:$form.nric}">
<input name=card_no type=hidden value="{$form.card_no}">
{if $add_mode}
<input name=add_mode type=hidden value="1">
{else}
<input name=old_name type=hidden value="{$form.name}">
{/if}
{if $form.card_no}
{if $config.membership_mobile_settings}
<table align="right">
	<tr><td align="center">
		<div id="div_profile_image">
		<div align="center">Profile Photo</div>
		<input type="hidden" id="inp_profile_image_url" name="profile_image_url" value="{$form.profile_image_url}" />
		<img {if $form.profile_image_url}src="thumb.php?img={$form.profile_image_url|urlencode}&h=100&w=100"{/if} onClick="profile_image_clicked()" width="100" height="100" style="cursor:pointer;" title="Click to view full size photo" />
		</div>
	</td></tr>
</table>
{/if}
<table class=body>
<tr><td>
<h3>Current {$config.membership_cardname}</h3>
<b>Card no:</b> {$form.card_no}<br><br>
<b>Issue Branch:</b> {$form.branch_code}<br><br>
<b>Issue Date:</b> {$form.issue_date|date_format:"%e/%m/%Y"}<br><br>
<b>Expiry Date:</b> {$form.next_expiry_date|date_format:"%e/%m/%Y"}<br>
</td><td>
<img src="{$form.card_type|string_format:$config.membership_cardimg}" hspace=40 align=right>
</td></tr>
</table>
{/if}

{if $config.membership_data_use_custom_field.principal_card}
	<div class="card mx-3">
		<div class="card-body">
			<table class="body">
				<tr>
					<div class="form-inline">
						<b class="form-label">Principal NRIC</b>
						&nbsp;&nbsp;	<input class="form-control" name="principal_nric" size="15" maxlength="15" value="{$form.parent_nric}" readonly />
							<input type="hidden" name="old_principal_nric" value="{$form.parent_nric}">
							&nbsp;&nbsp;<input class="btn btn-primary" type="button" value="Find" id="find_principal" onclick="principal_nric_verify(this);" {if $form.parent_nric}style="display:none;"{/if}>
							<input class="btn btn-danger" type="button" value="Unlink Relation" id="unlink_principal"  onclick="principal_unlink(this);" {if !$form.parent_nric}style="display:none;"{/if}>
					</div>
					<span id="pnric_loading"></span>
				</tr>
			</table>
		</div>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe">

			<div id="ic_org" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:absolute; top:150px; left:150px;">
			<div class="small" style="position:absolute; right:10px;"><a href="javascript:void(hidediv('ic_org'))"><img src="ui/closewin.png" border="0" align="absmiddle"></a></div>
			<div id="full_ic_photo_display_area"><img src="{$form.ic_path}"></div>
			</div>
			
			{if !$add_mode}
				<table align=right>
					<tr>
						<td align=center>
							<h4>Scanned IC Image</h4>
							<div id="div_ic_photo_area">
								<img src="{$form.ic_path}" width="200" style="border:1px solid #999; padding:8px; background-color:#fff;cursor:pointer;z-index:100" onClick="{if $BRANCH_CODE eq $form.apply_branch_code || $config.single_server_mode}show_context_menu(this);{else}showdiv('ic_org');{/if}" title="Click to view full size or update photo"><br />
								Click to view full size {if $BRANCH_CODE eq $form.apply_branch_code || $config.single_server_mode}/ Update photo {/if}<br />
							</div>
						</td>
					</tr>
				</table>
			{/if}
			
			
			<h3 class="text-primary">Member's Particular</h3>
			<h5>Please update the data if necessary. <font color=red size=+2>*</font> = Required field</h5>
			<div class="table-responsive">
				<table class="body mt-2">
					<tr>
						<td><b class="form-label">Apply Branch</b></td><td>
							{if $add_mode}
								{if $BRANCH_CODE eq 'HQ'}
								<select class="form-control" name="apply_branch_id">
									{foreach from=$branch_list key=r item=branch}
									<option value="{$branch_list.$r.id}" {if $sessioninfo.branch_id eq $branch_list.$r.id}selected{/if}>{$branch_list.$r.code}</option>
									{/foreach}
								</select>
								{else}
								<input type="hidden" name="apply_branch_id" value="{$sessioninfo.branch_id}">
								{$smarty.const.BRANCH_CODE}
								{/if}
							{else}
								{if $BRANCH_CODE eq 'HQ'}
									{assign var=branch_list_selected value=false}
									<select name="apply_branch_id" class="required" title="Apply Branch ID">
										{foreach from=$branch_list key=r item=branch}
											<option value="{$branch_list.$r.id}" {if $form.apply_branch_id eq $branch_list.$r.id}{assign var=branch_list_selected value=true} selected{/if}>{$branch_list.$r.code}</option>
										{/foreach}
										{if $form.apply_branch_id && $branch_list_selected eq false}
											<option value="{$form.apply_branch_id}" selected>{$form.apply_branch_code}</option>
										{/if}
									</select>
								{elseif $BRANCH_CODE neq 'HQ' && $form.apply_branch_id > 0}
									<input type="hidden" name="apply_branch_id" value="{$form.apply_branch_id}" />
									{$form.apply_branch_code}
								{else}
									<input type="hidden" name="apply_branch_id" value="{$sessioninfo.branch_id}" />
									{$BRANCH_CODE}
								{/if}
							{/if}
							
							{*
							{$form.apply_branch_code|default:$smarty.const.BRANCH_CODE}
							*}
						</td>
					</tr>
					<tr>
						<td nowrap><b class="form-label">NRIC / Passport no.<span class="text-danger" >&nbsp;*</span></b></td><td>
							<input type="text" name="nric" value="{$form.nric}" onblur="ucz(this)" class="required form-control" title="NRIC / Passport No." {if (!$add_mode && !$update) || !$sessioninfo.privilege.MEMBERSHIP_TOPEDIT}readonly{/if}></td>
					</tr>
					
					{if $add_mode && $config.membership_add_member_can_issue_card && $config.membership_auto_verify_member}
					<tr><td>
					<b class="form-label">Card number<span class="text-danger">&nbsp;*</span></b></td>
					<td><input type="text" name="add_card_no" class="form-control required" title="Card No" size="{$config.membership_length}" maxlength="{$config.membership_length}" onchange="validate_newcard(this)" value="{$form.add_card_no}" id="inp_add_card_no" /><span id="card_check" class="small"></span>
					</td></tr>
					<tr><td>
					{assign var=issue_date value=$form.issue_date}
					{if !$issue_date}
						{assign var=issue_date value=$smarty.now|date_format:'%d/%m/%Y'}
					{/if}
					<b class="form-label">Issue Date<span class="text-danger">&nbsp;*</span></b></td>
					<td><div class="form-inline"><input type="text" name="issue_date" id="issue_date" class="form-control required" title="Issue Date" size="68" value="{$issue_date}">&nbsp;&nbsp; <img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Issue Date">&nbsp; (Max {$MAX_MYSQL_DATETIME})</div>
					</td></tr>
					<tr><td>
					{assign var=expiry_date value=$form.expiry_date}
					{if !$expiry_date}
						{assign var=expiry_date value=$smarty.now|date_add:'+1 year'|date_format:'%d/%m/%Y'}
					{/if}
					<b class="form-label">Expiry Date<span class="text-danger">&nbsp;*</span></b></td>	
					<td><div class="form-inline"><input type="text" name="expiry_date" id="expiry_date" class="form-control required" title="Expiry Date" size="68" onchange="uc(this)" value="{$expiry_date}">&nbsp;&nbsp; <img align="absmiddle" src="ui/calendar.gif" id="t_added3" style="cursor: pointer;" title="Select Next Expiry Date">&nbsp;(Max {$MAX_MYSQL_DATETIME})</div>
					</td></tr>
					{/if}
					
					<tr>
						<td><b class="form-label">Full Name<span class="text-danger">&nbsp;*</span></b></td><td>
						<input type="text" name="name" size="50" maxlength="80" value="{$form.name}" onBlur="uc(this)" onChange="chg(this.name)" {if !$config.membership_required_fields || $config.membership_required_fields.name}class="form-control required" title="Name"{/if} {if (!$add_mode && !$update) || !$sessioninfo.privilege.MEMBERSHIP_TOPEDIT}readonly{/if}> {if !$config.membership_required_fields || $config.membership_required_fields.name}{/if}</td>
					</tr>
					
					{* Member Type&nbsp;*}
					{if $config.membership_type}
						<tr>
							<td><b class="form-label">Membership Type {if !$config.membership_required_fields || $config.membership_required_fields.member_type}<font color=red class="text-danger">&nbsp;*</font>{/if}</b></td>
							<td>
								<input class="form-control" type="hidden" name="old_member_type" value="{$form.member_type}" />
						
								<select class="form-control" name="member_type" {if !$config.membership_required_fields || $config.membership_required_fields.member_type}class="required" title="Membership Type"{/if}>
									{foreach from=$config.membership_type key=member_type item=mtype_desc}
										{if is_numeric($member_type)}
											{assign var=mt value=$mtype_desc}
										{else}
											{assign var=mt value=$member_type}
										{/if}
										<option value="{$mt}" {if $mt eq $form.member_type}selected{/if}>{$mtype_desc}</option>
									{/foreach}
								</select>
								
							</td>
						</tr>
					{/if}
					
					{* Staff Type&nbsp;*}
					{if $config.membership_enable_staff_card}
						<tr>
							<td><b class="form-label">Staff Type</b></td>
							<td>
								<input type="hidden" name="old_staff_type" value="{$form.staff_type}" />
								
								{if $sessioninfo.privilege.MEMBERSHIP_UPDATE_STAFF_TYPE}
								<select class="form-control" name="staff_type">
									<option value="">-- Not Staff --</option>
									{foreach from=$config.membership_staff_type key=staff_type item=staff_label}
										<option value="{$staff_type}" {if $staff_type eq $form.staff_type}selected {/if}>{$staff_label}</option>
									{/foreach}
								</select>
								{else}
									<input type="hidden" name="staff_type" value="{$form.staff_type}" />
									
									{if $form.staff_type}
										{$config.membership_staff_type[$form.staff_type]|default:$form.staff_type}
									{else}
										-- Not Staff --
									{/if}
								{/if}
							</td>
						</tr>
					{/if}
					
					<tr>
						<td class="form-inline"><b class="form-label">Title</b>{if !$config.membership_required_fields || $config.membership_required_fields.designation}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=des1 name=designation type=radio value="Mr" {if $form.designation eq 'Mr'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Mr
						<input id=des2 name=designation type=radio value="Mrs" {if $form.designation eq 'Mrs'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Mrs
						<input id=des3 name=designation type=radio value="Ms" {if $form.designation eq 'Ms'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Ms
						<input id=des4 name=designation type=radio value="Others" {if $form.designation eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Others
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Gender</b>{if !$config.membership_required_fields || $config.membership_required_fields.gender}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=gender1 name=gender type=radio value="M" {if $form.gender eq 'M'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.gender}class="required" title="Gender"{/if}>Male
						<input id=gender2 name=gender type=radio value="F" {if $form.gender eq 'F'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.gender}class="required" title="Gender"{/if}>Female
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Date of Birth </b>{if !$config.membership_required_fields || $config.membership_required_fields.dob}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
					<div class="form-inline">
						DD&nbsp; <input class="form-control" type="text" name=dob_d size=2 maxlength=2 value="{$form.dob_d}"{if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Day)"{/if}>
						&nbsp;MM&nbsp; <input class="form-control" type="text" name=dob_m size=2 maxlength=2 value="{$form.dob_m}"{if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Month)"{/if}>
						&nbsp;YYYY&nbsp; <input class="form-control" type="text" name=dob_y size=4 maxlength=4 value="{$form.dob_y}" onBlur="if (this.value.length==2)this.value='19'+this.value" {if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Year)"{/if}>
					</div>
						
						<input type="hidden" name="old_dob" value="{$form.dob}" />
						</td>
					</tr><tr>
					<td>&nbsp;</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Marital Status</b>{if !$config.membership_required_fields || $config.membership_required_fields.marital_status}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=ms1 name=marital_status type=radio value="1" {if $form.marital_status == 1}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.marital_status}class="required" title="Marital Status"{/if}>Married
						<input id=ms2 name=marital_status type=radio value="0" {if $form.marital_status != '' && $form.marital_status == 0}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.marital_status}class="required" title="Marital Status"{/if}>Single
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">National</b>{if !$config.membership_required_fields || $config.membership_required_fields.national}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=nt1 name=national type=radio value="Malaysian" {if $form.national eq 'Malaysian'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.national}class="required" title="National"{/if}>Malaysian
						<input id=nt2 name=national type=radio value="Others" {if $form.national eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.national}class="required" title="National"{/if}>Others
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Race</b>{if !$config.membership_required_fields || $config.membership_required_fields.race}<font class="text-danger" color=red >&nbsp;*</font>{/if}</td><td>
						<input id=race1 name=race type=radio value="Malay" {if $form.race eq 'Malay'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Malay
						<input id=race2 name=race type=radio value="Chinese" {if $form.race eq 'Chinese'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Chinese
						<input id=race3 name=race type=radio value="Indian" {if $form.race eq 'Indian'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Indian
						<input id=race4 name=race type=radio value="Others" {if $form.race eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Others
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Level of Education</b>{if !$config.membership_required_fields || $config.membership_required_fields.education_level}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=edu1 name=education_level type=radio value="Secondary" {if $form.education_level eq 'Secondary'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>Secondary
						<input id=edu2 name=education_level type=radio value="College" {if $form.education_level eq 'College'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>College
						<input id=edu3 name=education_level type=radio value="University" {if $form.education_level eq 'University'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>University
						<input id=edu4 name=education_level type=radio value="Others" {if $form.education_level eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>Others
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Preferred Language</b>{if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=lang1 name=preferred_lang type=radio value="Malay" {if $form.preferred_lang eq 'Malay'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>Malay
						<input id=lang2 name=preferred_lang type=radio value="Chinese" {if $form.preferred_lang eq 'Chinese'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>Chinese
						<input id=lang3 name=preferred_lang type=radio value="English" {if $form.preferred_lang eq 'English'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>English
						
						</td>
					</tr><tr>
					<td>&nbsp;</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Address</b>{if !$config.membership_required_fields || $config.membership_required_fields.address}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=address size=80 maxlength=100 value="{$form.address}" onBlur="uc(this)" {if !$config.membership_required_fields || $config.membership_required_fields.address}class="required" title="Address"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Post Code</b>{if !$config.membership_required_fields || $config.membership_required_fields.postcode}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=postcode size=5 maxlength=10 value="{$form.postcode}" {if !$config.membership_required_fields || $config.membership_required_fields.postcode}class="required" title="Post Code"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">City</b>{if !$config.membership_required_fields || $config.membership_required_fields.city}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=city size=20 maxlength=50 value="{$form.city}" onBlur="uc(this)" {if !$config.membership_required_fields || $config.membership_required_fields.city}class="required" title="City"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">State</b>{if !$config.membership_required_fields || $config.membership_required_fields.state}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
							<select class="form-control" name="state" {if !$config.membership_required_fields || $config.membership_required_fields.state}class="required" title="State"{/if}>
								{if $form.state}
								<option value="{$form.state}">{$form.state}</option>
								{/if}
								<option value="">-----------</option>
								{if $config.membership_state_settings}
									{foreach from=$config.membership_state_settings item=state}
										<option value="{$state}">{$state}</option>
									{/foreach}
								{/if}
							</select>
							
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Phone (Home)</b>{if $config.membership_required_fields.phone_1}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=phone_1 size=15 maxlength=15 value="{$form.phone_1}" {if $config.membership_required_fields.phone_1}class="required" title="Phone (Home)"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Phone (Office)</b>{if $config.membership_required_fields.phone_2}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=phone_2 size=15 maxlength=15 value="{$form.phone_2}" {if $config.membership_required_fields.phone_2}class="required" title="Phone (Office)"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Phone (Mobile)</b>{if $config.membership_required_fields.phone_3}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=phone_3 size=15 maxlength=15 value="{$form.phone_3}" {if $config.membership_required_fields.phone_3}class="required" title="Phone (Mobile)"{/if}>
						
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Email Address</b>{if $config.membership_required_fields.email}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input class="form-control" type="text" name=email size=30 maxlength=50 value="{$form.email}" onBlur="lc(this)" {if $config.membership_required_fields.email}class="required" title="Email Address"{/if}>
						
						</td>
					</tr>
					{if $config.membership_data_use_custom_field.recruit_by}
						<tr>
							<td class="form-inline"><b class="form-label">Recruit By</b>{if $config.membership_required_fields.recruit_by}<font color=red class="text-dager"></font>&nbsp;*</font>{/if}</td><td>
							<input class="form-control" type="text" name=username size=15 maxlength=15 value="{$form.recruit_name}" onchange="user_verify(this);" {if $config.membership_required_fields.recruit_by}class="required" title="Recruit By"{/if}> <span id="rb_loading"></span>
							<input type="hidden" name="recruit_by" value="{$form.recruit_by}">
							
							</td>
						</tr>
					{/if}
					{if $config.membership_extra_info}
						{foreach from=$config.membership_extra_info key=col item=info}
							<tr>
								<td class="form-inline"><b class="form-label">{$info.description}</b>{if $config.membership_required_fields.$col}<font color=red class="text-danger">&nbsp;*</font>{/if}</td>
								<td>
									<input class="form-control" type="text" name="{$col}" size="{$info.input_size}" maxlength="{$info.input_size}" value="{$form.$col}" {if $info.onblur}onblur="{$info.onblur}"{/if} {if $info.onchange}onchange="{$info.onchange}"{/if} {if $config.membership_required_fields.$col}class="required" title="{$info.description}"{/if}>
									
								</td>
							</tr>
						{/foreach}
					{/if}
					<tr>
					<td>&nbsp;</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Occupation</b>{if !$config.membership_required_fields || $config.membership_required_fields.occupation}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=occ1 name=occupation type=radio value="Administrative" {if $form.occupation eq 'Administrative'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Administrative
						<input id=occ2 name=occupation type=radio value="Executive" {if $form.occupation eq 'Executive'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Executive
						<input id=occ3 name=occupation type=radio value="Professional" {if $form.occupation eq 'Professional'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Professional
						<input id=occ4 name=occupation type=radio value="Businessman" {if $form.occupation eq 'Businessman'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Businessman
						<input id=occ5 name=occupation type=radio value="Skilled Worker" {if $form.occupation eq 'Skilled Worker'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Skilled Worker
						
						</td>
					</tr><tr>
						<td>&nbsp;</td><td>
						<input id=occ6 name=occupation type=radio value="Housewife" {if $form.occupation eq 'Housewife'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Housewife
						<input id=occ7 name=occupation type=radio value="Student" {if $form.occupation eq 'Student'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Student
						<input id=occ8 name=occupation type=radio value="Teacher" {if $form.occupation eq 'Teacher'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Teacher
						<input id=occ9 name=occupation type=radio value="Government Servant" {if $form.occupation eq 'Government Servant'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Government Servant
						<input id=occ10 name=occupation type=radio value="Others" {if $form.occupation eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Others
						</td>
					</tr><tr>
						<td class="form-inline"><b class="form-label">Income</b>{if $config.membership_required_fields.income}<font color=red class="text-danger">&nbsp;*</font>{/if}</td><td>
						<input id=income1 name=income type=radio value="{$config.arms_currency.symbol}1000 & Below" {if $form.income eq $config.arms_currency.symbol|cat:'1000 & Below'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}1000 & Below
						<input id=income2 name=income type=radio value="{$config.arms_currency.symbol}1001-{$config.arms_currency.symbol}2000" {if $form.income eq $config.arms_currency.symbol|cat:'1001-'|cat:$config.arms_currency.symbol|cat:'2000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}1001-{$config.arms_currency.symbol}2000
						<input id=income3 name=income type=radio value="{$config.arms_currency.symbol}2001-{$config.arms_currency.symbol}4000" {if $form.income eq $config.arms_currency.symbol|cat:'2001-'|cat:$config.arms_currency.symbol|cat:'4000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}2001-{$config.arms_currency.symbol}4000
						
						</td>
					</tr><tr>
						<td>&nbsp;</td><td>
						<input id=income4 name=income type=radio value="{$config.arms_currency.symbol}4001-{$config.arms_currency.symbol}7000" {if $form.income eq $config.arms_currency.symbol|cat:'4001-'|cat:$config.arms_currency.symbol|cat:'7000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}4001-{$config.arms_currency.symbol}7000
						<input id=income5 name=income type=radio value="{$config.arms_currency.symbol}7001-{$config.arms_currency.symbol}10000" {if $form.income eq $config.arms_currency.symbol|cat:'7001-'|cat:$config.arms_currency.symbol|cat:'10000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}7001-{$config.arms_currency.symbol}10000
						<input id=income6 name=income type=radio value="{$config.arms_currency.symbol}10000 & Above" {if $form.income eq $config.arms_currency.symbol|cat:'10000 & Above'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}10000 & Above
						</td>
					</tr><tr>
					<td>&nbsp;</td>
					</tr>
					{if $config.membership_data_use_customize_value}
						{foreach from=$config.membership_data_use_customize_value item=row_value key=row}
							{assign var=title value=$config.membership_data_use_customize_value.$row.title}
							{assign var=type value=$config.membership_data_use_customize_value.$row.type}
							{assign var=input_name value=$config.membership_data_use_customize_value.$row.input_name}
							<tr>
								<td valign="top"><b class="form-label">{$title}</b></td><td>
								<table><tr>
									{assign var=rows_count value=1}
									{foreach from=$config.membership_data_use_customize_value.$row.value item=value_name key=value_type}
										<td>
											<input class="form-control" type="{$type}" name="{$input_name}[{$value_type}]" {if $form.$input_name.$value_type}checked{/if}> {$value_name}
										</td>
										{if $rows_count eq '5'}
											{assign var=rows_count value=0}
											</tr><tr>
										{/if}
									{assign var=rows_count value=$rows_count+1}
									{/foreach}
								</tr></table>
							</tr>
						{/foreach}
					{else}
						{if !$config.membership_not_malaysian}
							<tr>
								<td valign=top><b class="form-label">Choice of Newspaper</b></td><td>
								<table ><tr><td>
									<input type=checkbox name="newspaper[nst]" {if $form.newspaper.nst}checked{/if}> New Strait Time
									</td><td>
									<input type=checkbox name="newspaper[thestar]" {if $form.newspaper.thestar}checked{/if}> The Star
									</td><td>
									<input type=checkbox name="newspaper[kwongwah]" {if $form.newspaper.kwongwah}checked{/if}> Kwong Wah Yit Poh
									</td>
								</tr><tr><td>
									<input type=checkbox name="newspaper[sinchew]" {if $form.newspaper.sinchew}checked{/if}> Sin Chew Jit Poh
									</td><td>
									<input type=checkbox name="newspaper[nanyang]" {if $form.newspaper.nanyang}checked{/if}> Nanyang Siang Poh
									</td><td>
									<input type=checkbox name="newspaper[guangming]" {if $form.newspaper.guangming}checked{/if}> Guang Ming
									</td>
								</tr><tr><td>
									<input type=checkbox name="newspaper[utusan]" {if $form.newspaper.utusan}checked{/if}> Utusan Malaysia
									</td><td>
									<input type=checkbox name="newspaper[bharian]" {if $form.newspaper.bharian}checked{/if}> Berita Harian
									</td><td>
									<input type=checkbox name="newspaper[malaymail]" {if $form.newspaper.malaymail}checked{/if}> Malay Mail
									</td>
								</tr><tr><td>
									<input type=checkbox name="newspaper[thesun]" {if $form.newspaper.thesun}checked{/if}> The Sun
									</td><td>
									<input type=checkbox name="newspaper[tamil]" {if $form.newspaper.tamil}checked{/if}> Tamil Newspaper
									</td><td>
									<input type=checkbox name="newspaper[chinapress]" {if $form.newspaper.chinapress}checked{/if}> China Press
									</td>
								</tr></table>
							</tr><tr>
								<td valign=top><b class="form-label">Other VIP Card</b></td><td>
								<table ><tr><td>
									<input type=checkbox name="other_vip_card[sunshine]" {if $form.other_vip_card.sunshine}checked{/if}> Sunshine
									</td><td>
									<input type=checkbox name="other_vip_card[thestore]" {if $form.other_vip_card.thestore}checked{/if}> The Store
									</td><td>
									<input type=checkbox name="other_vip_card[fajar]" {if $form.other_vip_card.fajar}checked{/if}> Fajar
									</td>
								</tr><tr><td>
									<input type=checkbox name="other_vip_card[parkson]" {if $form.other_vip_card.parkson}checked{/if}> Parkson
									</td><td>
									<input type=checkbox name="other_vip_card[jusco]" {if $form.other_vip_card.jusco}checked{/if}> Jaya Jusco
									</td><td>
									<input type=checkbox name="other_vip_card[yawata]" {if $form.other_vip_card.yawata}checked{/if}> Yawata
									</td>
								</tr><tr><td>
									<input type=checkbox name="other_vip_card[pacific]" {if $form.other_vip_card.pacific}checked{/if}> Pacific
									</td><td>
									<input type=checkbox name="other_vip_card[makro]" {if $form.other_vip_card.makro}checked{/if}> Makro
									</td><td>
									<input type=checkbox name="other_vip_card[metrojaya]" {if $form.other_vip_card.metrojaya}checked{/if}> Metro Jaya
									</td>
								</tr></table>
							</tr>
						{/if}
					{/if}
					<tr>
						<td valign=top><b class="form-label">Credit Card</b></td><td>
						<input bgcolor="#000000" type=checkbox name="credit_card[visa]" {if $form.credit_card.visa}checked{/if}> Visa
						<input type=checkbox name="credit_card[master]" {if $form.credit_card.master}checked{/if}> Master
						<input type=checkbox name="credit_card[amex]" {if $form.credit_card.amex}checked{/if}> Amex
						<input type=checkbox name="credit_card[diners]" {if $form.credit_card.diners}checked{/if}> Diners
						<input type=checkbox name="credit_card[others]" {if $form.credit_card.others}checked{/if}> Others
					</tr>
					
					{if $config.enable_gst}
					<tr>
						<td valign=top><b class="form-label">Always Print Full Tax Invoice</b></td>
						<td>
							<select class="form-control" name="print_full_tax_invoice">
								<option value="0" {if !$form.print_full_tax_invoice}selected{/if}>No</option>
								<option value="1" {if $form.print_full_tax_invoice eq 1}selected{/if}>Yes</option>
							</select>
						</td>
					</tr>
					
					<tr>
						<td valign=top><b class="form-label">GST Type</b></td>
						<td>
							<select class="form-control" name="gst_type">
								<option value="" {if !$form.gst_type}selected{/if}>--</option>
								{foreach from=$gst_list item=r}
									<option value="{$r.id}" {if $form.gst_type eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					{/if}
					
					<tr>
						<td valign=top><b class="form-label">Remark</b></td>
						<td>
							<textarea class="form-control" rows="3" cols="40" name="remark">{$form.remark}</textarea>
						</td>
					</tr>
					
					{if $config.membership_pmr}
					<tr>
						<td valign=top><b class="form-label">{$config.membership_pmr_name}</b></td>
						<td>
							<textarea class="form-control" rows="3" cols="40" name="pmr">{$form.pmr}</textarea>
						</td>
					</tr>
					{/if}
					
					</table>
			</div>
			</div>
			{if !$read_only}
			<p align=center><input class="btn btn-success mt-2" id="save_btn" type="button" value="Save" onclick="check_a();"> 
				<input class="btn btn-warning mt-2" type=reset value="Reset" onclick="return confirm('Forfeit changes?')"></p>
			{/if}
	</div>
</div>
</form>

{if !$read_only}
{if !$add_mode}
<div align=center>
<form name=f_b>
<input name=nric type=hidden value="{$form.nric}">
{if $form.blocked_by > 0}
	{if $sessioninfo.privilege.MEMBERSHIP_UNBLOCK}
	<input class="btn btn-primary" onclick="unblock_card()" type=button value="Unblock Card">
	{/if}
{else}
	{if $sessioninfo.privilege.MEMBERSHIP_BLOCK}
	<select style="vertical-align: middle;" name=reason onchange="$('reason_other').style.display=(this.value=='others')?'':'none';">
	<option>-- Select a Reason --</option>
	<option>Fill New Application Form</option>
	<option>Require New IC Copied</option>
	<option>Signature Required</option>
	<option value="others">Others</option>
	</select>
	<span id=reason_other style="display:none"> Enter reason: <input name=reason_other value=""></span>
	<input class="btn btn-primary" onclick="block_card()" type=button value="Block Card">
	{/if}
{/if}
</form>
</div>
{/if}
{/if}
<script>
init_chg(document.f_a);
{if $read_only}
	{literal}
	var checkedgif = '<img src="ui/checked.gif" align=absmiddle> ';
	var uncheckedgif = '<img src="ui/unchecked.gif" align=absmiddle> ';
	
	Form.disable($('id_f_a'));
	$$('input[type=radio]').each(function(obj) {
		if (obj.checked)
			new Insertion.After(obj,checkedgif)
		else
			new Insertion.After(obj,uncheckedgif);
		Element.remove(obj);
	});
	
	$$('input[type=checkbox]').each(function(obj) {
		if (obj.checked)
			new Insertion.After(obj,checkedgif)
		else
			new Insertion.After(obj,uncheckedgif);
		Element.remove(obj);
	});
	{/literal}
{/if}
{literal}
if (!document.f_a.nric.readonly){
    document.f_a.nric.focus();
}
else{
    document.f_a.name.focus();
}
{/literal}
	
new Draggable('ic_org');
new Draggable('photo_upload_menu');
</script>

{if $add_mode && $config.membership_add_member_can_issue_card && $config.membership_auto_verify_member}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "issue_date",     // id of the input field
        ifFormat       :    "%d/%m/%Y",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		dateStatusFunc :    check_exceed_max_timestamp
    });
    Calendar.setup({
        inputField     :    "expiry_date",     // id of the input field
        ifFormat       :    "%d/%m/%Y",      // format of the input field
        button         :    "t_added3",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		dateStatusFunc :    check_exceed_max_timestamp
    });
	$('save_btn').disabled = true;
	validate_newcard($('inp_add_card_no'));
</script>
{/literal}
{/if}

{include file=footer.tpl}
