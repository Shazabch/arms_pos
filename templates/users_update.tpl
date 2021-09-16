{*
	12/10/2010 3:03:09 PM Andy
	- Add NRIC field at user profile, must be enter and unique. (need config)
	
	5/11/2011 4:31:02 PM Alex
	- add function toggle_all_check()
	
	9/13/2012 10:00:00 AM Fithri
	- member update - can search by u,l,fullname
	
	9/13/2012 4:59 PM Andy
	- Fix user dropdown option ID.
	
	1/9/2013 4:33:00 PM Fithri
	- fix when the ajax is searching for user, press again enter will cause the page become weird
	
	2/4/2013 4:27 PM Justin
	- Enhanced to capture regions.
	
	3/12/2013 5:21 PM Justin
	- Enhanced to have checking for those compulsory fields before submit.
	
	4/8/2013 5:04 PM Andy
	- Fix javascript validation error if no IC config.
	
	4/30/2013 10:42 AM Fithri
	- bugfix : update profile autocomplete got error when login at branch
	
	10/1/2013 6:01 PM Fithri
	- make all email field not compulsary
	- when sending email, check to trigger send function only when email is set
	
	12/4/2015 9:21AM DingRen
	- add check login for form submit and ajax call
	
	05/23/2016 10:00 Edwin
	- Enhanced on loading window pop out when update profile
	
	06/30/2016 14:30 Edwin
	- Enhanced on loading window pop out when active/deactive or lock/unlock user.
	
	05/08/2019 15:35 Liew
	- when vendor and brand din't tick meaning allow all
	- hide the vendor / brand list
	
	11/1/2019 3:02 PM William
	- Keep changed value of update profile when update has error.
	
	11/21/2019 9:33 AM Andy
	- Fixed toggle_sms_notification javascript error.
	
	6/23/2020 10:30 AM Sheila
	- Updated button css
	
	3/1/2021 5:30 PM Rayleen
	- Auto load user profile if redirected from user eform page
	*}
	
	{include file=header.tpl}
	{literal}
	<style>
		#select_disabled{
	
		}
	</style>
	{/literal}
	<script type="text/javascript">
	
	var is_form_user = '{$eform_user}';
	
	{literal}
	function refresh()
	{
		if (document.f_u.user_id.value==0) return;
		document.getElementById('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
		/*new Ajax.Updater(
			'udiv', 'users.php', { 
			parameters: Form.serialize(document.f_u),
			onComplete: function() {
				toggle_sms_notification();
			},
			evalScripts:true
		});*/
		
		new Ajax.Request('users.php', {
			parameters: Form.serialize(document.f_u),
			onComplete: function(msg) {
				var str = msg.responseText.trim();
				$('udiv').update(str);
				toggle_sms_notification();
			},
			evalScripts:true
		})
		//document.f_u.search_username.value = '';
		return false;
	}
	
	function refresh2()
	{
		document.f_u.a.value = 'refresh';
		document.f_u.t.value = 'update';
		document.f_u.user_id.selectedIndex=0;
		document.f_u.submit();
	}
	
	function run(params)
	{
		center_div($('div_wait_popup').show());
		curtain(true,'curtain2');
		
		new Ajax.Updater(
			'udiv', 'users.php', { parameters: params, evalScripts:true,onComplete: function(e){toggle_curtain();} }
		);
	}
	
	function check_e(is_tpl)
	{
		center_div($('div_wait_popup').show());
		curtain(true,'curtain2');
	
		if (check_login()) {
			if (!is_tpl)
			{
				if (document.f_e.template.value=='0')
				{
	
					if (document.f_e.ic_no && empty(document.f_e.ic_no, 'You must enter IC No'))
					{
						toggle_curtain();
						return false;
					}
					if (empty(document.f_e.fullname, 'You must enter Full Name'))
					{
						toggle_curtain();
						return false;
					}
					if (empty(document.f_e.position, 'You must enter Position'))
					{
						toggle_curtain();
						return false;
					}
					if (empty(document.f_e.newlogin, 'You must enter a Login ID'))
					{
						toggle_curtain();
						return false;
					}
					if (document.f_e.newpassword.value != '' && document.f_e.newpassword.value != document.f_e.newpassword2.value)
					{
						alert('Password does not match with confirmation password.');
						document.f_e.newpassword2.value = '';
						document.f_e.newpassword2.focus();
						toggle_curtain();
						return false;
					}
					/*
					if (empty(document.f_e.newemail, 'You must enter an email'))
					{
						return false;
					}
					*/
				}
			}
	
			// if got nric field
			if(document.f_e['ic_no']){
				if(document.f_e['ic_no'].value.trim()==''){
					alert('Please enter IC');
					document.f_e['ic_no'].focus();
					toggle_curtain();
					return false;
				}
			}
	
			document.f_e.submitbtn.disabled = true;
			document.f_e.resetbtn.disabled = true;
			document.f_e.submitbtn.value = 'Updating...';
	
			new Ajax.Request('users.php', {
				parameters: Form.serialize(document.f_e),
				type: 'post',
				onSuccess: function(e){
					toggle_curtain();
					// Update html
					$('udiv').update(e.responseText);
				},
				onFailure: function(e){
					toggle_curtain();
					alert(e.responseText);
				}
			});
		}
		return false;
	}
	
	function copy_template(formobj)
	{
		if (formobj.template_id.value!=0)
		{
			if (confirm("Copy the privileges from selected user/template?"))
			{
				new Ajax.Updater(
					'udiv', 'users.php', { parameters: Form.serialize(formobj), evalScripts:true }
				);
			}
		}
	}
	
	function checkallrow(p, r, v)
	{
		var x = document.f_e.getElementsByTagName('input');
		for (var i=0;i<x.length;i++)
		{
			if (x[i].type.indexOf('checkbox') >= 0 && x[i].name.indexOf(p + '[') == 0 && x[i].name.indexOf('[' + r + ']') > p.length+2)
			{
				x[i].checked = v;
			}
		}
	}
	
	function checkallcol(p, c, v)
	{
		var x = document.f_e.getElementsByTagName('input');
		for (var i=0;i<x.length;i++)
		{
			if (x[i].type.indexOf('checkbox') >= 0 && x[i].name.indexOf(p + '[' + c + ']') >= 0)
			{
				x[i].checked = v;
			}
		}
	}
	
	// Clone Selected Column
	function clone_selected_col()
	{
		sc_bid = document.getElementById('sc').value;
		dc_bid = document.getElementById('dc').value;
		
		if (sc_bid == dc_bid)
		{
			alert("Can't Clone From Same Source To Same Destination Branch");
		}
		else
		{
			if (confirm("Clone selected branch privileges?")) 
			{							
				var sc_value = document.f_e.getElementsByClassName("inp_priv-" + sc_bid);
				var dc_value = document.f_e.getElementsByClassName("inp_priv-" + dc_bid);
				
				for (var j = 0; j < dc_value.length; j++)
				{
					dc_value[j].checked = false;
				}
				
				for (var i = 0, len=sc_value.length; i < len; i++)
				{				
					// Get Source Privilege Code
					var priv_code = sc_value[i].getAttribute("priv_code");
					// Get Target Input
					var target_inp = document.f_e['user_privilege['+dc_bid+']['+priv_code+']'];
					
					if (target_inp){
						target_inp.checked = sc_value[i].checked;
					}
				}
			}
		}
	}
	
	function toggle_all_check(obj, type, class_name){
		if (type=="departments"){
			$$("#departments_id ."+class_name).each(function (ele,index){
				ele.checked=obj.checked;
			});
	
		}else if (type=="vendors"){
			$$("#vendors_id ."+class_name).each(function (ele,index){
				ele.checked=false;
			});
	
			// if Vendors All Is Checked		
			$("vendors_id").style.display = (obj.checked) ? "none" : "";
		}else if (type=="brands"){
			$$("#brands_id ."+class_name).each(function (ele,index){
				ele.checked=false;
			});
			
			// if Brands All Is Checked
			$("brands_id").style.display = (obj.checked) ? "none" : "";
		}else if (type=="regions"){
			$$("#regions ."+class_name).each(function (ele,index){
				ele.checked=obj.checked;
			});
		}
	}
	
	function toggle_sms_notification() {
		if(!document.f_e || document.f_e['phone_1'])	return;
		if (document.f_e.phone_1.value.trim() == '') {
			document.f_e.sms_notification.checked = false;
		}
		document.f_e.sms_notification.disabled = document.f_e.phone_1.value.trim() == '';
	}
	
	function toggle_curtain() {
		$('div_wait_popup').hide();
		curtain(false,'curtain2');
	
	}
	
	{/literal}
	</script>
	
	
	<script type="text/javascript">
	//var userlist = [];
	//{foreach from=$users key=ukey item=uname}
	//	userlist.push('{$uname.u}');
	//{/foreach}
		
		
	
	</script>

{* <p>You currently have {$active_count} active user. The system currently allow up to {$MAX_ACTIVE_USER} active users</p> *}
	<div id="div_wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
		<p align="center">
			Please wait..
			<br /><br />
			<img src="ui/clock.gif" border="0" />
		</p>
	</div>

<div class="container">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">Update Profile</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	<div class="card mx-3">
		<div class="card-body">
			<form class="from-horizontal" name=f_u method=post action="users.php" onsubmit="return false;">
					<input type=hidden name=a value="u">
					<input type=hidden name=t value="">
					{if $eform_user}
					<input type=hidden name=eform value="{$eform_user}">
					{/if}
					{if $msg.u}<ul class=msg>{foreach item=m from=$msg.u}<li>{$m}{/foreach}</ul>{/if}
					{if $eform_user }
						<ul class=msg><li>Eform User successfully approved, please update user data.</li></ul>
					{/if}
				<div {if $eform_user}style="display:none;"{/if}>
					<div class="form-group" >
						<div class="row">
							<div class="col-3">
								{if $BRANCH_CODE eq 'HQ'}
									<label class="mt-3">Branch</label>
									<select class="form-control select2" name="branch_id" onChange="refresh2();">
										<option value=0>- All -</option>
										{section name=i loop=$branches}
										<option value={$branches[i].id} {if $smarty.request.branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
										{/section}
									</select>
								{else}
									<input type=hidden name=branch_id value={$sessioninfo.branch_id}>
								{/if}
							</div>
							
							<div class="col-3">
								<label class="mt-3">Status</label>
								<select class="form-control select2" name="status" onChange="refresh2();">
									<option value=-1>- All -</option>
									<option value=1 {if $smarty.request.status eq '1'}selected{/if}>Active</option>
									<option value=0 {if $smarty.request.status eq '0'}selected{/if}>Inactive</option>
								</select>
							</div>
							<div class="col-6">
								<label class="mt-3">Select username</label> 
								<select class="form-control select2" name="user_id" onChange="refresh();document.f_u.search_username.value = '';" {if $eform_user}id="select_disabled"{/if}>
									<option value=0>----------</option>
									{section name=i loop=$users}
									<option id="opt_uid-{$users[i].id}" value={$users[i].id} {if ($smarty.request.user_id eq $users[i].id) || $eform_user eq $users[i].id}selected{/if}>{$users[i].u} {if $users[i].template}(Template){else}({$users[i].branch_code}){/if}{if !$users[i].active} - inactive{/if}</option>
									{/section}
								</select>
							</div>
							<div class="col">
									<label class="mt-3">Search username</label>
									<input class="form-control" type="text" id="search_username" name="search_username"  />
									<div id="autocomplete_username" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
									<button class="btn btn-primary mt-3" name="submitbtn"  onclick="refresh()">Refresh</button>
							</div>

						</div>		
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div id=udiv class="stdframe"></div>
	
	{include file=footer.tpl}
	
	{literal}
	<script type="text/javascript">
	var a = new Ajax.Autocompleter(
		'search_username',
		'autocomplete_username',
		'ajax_autocomplete.php?a=ajax_search_user&user_profile=1',
		{
			fullSearch:true,
			ignoreCase:true,
			afterUpdateElement:function(sel,li) {
				$('opt_uid-'+li.title).selected = true;
				refresh();
			}
		}
	);
	
	if(is_form_user){
		refresh();
	}
	
	</script>
	{/literal}
	