{*
06/29/2020 05:55 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var PACKAGE_REDEMPTION = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
		this.f['nric_card_no'].focus();
	},
	// core function to check form
	check_form: function(){
		if(!check_required_field(this.f))	return false;
		
		return true;
	},
	// function when user press enter to check member
	check_member: function(){
		// Check Form
		if(!this.check_form())	return;
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
		
		var params = $(this.f).serialize();
				
		var THIS = this;
		
		//alert(params);return;
		var THIS = this;
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
	                if(ret['ok'] && ret['nric']){ // success
						document.location = phpself+'?nric='+ret['nric'];
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
				$('span_err_msg').update(err_msg);
				GLOBAL_MODULE.hide_wait_popup();
			}
		});
	}
}
{/literal}
</script>
<br />

<div align="center">
	<h1>Package Redemption</h1>
	<form name="f_a" class="biginput" onSubmit="PACKAGE_REDEMPTION.check_member();return false;" method="post">
		<input type="hidden" name="a" value="ajax_check_member" />
		
		<table>
			<tr>
				<th><h2>NRIC / Card No</h2></th>
				<td>
					<input type="text" name="nric_card_no" title="NRIC / Card No" class="required" />
				</td>
			</tr>
		</table>
		<div align="center">
			<input class="btn btn-primary" style="font-size: 13px" type="button" value="Enter" onclick="PACKAGE_REDEMPTION.check_member();">
		</div>
	</form>

	<br /><span style="color:red;font-weight: bold;" id="span_err_msg"></span>

</div>

<script>PACKAGE_REDEMPTION.initialise();</script>
{include file='footer.tpl'}