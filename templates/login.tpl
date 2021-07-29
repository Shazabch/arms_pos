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

<div class="container-fluid d-flex  align-items-center justify-content-center vww-80 p-0" style="height: 100vh;">
	<div class="col-md-12">
		<div class="card rounded-0 ">
			<div class="card-body p-0">
				<div class="row">
					<div class="col-lg-6 px-responsive-1 pt-4 pb-4">
						<div class="d-flex justify-content-center align-items-center">
							<div class="container-fluid">
								<div class="bg-navy-blue p-1 rounded text-center font-weight-bold text-white mb-4 sign-in" style="">
									<h2>Sign In</h2>
								</div>
								
								<div class="panel panel-primary border-0 tabs-style-3 p-0">
									<div class="tab-menu-heading mb-4">
										<div class="tabs-menu ">
											<!-- Tabs -->
											<ul class="nav panel-tabs">
												<li class="tab-button"><a href="#admin-tab" class="active" data-toggle="tab"> Admin</a></li>
												<li class="tab-button"><a href="#vendor-tab" data-toggle="tab"> Vendor</a></li>
												<li class="tab-button"><a href="#debtor-tab" data-toggle="tab"> Debtor</a></li>
												<li class="tab-button"><a href="#sales-agent-tab" data-toggle="tab"> Sales Agent</a></li>
											</ul>
										</div>
									</div>
									{if $errmsg}
										<div class="alert alert-danger mb-2 text-left" role="alert">
											<span class="alert-inner--icon"><i class="fe fe-slash"></i></span>
											<span class="alert-inner--text"> {$errmsg}</span>
										</div>
									{/if}
									{if $errmsg2}
										<div class="alert alert-danger mb-2 text-left" role="alert">
											<span class="alert-inner--icon"><i class="fe fe-slash"></i></span>
											<span class="alert-inner--text"> {$errmsg2}</span>
										</div>
									{/if}
									<div class="panel-body tabs-menu-body p-0">
										<div class="tab-content">
											<div class="tab-pane active" id="admin-tab">
												<form method="post" name="f_l" onSubmit="return do_branch_login();">
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Branch</label>
														</div>
														<div class="col-lg-9">
															<select id="branch"  name="login_branch" class="form-control form-control-b-line select2-no-search">
															{section name=i loop=$branch}
																{assign var=bcode value=$branch[i].code}
																	<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}
																	</option>
															{/section}
															</select>
														</div>
													</div>
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Login ID</label>
														</div>
														<div class="col-lg-9">
															<input class="form-control form-control-b-line" name="u" type="password" >
														</div>
													</div>
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Password</label>
														</div>
														<div class="col-lg-9">
															<input class="form-control form-control-b-line" name="p" type="password">
														</div>
													</div>
													<div class="form-group">
														<div class="checkbox pl-1">
															<div class="custom-checkbox custom-control">
																<input type="checkbox"  class="custom-control-input" id="checkbox-2" name="tnc" value="1" checked>
																<label for="checkbox-2" class="custom-control-label mt-1">I agree to the  <a href="https://agreement.arms.my/5" class="text-navy-blue" target="_blank">Terms & Conditions</a></label>
															</div>
														</div>
													</div>
													<input type="submit" class="btn btn-main-primary bg-navy-blue btn-block" value="Sign In">
												</form>
											</div>
											{if $config.po_allow_vendor_request}
											<div class="tab-pane" id="vendor-tab">
												<form method="post" name="f_b" onSubmit="return do_vendor_login();">
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Branch</label>
														</div>
														<div class="col-lg-9">
															<select id="sel_vp_branch" name="login_branch" class="form-control form-control-b-line select2-no-search">
																{section name=i loop=$branch}
																	{assign var=bcode value=$branch[i].code}
																	<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
																{/section}
															</select>
														</div>
													</div>
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Enter Ticket No</label>
														</div>
														<div class="col-lg-9">
															<input class="form-control form-control-b-line" name="ac" type="password">
														</div>
													</div>
													<input type="submit" value="Sign In" class="btn btn-main-primary bg-navy-blue btn-block">
												</form>
											</div>
											{/if}
											<div class="tab-pane" id="debtor-tab">
												<form action="#">
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Branch</label>
														</div>
														<div class="col-lg-9">
															<select class="form-control form-control-b-line select2-no-search">
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
													</div>
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Enter Ticket No</label>
														</div>
														<div class="col-lg-9">
															<input class="form-control form-control-b-line" placeholder="" type="text">
														</div>
													</div>
													<button class="btn btn-main-primary bg-navy-blue btn-block">Sign In</button>
												</form>
											</div>
											<div class="tab-pane" id="sales-agent-tab">
												<form action="#">
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Branch</label>
														</div>
														<div class="col-lg-9">
															<select class="form-control form-control-b-line select2-no-search">
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
													</div>
													<div class="row row-xs align-items-end mg-b-20">
														<div class="col-lg-3">
															<label class="form-label">Enter Ticket No</label>
														</div>
														<div class="col-lg-9">
															<input class="form-control form-control-b-line" placeholder="" type="text">
														</div>
													</div>
													<button class="btn btn-main-primary bg-navy-blue btn-block">Sign In</button>
												</form>
											</div>
										</div>
									</div>
								</div>
								<div class="main-signin-footer mt-1">
									<p><a href="password_reset.php" class="text-navy-blue">Forgot password?</a></p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6 bg-navy-blue d-none d-lg-flex justify-content-center align-items-center">
						<div>
							{if $config.login_page_header}
								<div style="width: 70%; margin: 0px auto;">
									<table class="table table-borderless text-white bg-transparent">
										<tr>
											{foreach from=$config.login_page_header key=dummy1 item=r}
												{if $r.type eq 'image'}
													<td align="center" rowspan="{$header_info.rowspan_count}">
														<img src="../{$r.path}" align="absmiddle" {if $r.width}width="{$r.width}"{/if} {if $r.height}height="{$r.height}"{/if} />
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
							{else}
								<img src="../../assets/img/brand/fvc.png">
								<h2 class="text-center text-white font-weight-bold mt-2 tx-spacing-8">ARMS</h2>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

{literal}
document.f_l.u.focus();

console.log("xxx");

/*var isFirefox = typeof InstallTrigger !== 'undefined';   // Firefox 1.0+
if(isFirefox && typeof(jsPrintSetup) == 'undefined'){
	$('div_install_jsprintsetup').show();
}*/
{/literal}
</script>

	<script src="../assets/plugins/jquery/jquery.min.js"></script>

			<script type="text/javascript">
			{literal}
				jQuery.noConflict();
				 jQuery(document).ready(function(){
			      console.log("ready jix");
			      });


			{/literal}	
		</script>
		<script src="../assets/js/custom.js"></script>

			<script type="text/javascript">
			{literal}
				 jQuery(document).ready(function(){
			     	jQuery(".tab-button").click(function(e){
			     		e.preventDefault();
					   
					   	var tab_content_id = jQuery(this).children("a").attr("href");
					   	jQuery(".tab-button").children("a").removeClass("active");

					   	 jQuery(this).children("a").addClass("active");
					   	 jQuery(".tab-pane").removeClass("active");
					   	 jQuery(tab_content_id).addClass("active");
					});
			      });


			{/literal}	
		</script>