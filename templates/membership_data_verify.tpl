{*
REVISION HISTORY
================
2/29/2008 5:34:12 PM gary (request by SLLEE, tracker id=227)
- hide the block and un-block akad function.

12/31/2008 3:15:25 PM yinsee
- cleanup coding
- add member type

5/11/2011 10:56:12 AM Justin
- Added checking for apply branch id in JS.
- Added drop down list for user to select apply branch when found no apply branch id and login as HQ.
- Added the missing of customized menu that allow user to maintain different list of membership info.
- Added the missing of customized menu which starts from "Choice of Newspaper" and ends with "Other VIP Card".

10/19/2011 10:38:43 AM Justin
- Added new field "Recruit By".
- Added new JS function to monitor and return user ID into hidden field when found valid username from database.

2/10/2012 5:43:23 PM Justin
- Modified to include new occupation "Teacher" and "Government Servant".
- Modified the JS checking to include these two data as well.

6/22/2012 6:01:23 PM Justin
- Added new field "Parent Card" to create inheritance between sub and principal card.

7/26/2012 10:21:34 AM Justin
- Enhanced the Membership Type to show additional description if found.

10:31:12 AM 7/30/2012 Justin
- Enhanced to capture new info from extra info base on config set the fields.

8/2/2012 4:23:23 PM Justin
- Enhanced the form validation to have config checking as if found it is set.

8/28/2012 4:51:11 PM Justin
- Enhanced to store original DOB on hidden field.

9/18/2012 4:20 PM Justin
- Changed the wording "Designation" into "Title".

8/19/2013 2:28 PM Justin
- Enhanced to have new feature to unlink principal relationship.
- Enhanced to improves principal card validation.

2:32 PM 9/12/2013 Justin
- Modified the capture from "Applying Branch to "Issue Branch".

2:39 PM 8/22/2014 Justin
- Enhanced to have "Always Print Full Tax Invoice" and "GST Type".

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 

10/27/2017 5:31 PM Andy
- Enhanced to hide Newspaper and VIP Card when got config "membership_not_malaysian".

06/29/2020 02:15 PM Sheila
- Updated button css.
*}

{config_load file="site.conf"}
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<script src="/js/forms.js" language=javascript type="text/javascript"></script>
<script src="/js/prototype.js" language=javascript type="text/javascript"></script>
{literal}
<script>
var _valid_ = '<img src="/ui/approved.png" style="width:18px;" align="absmiddle"  title="Valid Username">';
var _invalid_ = '<img src="/ui/deact.png" style="width:18px;" align="absmiddle" title="Invalid Username">';

