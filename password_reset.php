<?
/*
06/30/2016 16:30 Edwin
- unlock user when password reset

8/18/2016 10:46 AM Andy
- Fixed user retry counter no reset after un-lock.
- Fixed user id is not recorded in log when reset password.

12/19/2019 10:40 AM Justin
- Bug fixed send email function couldn't send out the email.

1/7/2020 5:41 PM Justin
- Removed the IsMail since it causes customers who are using smtp couldn't send out email.
*/
include("include/common.php");

class PASSWORD_RESET extends MODULE {
    function __construct($title) {
        parent::__construct($title);
    }
    
    function _default() { 	
        $this->display();
    }
	
	function check_email(){
		global $con;
		
		$form = $_REQUEST;
        $this->form = $form;
		
		if($this->form['email_add'] == '') die('error');
		
		$mail_sql = "select * from user where template = 0 and email=".ms($this->form['email_add']);
		$mail_query = $con->sql_query($mail_sql);
		$data = $con->sql_fetchassoc($mail_query);
		$this->login = $data['l'];
		$this->uid = $data['id'];
		
		$mail_count = $con->sql_numrows($mail_query);
		$con->sql_freeresult($mail_query);
		
		if($mail_count == 1) 	$this->send_mail('email');
		elseif($mail_count > 1)	print 'more_email';
		else					print 'email_error';
		
		exit;
	}
	
	function check_login_name(){
		global $con;
		
		$form = $_REQUEST;
        $this->form = $form;
		
		if($this->form['email_add'] == '' || $this->form['login_name'] == '') die('error');
		
		$login_name_sql = "select * from user where template = 0 and email = ".ms($form['email_add'])." and l = ".ms($form['login_name']);
		$login_name_query = $con->sql_query($login_name_sql);
		$data = $con->sql_fetchassoc($login_name_query);
		$this->login = $data['l'];
		$this->uid = $data['id'];
		
		$login_name_count = $con->sql_numrows($login_name_query);
		$con->sql_freeresult($login_name_query);
		
		if($login_name_count == 1)	$this->send_mail('login_name');
		else						print 'login_name_error';
		
		exit;
	}
    
    function send_mail($type) {
        global $con;
        
		if(($type == 'email' && $this->form['email_add'] == '') || ($type == 'login_name' && $this->form['email_add'] == '' && $this->form['login_name'] == '')) {
			die("error");
		}
		
		if(!$this->uid){
			die("user id error");
		}
		
        $temp_pass = $this->generate_temp_passsword();
        
        /*if($type == 'email') {
            $con->sql_query("update user set p = md5(".ms($temp_pass)."), locked=0 where template = 0 and email = ".ms($this->form['email_add']));
			log_br($this->uid, 'LOGIN', 'NULL', "Account's password reset and account unlocked");
        }else {
            $con->sql_query("update user set p = md5(".ms($temp_pass)."), locked=0 where template = 0 and email = ".ms($this->form['email_add'])." and l = ".ms($this->form['login_name']));
			log_br($this->uid, 'LOGIN', 'NULL', "Account's password reset and account unlocked");
        }*/
		$con->sql_query("update user set p = md5(".ms($temp_pass)."), locked=0 where id=".mi($this->uid));
		log_br($this->uid, 'LOGIN', 'NULL', "Account's password reset and account unlocked");
		
		// reset retry counter, but don update lastlogin
		$con->sql_query("update user_status set retry=0,lastlogin=lastlogin where user_id=".mi($this->uid));
				
        include_once("include/class.phpmailer.php");
        $mailer = new PHPMailer(true);
        
        // set ARMS as sender
        $mailer->FromName = "ARMS Software International Sdn Bhd";

        $mailer->Subject = "Your password has been reset.";
        $mailer->IsHTML(true);

        if($mailer->ValidateAddress($this->form['email_add'])){
            // add email for sender
            $mailer->AddAddress($this->form['email_add']);
            $mailer->Body = "<h3>Good day,</h3>
                            <p>We have received a request to reset your password.</p>
                            <p>Login ID : ".$this->login."</p>
                            <p>New Password : $temp_pass</p>";
                            
            // send email
            $send_success = phpmailer_send($mailer);
            print $type.'_ok';
        }else {
            print $type.'_error';
        }
    }
    
    function generate_temp_passsword() {
        $char = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $password = substr(str_shuffle($char), 0, 8);
        return $password;
    }
}
$PASSWORD_RESET = new PASSWORD_RESET('Password Reset');
?>