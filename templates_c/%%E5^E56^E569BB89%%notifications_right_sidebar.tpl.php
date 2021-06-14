<?php /* Smarty version 2.6.18, created on 2021-06-11 15:52:21
         compiled from notifications_right_sidebar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'notifications_right_sidebar.tpl', 55, false),array('modifier', 'default', 'notifications_right_sidebar.tpl', 118, false),array('modifier', 'date_format', 'notifications_right_sidebar.tpl', 119, false),array('modifier', 'string_format', 'notifications_right_sidebar.tpl', 239, false),array('modifier', 'num_format', 'notifications_right_sidebar.tpl', 243, false),array('function', 'count', 'notifications_right_sidebar.tpl', 282, false),)), $this); ?>

<!-- Price Change Notify -->
<?php if ($this->_tpl_vars['price_history']): ?>

<!-- History Popup Start -->
<div class="modal fade" id="history_popup" data-backdrop="false">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content modal-content-demo">
			<div class="modal-header">
				<h6 class="modal-title">Item History</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="history_popup_content"></div>
			</div>
		</div>
	</div>
</div>
<!-- History Popup End-->

<!-- <div id="history_popup" style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
	<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
	<div id="history_popup_content"></div>
</div> -->

<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="fas fa-tag"></i> Price Change History</div>
		<div class="mb-2">
			<span class="fs-07 text-muted">Last 25 price change items</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height:200px;">
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['price_history']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<li  class="fs-08">
				<span class="text-muted fs-06"><?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['last_update']; ?>
 - <?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['branch']; ?>
</span><br>
				<strong class="fs-09">
					<i class="fas fa-search"  data-toggle="modal" href="#history_popup" onclick="price_history(this,<?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['id']; ?>
,<?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['branch_id']; ?>
)" role="button"></i>
					<?php if ($this->_tpl_vars['config']['notification_price_change_show_artno']): ?>
						<?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['artno']; ?>

					<?php else: ?>
						<?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['sku_item_code']; ?>

					<?php endif; ?>
				</strong>= <?php echo $this->_tpl_vars['config']['arms_currency']['symbol']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['price_history'][$this->_sections['i']['index']]['price'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
<br>
				<span class="fs-06 text-secondary"><?php echo $this->_tpl_vars['price_history'][$this->_sections['i']['index']]['description']; ?>
</span>
			</li>
			<?php endfor; endif; ?>
		</ul>
	</div>
</div>

<?php endif; ?>
<!-- Price Change Notify  End -->

<!-- Batch Price Change notification -->
<?php if ($this->_tpl_vars['batch_price_change']['ok'] == 1): ?>
<div id="div_bpc" <?php if ($this->_tpl_vars['sessioninfo']['level'] == 0): ?>style="display:none;"<?php endif; ?>>
	<h5>
		<i class="icofont-price icofont"></i> Batch Price Change</h5>
	<div class="ntc">
		The following Price Change item(s) soon will be updated (Show last 100 items)
	</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		<div id="div_bpc_items"><?php echo $this->_tpl_vars['batch_price_change']['html']; ?>
</div>
	</div>	
</div>
<?php endif; ?>

<!-- SKU Items Lock Price-->
<?php if ($this->_tpl_vars['temp_price_history']): ?>
<h5>
<i class="icofont-price icofont"></i> Temp Price Items</h5>
<div class="ntc">Last 25 temp price items</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['temp_price_history']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<div style="border-bottom:1px solid #eee">
<font color="#666666" class="small">
<?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['lastupdate']; ?>
 - <?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['branch']; ?>

</font><br />
<font class="temp_item" color="#d00000">
<?php if ($this->_tpl_vars['config']['notification_price_change_show_artno']): ?>
    <?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['artno']; ?>

<?php else: ?>
	<?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['sku_item_code']; ?>

<?php endif; ?>
</font>
=
<font class="temp_item" color="blue"><?php echo $this->_tpl_vars['config']['arms_currency']['symbol']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['temp_price'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
</font><br />
<font class="small"><?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['description']; ?>
</font><br />
<font><?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['username']; ?>
 : <?php echo $this->_tpl_vars['temp_price_history'][$this->_sections['i']['index']]['reason']; ?>
</font>
</div>
<?php endfor; endif; ?>
</div>
<?php endif; ?>

<!--PO Overdue-->
<?php if ($this->_tpl_vars['po_overdue']): ?>
<h5><img src=/ui/store.png align=absmiddle border=0> PO Delivery Overdue</h5>
<div class=ntc>The following PO had Overdue</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
<?php $_from = $this->_tpl_vars['po_overdue']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['r']):
?>
	<?php $_from = $this->_tpl_vars['po_overdue'][$this->_tpl_vars['id']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['branch_id'] => $this->_tpl_vars['r2']):
?>
		<div style="border-bottom:1px solid #eee">
		<font color=#666666 class=small>
		PO No: <a href="/po.php?a=view&id=<?php echo $this->_tpl_vars['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['branch_id']; ?>
"><?php echo $this->_tpl_vars['r2']['po_no']; ?>
</a>(<?php echo ((is_array($_tmp=@$this->_tpl_vars['r2']['department'])) ? $this->_run_mod_handler('default', true, $_tmp, "-") : smarty_modifier_default($_tmp, "-")); ?>
/<font color=blue><?php echo $this->_tpl_vars['r2']['user']; ?>
</font>)<br>
		Created: <font color=blue><?php echo ((is_array($_tmp=$this->_tpl_vars['r2']['po_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['config']['dat_format']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['config']['dat_format'])); ?>
</font><br>
		Delivery Date: <font color=blue><?php echo ((is_array($_tmp=$this->_tpl_vars['r2']['delivered_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['config']['dat_format']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['config']['dat_format'])); ?>
</font>
		Cancelation Date: <font color=blue><?php echo ((is_array($_tmp=$this->_tpl_vars['r2']['cancel_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, $this->_tpl_vars['config']['dat_format']) : smarty_modifier_date_format($_tmp, $this->_tpl_vars['config']['dat_format'])); ?>
</font>
		</font>
		</div>
	<?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?>
</div>
<?php endif; ?>

<!-- New SKU Notify -->
<?php if ($this->_tpl_vars['new_sku']): ?>
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09" ><i class="fas fa-tag"></i> New SKU</div>
		<div class="mb-2 fs-07">
			<span class="text-muted">Last 25 new SKU items</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 300px;">
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['new_sku']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<li  class="fs-08">
				<a href="masterfile_sku.php?a=view&id=<?php echo $this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['sku_id']; ?>
" target="_blank" class="text-reset">
					<span class="text-muted fs-06"><?php echo $this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['added']; ?>
</span><br>
					<strong class="fs-09">
						<?php if ($this->_tpl_vars['config']['notification_price_change_show_artno']): ?>
						    <?php echo $this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['artno']; ?>

						<?php else: ?>
							<?php echo ((is_array($_tmp=@$this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['mcode'])) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['sku_item_code']) : smarty_modifier_default($_tmp, @$this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['sku_item_code'])); ?>

						<?php endif; ?>
					</strong>= <?php echo $this->_tpl_vars['config']['arms_currency']['symbol']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['selling_price'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
<br>
					<span class="text-secondary fs-06"><?php echo $this->_tpl_vars['new_sku'][$this->_sections['i']['index']]['description']; ?>
</span>
				</a>
			</li>
			<?php endfor; endif; ?>
		</ul>
	</div>
</div>
<?php endif; ?>
<!-- New SKU Notify  End-->

<!-- GRA Notify -->

<?php if ($this->_tpl_vars['last_gra']): ?>
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="far fa-building"></i> Gra Status</div>
		<div class="fs-07 mb-2 text-muted">
			<span>The following GRA has been pending for more than a week</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 200px;">
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['last_gra']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<li class="fs-08">
				<a href="/goods_return_advice.php?a=view&id=<?php echo $this->_tpl_vars['last_gra'][$this->_sections['i']['index']]['id']; ?>
" target="_blank" class="text-reset">
					<strong class="fs-08"><?php echo $this->_tpl_vars['last_gra'][$this->_sections['i']['index']]['vendor']; ?>
</strong><br>
					<span class="text-secondary fs-06">Created: <span class="text-muted"> <?php echo $this->_tpl_vars['last_gra'][$this->_sections['i']['index']]['added']; ?>
</span></span><br>
					<span class="text-secondary fs-06">Last Update: <span class="text-muted"> <?php echo $this->_tpl_vars['last_gra'][$this->_sections['i']['index']]['last_update']; ?>
</span></span>
				</a>
			</li>
			<?php endfor; endif; ?>
		</ul>
	</div>
</div>
<?php endif; ?>
<!-- GRA Notify End -->

<!-- GRR Notify -->
<?php if ($this->_tpl_vars['grr_notify']): ?>
<div class="card">
	<div class="card-body text-center pricing ">
		<div class="card-category fs-09"><i class="far fa-building"></i> GRR Status</div>
		<div class="mb-2 fs-07 text-muted">
			<span>The following GRR has been pending for more than <?php echo ((is_array($_tmp=@$this->_tpl_vars['config']['grr_incomplete_notification'])) ? $this->_run_mod_handler('default', true, $_tmp, 3) : smarty_modifier_default($_tmp, 3)); ?>
 days</span>
		</div>
		<ul class="list-unstyled leading-loose text-left overflow-auto" style="max-height: 200px;">
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['grr_notify']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<li class="fs-08">
				<a href="/goods_receiving_record.php?a=view&id=<?php echo $this->_tpl_vars['grr_notify'][$this->_sections['i']['index']]['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['grr_notify'][$this->_sections['i']['index']]['branch_id']; ?>
" target="_blank" class="text-reset">
					<strong class="fs-08"><?php echo $this->_tpl_vars['grr_notify'][$this->_sections['i']['index']]['vendor']; ?>
</strong><br>
					<span class="text-secondary fs-06">Received Date : <span class="text-muted"></span> <?php echo $this->_tpl_vars['grr_notify'][$this->_sections['i']['index']]['rcv_date']; ?>
</span>
				</a>
			</li>
			<?php endfor; endif; ?>
		</ul>
	</div>
</div>
<?php endif; ?>
<!-- GRR Notify End -->

<!-- Redemption item Notify -->
<?php if ($this->_tpl_vars['redemption_items']): ?>
<h5><img src=/ui/store.png align=absmiddle border=0> Redemption Item Status</h5>
<div class=ntc>The following Redemption item(s) will be expired within <?php echo $this->_tpl_vars['config']['membership_redemption_expire_days']; ?>
 days</div>
<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
<a href="/membership.redemption_setup.php">Go to Redemption Item Setup</a>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['redemption_items']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<div style="border-bottom:1px solid #eee"> 
<br>
<font color=#666666 class=small>
Item : <?php echo $this->_tpl_vars['redemption_items'][$this->_sections['i']['index']]['sku_item_code']; ?>
 [ <?php echo $this->_tpl_vars['redemption_items'][$this->_sections['i']['index']]['days_left']; ?>
 day(s) left ]<br>
</font>
</div>
<?php endfor; endif; ?>
</div>
<?php endif; ?>

<!-- GRN Distribution Status -->
<?php if ($this->_tpl_vars['grn_deliver_monitor']['grn']): ?>
	<h5>
		<i class="icofont-building icofont"></i>GRN Distribution Status</h5>
	<div class="ntc">The following GRN are slow in DO to others branches (below <?php echo $this->_tpl_vars['grn_deliver_monitor']['info']['min_do_qty_percent']; ?>
% after <?php echo $this->_tpl_vars['grn_deliver_monitor']['info']['monitor_after_day']; ?>
 days)</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
	
	<?php $_from = $this->_tpl_vars['grn_deliver_monitor']['grn']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['grn']):
?>
		<div style="border-bottom:1px solid #eee" id="div_grn_distribution-<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
-<?php echo $this->_tpl_vars['grn']['id']; ?>
">
			<?php if ($this->_tpl_vars['sessioninfo']['level'] >= 9999): ?>
				<a href="javascript:void(delete_grn_distribution('<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
', '<?php echo $this->_tpl_vars['grn']['id']; ?>
'))">
					<img src="/ui/del.png" align="absmiddle" border="0" title="Delete this notify" id="img_delete_grn_distribution-<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
-<?php echo $this->_tpl_vars['grn']['id']; ?>
" />
				</a>
			<?php endif; ?> 
			<a href="/goods_receiving_note.php?a=view&id=<?php echo $this->_tpl_vars['grn']['id']; ?>
&branch_id=<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
" target="_blank"><?php echo $this->_tpl_vars['grn']['report_prefix']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['grn']['id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%05d') : smarty_modifier_string_format($_tmp, '%05d')); ?>
</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<font class="small">
				<a href="goods_receiving_note.distribution_report.php?load_report=1&grn_bid_id=<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
_<?php echo $this->_tpl_vars['grn']['id']; ?>
" target="_blank">
					Delivered <?php echo ((is_array($_tmp=$this->_tpl_vars['grn']['do_per'])) ? $this->_run_mod_handler('num_format', true, $_tmp, 2) : smarty_num_format($_tmp, 2)); ?>
%
				</a>
			</font> 
			<br />
			<font color="#666666" class="small">
				Received Date : <?php echo $this->_tpl_vars['grn']['rcv_date']; ?>
<br>
			</font>
		</div>
	<?php endforeach; endif; unset($_from); ?>
	<?php if ($this->_tpl_vars['grn_deliver_monitor']['have_more']): ?>
		<div style="text-align:center;">
			<a href="goods_receiving_note.distribution_report.php?a=view_status">Click here to view more</a>
		</div>
	<?php endif; ?>
	</div>
<?php endif; ?>

<!-- Stock Reorder -->
<?php if ($this->_tpl_vars['stock_reorder_data']): ?>
	<h5>
		<i class="icofont-user-suited icofont"></i> Vendor Stock Reorder</h5>
	<div class="ntc">Belows are some pre-generated reorder list by vendor and department.</div>
	<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		<?php $_from = $this->_tpl_vars['stock_reorder_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['vendor_id'] => $this->_tpl_vars['tmp_vendor_data']):
?>
			<div style="border-bottom:1px solid #eee"> 
				<?php $_from = $this->_tpl_vars['tmp_vendor_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['f_st'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['f_st']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['category_id'] => $this->_tpl_vars['r']):
        $this->_foreach['f_st']['iteration']++;
?>
					<?php if (($this->_foreach['f_st']['iteration'] <= 1)): ?>
						<?php echo $this->_tpl_vars['r']['v_desc']; ?>

						<br />
					<?php endif; ?>
					
					<div>
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" /> 
						<a href="/report.stock_reorder.php?load_report=1&category_id=<?php echo $this->_tpl_vars['r']['category_id']; ?>
&vendor_id=<?php echo $this->_tpl_vars['r']['vendor_id']; ?>
&use_pregen_sku=1&reorder_type=<?php echo $this->_tpl_vars['r']['reorder_type']; ?>
&by_last_vendor=1" target="_blank">
						<?php echo $this->_tpl_vars['r']['c_desc']; ?>

						</a>
						<br />
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" />
						<font color="006600">(Est: <?php echo smarty_function_count(array('var' => $this->_tpl_vars['r']['sku_id_list']), $this);?>
 SKU)</font>
						<br />
						<img src="/ui/pixel.gif" width="20" align="absmiddle" height="1" />
						<font color="#666666" class="small">
							Pregen at: <?php echo ((is_array($_tmp=$this->_tpl_vars['r']['added'])) ? $this->_run_mod_handler('date_format', true, $_tmp, '%Y-%m-%d %H:%M') : smarty_modifier_date_format($_tmp, '%Y-%m-%d %H:%M')); ?>

						</font>
					</div>
				<?php endforeach; endif; unset($_from); ?>
			</div>
		<?php endforeach; endif; unset($_from); ?>
	</div>
<?php endif; ?>