{*
7/25/2017 4:46 PM Justin
- Enhanced to use email regular expression checking from global settings.
*}
{include file=header.tpl}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var email_prefix = {$EMAIL_REGEX}; // cannot use single quote due to it will spoil the regexp
{literal}
var PASSWORD_RESET = {
    e: undefined,
    l: undefined,
    mail: undefined,
    initialize: function() {
        this.e = document.f_e;
        this.l = document.f_l;
        this.e['email_add'].focus();
    },
    submit_email: function() {
		var THIS = this;
        var mail = this.e['email_add'].value;
        this.mail = mail;
        if (mail == '') {
            $('div_email_error').update('Please fill in email address.');
            return false;
        }else {
            if(!email_prefix.test(mail)) {
                $('div_email_error').update('Invalid email format.');
                return false;    
            }else	THIS.sending('email_btn');
        }
		
        $('div_email_error').update('');
		
        new Ajax.Request(phpself ,{
            parameters: Form.serialize(this.e),
            onComplete: function(v){
                switch(v.responseText.trim()) {
                    case 'email_ok':
						THIS.show_info_div('email');
						$('info_msg').innerHTML = "Your password has been reset. You will receive an email with your new temporary password.";
                        break;
                    case 'email_error':
                        THIS.show_info_div('email');
						$('info_msg').innerHTML = "Record not found, make sure your email is correct.";
                        break;
                    case 'more_email':
						$('email').hide();
						$('login').show('table-cell');
						THIS.l['login_name'].focus();
                        break;
					case 'error':
						alert("Error!! Please try again.");
						window.location = phpself;
						break;
                }
            }
        });
    },
    submit_login_name: function() {
        var THIS = this;
        this.l['email_add'].value = this.mail;
        var login = this.l['login_name'].value;
        if (login == '') {
            $('login_name_error').show();
            return false;
        }else	THIS.sending('login_name_btn');
		
		$('login_name_error').hide();
		
        new Ajax.Request(phpself ,{
            parameters: Form.serialize(this.l),
            onComplete: function(v){
                switch(v.responseText.trim()) {
                    case 'login_name_ok':
                        THIS.show_info_div('login');
						$('info_msg').innerHTML = "Your password has been reset. You will receive an email with your new temporary password.";
                        break;
                    case 'login_name_error':
                        THIS.show_info_div('login');
						$('info_msg').innerHTML = "Record not found, make sure your email or login name is correct.";
                        break;
					case 'error':
						alert("Error!! Please try again.");
						window.location = phpself;
						break;
                }
            }
        });
    },
	sending: function(type) {
		$(type).value = "Sending...";
		$(type).disabled = true;
	},
	show_info_div: function(type) {
		$(type).hide();
		$('info').show('table-cell');
	},
    close: function() {
        window.location = "login.php";
    }
}
{/literal}
</script>
<div id="main_div" style="display:table;margin: 200px auto;margin-bottom: 200px;">
    <div id="email" style="display: table-cell; vertical-align: middle">
        <form method="post" name="f_e" onSubmit="return false;">
            <input type="hidden" name="a" value="check_email">
            <h3>Enter your email address and we'll reset your password.</h3>
            <table cellpadding="5px" align="center">
                <tr><th>EMAIL ADDRESS</th></tr>
                <tr><th>
                    <input type="text" name="email_add" focus>
                    <div style="color: red;" id="div_email_error"></div>
                </th></tr>
                <tr><th><input type="submit" id="email_btn" value="Submit" onclick="PASSWORD_RESET.submit_email();"></th></tr>
            </table>
        </form>
    </div>
    
    <div id="login" style="display:none; vertical-align: middle;">
        <form method="post" name="f_l" onSubmit="return false;">
            <input type="hidden" name="a" value="check_login_name">
            <input type="hidden" name="email_add">
            <h3>More than one record was found, enter login name to specify your account.</h3>
            <table cellpadding="5px" align="center">
                <tr><th>LOGIN NAME</th></tr>
                <tr><th>
                    <input type="text" name="login_name">
                    <div style="display: none;color: red;" id="login_name_error">Please fill in login name.</div>
                </th></tr>
                <tr><th><input type="submit" id="login_name_btn" value="Submit" onclick="PASSWORD_RESET.submit_login_name();"></th></tr>
            </table>
        </form>
    </div>
    
	<div id="info" style="display: none; text-align: center">
        <h3 id="info_msg"></h3>
        <button type="button" onClick="PASSWORD_RESET.close();">OK</button>
    </div>
</div>
{include file=footer.tpl}

<script type="text/javascript">
{literal}
PASSWORD_RESET.initialize();
{/literal}
</script>