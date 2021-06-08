{*
6/19/2007 12:06:48 PM - yinsee
- add support for single server mode "remote" login

12/27/2008 4:17:06 PM yinsee
- single server login with same http-port

12/9/2011 6:00:43 PM Justin
- Added sales agent login menu.
- Fixed html tags that is not close properly for general and vendor login menu.

11/21/2012 10:22 AM Justin
- Changed the "Username" to "Login ID".

4/2/2013 4:36 PM Andy
- Add debtor login screen.

7/11/2013 4:10 PM Andy
- Show user to install jsPrintSetup if found the add-on is not exists.

11/29/2013 11:23 AM Andy
- Enhance the multi server mode to allow some sub-branch to working in HQ server.

05/25/2016 15:30 Edwin
- Added password reset feature.

4/10/2017 10:11 AM Justin
- Enhanced to adjust lower down the "Forgot Password?" link, not to stick too close with password field.
- Enhanced to add checkbox "Terms & Conditions" checkbox.
- Enhanced to have validation checking for "Terms & Conditions".

4/17/2017 5:54 PM Andy
- Changed Terms and Conditions url to "https://agreement.arms.my/5" regardless of server type.

10/5/2017 4:48 PM Justin
- Enhanced to show custom header base on config.

5/6/2019 5:33 PM Justin
- Enhanced to removed some of the extra line breaking.

03/23/2020 5:33 PM Sheila
- Modified layout to compatible with new UI.

04/16/2020 1:55 PM Sheila
- Added Background image

*}

{include file=header.tpl}

{*<div id="div_install_jsprintsetup" style="display:none;">
	<div style="float:right;border:1px solid black;padding:2px;background-color:#ffee99;">
		<a href="include/jsprintsetup/jsprintsetup-0.9.2.xpi">
			<img src="ui/icons/information.png" align="absmiddle" /> Click here to install jsPrintSetup to enable Printer Settings Auto Configuration.
		</a>
	</div><br style="clear:both;" />
</div>*}
<div align="center">
{include file=front_end.tpl}

<script type="text/javascript">

var single_server_mode = int('{$config.single_server_mode}');
var BRANCH_CODE = '{$BRANCH_CODE}';
var hq_url = '{"hq"|string_format:$config.no_ip_string}';
var curr_branch_at_hq = int('{$config.branch_at_hq.$BRANCH_CODE}');

{literal}
function do_branch_login(){
	if(!single_server_mode){	// is multi server
		var bcode = $('branch').value;
		var action_path = '';
		
		if(bcode != BRANCH_CODE){	// login to other branch
			var opt = $('branch').options[$('branch').selectedIndex];	// get selected branch <option>
			var branch_url = $(opt).readAttribute('branch_url');	// get original branch url
			var login_branch_at_hq = int($(opt).readAttribute('branch_at_hq'));	// get whether this branch is at hq server
			
			if(BRANCH_CODE == 'HQ' || curr_branch_at_hq){	// currently at HQ, or the branch in hq
				if(bcode == 'HQ' || login_branch_at_hq){	// this branch should login to HQ, or is login to hq, no need to change server
					// nothing to change action path
				}else{
					action_path = branch_url;	// login to branch server
				}
			}else{	// currently at other branch which is not in hq server
				if(login_branch_at_hq){	// this branch should login to HQ
					action_path = hq_url;	// login to hq server
				}else{
					action_path = branch_url;	// login to branch server
				}
			}
			document.f_l.action = action_path+'/login.php';
		}
	}
	
	if(document.f_l['tnc'].checked == false){
		alert("You must agree to the Terms & Conditions in order to login.");
		return false;
	}
	
	return true;
}

