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
<div class="container-fluid">
                <div class="row no-gutter">
                    <!-- The image half -->
                    <div class="col-md-6 col-lg-6 col-xl-7 d-none d-md-flex bg-primary-transparent">
                        <div class="row wd-100p mx-auto text-center">
                            <div class="col-md-12 col-lg-12 col-xl-12 my-auto mx-auto wd-100p">
                                <img src="../../assets/img/backgrounds/login-bg.png" class="my-auto ht-xl-80p wd-md-100p wd-xl-80p mx-auto" alt="logo">
                            </div>
                        </div>
                    </div>
                    <!-- The content half -->
                    <div class="col-md-6 col-lg-6 col-xl-5 bg-white">
                        <div class="login d-flex align-items-center py-2">
                            <!-- Demo content-->
                            <div class="container p-0">
                                <div class="row">
                                    <div class="col-md-10 col-lg-10 col-xl-9 mx-auto">
                                    <div class="mb-5 d-flex"> <a href="index.html"><img src="../../assets/img/brand/favicon.png" class="sign-favicon ht-40" alt="logo"></a><h1 class="main-logo1 ml-1 mr-0 my-auto tx-28">Va<span>le</span>x</h1></div>
                                        <div class="main-card-signin d-md-flex bg-white">
                                            <div class="wd-100p">
                                                <div class="main-signin-header" id="email">
                                                    <h2>Forgot Password!</h2>
                                                    <h4>Please Enter Your Email</h4>
                                                    <form method="post" name="f_e" onSubmit="return false;">
                                                        <input type="hidden" name="a" value="check_email">
                                                        <div class="form-group">
                                                            <label>Email</label> <input class="form-control" placeholder="Enter your email" type="text" name="email_add" focus>
                                                            <span class="text-sm text-danger" id="div_email_error"></span>
                                                        </div>
                                                        <input type="submit"  id="email_btn" value="Send" onclick="PASSWORD_RESET.submit_email();" class="btn btn-primary btn-block">
                                                    </form>
                                                </div>
                                                <div class="main-signup-footer mg-t-20">
                                                    <p>Forget it, <a href="#"> Send me back</a> to the sign in screen.</p>
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

<div id="main_div" style="display:table;margin: 200px auto;margin-bottom: 200px;">
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