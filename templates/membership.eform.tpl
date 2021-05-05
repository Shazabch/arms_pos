{*
7/15/2019 2:22 PM Andy
- Enhanced to auto focus on NRIC field when loaded.
- Fixed banner position.
- Enhanced to check 'mobile_scale' to scale the screen.

7/17/2019 2:20 PM Andy
- Added Address, City and State.

7/19/2019 11:20 AM Andy
- Remove postcode to become not mandatory, hide all optional fields.

10/13/2020 3:13 PM Andy
- Rework the module to become Self Registration.
*}

{include file='header.tpl' no_menu_templates=1 mobile_scale=0.8}

<style>
{literal}
input[disabled]{
	background-color: grey !important;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MEMBER_EFORM = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		
		this.f['email'].focus();
		this.f['email'].select();
	},
	// function when user click register
	submit_form: function(){
		/*if(!check_required_field(this.f)){
			return false;
		}*/
		
		var btn_register = $('btn_register');
		$(btn_register).value = "Processing...";
		$(btn_register).disabled = true;
		
		var params = $(this.f).serialize();
		
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
	                if(ret['ok'] && ret['card_no']){ // success
						// Update html
						$('div_register').hide();
						$('div_success').show();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
				$(btn_register).value = "Register";
				$(btn_register).disabled = false;
			}
		});
	},
	// function when user expand / collapse optional info
	toggle_optional_info: function(){
		var img = $('img_optional_info');
		var tbody = $('tbody_optional_info');
		
		if(tbody.style.display == ''){
			tbody.style.display = 'none';
			$(img).src = 'ui/expand.gif';
		}else{
			tbody.style.display = '';
			$(img).src = 'ui/collapse.gif';
		}
	}
}
{/literal}
</script>

<div style="margin:auto;text-align:center;">
	<img src="{get_logo_url mod='full_tax_invoice'}" height="100" style="max-height: 80px;">
</div>
		
<h1 align="center">New Member Registration</h1>


<div id="div_register">
	
	
	<form name="f_a" onSubmit="return false;" method="post">
		<input type="hidden" name="a" value="ajax_add_member" />
				
		<div class="stdframe" style="width:400px;margin:auto;">
			<table width="100%">
				{* Email Address *}
				<tr>
					<td nowrap><b>Email Address</b></td>
					<td nowrap>
						<input type="text" name="email" style="width:90%;" maxlength="50" class="required" title="Email Address" />
						<img src="/ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
				
				{* Full Name *}
				<tr>
					<td nowrap><b>Full Name</b></td>
					<td valign="top" nowrap>
						<input type="text" name="name" style="width:90%;" maxlength="80" title="Full Name" class="required" />
						<img src="/ui/rq.gif" align="absmiddle" />
					</td>
				</tr>
				
				{* Phone (Mobile) *}
				<tr>
					<td nowrap><b>Phone (Mobile)</b></td>
					<td nowrap>
						<input type="text" name="phone_3" style="width:90%;" maxlength="15" class="required" title="Phone (Mobile)" />
						<img src="/ui/rq.gif" align="absmiddle" />
					</td>
				</tr>

				
				
				<tr>
					<td colspan="2" class="small">
						<a href="javascript:void(MEMBER_EFORM.toggle_optional_info());">
							Optional Info
							<img src="ui/expand.gif" align="absmiddle" class="clickable" id="img_optional_info" />
						</a>
					</td>
				</tr>
				
				<tbody style="display:none;" id="tbody_optional_info">
					{* Gender *}
					<tr>
						<td nowrap><b>Gender</b></td>
						<td valign="top" nowrap>
							<input type="radio" name="gender" value="M" /> Male
							&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="radio" name="gender" value="F" /> Female
							&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					</tr>
					
					{* DOB *}
					<tr>
						<td nowrap><b>Date of Birth</b></td>
						<td nowrap>
							DD <input type="text" name="dob_d" size="2" maxlength="2" title="Date of Birth (Day)" onChange="miz(this);" />
							MM <input type="text" name="dob_m" size="2" maxlength="2" title="Date of Birth (Month)" onChange="miz(this);" />
							YYYY <input type="text" name="dob_y" size="3" maxlength="4" title="Date of Birth (Year)" onChange="miz(this);" />
						</td>
					</tr>
					
					{* Post Code *}
					<tr>
						<td><b>Post Code</b></td>
						<td>
							<input type="text" name="postcode" style="width:90%;" maxlength="10" />
						</td>
					</tr>
					
					{* Address *}
					<tr>
						<td><b>Address</b></td>
						<td>
							<input type="text" name="address" style="width:90%;" maxlength="100" />
						</td>
					</tr>
					
					{* City *}
					<tr>
						<td><b>City</b></td>
						<td>
							<input type="text" name="city" style="width:90%;" maxlength="50" />
						</td>
					</tr>
					
					{* State *}
					<tr>
						<td><b>State</b></td>
						<td>
							<input type="text" name="state" style="width:90%;" maxlength="20" />
						</td>
					</tr>
				
				</tbody>
			</table>
			
			<br />
			<p align="center">
				<input type="button" value="Register" style="font:bold 20px Arial; background-color:#091; color:#fff;" onClick="MEMBER_EFORM.submit_form();" id="btn_register" />
			</p>
		</div>
	</form>
</div>

<div id="div_success" class="stdframe" style="width:400px;margin:auto;display:none;">
	<h3>Register Success</h3>
	You have successfully registered as a member, <b>please check your email</b> to verify your membership. <br /><br />
	
	<span>* Please take note your member will not be active until you verify.</span>
</div>

<script>MEMBER_EFORM.initialise();</script>

{* include file='footer.tpl' *}