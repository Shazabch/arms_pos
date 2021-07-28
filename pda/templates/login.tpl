{*
3/15/2012 11:56:32 AM Justin
- Added "pda" onto multi server mode branch choice menu to redirect user into PDA home page.

8/13/2013 5:13 PM Andy
- Add show HTTP_USER_AGENT.
- Add submit form action to /pda/login.php (fix MOTOROLA HANDHELD COMPUTER MC2180)

2/20/2014 5:23 PM Andy
- Enhance the multi server mode to allow some sub-branch to working in HQ server.

10/22/2020 6:11 PM Rayleen
- Align Login div to center
- Add border and shadow in login table

10/29/2020 11:32 PM Sheila
- Fixed footer css

11/05/2020 9:54 AM Rayleen
- Add login logo

*}

{include file='header.tpl'}

<script type="text/javascript">

var single_server_mode = int('{$config.single_server_mode}');
var BRANCH_CODE = '{$BRANCH_CODE}';
var hq_url = '{"hq"|string_format:$config.no_ip_string}';
var curr_branch_at_hq = int('{$config.branch_at_hq.$BRANCH_CODE}');

{literal}
function do_branch_login(){	
	if(!single_server_mode){	// is multi server
		var sel_branch = $('#branch').get(0);
		var bcode = sel_branch.value;
		var action_path = '';
		
		
		if(bcode != BRANCH_CODE){	// login to other branch
			var opt = sel_branch.options[sel_branch.selectedIndex];	// get selected branch <option>
			var branch_url = $(opt).attr('branch_url');	// get original branch url
			var login_branch_at_hq = int($(opt).attr('branch_at_hq'));	// get whether this branch is at hq server
			
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

			document.f_l.action = action_path+'/pda/login.php';
		}
	}
	
	return true;
}
{/literal}
</script>
<div class="container-fluid d-flex  align-items-center justify-content-center" style="height: 100vh; width: 80vw;">
	<div class="col-12">
		<div class="card rounded-0">
			<div class="card-body p-0">
				<div class="row">
					<div class="col-lg-6 px-5 pt-5 pb-3">
						<div class="d-flex justify-content-center align-items-center">
							<div class="container-fluid">
								<div class="d-flex justify-content-between align-items-center mb-5">
									<h4 class="text-navy-blue font-weight-bold">{#SITE_NAME#}</h4>
									<h5 class="bg-success text-white p-2 rounded shadow font-weight-bold">{$BRANCH_CODE}</h5>
								</div>
								<h3 class="text-navy-blue font-weight-bold">Sign In</h3>
								<p class="text-muted">Please sign in to continue.</p>
								{if $errmsg}
									<div class="alert alert-danger mb-2" role="alert">
										<button aria-label="Close" class="close" data-dismiss="alert" type="button">
											<span aria-hidden="true">&times;</span>
										</button>
										{$errmsg}
									</div>
								{/if}
								<form class="mt-4" method="post" name="f_l" action="/pda/login.php" onSubmit="return do_branch_login();">
									<div class="row row-xs align-items-end mg-b-20">
										<div class="col-lg-2">
											<label class="form-labels">Branch</label>
										</div>
										<div class="col-lg-10">
											<select id="branch" name="login_branch" class="form-control form-control-b-line">
												{section name=i loop=$branch}
													{section name=i loop=$branch}
														{assign var=bcode value=$branch[i].code}
														<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
													{/section}
												{/section}
											</select>
										</div>
									</div>
									<div class="row row-xs align-items-end mg-b-20">
										<div class="col-lg-2">
											<label class="form-label">Username</label>
										</div>
										<div class="col-lg-10">
											<input type="text" class="form-control form-control-b-line" name="u" size="20">
										</div>
									</div>
									<div class="row row-xs align-items-end mg-b-20">
										<div class="col-lg-2">
											<label class="form-label">Password</label>
										</div>
										<div class="col-lg-10">
											<input type="password" class="form-control form-control-b-line" name="p" size="20">
										</div>
									</div>
									<div class="row row-xs align-items-center mg-b-20 mt-5">
										<div class="col-lg-12">
											<input type="submit" class="btn btn-main-primary btn-block" value="Sign In" name="">
										</div>
									</div>	
								</form>
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
								<h2 class="text-center text-white font-weight-bold mt-2 tx-spacing-8">ARMS®</h2>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div align="center">
{if $config.login_page_header}
	<div style="width: 60%; margin: 0px auto;" class="login-header">
		<table cellpadding="0" cellspacing="5" border="0">
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
{/if}
</div>
<script type="text/javascript">
{literal}
document.f_l['u'].focus();
{/literal}
</script>
<!-- Footer opened -->
<!-- <div class="main-footer ht-40">
	<div class="container-fluid ">
		<h3>Device Info</h3>
		<span>({$smarty.server.HTTP_USER_AGENT})</span>
	</div>
</div> -->
<!-- Footer closed -->

{include file='footer.tpl'}