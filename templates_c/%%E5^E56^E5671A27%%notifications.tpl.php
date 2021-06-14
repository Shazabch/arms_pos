<?php /* Smarty version 2.6.18, created on 2021-06-11 18:27:22
         compiled from notifications.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'intval', 'notifications.tpl', 417, false),array('modifier', 'upper', 'notifications.tpl', 481, false),array('modifier', 'string_format', 'notifications.tpl', 497, false),)), $this); ?>
<?php echo '
<style>
.ntc {
	font-size:0.8em;
	color:#666;
}
ul {
padding:0;margin:0;
}
</style>
'; ?>


<script>
var user_level = '<?php echo $this->_tpl_vars['sessioninfo']['level']; ?>
';
var userz = '<?php echo $this->_tpl_vars['sessioninfo']['u']; ?>
';

<?php echo '
function delete_grn_distribution(bid, grn_id){
	var img = $(\'img_delete_grn_distribution-\'+bid+\'-\'+grn_id);
	
	if(img.src.indexOf(\'clock\')>=0){
		alert(\'Please wait...\');
		return false;
	}
	var ori_src = img.src;
	
	img.src = \'/ui/clock.gif\';
	
	new Ajax.Request(\'ajax_autocomplete.php\', {
		parameters:{
			a: \'ajax_delete_grn_distribution\',
			bid: bid,
			grn_id: grn_id,
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			if(str == \'OK\'){
				$(\'div_grn_distribution-\'+bid+\'-\'+grn_id).remove();
			}else{
				alert(str);
				img.src = ori_src;
			}
		}
	});
}

// MOVE TO ajax_notification and notifications_right_sidebar.tpl
// function ajax_notification_updates(){
// 	ajax_get_bpc();
// }

// function ajax_get_bpc(){
// 	if(user_level == 0) return; // not guest

// 	new Ajax.Request(\'ajax_notification.php\', {
// 		parameters:{
// 			a: \'ajax_get_bpc\'
// 		},
// 		onComplete: function(msg){
// 			var str = msg.responseText.trim();
// 			var ret = {};
// 			var err_msg = \'\';

// 			ret = JSON.parse(str); // try decode json object
// 			if(ret[\'ok\'] == 1 && ret[\'html\']){
// 				$(\'div_bpc_items\').update(ret[\'html\']);
// 			}else{
// 				$(\'div_bpc\').hide();
// 			}
// 		}
// 	});
// }

function ajax_left_sidebar(){
	new Ajax.Request(\'ajax_notification.php\', {
		parameters:{
			a: \'ajax_left_sidebar\'
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$(\'left_content\').update(str);
			
			// start load after 1s
			setTimeout(function(){ ajax_right_sidebar(); }, 1000);
		}
	});
}

function ajax_right_sidebar(){
	new Ajax.Request(\'ajax_notification.php\', {
		parameters:{
			a: \'ajax_right_sidebar\'
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$(\'right_content\').update(str);
		}
	});
}

function ajax_get_pm(page_start, load_leftsidebar){
	if(load_leftsidebar == undefined){
		load_leftsidebar = 0;
	}
	$(\'pm\').update(_loading_+ \' Please wait...\');
	new Ajax.Request(\'pm.php\', {
		parameters:{
			a: \'ajax_get_pm\',
			s: page_start
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			
			$(\'pm\').update(str);
			
			if(load_leftsidebar){
				setTimeout(function(){ ajax_left_sidebar(); }, 1000);
			}
		}
	});
}

function pm_delete(branch_id,id)
{
	new Ajax.Request("pm.php",
	{
		parameters:\'a=ajax_mark_read&branch_id=\'+branch_id+\'&id=\'+id,
		onComplete:function()
		{
			new Effect.Fade(\'pm-\'+branch_id+\'-\'+id, \'slow\');
			if($("s")){
				var s = $("s").value;
			}else{
				var s = 0;
			}
			ajax_get_pm(s);
		}
	}); 
	return false;
}

function clear_all_pm()
{
    if (confirm("Are you sure you want to mark all as read?") == true) 
	{
		new Ajax.Updater(\'pm\', \'pm.php\',{
			parameters: \'a=mark_all_read\',
			evalScripts: true,
			onComplete: function(m){
				ajax_get_pm(0);
			}
		});
		
	}
	return false;
}

function change_style(obj,branch_id,id){
	obj.style.fontWeight = "normal";
	$(\'pm-\'+branch_id+\'-\'+id).style.backgroundColor  = "white";
}
'; ?>

</script>

<div class="container-fluid mt-2">
	<div class="row">
		<!-- Start LEft -->
		<div class="col-md-3 border p-0 m-0">
			<!-- Annoucement Start -->
			<?php if ($this->_tpl_vars['announcementList']): ?>
			<div class="card">
				<div class="card-body text-center pricing ">
					<div class="card-category fs-09"><i class="fas fa-bullhorn"></i> Annoucements</div>
					<ul class="list-unstyled leading-loose text-left">
					<?php $_from = $this->_tpl_vars['announcementList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['announcement_code'] => $this->_tpl_vars['r']):
?>
						<li id="li_announcement-<?php echo $this->_tpl_vars['announcement_code']; ?>
" class="fs-08"><a href="announcement.php?a=view&code=<?php echo $this->_tpl_vars['announcement_code']; ?>
" target="_blank" class="text-reset"><?php echo $this->_tpl_vars['r']['title']; ?>
</a><?php if ($this->_tpl_vars['r']['is_new']): ?> <span class="badge badge-pill badge-warning">new</span> <?php endif; ?></li>
					<?php endforeach; endif; unset($_from); ?>
					</ul>
				</div>
			</div>
			<?php endif; ?>
			<!-- Annoucement End -->

			<!-- Ajax Fetch left side content -->
			<div id="left_content">
				<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
			</div>
			<!-- /Ajax Fetch left side content -->

		</div>
		<!-- End Left -->

		<!-- Start Center -->
		<div class="col border">
			2
		</div>
		<!-- End Center -->

		<!-- Start Right -->
		<div class="col-md-3 border p-0 m-0">
			<div id="right_content">
				<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
			</div>
		</div>
		<!-- End Right -->
	</div>
</div>

<!-- start left -->
<div class="leftbar" style="float:left; padding-right:10px; border-right: 1px dashed #ddd; width:200px;">

<!-- disk space -->
<?php if ($this->_tpl_vars['disk_space']): ?>
<div class="leftbar-div">
	<h5>
		<i class="icofont-database icofont"></i> Free Space</h5>
	<table class="tb small free_space_tbl" cellpadding=4 cellspacing=0 >
	<tr bgcolor=#ffee99 class="tbl-thead"><th>Device</th><th>Free</th><th>Mount</th></tr>
	<?php $_from = $this->_tpl_vars['disk_space']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ds']):
?>
	<?php $this->assign('pct', ((is_array($_tmp=$this->_tpl_vars['ds'][3]/$this->_tpl_vars['ds'][1]*100)) ? $this->_run_mod_handler('intval', true, $_tmp) : intval($_tmp))); ?>
	<tr <?php if ($this->_tpl_vars['pct'] < 10): ?>style="color:red;background:yellow"<?php endif; ?>><td><?php echo $this->_tpl_vars['ds'][0]; ?>
</td><td><?php echo ((is_array($_tmp=$this->_tpl_vars['ds'][3]/1024)) ? $this->_run_mod_handler('intval', true, $_tmp) : intval($_tmp)); ?>
MB (<?php echo $this->_tpl_vars['pct']; ?>
%)</td><td><?php echo $this->_tpl_vars['ds'][5]; ?>
</td></tr>
	<?php endforeach; endif; unset($_from); ?>
	</table>
</div>
<?php endif; ?>
<?php if ($this->_tpl_vars['config']['db_last_cutoff_date']): ?>
	<div class="ntc">Last DB Cutoff Date: <?php echo $this->_tpl_vars['config']['db_last_cutoff_date']; ?>
</div>
<?php endif; ?>



<!-- Inactive User notification -->
</div>
<!-- end left -->

<!-- start right -->
<div class="rightbar" style="float:right; padding-left:10px; border-left: 1px dashed #ddd; width:200px;">

<!-- Offline Documents -->
<?php if ($this->_tpl_vars['off_docs']): ?>
<div class="leftbar-div">
	<h5><img src=/ui/store.png align=absmiddle border=0> Offline Documents</h5>
	<div class=ntc>The following documents have been uploaded from Offline Server</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		<?php $_from = $this->_tpl_vars['off_docs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m'] => $this->_tpl_vars['dl']):
?>
			<?php $_from = $this->_tpl_vars['dl']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
				<?php if ($this->_tpl_vars['m'] == 'adj'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/adjustment.php?a=open&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
" target="_blank">Adjustment #<?php echo $this->_tpl_vars['r']['id']; ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received Date : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'sku'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/masterfile_sku_application.php?a=view&id=<?php echo $this->_tpl_vars['r']['id']; ?>
" target="_blank">SKU #<?php echo $this->_tpl_vars['r']['id']; ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received Date : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'do'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/do.php?a=open&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
&do_type=<?php echo $this->_tpl_vars['r']['do_type']; ?>
" target="_blank">DO #<?php echo $this->_tpl_vars['r']['id']; ?>
</a>
					&nbsp;&nbsp;&nbsp;&nbsp;<font color="006600" class="small"><?php echo ((is_array($_tmp=$this->_tpl_vars['r']['do_type'])) ? $this->_run_mod_handler('upper', true, $_tmp) : smarty_modifier_upper($_tmp)); ?>
</font>
					<br />
					<font color="#666666" class="small">
						Received Date : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'po'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/po.php?a=open&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
" target="_blank">PO #<?php echo $this->_tpl_vars['r']['id']; ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'gra'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_return_advice.php?a=open&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
" target="_blank">GRA<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%05d') : smarty_modifier_string_format($_tmp, '%05d')); ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font> 
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'grn'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_receiving_note.php?a=open&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
&action=edit" target="_blank">GRN<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%05d') : smarty_modifier_string_format($_tmp, '%05d')); ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php elseif ($this->_tpl_vars['m'] == 'grr'): ?>
				<div style="border-bottom:1px solid #eee">
					<a href="/goods_receiving_record.php?a=view&id=<?php echo $this->_tpl_vars['r']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
" target="_blank">GRR<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%05d') : smarty_modifier_string_format($_tmp, '%05d')); ?>
</a>
					<br />
					<font color="#666666" class="small">
						Received : <?php echo $this->_tpl_vars['r']['added']; ?>
<br>
					</font>
				</div>
				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		<?php endforeach; endif; unset($_from); ?>
	</div>
</div>
<?php endif; ?>
</div>
<!-- end right -->

<div style="margin-left:220px;margin-right:220px;">
<!-- SKU revise notification -->
<?php if ($this->_tpl_vars['sku_revision']): ?>
<div class="rightbar-div">
	<h5><img src=/ui/notify_sku_reject.png align=absmiddle border=0> Rejected SKU Applications</h5>
	<div class=ntc>Please Revise your SKU Application and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['sku_revision']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li> <a href="masterfile_sku_application.php?a=revise&id=<?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['id']; ?>
">#<?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['id']; ?>
 - <?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['brand_desc']; ?>
 <?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['category_desc']; ?>
 (Department: <?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['department']; ?>
)</a>
	<br><font color=#666666 class=small><?php echo $this->_tpl_vars['sku_revision'][$this->_sections['i']['index']]['added']; ?>
</font>
	<?php endfor; endif; ?>
	</ul>
</div>
<?php endif; ?>

<!-- SKU pending notification -->
<?php if ($this->_tpl_vars['sku_pending']): ?>
<div class="rightbar-div">
	<h5><img src=/ui/notify_sku_pending.png align=absmiddle border=0> Pending SKU Applications</h5>
	<div class=ntc>The following applications are Pending. Please Review and Approve them.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['sku_pending']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li> <a href="masterfile_sku_approval.php?id=<?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['id']; ?>
">#<?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['id']; ?>
 - <?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['u']; ?>
 (<?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['apply_branch']; ?>
)
	<?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['brand_desc']; ?>
 <?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['category_desc']; ?>

	(Department: <?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['department']; ?>
)
	</a>
	<br><font color=#666666 class=small><?php echo $this->_tpl_vars['sku_pending'][$this->_sections['i']['index']]['added']; ?>
</font>
	<?php endfor; endif; ?>
	</ul>
</div>
<?php endif; ?>

<!-- PO revise notification -->
<?php if ($this->_tpl_vars['po_revision']): ?>
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected PO</h5>
	<div class=ntc>Please Revise your Purchase Orders and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['po_revision']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li> 
		<a href="po.php?a=open&id=<?php echo $this->_tpl_vars['po_revision'][$this->_sections['i']['index']]['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['po_revision'][$this->_sections['i']['index']]['branch_id']; ?>
">
	#<?php echo $this->_tpl_vars['po_revision'][$this->_sections['i']['index']]['id']; ?>
 - (Department: <?php echo $this->_tpl_vars['po_revision'][$this->_sections['i']['index']]['department']; ?>
)</a>
	<br><font color=#666666 class=small><?php echo $this->_tpl_vars['po_revision'][$this->_sections['i']['index']]['last_update']; ?>
</font>
	<?php endfor; endif; ?>
	</ul>
</div>
<?php endif; ?>

<!-- Promotion reject notification -->
<?php if ($this->_tpl_vars['promo_reject']): ?>
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected Promotion</h5>
	<div class=ntc>Please Revise your Promotion and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['promo_reject']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li> 
	<a href="promotion.php?a=open&id=<?php echo $this->_tpl_vars['promo_reject'][$this->_sections['i']['index']]['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['promo_reject'][$this->_sections['i']['index']]['branch_id']; ?>
">
	#<?php echo $this->_tpl_vars['promo_reject'][$this->_sections['i']['index']]['id']; ?>
 <?php echo $this->_tpl_vars['promo_reject'][$this->_sections['i']['index']]['title']; ?>
</a>
	<br><font color=#666666 class=small><?php echo $this->_tpl_vars['promo_reject'][$this->_sections['i']['index']]['last_update']; ?>
</font>
	<?php endfor; endif; ?>
	</ul>
</div>
<?php endif; ?>


<!-- DO revise notification -->
<?php if ($this->_tpl_vars['do_revision']): ?>
<div class="rightbar-div">
	<h5><img src=/ui/rejected.png align=absmiddle border=0> Rejected DO</h5>
	<div class=ntc>Please Revise your Delivery Orders and submit again.</div>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['do_revision']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li> 
	<a href="do.php?a=open&id=<?php echo $this->_tpl_vars['do_revision'][$this->_sections['i']['index']]['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['do_revision'][$this->_sections['i']['index']]['branch_id']; ?>
">
	#<?php echo $this->_tpl_vars['do_revision'][$this->_sections['i']['index']]['id']; ?>
 - (Branch: <?php echo $this->_tpl_vars['do_revision'][$this->_sections['i']['index']]['branch']; ?>
)</a>
	<br><font color=#666666 class=small><?php echo $this->_tpl_vars['do_revision'][$this->_sections['i']['index']]['last_update']; ?>
</font>
	<?php endfor; endif; ?>
	</ul>
</div>
<?php endif; ?>

<!-- PM notification -->
<div id=pm>
	<p><img src="/ui/clock.gif" align="absmiddle"> Loading content, please wait. . .</p>
</div>
</div>
<script>
<?php echo '
//ajax_notification_updates();
setTimeout(function(){ ajax_get_pm(0,1); }, 1000);
//ajax_left_sidebar();
//ajax_right_sidebar();
'; ?>

</script>