{if !$no_header_footer}
</div>
<!-- Container-fluid closed -->

</div>
<!-- Page closed -->

<!-- Footer opened -->
	
			
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
		
		<!-- Footer opened -->
			<div class="main-footer">
				<div class="container-fluid pd-t-0-f ht-100p">
					
					<div style="height:25px">Copyright &copy; 2007-{$smarty.now|date_format:"%Y"} <a class="footer-linkx" href="http://arms.my" target=_blank>ARMS Software International Sdn Bhd</a></div> 
					{*	More info at *}
					{*	{if strpos($smarty.server.SERVER_NAME, 'arms-go') !==false} *}
					{*		<a class="footer-linkx" href="http://arms-go.com" target=_blank>arms-go.com</a>. *}
					{*	{else} *}
					{*		<a class="footer-linkx" href="http://arms.my" target=_blank>arms.my</a>. *}
					{*	{/if} *}
					<div style="height:25px">For Technical Assistance: Please <a class="footer-linkx" href="https://helpdesk.arms.my/" target="_blank">send ticket</a>
					(include screenshots attachment) or call +6012-4016647.</div>
					<div style="height:25px;margin-bottom:20px">For Other Assistance: Please email cs@arms.my or call +604-5026265.</div>
						
					{*	{if strpos(strtolower($smarty.server.HTTP_USER_AGENT), 'firefox') ===false} *}
					{*		<br /> *}
					{*		For 100% compatibility, please use the latest version of <a href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>. *}
					{*	{/if} *}

				</div>
			</div>
			<!-- Footer closed -->

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

		

</div>
<!-- End Page -->

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