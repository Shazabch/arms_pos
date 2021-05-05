{*
10/9/2018 3:20 PM Justin
- Bug fixed on wording issue.
*}
<script>

{literal}
var PRIV_CHECK = {
	call_back: undefined,
	privilege_code: undefined,
	// call this function to check privilege
	check_privilege: function(privilege_code, call_back, u, p, prompt_error){
		u = (u == undefined) ? "" : u;
		p = (p == undefined) ? "" : p;
		prompt_error = (prompt_error == undefined) ? false : prompt_error;
		this.privilege_code = privilege_code;
		
		// No Callback
		if(call_back == undefined){
			alert('Invalid Callback');
			return;
		}
		// assign call back
		this.call_back = call_back;
	
		// Show Loading Message
		curtain(true,'curtain2');
		center_div($('div_checking_privilege').show());
		
		var THIS = this;
		var params = {
				a: 'check_privilege',
				privilege_code: this.privilege_code
			};
		if(u && p){
			params['u'] = u;
			params['p'] = p;
		}
		
		new Ajax.Request('ajax_user.php',{
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				// insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				var can_callback = false;
				var need_override = false;
				var override_by_user_id = 0;
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						if(ret['granted']){
							// Got Privilege
							can_callback = true;
							override_by_user_id = int(ret['override_by_user_id']);
						}else{
							// No Privilege
							need_override = true;
						}
					}else{  // save failed
						if(ret['err'])	err_msg = ret['err'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(err_msg){
					// prompt the error
					alert(err_msg);
				}
				
				$('div_checking_privilege').hide();
				
				if(can_callback){
					curtain(false, 'curtain2');
					THIS.call_back(override_by_user_id);
				}else if(need_override){
					if(prompt_error){
						alert('Invalid Username / Password');
					}
					THIS.prompt_override();
				}else{
					THIS.close();
				}
			}
		});
	},
	// call this function when want to prompt override privilege
	prompt_override: function(privilege_code){
		if(privilege_code)	this.privilege_code = privilege_code;
		
		document.f_user_privilege.reset();
		$('span_privilge_code_required').update(this.privilege_code);
		
		// Show Override Screen
		curtain(true,'curtain2');
		center_div($('div_override_privilege_dialog').show());
		
		document.f_user_privilege['u'].focus();
	},
	// call this function to close all popup
	close: function(){
		curtain(false, 'curtain2');
		$('div_override_privilege_dialog').hide();
		$('div_checking_privilege').hide();
	},
	// function when user submit username and password for override privlege
	submit_form: function(){
		if(!this.check_override_form())	return false;
		
		var u = document.f_user_privilege['u'].value.trim();
		var p = document.f_user_privilege['p'].value.trim();
		
		$('div_override_privilege_dialog').hide();
		
		this.check_privilege(this.privilege_code, this.call_back, u, p, true);
	},
	// function to check override privilege form
	check_override_form: function(){
		var u = document.f_user_privilege['u'].value.trim();
		var p = document.f_user_privilege['p'].value.trim();
		
		if(!u){
			alert('Please key in Username');
			document.f_user_privilege['u'].focus();
			return false;
		}
		
		if(!p){
			alert('Please key in Password');
			document.f_user_privilege['p'].focus();
			return false;
		}
		
		return true;
	}
}
{/literal}
</script>


{* Checking Privilege Popup *}
<div id="div_checking_privilege" class="curtain_popup" style="position:absolute;z-index:10000;width:300px;height:auto;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<h3 align="center">
		<img src="/ui/clock.gif" align="absmiddle" />
		Checking Privilege. . .
	</h3>
</div>

{* Override Privilege Popup *}
<div id="div_override_privilege_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:auto;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_override_privilege_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Privilege Required</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="PRIV_CHECK.close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_override_privilege_dialog_content" style="padding:2px;">
		<form name="f_user_privilege" method="post" onSubmit="PRIV_CHECK.submit_form();return false;">
			<input type="hidden" name="a" value="ajax_validate_user_privilege" />
			<input type="hidden" name="privlege_code" value="" />
			
			<h1 align="center"><span id="span_privilge_code_required" style="color:blue;"></span> is Required</h1>
			Please key in the user login information which have the above privilege to override your action.
			<div style="text-align:center;">
				<table width="100%">
					<tr>
						<td width="100"><b>Username</b></td>
						<td>
							<input type="password" name="u" value="" size="15" tabindex="1" />
						</td>
						<td width="100"><b>Password</b></td>
						<td>
							<input type="password" name="p" value="" size="15" tabindex="2" />
						</td>
					</tr>
					<tr>
						<td colspan="4" align="center">
							<button style="width:200px;height:30px;" tabindex="3">
								Confirm Override
							</button>
						</td>
					</tr>
				</table>
			</div>
		</form>
	</div>
</div>
