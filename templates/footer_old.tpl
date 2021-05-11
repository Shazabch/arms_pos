{*
6/6/2011 11:00:25 AM Andy
- Change footer to only print $query_count,$query_time when it is login as "wsatp", "admin" or at maximus

10/10/2011 6:26:53 PM Andy
- Add google analytic.

3/7/2013 2:46 PM Andy
- Change to hard code CopyRight text, and use the server time as To Year.

4/1/2013 2:27 PM Andy
- Change the support email to open ticket email.
- Check and show a link to download firefox if found user is not using the firefox.

5/10/2013 3:07 PM Andy
- Change the word "install" to "use" Firefox.

1/2/2014 9:27 AM Andy
- Change the support phone number from 04-5026265 to 04-5075842.

7/4/2014 11:58 AM Fithri
- prompt a one-time-only javascript alert if user is using other browser than Firefox

7/24/2014 9:30 AM Justin
- Modified to change the company name.

10/16/2014 3:31 PM Justin
- Modified to change the hyperlink description to small capital.
- Modified to remove the ".com" from some hyperlinks.

2/11/2015 2:22 PM Andy
- Enhance to record and show SQL log when using login as.

7/17/2017 10:42 AM Andy
- Change "send ticket" link to https://helpdesk.arms.my/

10/5/2017 4:44 PM Justin
- Enhanced to show custom footer or replace the current one if found config set.

5/6/2019 5:31 PM Andy
- Added footer advertisement.

8/1/2019 3:28 PM Andy
- Modified footer support message.

18/03/2020 Sheila
- Modified layout to compatible with new UI.

12/16/2020 12:36 PM Andy
- Change the support phone number from 04-5075842 to 04-5026265.

*}

{if !$no_header_footer}
</div>
<br><br>
<p align=center class="noprint footer-ads"  style="clear:both">
	{if !$config.hide_footer_ad and $smarty.server.PHP_SELF eq '/login.php'}
		{literal}
		<script async='async' src='https://www.googletagservices.com/tag/js/gpt.js'></script>
		<script>
		  var googletag = googletag || {};
		  googletag.cmd = googletag.cmd || [];
		</script>

		<script>
		  googletag.cmd.push(function() {
		    googletag.defineSlot('/158454254/arms_portal', [728, 90], 'div-gpt-ad-1556182887893-0').addService(googletag.pubads());
		    googletag.pubads().enableSingleRequest();
		    googletag.enableServices();
		  });
		</script>
		<table width='100%' class="login-ads">
			<tr align="center">
				<td width='100%'>
					<div id='div-gpt-ad-1556182887893-0' style='height:90px; width:728px;'>
						<script>
							googletag.cmd.push(function() { googletag.display('div-gpt-ad-1556182887893-0'); });
						</script>
					</div>
				</td>
			</tr>
		</table>
		{/literal}
		<br />
	{/if}

	{if $config.custom_page_footer && ((!$config.replace_page_footer && $smarty.server.PHP_SELF eq '/login.php') || $config.replace_page_footer)}
		{foreach from=$config.custom_page_footer key=dummy1 item=r name=cpf}
			{if $r.type eq 'text'}
				{$r.html}
				{if !$smarty.foreach.cpf.last}<br /><br />{/if}
			{/if}
		{/foreach}
		
		{if !$config.replace_page_footer}<br /><br />{/if}
	{/if}

	{if ($config.custom_page_footer && !$config.replace_page_footer) || !$config.custom_page_footer}
		{* #COPYRIGHT# *}
		<footer class="divfooter">
			Copyright &copy; 2007-{$smarty.now|date_format:"%Y"} <a class="footer-link" href="http://arms.my" target=_blank>ARMS Software International Sdn Bhd</a>. 
			{*	More info at *}
			{*	{if strpos($smarty.server.SERVER_NAME, 'arms-go') !==false} *}
			{*		<a class="footer-link" href="http://arms-go.com" target=_blank>arms-go.com</a>. *}
			{*	{else} *}
			{*		<a class="footer-link" href="http://arms.my" target=_blank>arms.my</a>. *}
			{*	{/if} *}
			<br>For Technical Assistance: Please <a class="footer-link" href="https://helpdesk.arms.my/" target="_blank">send ticket</a>
			(include screenshots attachment) or call +6012-4016647.<br />
			For Other Assistance: Please email cs@arms.my or call +604-5026265 .
				
			{*	{if strpos(strtolower($smarty.server.HTTP_USER_AGENT), 'firefox') ===false} *}
			{*		<br /> *}
			{*		For 100% compatibility, please use the latest version of <a href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>. *}
			{*	{/if} *}
		</footer>
	{/if}
{* </p> *}

{if $config.google_analytic_id}
	<script type="text/javascript">
		var google_analytic_id = '{$config.google_analytic_id}';
		
		{literal}
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', google_analytic_id]);
		_gaq.push(['_trackPageview']);
		
		(function() {
		  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		{/literal}
	</script>
{/if}

{if !$smarty.session.browser_check and strpos(strtolower($smarty.server.HTTP_USER_AGENT), 'firefox') ===false}
	<script type="text/javascript">
	{literal}
		new Ajax.Request("index.php",{
			method:'post',
			parameters: 'set_browser_check_session=1',
			evalScripts: true,
			onComplete: function () {
				alert("For 100% compatibility, please use the latest version of Firefox\n\nhttp://www.mozilla.org/en-US/firefox/");
			}
		});
	{/literal}
	</script>
{/if}

<a class="scroll-to-top rounded" id="scroll-to-top" href="#page-top" style="z-index: 2000">
  <i class="icofont-rounded-up icofont icon-up"></i>
</a> 
<script type="text/javascript">
	var test = document.getElementById("page-top")
	console.log(test.scrollHeight );

	if(test.scrollHeight > 200)
		document.getElementById("scroll-to-top").style.visibility = "visible";
	else
		document.getElementById("scroll-to-top").style.visibility = "hidden";
</script>
</body>
</html>

<!--
{if $license}
License Expiry: {$license.FILE_EXPIRY}
Copyright: {$license.Copyright.value}
Licensed To: {$license.Licensed.value}
---
{/if}
SSID: {$ssid}
URL: http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}
TIME: {php}global $mt_start;print getmicrotime()-$mt_start."\n"{/php}
{if $sessioninfo.u eq 'wsatp' or $sessioninfo.u eq 'admin' or $smarty.server.SERVER_NAME eq 'maximus' or $smarty.session.admin_session}
SQL: {php}global $query_count,$query_time;print "$query_count\n$query_time"{/php}
{/if}
-->
{/if}