function do_vendor_login(){
	if(!single_server_mode){	// is multi server
		var bcode = $('sel_vp_branch').value;
		var action_path = '';
		
		if(bcode != BRANCH_CODE){	// login to other branch
			var opt = $('sel_vp_branch').options[$('sel_vp_branch').selectedIndex];	// get selected branch <option>
			var branch_url = $(opt).readAttribute('branch_url');	// get original branch url
			var login_branch_at_hq = int($(opt).readAttribute('branch_at_hq'));	// get whether this branch is at hq server
			
			if(BRANCH_CODE == 'HQ' || curr_branch_at_hq){	// currently at HQ, or the branch in hq
				if(bcode == 'HQ' || login_branch_at_hq){	// this branch should login to HQ, or is login to hq, no need to change server
					// nothing to change action path
				}else{
					action_path = branch_url;	// login to branch server
				}
			}else{	// currently at other branch which is not in hq server
				if(login_branch_at_hq){	// this branch should login to HQ
					action_path = hq_url;	// login to hq server
				}else{
					action_path = branch_url;	// login to branch server
				}
			}

			document.f_b.action = action_path+'/login.php';
		}
	}
	
	return true;
}

function do_sa_login(){
	//alert($('ac').value);
	document.f_c.submit();
}

function do_debtor_login(){
	document.f_d.submit();
}

{/literal}
</script>

