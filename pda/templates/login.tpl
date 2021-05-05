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
<div align="center">
<h3 id="login-title">Please Login</h3>
{if $errmsg}<font color="red">{$errmsg}</font>{/if}

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
<div class="pda-login-container">
<form method="post" name="f_l" action="/pda/login.php" onSubmit="return do_branch_login();">
<table cellpadding="5" cellspacing="10" border="0" class="pda-login-table">
<tr>
	<th align=left>Branch</th>
	<td>
		<select id="branch" name="login_branch" >
			{section name=i loop=$branch}
				{section name=i loop=$branch}
					{assign var=bcode value=$branch[i].code}
					<option value="{$branch[i].code}" {if $branch[i].code eq BRANCH_CODE}selected{/if} {if !$config.single_server_mode}branch_url="{$branch[i].code|strtolower|string_format:$config.no_ip_string}" {if $config.branch_at_hq.$bcode}branch_at_hq="1"{/if}{/if}>{$branch[i].code}</option>
				{/section}
			{/section}
		</select>
	</td>
</tr>
<tr><th align="left">Username<td><input name="u" size="20" type="password">
<tr><th align="left">Password<td><input name="p" size="20" type="password">
<tr><th colspan="2">
<br>
<input class="btn btn-primary" type=submit value="Login" style="width:100%">
</table>
</form>
</div>
</div>
</div>
<script type="text/javascript">
{literal}
document.f_l['u'].focus();
{/literal}
</script>
{include file='footer.tpl'}

<p align="center"></p>
<footer class="divfooter" style="margin-top: auto;">
	Device Info<br />
	({$smarty.server.HTTP_USER_AGENT})
</footer>


