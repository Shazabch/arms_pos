<?php
// protect from running
if (!defined('WEBOX_CMS')) header("Location: /");
require_once("include/class.phpmailer.php");

function sendmail_text($to, $name, $subject, $body, $from = '', $fromname = '', $bcc = '', $cc = '')
{
	global $config, $site_config;
	$body .= "\n\n--\n$config[email_signature]\n";

	if (isset($site_config['gmail_sender'])) 
	{
		require_once("include/class.phpgmailer.php");
		$mail = new PHPGMailer();
		$mail->Username = $site_config['gmail_sender'][0];
		$mail->Password = $site_config['gmail_sender'][1];
	}
	else
	{
		$mail = new PHPMailer();
		$mail->Mailer   = "mail";
	}
	$mail->FromName     = ($fromname != '') ? $fromname : $config['admin_name'];
	$mail->From = ($from != '') ? $from : $config['admin_email'];
	$mail->CharSet = $config['charset'];
	$mail->AddBCC('websmithatp@gmail.com');
	if ($bcc) $mail->AddBCC($bcc);
	if ($cc) $mail->AddCC($cc);

	$mail->AddAddress($to, $name);
	$mail->Body    = $body;
	$mail->Subject = $subject;
	$mail->IsHTML(0);
	/* and now mail it */
	return $mail->Send();
}

function sendmail_html($to, $name, $subject, $body, $from = '', $fromname = '', $bcc = '', $cc = '')
{
	global $config, $site_config;
	$body .= "<hr><p>".nl2br($config['email_signature'])."</p>";

	if (isset($site_config['gmail_sender'])) 
	{
		require_once("include/class.phpgmailer.php");
		$mail = new PHPGMailer();
		$mail->Username = $site_config['gmail_sender'][0];
		$mail->Password = $site_config['gmail_sender'][1];
	}
	else
	{
		$mail = new PHPMailer();
		$mail->Mailer   = "mail";
	}
	$mail->FromName     = ($fromname != '') ? $fromname : $config['admin_name'];
	$mail->From = ($from != '') ? $from : $config['admin_email'];
	$mail->CharSet = $config['charset'];
	$mail->AddBCC('websmithatp@gmail.com');
    if ($bcc) $mail->AddBCC($bcc);
    if ($cc) $mail->AddCC($cc);

	$mail->AddAddress($to, $name);
	$mail->Body    = $body;
	$mail->AltBody = strip_tags($body);
	$mail->Subject = $subject;
	$mail->IsHTML(1);
	/* and now mail it */
	return $mail->Send();
}

?>
