<?php /* Smarty version 2.6.18, created on 2021-06-08 21:08:10
         compiled from footer.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', 'footer.tpl', 24, false),)), $this); ?>
<?php if (! $this->_tpl_vars['no_header_footer']): ?>
</div>
				<!-- Container closed -->
			</div>
			<!-- main-content closed -->

			<!-- Sidebar-right-->
			<div class="sidebar sidebar-right sidebar-animate">
				<div class="panel panel-primary card mb-0 box-shadow">
					<div class="tab-menu-heading border-0 p-3">
						<div class="card-title mb-0">Notifications</div>
						<div class="card-options ml-auto">
							<a href="#" class="sidebar-remove"><i class="fe fe-x"></i></a>
						</div>
					</div>
				</div>
			</div>
			<!--/Sidebar-right-->

			
			<!-- Footer opened -->
			<div class="main-footer ht-40">
				<div class="container-fluid pd-t-0-f ht-100p">
					<div style="height:25px">Copyright &copy; 2007-<?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
 <a class="footer-linkx" href="http://arms.my" target=_blank>ARMS Software International Sdn Bhd</a></div> 
																																			<div style="height:25px">For Technical Assistance: Please <a class="footer-linkx" href="https://helpdesk.arms.my/" target="_blank">send ticket</a>
					(include screenshots attachment) or call +6012-4016647.</div>
					<div style="height:25px;margin-bottom:20px">For Other Assistance: Please email cs@arms.my or call +604-5026265.</div>
						
																								</div>
			</div>
			<!-- Footer closed -->

		</div>
		<!-- End Page -->

			
			<p align=center class="noprint footer-ads"  style="clear:both">
	<?php if (! $this->_tpl_vars['config']['hide_footer_ad'] && $_SERVER['PHP_SELF'] == '/login.php'): ?>
		<?php echo '
		<script async=\'async\' src=\'https://www.googletagservices.com/tag/js/gpt.js\'></script>
		<script>
		  var googletag = googletag || {};
		  googletag.cmd = googletag.cmd || [];
		</script>

		<script>
		  googletag.cmd.push(function() {
		    googletag.defineSlot(\'/158454254/arms_portal\', [728, 90], \'div-gpt-ad-1556182887893-0\').addService(googletag.pubads());
		    googletag.pubads().enableSingleRequest();
		    googletag.enableServices();
		  });
		</script>
		<table width=\'100%\' class="login-ads">
			<tr align="center">
				<td width=\'100%\'>
					<div id=\'div-gpt-ad-1556182887893-0\' style=\'height:90px; width:728px;\'>
						<script>
							googletag.cmd.push(function() { googletag.display(\'div-gpt-ad-1556182887893-0\'); });
						</script>
					</div>
				</td>
			</tr>
		</table>
		'; ?>

		<br />
	<?php endif; ?>

	<?php if ($this->_tpl_vars['config']['custom_page_footer'] && ( ( ! $this->_tpl_vars['config']['replace_page_footer'] && $_SERVER['PHP_SELF'] == '/login.php' ) || $this->_tpl_vars['config']['replace_page_footer'] )): ?>
		<?php $_from = $this->_tpl_vars['config']['custom_page_footer']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['cpf'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['cpf']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['dummy1'] => $this->_tpl_vars['r']):
        $this->_foreach['cpf']['iteration']++;
?>
			<?php if ($this->_tpl_vars['r']['type'] == 'text'): ?>
				<?php echo $this->_tpl_vars['r']['html']; ?>

				<?php if (! ($this->_foreach['cpf']['iteration'] == $this->_foreach['cpf']['total'])): ?><br /><br /><?php endif; ?>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		
		<?php if (! $this->_tpl_vars['config']['replace_page_footer']): ?><br /><br /><?php endif; ?>
	<?php endif; ?>

	<?php if (( $this->_tpl_vars['config']['custom_page_footer'] && ! $this->_tpl_vars['config']['replace_page_footer'] ) || ! $this->_tpl_vars['config']['custom_page_footer']): ?>
		
	<?php endif; ?>

<?php if ($this->_tpl_vars['config']['google_analytic_id']): ?>
	<script type="text/javascript">
		var google_analytic_id = '<?php echo $this->_tpl_vars['config']['google_analytic_id']; ?>
';
		
		<?php echo '
		var _gaq = _gaq || [];
		_gaq.push([\'_setAccount\', google_analytic_id]);
		_gaq.push([\'_trackPageview\']);
		
		(function() {
		  var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
		  ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
		  var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
		})();
		'; ?>

	</script>
<?php endif; ?>

<?php if (! $_SESSION['browser_check'] && strpos ( strtolower ( $_SERVER['HTTP_USER_AGENT'] ) , 'firefox' ) === false): ?>
	<script type="text/javascript">
	<?php echo '
		new Ajax.Request("index.php",{
			method:\'post\',
			parameters: \'set_browser_check_session=1\',
			evalScripts: true,
			onComplete: function () {
				alert("For 100% compatibility, please use the latest version of Firefox\\n\\nhttp://www.mozilla.org/en-US/firefox/");
			}
		});
	'; ?>

	</script>
<?php endif; ?>

<!-- <script type="text/javascript">
	var test = document.getElementById("top")

	if(test.scrollHeight > 200)
		document.getElementById("back-to-top").style.visibility = "visible";
	else
		document.getElementById("back-to-top").style.visibility = "hidden";
</script> -->

		<!-- JQuery min js -->
		<script src="../assets/plugins/jquery/jquery.min.js"></script>

			<script type="text/javascript">
			<?php echo '
				jQuery.noConflict();
				 // jQuery(document).ready(function(){
			  //     console.log("ready ji");
			  //     });


			'; ?>
	
		</script>
		

		<!-- Back-to-top -->
		<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>

		<!-- Bootstrap Bundle js -->
		<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

		<!-- Ionicons js -->
		<script src="../assets/plugins/ionicons/ionicons.js"></script>

		<!-- Moment js -->
		<script src="../assets/plugins/moment/moment.js"></script>

		<!-- P-scroll js -->
		<script src="../assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="../assets/plugins/perfect-scrollbar/p-scroll.js"></script>

		<!-- eva-icons js -->
		<script src="../assets/js/eva-icons.min.js"></script>

		<!-- Rating js-->
		<script src="../assets/plugins/rating/jquery.rating-stars.js"></script>
		<script src="../assets/plugins/rating/jquery.barrating.js"></script>

		<!-- Custom Scroll bar Js-->
		<script src="../assets/plugins/mscrollbar/jquery.mCustomScrollbar.concat.min.js"></script>

		<!-- Horizontalmenu js-->
		<script src="../assets/plugins/horizontal-menu/horizontal-menu-2/horizontal-menu.js"></script>

		<!-- Sticky js -->
		<script src="../assets/js/sticky.js"></script>

		<!-- Right-sidebar js -->
		<script src="../assets/plugins/sidebar/sidebar.js"></script>
		<script src="../assets/plugins/sidebar/sidebar-custom.js"></script>

		<!-- custom js -->
		<script src="../assets/js/custom.js"></script>

</body>
</html>

<!--
<?php if ($this->_tpl_vars['license']): ?>
License Expiry: <?php echo $this->_tpl_vars['license']['FILE_EXPIRY']; ?>

Copyright: <?php echo $this->_tpl_vars['license']['Copyright']['value']; ?>

Licensed To: <?php echo $this->_tpl_vars['license']['Licensed']['value']; ?>

---
<?php endif; ?>
SSID: <?php echo $this->_tpl_vars['ssid']; ?>

URL: http://<?php echo $_SERVER['HTTP_HOST']; ?>
<?php echo $_SERVER['REQUEST_URI']; ?>

TIME: <?php global $mt_start;print getmicrotime()-$mt_start."\n" ?>
<?php if ($this->_tpl_vars['sessioninfo']['u'] == 'wsatp' || $this->_tpl_vars['sessioninfo']['u'] == 'admin' || $_SERVER['SERVER_NAME'] == 'maximus' || $_SESSION['admin_session']): ?>
SQL: <?php global $query_count,$query_time;print "$query_count\n$query_time" ?>
<?php endif; ?>
-->
<?php endif; ?>