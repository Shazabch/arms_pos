{*
3/4/2015  Yinsee
- add IP address

3/9/2015 Yinsee
- reformat 

3/12 15:18 yinsee
- update T&C
*}
{include file='header.print.tpl'}
{literal}
<style>
#tbl {
	text-align: justify;
	text-justify: inter-word;
	width: 50%;
	min-width: 600px;
}

#registration{
	line-height: 2;
}

.errmsg {
	font: bold 12px arial, "Sans Serif";
	color: #f00;
}

.errmsg ul {
  margin:.5em 0 1.25em;
  padding:0;
  list-style:none;
}

.errmsg ul li {
  background:url("/ui/messages.gif") no-repeat 2px .25em;
  margin:0;
  padding:1px 0 1px 20px;
  margin-bottom:3px;
  line-height:1.4em;
}

.printarea {
    clear: both;
    page-break-after: auto;
}

body {
	line-height: 2em;
}
p {
	line-height: 2em;
}

.greyfont td, .greyfont td a { 
	color: #999; font-size:8pt; text-align: justify;
	text-justify: inter-word;
 }

 .scrollbox {
 	margin:0 auto;
 	height:300px; overflow-y:scroll; border:1px solid #ccc; background:#eee;
 }

@media print {
	#tbl{
		width: 100% !important;
	}
	.scrollbox {
		width: 100% !important;
		height: auto !important;
		border: none;
		background: none;	
	}
	.noprint {
		display: none;
	}
}

</style>
{/literal}

{literal}
<script>
function check_form(){
	if(!confirm("Please select OK to confirm submitting the request.")) return false;

	if(document.f_a['register_gst'].checked == false){
		alert("Please tick to agree for activate GST");
		document.f_a['register_gst'].focus();
		return false;
	}

	if(document.f_a['full_name'].value.trim() == ""){
		alert("Please enter your Full Name");
		document.f_a['full_name'].focus();
		return false;
	}

	if(document.f_a['nric'].value.trim() == ""){
		alert("Please enter your I/C");
		document.f_a['nric'].focus();
		return false;
	}

	if(document.f_a['company_name'].value.trim() == ""){
		alert("Please enter your Company Name");
		document.f_a['company_name'].focus();
		return false;
	}
	
	if(document.f_a['email'].value.trim() == ""){
		alert("Please enter your Email Address");
		document.f_a['email'].focus();
		return false;
	}
	
	return true;
}
</script>
{/literal}

<div class="printarea" align="center">
<form name="f_a" method="post" onsubmit="return check_form();">
<input type="hidden" name="a" value="activate" />
<table id="tbl">
<tr><td>
<h1 align="center">ARMS GST Module Activation</h1>
<p>
In line with Goods and Services Tax Act 2014, we have upgraded our system to be ready for GST implementation on 1st April 2015. We shall activate your system within 1 working day (Monday-Friday 9am-6pm) upon receiving your request form below.
</p>
<br>
</td></tr>
{if $err_msg}
<tr><td>
	<div id=err><div class=errmsg><ul>
	{foreach from=$err_msg item=e}
	<li> {$e}
	{/foreach}
	</ul></div></div>
</td></tr>
{/if}
	<tr><td {if !$is_generate}id="registration"{/if}>
	<input type="checkbox" name="register_gst" value="1" checked {if $is_generate}disabled{/if} /> Yes, I, 
	{if !$is_generate}
		<input type="text" name="full_name" size="30" value="{$form.full_name}" /> 
	{else}
		<b><u>{$form.full_name|strtoupper}</u></b>
	{/if}
	(Full Name), NRIC No.: 
	{if !$is_generate}
		<input type="text" name="nric" size="15" value="{$form.nric}" />
	{else}
		<b><u>{$form.nric}</u></b>
	{/if}
	, on behalf of 
	{if !$is_generate}
		<input type="text" name="company_name" size="60" value="{$form.company_name}" />
	{else}
		<b><u>{$form.company_name|strtoupper}</u></b>
	{/if}
	(Company Name as per business registration) hereby confirm acceptance of ARMS Software International Sdn Bhd's ["ASI"] Terms and Conditions as stated below.<br /><br />

	<table>
	<tr>
		<td>Email Address:</td>
		<td>
		{if !$is_generate}
			<input type="text" name="email" size="25" value="{$form.email}" />
		{else}
			{$form.email}
		{/if}
		</td>
	</tr>
	<tr>
		<td>Timestamp:</td>
		<td>
		{if !$is_generate}
			<input type="text" name="timestamp" size="18" value="{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}" readonly />
		{else}
			{$form.timestamp}
		{/if}
		</td>
	</tr>
	<tr>
		<td>IP:</td>
		<td>
		{if !$is_generate}
			<input type="text" name="ip_address" size="18" value="{$smarty.server.REMOTE_ADDR}" readonly />
		{else}
			{$form.ip_address}
		{/if}
		</td>
	</tr>
	</table>
	<br /><br />