function check_a()
{
	if(!check_required_field(document.f_a))	return false;
	/*if (empty(document.f_a.apply_branch_id, 'You must enter apply branch'))
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

function user_verify(obj){
	var username = obj.value.trim();
	if (username == ''){
		document.f_a['recruit_by'].value = "";
		$('rb_loading').update();
		return;
	}

	$('rb_loading').update(_loading_);
	new Ajax.Request(
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
	new Ajax.Request(
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
	$('pnric_loading').update(_loading_);
	new Ajax.Request(
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
</script>
{/literal}

<div style="padding:10px">

<form name=f_a method=post target=_irs onsubmit="return false" action="{$smarty.server.PHP_SELF}?a=u&t=verify">
<input name=old_nric type=hidden value="{$form.nric}">
<input name=old_name type=hidden value="{$form.name}">
<input name=card_no type=hidden value="{$form.card_no}">

<h1>Member's Particular</h1>
{if $config.membership_data_use_custom_field.principal_card}
	<h4>Principal Card</h4>
	<table class="body">
		<tr>
			<td><b>Principal NRIC</b></td><td>
			<input name="principal_nric" size="15" maxlength="15" value="{$form.parent_nric}" readonly />
			<input type="hidden" name="old_principal_nric" value="{$form.parent_nric}">
			<input type="button" value="Find" id="find_principal" onclick="principal_nric_verify(this);" {if $form.parent_nric}style="display:none;"{/if}>
			<input type="button" value="Unlink Relation" id="unlink_principal"  onclick="principal_unlink(this);" {if !$form.parent_nric}style="display:none;"{/if}>
			<span id="pnric_loading"></span>
			</td>
		</tr>
	</table>
{/if}
<h4>Please update the data if necessary. <font color=red size=+2>*</font> = Required field</h4>
<div class=stdframe>
<table class=body width=100% border=0>
<tr>
	<td><b>Issue Branch</b></td>
	<td>
		{if $form.apply_branch_code}
			<!-- already got branch code -->
			<input type="hidden" name="apply_branch_id" value="{$form.apply_branch_id}" />
			{$form.apply_branch_code}
		{else}
			{if $BRANCH_CODE eq 'HQ'}
				<select name="apply_branch_id" {if !$config.membership_required_fields || $config.membership_required_fields.appy_branch_id}class="required" title="Apply Branch ID"{/if}>
					<option value="">--</option>
					{foreach from=$branch_list key=r item=branch}
						<option value="{$branch_list.$r.id}" {if $form.apply_branch_id eq $branch_list.$r.id}selected{/if}>{$branch_list.$r.code}</option>
					{/foreach}
				</select>
			{else}
				<input type="hidden" name="apply_branch_id" value="{$sessioninfo.branch_id}" />
				{$BRANCH_CODE}
			{/if}
		{/if}
	</td>
	<td colspan=2 rowspan=10 align=center valign="top">
		<h4>Scanned IC Image</h4>
		<img src="{$form.ic_path}" style="border:1px solid #999;">
	</td>
</tr><tr>
	<td><b>NRIC / Passport no.</b></td><td><input type="text" name=nric value="{$form.nric}" {if !$config.membership_required_fields || $config.membership_required_fields.nric}class="required" title="NRIC / Passport No."{/if} {if !$sessioninfo.privilege.MEMBERSHIP_TOPEDIT}readonly{/if}> {if !$config.membership_required_fields || $config.membership_required_fields.nric}<font color=red size=+2>*</font>{/if}</td>
</tr><tr>
	<td><b>Full Name</b></td><td nowrap>
	<input type="text" name="name" size=40 maxlength=80 value="{$form.name}" onBlur="uc(this)" onChange="chg(this.name)" {if !$config.membership_required_fields || $config.membership_required_fields.name}class="required" title="Name"{/if} {if !$sessioninfo.privilege.MEMBERSHIP_TOPEDIT}readonly{/if}> {if !$config.membership_required_fields || $config.membership_required_fields.name}<font color=red size=+2>*</font>{/if}</td>
</tr>
{if $config.membership_type}
<tr>
	<td><b>Membership Type</b></td>
	<td>
		<select name="member_type" {if !$config.membership_required_fields || $config.membership_required_fields.member_type}class="required" title="Membership Type"{/if}>
			{foreach from=$config.membership_type key=member_type item=mtype_desc}
				{if is_numeric($member_type)}
					{assign var=mt value=$mtype_desc}
				{else}
					{assign var=mt value=$member_type}
				{/if}
				<option value="{$mt}" {if $mt eq $form.member_type}selected{/if}>{$mtype_desc}</option>
			{/foreach}
		</select>
		{if !$config.membership_required_fields || $config.membership_required_fields.member_type}<font color=red size=+2>*</font>{/if}
	</td>
</tr>
{/if}
<tr>
	<td><b>Title</b></td><td>
	<input id=des1 name=designation type=radio value="Mr" {if $form.designation eq 'Mr'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Mr
	<input id=des2 name=designation type=radio value="Mrs" {if $form.designation eq 'Mrs'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Mrs
	<input id=des3 name=designation type=radio value="Ms" {if $form.designation eq 'Ms'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Ms
	<input id=des4 name=designation type=radio value="Others" {if $form.designation eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.designation}class="required" title="Designation"{/if}>Others
	{if !$config.membership_required_fields || $config.membership_required_fields.designation}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Gender</b></td><td>
	<input id=gender1 name=gender type=radio value="M" {if $form.gender eq 'M'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.gender}class="required" title="Gender"{/if}>Male
	<input id=gender2 name=gender type=radio value="F" {if $form.gender eq 'F'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.gender}class="required" title="Gender"{/if}>Female
	{if !$config.membership_required_fields || $config.membership_required_fields.gender}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Date of Birth</b></td><td>
	DD <input type="text" name=dob_d size=2 maxlength=2 value="{$form.dob_d}" onKeyPress="if (this.value.length==2) dob_m.focus()" {if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Day)"{/if}>
	MM <input type="text" name=dob_m size=2 maxlength=2 value="{$form.dob_m}" onKeyPress="if (this.value.length==2) dob_y.focus()" {if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Month)"{/if}>
	YYYY <input type="text" name=dob_y size=4 maxlength=4 value="{$form.dob_y}" onBlur="if (this.value.length==2)this.value='19'+this.value" {if !$config.membership_required_fields || $config.membership_required_fields.dob}class="required" title="Date of Birth (Year)"{/if}>
	{if !$config.membership_required_fields || $config.membership_required_fields.dob}<font color=red size=+2>*</font>{/if}
	<input type="hidden" name="old_dob" value="{$form.dob}" />
	</td>
</tr><tr>
<td>&nbsp;</td>
</tr><tr>
	<td><b>Marital Status</b></td><td>
	<input id=ms1 name=marital_status type=radio value="1" {if $form.marital_status == 1}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.marital_status}class="required" title="Marital Status"{/if}>Married
	<input id=ms2 name=marital_status type=radio value="0" {if $form.marital_status != '' && $form.marital_status == 0}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.marital_status}class="required" title="Marital Status"{/if}>Single
	{if !$config.membership_required_fields || $config.membership_required_fields.marital_status}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>National</b></td><td>
	<input id=nt1 name=national type=radio value="Malaysian" {if $form.national eq 'Malaysian'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.national}class="required" title="National"{/if}>Malaysian
	<input id=nt2 name=national type=radio value="Others" {if $form.national eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.national}class="required" title="National"{/if}>Others
	{if !$config.membership_required_fields || $config.membership_required_fields.national}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Race</b></td><td>
	<input id=race1 name=race type=radio value="Malay" {if $form.race eq 'Malay'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Malay
	<input id=race2 name=race type=radio value="Chinese" {if $form.race eq 'Chinese'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Chinese
	<input id=race3 name=race type=radio value="Indian" {if $form.race eq 'Indian'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Indian
	<input id=race4 name=race type=radio value="Others" {if $form.race eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.race}class="required" title="Race"{/if}>Others
	{if !$config.membership_required_fields || $config.membership_required_fields.race}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Level of Education</b></td><td colspan=3>
	<input id=edu1 name=education_level type=radio value="Secondary" {if $form.education_level eq 'Secondary'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>Secondary
	<input id=edu2 name=education_level type=radio value="College" {if $form.education_level eq 'College'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>College
	<input id=edu3 name=education_level type=radio value="University" {if $form.education_level eq 'University'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>University
	<input id=edu4 name=education_level type=radio value="Others" {if $form.education_level eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.education_level}class="required" title="Level of Education"{/if}>Others
	{if !$config.membership_required_fields || $config.membership_required_fields.education_level}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Preferred Language</b></td><td>
	<input id=lang1 name=preferred_lang type=radio value="Malay" {if $form.preferred_lang eq 'Malay'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>Malay
	<input id=lang2 name=preferred_lang type=radio value="Chinese" {if $form.preferred_lang eq 'Chinese'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>Chinese
	<input id=lang3 name=preferred_lang type=radio value="English" {if $form.preferred_lang eq 'English'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}class="required" title="Preferred Language"{/if}>English
	{if !$config.membership_required_fields || $config.membership_required_fields.preferred_lang}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
<td>&nbsp;</td>
</tr><tr>
	<td><b>Address</b></td><td colspan=3>
	<input type="text" name=address size=80 maxlength=100 value="{$form.address}" onBlur="uc(this)" {if !$config.membership_required_fields || $config.membership_required_fields.address}class="required" title="Address"{/if}>
	{if !$config.membership_required_fields || $config.membership_required_fields.address}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Post Code</b></td><td>
	<input type="text" name=postcode size=5 maxlength=10 value="{$form.postcode}" {if !$config.membership_required_fields || $config.membership_required_fields.postcode}class="required" title="Post Code"{/if}>
	{if !$config.membership_required_fields || $config.membership_required_fields.postcode}<font color=red size=+2>*</font>{/if}
	</td>
	<td><b>City</b></td><td>
	<input type="text" name=city size=20 maxlength=50 value="{$form.city}" onBlur="uc(this)" {if !$config.membership_required_fields || $config.membership_required_fields.city}class="required" title="City"{/if}>
	{if !$config.membership_required_fields || $config.membership_required_fields.city}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>State</b></td><td>
		<select name="state" {if !$config.membership_required_fields || $config.membership_required_fields.state}class="required" title="State"{/if}>
			{if $form.state}
			<option value="{$form.state}">{$form.state}</option>
			{/if}
			<option value="">-----------</option>
			<option value="Johor">Johor</option>
			<option value="Kedah">Kedah</option>
			<option value="Kuala Lumpur">Kuala Lumpur</option>
			<option value="Kelantan">Kelantan</option>
			<option value="Melaka">Melaka</option>
			<option value="Negeri Sembilan">Negeri Sembilan</option>
			<option value="Penang">Penang</option>
			<option value="Pahang">Pahang</option>
			<option value="Perak">Perak</option>
			<option value="Perlis">Perlis</option>
			<option value="Selangor">Selangor</option>
			<option value="Terengganu">Terengganu</option>
			<option value="Sabah">Sabah</option>
			<option value="Sarawak">Sarawak</option>
			<option value="Others">Others *</option>
		</select>
	{if !$config.membership_required_fields || $config.membership_required_fields.state}<font color=red size=+2>*</font>{/if}
	</td>
	<td><b>Phone (Home)</b></td><td>
	<input name=phone_1 size=15 maxlength=15 value="{$form.phone_1}" {if $config.membership_required_fields.phone_1}class="required" title="Phone (Home)"{/if}>
	{if $config.membership_required_fields.phone_1}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Phone (Office)</b></td><td>
	<input name=phone_2 size=15 maxlength=15 value="{$form.phone_2}" {if $config.membership_required_fields.phone_2}class="required" title="Phone (Office)"{/if}>
	{if $config.membership_required_fields.phone_2}<font color=red size=+2>*</font>{/if}
	</td>
	<td><b>Phone (Mobile)</b></td><td>
	<input name=phone_3 size=15 maxlength=15 value="{$form.phone_3}" {if $config.membership_required_fields.phone_3}class="required" title="Phone (Mobile)"{/if}>
	{if $config.membership_required_fields.phone_3}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td><b>Email Address</b></td><td>
	<input name=email size=30 maxlength=50 value="{$form.email}" onBlur="lc(this)" {if $config.membership_required_fields.email}class="required" title="Email Address"{/if}>
	{if $config.membership_required_fields.email}<font color=red size=+2>*</font>{/if}
	</td>
</tr>
{if $config.membership_data_use_custom_field.recruit_by}
	<tr>
		<td><b>Recruit By</b></td><td>
		<input name=username size=15 maxlength=15 value="{$form.recruit_name}" onchange="user_verify(this);" {if $config.membership_required_fields.recruit_by}class="required" title="Recruit By"{/if}> <span id="rb_loading"></span>
		<input type="hidden" name="recruit_by" value="{$form.recruit_by}">
		{if $config.membership_required_fields.recruit_by}<font color=red size=+2>*</font>{/if}
		</td>
	</tr>
{/if}
{if $config.membership_extra_info}
	{foreach from=$config.membership_extra_info key=col item=info}
		<tr>
			<td><b>{$info.description}</b></td>
			<td>
				<input name="{$col}" size="{$info.input_size}" maxlength="{$info.input_size}" value="{$form.$col}" {if $info.onblur}onblur="{$info.onblur}"{/if} {if $info.onchange}onchange="{$info.onchange}"{/if} {if $config.membership_required_fields.$col}class="required" title="{$info.description}"{/if}>
				{if $config.membership_required_fields.$col}<font color=red size=+2>*</font>{/if}
			</td>
		</tr>
	{/foreach}
{/if}
<tr>
<td>&nbsp;</td>
</tr><tr>
	<td><b>Occupation</b></td><td colspan=3>
	<input id=occ1 name=occupation type=radio value="Administrative" {if $form.occupation eq 'Administrative'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Administrative
	<input id=occ2 name=occupation type=radio value="Executive" {if $form.occupation eq 'Executive'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Executive
	<input id=occ3 name=occupation type=radio value="Professional" {if $form.occupation eq 'Professional'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Professional
	<input id=occ4 name=occupation type=radio value="Businessman" {if $form.occupation eq 'Businessman'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Businessman
	<input id=occ5 name=occupation type=radio value="Skilled Worker" {if $form.occupation eq 'Skilled Worker'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Skilled Worker
	{if !$config.membership_required_fields || $config.membership_required_fields.occupation}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td>&nbsp;</td><td colspan=3>
	<input id=occ6 name=occupation type=radio value="Housewife" {if $form.occupation eq 'Housewife'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Housewife
	<input id=occ7 name=occupation type=radio value="Student" {if $form.occupation eq 'Student'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Student
	<input id=occ8 name=occupation type=radio value="Teacher" {if $form.occupation eq 'Teacher'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Teacher
	<input id=occ9 name=occupation type=radio value="Government Servant" {if $form.occupation eq 'Government Servant'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Government Servant
	<input id=occ10 name=occupation type=radio value="Others" {if $form.occupation eq 'Others'}checked{/if} {if !$config.membership_required_fields || $config.membership_required_fields.occupation}class="required" title="Occupation"{/if}> Others
	</td>
</tr><tr>
	<td><b>Income</b></td><td colspan=3>
	<input id=income1 name=income type=radio value="{$config.arms_currency.symbol}1000 & Below" {if $form.income eq $config.arms_currency.symbol|cat:'1000 & Below'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}1000 & Below
	<input id=income2 name=income type=radio value="{$config.arms_currency.symbol}1001-{$config.arms_currency.symbol}2000" {if $form.income eq $config.arms_currency.symbol|cat:'1001-'|cat:$config.arms_currency.symbol|cat:'2000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}1001-{$config.arms_currency.symbol}2000
	<input id=income3 name=income type=radio value="{$config.arms_currency.symbol}2001-{$config.arms_currency.symbol}4000" {if $form.income eq $config.arms_currency.symbol|cat:'2001-'|cat:$config.arms_currency.symbol|cat:'4000'}checked{/if} {if $config.membership_required_fields.income}class="required" title="Income"{/if}> {$config.arms_currency.symbol}2001-{$config.arms_currency.symbol}4000
	{if $config.membership_required_fields.income}<font color=red size=+2>*</font>{/if}
	</td>
</tr><tr>
	<td>&nbsp;</td><td colspan=3>
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
			<td valign="top"><b>{$title}</b></td><td>
			<table><tr>
				{assign var=rows_count value=1}
				{foreach from=$config.membership_data_use_customize_value.$row.value item=value_name key=value_type}
					<td>
						<input type="{$type}" name="{$input_name}[{$value_type}]" {if $form.$input_name.$value_type}checked{/if}> {$value_name}
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
			<td valign=top><b>Choice of Newspaper</b></td><td>
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
			<td valign=top><b>Other VIP Card</b></td><td>
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
	<td valign=top><b>Credit Card</b></td><td colspan=3>
	<input type=checkbox name="credit_card[visa]" {if $form.credit_card.visa}checked{/if}> Visa
	<input type=checkbox name="credit_card[master]" {if $form.credit_card.master}checked{/if}> Master
	<input type=checkbox name="credit_card[amex]" {if $form.credit_card.amex}checked{/if}> Amex
	<input type=checkbox name="credit_card[diners]" {if $form.credit_card.diners}checked{/if}> Diners
	<input type=checkbox name="credit_card[others]" {if $form.credit_card.others}checked{/if}> Others
</tr>

<tr>
	<td valign=top><b>Always Print Full Tax Invoice</b></td>
	<td>
		<select name="print_full_tax_invoice">
			<option value="0" {if !$form.print_full_tax_invoice}selected{/if}>No</option>
			<option value="1" {if $form.print_full_tax_invoice eq 1}selected{/if}>Yes</option>
		</select>
	</td>
</tr>

<tr>
	<td valign=top><b>GST Type</b></td>
	<td>
		<select name="gst_type">
			<option value="" {if !$form.print_full_tax_invoice}selected{/if}>--</option>
			{foreach from=$gst_list item=r}
				<option value="{$r.id}" {if $form.print_full_tax_invoice eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
			{/foreach}
		</select>
	</td>
</tr>

</table>
<p align=center>
<input class="btn btn-success" type=button value="Save and Approve!" onclick="check_a();">
</p>
</form>
</div>
</div>


<div style="display:none"><iframe name="_irs" width=120 height=120></iframe></div>

<script>
init_chg(document.f_a);
{if $sessioninfo.privilege.MEMBERSHIP_TOPEDIT}
document.f_a.nric.focus();
{else}
document.f_a.name.focus();
{/if}
</script>