<div class="container-fluid m-0 p-0">
	<div class="row no-gutter">
		<!-- The image half -->
		<div class="col-md-5 col-lg-5 col-xl-5 d-none d-md-flex bg-primary-transparent">
			<div class="row wd-100p mx-auto text-center">
				<div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">
					<img src="../../assets/img/media/login.png" class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="logo">
				</div>
			</div>
		</div>
		<!-- The content half -->
		<div class="col-md-7 col-lg-7 col-xl-7 bg-white">
			<div class="login d-flex align-items-center py-2">
				<!-- Demo content-->
				<div class="container p-0">
					<div class="row">
						<div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
							<div class="card-sigin">
								<div class="mb-5 d-flex"> <a href="index.html"><img src="../../assets/img/brand/favicon.png" class="sign-favicon ht-40" alt="logo"></a><h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">Va<span>le</span>x</h1></div>
								<div class="card-sigin">
									<div class="main-signup-header">
										<h2>Welcome back!</h2>
										<h5 class="font-weight-semibold mb-4">Please sign in to continue.</h5>
										<!-- Error Meassages -->
										{if $errmsg}
											<div class="alert alert-danger mb-0 text-left" role="alert">
												<span class="alert-inner--icon"><i class="fe fe-slash"></i></span>
												<button aria-label="Close" class="close" data-dismiss="alert" type="button">
													<span aria-hidden="true">&times;</span>
												</button>
												<span class="alert-inner--text"> {$errmsg}</span>
											</div>
										{/if}
										{if $errmsg2}
											<div class="alert alert-danger mb-0 text-left" role="alert">
												<span class="alert-inner--icon"><i class="fe fe-slash"></i></span>
												<button aria-label="Close" class="close" data-dismiss="alert" type="button">
													<span aria-hidden="true">&times;</span>
												</button>
												<span class="alert-inner--text"> {$errmsg2}</span>
											</div>
										{/if}
										<!-- Error Messages End -->
										<div class="panel panel-primary border-0 tabs-style-3">
											<div class="tab-menu-heading">
												<div class="tabs-menu ">
													<!-- Tabs -->
													<ul class="nav panel-tabs">
														<li class=""><a href="#admin-tab" class="active" data-toggle="tab"><i class="fa fa-laptop"></i> Admin</a></li>
														<li><a href="#vendor-tab" data-toggle="tab"><i class="fa fa-cube"></i> Vendor</a></li>
														<li><a href="#debtor-tab" data-toggle="tab"><i class="fa fa-cube"></i> Debtor</a></li>
														<li><a href="#sales-agent-tab" data-toggle="tab"><i class="fa fa-cube"></i> Sales Agent</a></li>
													</ul>
												</div>
											</div>
											<div class="panel-body tabs-menu-body">
												<div class="tab-content">
													<div class="tab-pane active" id="admin-tab">
														<form method="post" name="f_l" onSubmit="return do_branch_login();">
															<div class="form-group">
																<label>Branch</label>
																<select id="branch"  name="login_branch" class="form-control select2-no-search">
																{section name=i loop=$branch}
																	{assign var=bcode value=$branch[i].code}
																		<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}
																		</option>
																{/section}
																</select>
															</div>
															<div class="form-group">
																<label>Login ID</label> <input class="form-control" name="u" type="password" >
															</div>
															<div class="form-group">
																<label>Password</label> <input class="form-control" name="p" type="password">
															</div>
															<div class="form-group">
																<div class="checkbox">
																	<div class="custom-checkbox custom-control">
																		<input type="checkbox"  class="custom-control-input" id="checkbox-2" name="tnc" value="1" checked>
																		<label for="checkbox-2" class="custom-control-label mt-1">I agree to the  <a href="https://agreement.arms.my/5" target="_blank">Terms & Conditions</a></label>
																	</div>
																</div>
															</div>
															<input type="submit" class="btn btn-main-primary btn-block" value="Sign In">
														</form>
													</div>
													{if $config.po_allow_vendor_request}
													<div class="tab-pane" id="vendor-tab">
														<form method="post" name="f_b" onSubmit="return do_vendor_login();">
															<div class="form-group">
																<label>Branch</label>
																<select id="sel_vp_branch" name="login_branch" class="form-control select2-no-search">
																	{section name=i loop=$branch}
																		{assign var=bcode value=$branch[i].code}
																		<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
																	{/section}
																</select>
															</div>
															<div class="form-group">
																<label>Enter Ticket No</label> <input class="form-control" name="ac" type="password">
															</div>
															<input type="submit" value="Sign In" class="btn btn-main-primary btn-block">
														</form>
													</div>
													{/if}
													<div class="tab-pane" id="debtor-tab">
														<form action="#">
															<div class="form-group">
																<label>Branch3</label>
																<select class="form-control select2-no-search">
																	<option label="Choose one">
																	</option>
																	<option value="Firefox">
																		HQ 1
																	</option>
																	<option value="Chrome">
																		HQ 2
																	</option>
																</select>
															</div>
															<div class="form-group">
																<label>Enter Ticket No</label> <input class="form-control" placeholder="" type="text">
															</div>
															<button class="btn btn-main-primary btn-block">Sign In</button>
														</form>
													</div>
													<div class="tab-pane" id="sales-agent-tab">
														<form action="#">
															<div class="form-group">
																<label>Branch1</label>
																<select class="form-control select2-no-search">
																	<option label="Choose one">
																	</option>
																	<option value="Firefox">
																		HQ 1
																	</option>
																	<option value="Chrome">
																		HQ 2
																	</option>
																</select>
															</div>
															<div class="form-group">
																<label>Enter Ticket No</label> <input class="form-control" placeholder="" type="text">
															</div>
															<button class="btn btn-main-primary btn-block">Sign In</button>
														</form>
													</div>
												</div>
											</div>
										</div>

										<div class="main-signin-footer mt-3">
											<p><a href="password_reset.php">Forgot password?</a></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- End -->
			</div>
		</div><!-- End -->
	</div>
</div>


{if $config.login_page_header}
	<div style="width: 60%; margin: 0px auto;" class="login-header">
		<table cellpadding="0" cellspacing="5" border="0">
			<tr>
				{foreach from=$config.login_page_header key=dummy1 item=r}
					{if $r.type eq 'image'}
						<td align="center" rowspan="{$header_info.rowspan_count}">
							<img src="{$r.path}" align="absmiddle" {if $r.width}width="{$r.width}"{/if} {if $r.height}height="{$r.height}"{/if} />
						</td>
					{elseif $r.type eq 'text'}
						<td valign="top" {if !$header_info.show_image_first}align="center"{/if}><h4>{$r.html}</h4></td>
					{/if}
					{if $r.next_row}
						</tr>
						<tr>
					{/if}
				{/foreach}
			</tr>
		</table>
	</div>
{/if}