</td></tr>

{if !$is_generate}
	<tr class="noprint"><td align="center">
	<input type="submit" name="registration_btn" value="Submit &amp; Activate GST" />
	</td></tr>
{/if}
</table>
<br>

{if !$is_generate}<div id="tbl" class="scrollbox">{/if}
<table {if !$is_generate}class="greyfont"{else}id="tbl"{/if}>
{if $config.arms_go_module}

	<tr><td>
	PLEASE NOTE THAT YOU HEREBY AGREE TO AND INTEND TO BE BOUND BY ASI’S TERMS AND CONDITIONS OF SALE HEREIN AND ALSO THE AFORESAID ONLINE TERMS AND CONDITIONS, AS APPLICABLE IRRESPECTIVE WHETHER OR NOT YOU HAVE EXECUTED ANY OF THE APPLICABLE AGREEMENTS IN A HARDCOPY FORMAT.<br /><br />

	FOR YOUR OWN BENEFIT AND PROTECTION, PLEASE READ THESE CONDITIONS CAREFULLY. PLEASE PRINT A COPY EACH OF THE ABOVE DOCUMENTS/ONLINE AGREEMENTS FOR YOUR RECORDS. IF YOU HAVE ANY QUESTIONS ABOUT THESE CONDITIONS, PLEASE CONTACT US IMMEDIATELY IN WRITING.<br /><br />
	</td></tr>
	
	<tr><td><b><u>
	General T&#38;C<br /> <br />
	</u></b></td></tr>
	<tr><td>

	Overdue Payment: 1.8 % monthly interest may be levied on overdue payment<br /><br />

	If payment is not being made as agreed payment terms, we reserve the right to turn off the software service,  turn off certain features, turn off the additional enchancement, stop giving technical support and stop the remote backup service whichever applicable<br /><br />

	Cancellation: All payment made will be forfeited and respective contract will automatically become null and void<br /><br />

	Goods return or exchange policy: Goods once sold are not returned or exchanged<br /><br />

	Price quoted above includes three sessions of training on Arms Go only.<br /><br />

	Any additional changes or modification that are not stated above are subjected to charges.<br /><br />

	Traveling expenses, food and lodging are excluded for on-site setup and implementation for East Malaysia. Additional charges apply.<br /><br />

	Delivery time is subject to pre-agreed schedule<br /><br />

	Price quoted include one time 8 hours on-site standby service during POS Counter kick-off for business. Additional hour(s) will be charged accordingly.<br /><br />

	Shipping and handling charges are excluded from the quoted price above.<br /><br />

	ARMS shall not be held responsible for any delay of delivery or implementation due to problem caused by customer or third party<br /><br />

	If the Network cabling work (LAN) or Wireless local area network (WLAN) is not provided by WSATP, then the Network cabling work (LAN) or Wireless local area network (WLAN) has to be fully setup and tested before POS Counter Terminal setup.<br /><br />

	WSATP reserves the right to claim compensation (such as additional manhour, transportation, board and lodging) from the Purchaser for expanses incurred due to any problem caused by networking and/or hardware where the networking was not setup by WSATP and/or hardware was not purchased from WSATP. <br /><br />

	WSATP is not responsible for any third party's software and shall have no liability for your use of third party software.<br /><br />

	Onsite support can be arranged if necessary and will be charged according to our on-site support charges.<br /><br />

	Support will be given via the following modes remotely:<br /><br />

	By Ticketing: <a href="http://support.arms.com.my" target="_blank">http://support.arms.com.my/</a><br />
	By Email: support@arms.my<br /><br />
	By Phone: 604-5075842<br /><br />


	Onsite Training: 3 sessions free on-site training will be given to the customer within the first 6 months.<br /><br />
	 
	Service Reconnection: A reconnection fee of RM50.00 will be charged and advance payment should be made before the reactivation of the service.<br /><br />
	 
	Termination of service: ARMS International Software Sdn Bhd must be notified 30 days in advance via written notice or otherwise, as stated in the SOFTWARE AS A SERVICE (SaaS) SUBSCRIPTION AGREEMENT<br /><br />
	 
	Data Hand-over Charges: Termination data (mysql dump) hand over will be charge at RM500.00<br /><br />
	</td></tr>

	<tr><td>
	ARMS GO SaaS Agreement, Ver. 1 at http://www.arms.my/agreements/4<br /><br />
	</td></tr>
{else}

	<tr><td>
	PLEASE NOTE THAT YOU HEREBY AGREE TO AND INTEND TO BE BOUND BY ASI’S TERMS AND CONDITIONS OF SALE HEREIN AND ALSO THE AFORESAID ONLINE TERMS AND CONDITIONS, AS APPLICABLE IRRESPECTIVE WHETHER OR NOT YOU HAVE EXECUTED ANY OF THE APPLICABLE AGREEMENTS IN A HARDCOPY FORMAT.<br /><br />

	FOR YOUR OWN BENEFIT AND PROTECTION, PLEASE READ THESE CONDITIONS CAREFULLY. PLEASE PRINT A COPY EACH OF THE ABOVE DOCUMENTS/ONLINE AGREEMENTS FOR YOUR RECORDS. IF YOU HAVE ANY QUESTIONS ABOUT THESE CONDITIONS, PLEASE CONTACT US IMMEDIATELY IN WRITING.<br /><br />
	</td></tr>

	<tr><td><b><u>
	General T&#38;C<br /> <br />
	</u></b></td></tr>
	<tr><td>
	Overdue Payment: 1.8 % monthly interest may be levied on overdue payments<br /><br />
	
	Cancellation: All payments made will be forfeited and the respective contract will automatically be rendered null and void<br /><br />
	
	Goods return or exchange policy: Goods once sold are not refundable, returnable or exchangeable<br /><br />
	
	Any additional changes or modifications that are not stated above are subject to additional charges.<br /><br />
	
	Travelling expenses, food and lodging are excluded for on-site setup and implementation for East Malaysia only. Additional charges apply in all other regions.<br /><br />
	
	Delivery time is subject to pre-agreed schedule<br /><br />
	
	Shipping and handling charges are excluded from the quoted price above.<br /><br />
	
	The price quoted includes a one time, eight (8) hours, on-site standby service during the POS Counter kick-off for business's. Additional hour(s) will be charged accordingly<br /><br />
	
	If the HQ Server is not hosted at the Data Centre, the Customer must apply for a Fixed IP (Static IP) to maintain stable connectivity.<br /><br />
	
	ARMS International Software Sdn Bhd shall not be held responsible for any delay of delivery or implementation due to problems caused by the customer or a third party<br /><br />
	
	If the Local Area Network (LAN) or Wireless Local Area Network (WLAN) cable layout is not provided by ARMS International Software Sdn Bhd, the LAN or WLAN is required to be thoroughly setup and tested before POS Counter Terminal setup<br /><br />
	
	ARMS International Software Sdn Bhd reserves the right to claim compensation (such as additional man hours, transportation, board and lodging) from the Purchaser for expenses incurred due to any problems caused by irregular networking and/or hardware, whereupon the networking was incorrectly configured and/or the hardware was not purchased from ARMS International Software Sdn Bhd<br /><br />
	
	ARMS International Software Sdn Bhd will not be held responsible in any manner for any third party software and shall have no liability for the use of third party software<br /><br />
	
	On site support can be arranged if necessary and will be charged according to our current on-site support charges<br /><br />
	
	Support will be given via the following modes remotely:<br />
	By Ticketing: <a href="http://support.arms.com.my" target="_blank">http://support.arms.com.my/</a><br />
	By Email: support@arms.my<br />
	By Phone: 604-5075842<br /><br />

	<b><u>MAINTENANCE CONTRACT</u></b><br /><br />
	
	It is mandatory for the customer to sign a software maintenance agreement based upon the pre-agreed, per annum, maintenance fee and adhere to all the terms and conditions listed therein.<br /><br />
	</td></tr>
	
	<tr><td>
	ARMS PREMIUM Maintenance Agreement, Ver. 1 at http://www.arms.my/agreements/2<br /><br />
	</td></tr>
{/if}

</td></tr>
</table>
{if !$is_generate}</div>{/if}
<br>
{if !$is_generate}
<table id="tbl" class="noprint">
	<tr><td align="center">
	<input type="submit" name="registration_btn" value="Submit &amp; Activate GST" />
	</td></tr>
</table>
<br>
{/if}
</form>
</div>