<br style="clear:both">
{*<h1>Please Login</h1>*}
{if $errmsg}<font color="red">{$errmsg}</font>{/if}
<form method="post" name="f_l" onSubmit="return do_branch_login();">
	<table cellpadding="0" cellspacing="10" border="0" class="tbl-shadow login-container" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Please Login</h1></th></tr>
		<tr>
			<th align="left">Branch</th>
			<td>
				<select id="branch" class="form-control-2" name="login_branch">
				{section name=i loop=$branch}
					{assign var=bcode value=$branch[i].code}
					<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
				{/section}
				</select>
			</td>
		</tr>
	<tr>
		<th align="left">Login ID</th><td><input name="u" class="form-control" size="20" type="password"></td>
	</tr>
	<tr>
		<th align="left">Password</th><td><input name="p" class="form-control" size="20" type="password"></td>
	</tr>
	<tr>
		<th align="left">&nbsp;</th><td><a href="password_reset.php" style="font-size: 12px;color: #32405b;">Forgot Password?</a></td>
	</tr>
	<tr>
		<th align="right"><input type="checkbox" name="tnc" value="1" checked /></th><td nowrap><span class="small agreement-link">I agree to the <a href="https://agreement.arms.my/5" class="login-link" style="font-size: smaller;color: dodgerred; text-decoration: underline" target="_blank">Terms & Conditions</a></span></td>
	</tr>
	<tr>
		<th colspan="2"><input class="btn btn-primary" type="submit" value="Login"></th>
	</tr>
	</table>
</form>

{if $config.po_allow_vendor_request}
<br>
{*<h1>Vendor Login</h1>*}
{if $errmsg2}<font color="red">{$errmsg2}</font>{/if}
<form method="post" name="f_b" onSubmit="return do_vendor_login();">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Vendor Login</h1></th></tr>
		<tr>
			<th align="left">Branch</th>
			<td>
				<select id="sel_vp_branch" class="form-control-2" name="login_branch">
					{section name=i loop=$branch}
						{assign var=bcode value=$branch[i].code}
						<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
					{/section}
				</select>
			</td>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input name="ac" class="form-control" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input class="btn btn-primary" type="submit" value="Login" /></td>
		</tr>
	</table>
</form>
{/if}

{if $config.masterfile_enable_sa}
<br>
{*<h1>Sales Agent Login</h1>*}
{if $errmsg3}<font color="red">{$errmsg3}</font>{/if}
<form method="post" name="f_c">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Sales Agent Login</h1></th></tr>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input name="sa_ticket" class="form-control" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="button" class="btn btn-primary" onclick="do_sa_login();" value="Login"></td>
		</tr>
	</table>
</form>
{/if}

{if $config.enable_debtor_portal and $smarty.request.dp eq 1}
<h1>Debtor Login</h1>
{if $deb_login_err}<font color="red">{$deb_login_err}</font>{/if}
<form method="post" name="f_d">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr>
			<th align="left">Branch</th>
			<td>
				<select class="form-control" id="sel_dp_branch" {if $config.single_server_mode}name="login_branch"{else}onchange="form.action=this.value+'/login.php'"{/if}>
					{section name=i loop=$branch}
						{if $config.single_server_mode}
							<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if}>{$branch[i].code}</option>
						{else}
							<option value="{if $branch[i].code ne BRANCH_CODE}{$branch[i].code|strtolower|string_format:$config.no_ip_string}{/if}" {if $branch[i].code eq BRANCH_CODE}selected{/if}>{$branch[i].code}</option>
						{/if}
					{/section}
				</select>
			</td>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input class="form-control" name="debtor_key" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input class="btn btn-primary" type="button" onclick="do_debtor_login();" value="Login"></td>
		</tr>
	</table>
</form>
{/if}
</div>

<script type="text/javascript">

{literal}
document.f_l.u.focus();

/*var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
if(isFirefox && typeof(jsPrintSetup) == 'undefined'){
	$('div_install_jsprintsetup').show();
}*/
{/literal}
</script>

{include file=footer.tpl}
