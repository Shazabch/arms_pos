<?php /* Smarty version 2.6.18, created on 2021-05-07 18:20:50
         compiled from frontend.table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'frontend.table.tpl', 48, false),array('modifier', 'number_format', 'frontend.table.tpl', 128, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>


<span id="span_refreshing"></span>
<table  border=0 cellpadding=4 cellspacing=1>
	<tr>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_CORNER']; ?>
 width=40>&nbsp;</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Network Name</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Location</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>POS<br>Settings</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Print<br>Receipt Reference Code</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Deposit Setting</th>
		<!--<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Return Policy</th>-->
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Trade In</th>
		<?php if ($this->_tpl_vars['config']['membership_control_counter_adjust_point']): ?><th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Adjust Member Point</th><?php endif; ?>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Block Goods Return</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Hold Bill Slot</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>MEMBERSHIP<br>Settings</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Last User</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Open Drawer<br> Count</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>C.O.H</th>
		<th colspan=3 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Card Inventory</th>
		<th <?php if (! $this->_tpl_vars['mprice']): ?>style="display:none;"<?php endif; ?> colspan="<?php echo $this->_tpl_vars['mprice_colspan']; ?>
" bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Mprice Settings</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Sync to weight scale</th>
		<th rowspan=2 bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Self Checkout Counter</th>
	</tr>
	<tr>
		<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>R</th>
		<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>G</th>
		<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>B</th>
		<?php $_from = $this->_tpl_vars['mprice']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
			<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
><?php echo $this->_tpl_vars['val']; ?>
</th>
		<?php endforeach; endif; unset($_from); ?>
	</tr>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['counters']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<tr onmouseover="this.bgColor='<?php echo $this->_config[0]['vars']['TB_ROWHIGHLIGHT']; ?>
';" onmouseout="this.bgColor='';">
		<td bgcolor=<?php echo $this->_config[0]['vars']['TB_ROWHEADER']; ?>
 nowrap>
			<a href="javascript:void(open(<?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['id']; ?>
))"><img src=ui/ed.png title="Edit" border=0></a>
			<a href="javascript:void(act(<?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['id']; ?>
,<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['active']): ?>0<?php else: ?>1<?php endif; ?>))">
				<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['active']): ?><img src=ui/deact.png title="Deactivate" border=0><?php else: ?><img src=ui/act.png title="Activate" border=0><?php endif; ?>
			</a>
			<?php if ($this->_tpl_vars['sessioninfo']['privilege']['COUNTER_ALLOW_UNSET_STATUS'] && $this->_tpl_vars['counters'][$this->_sections['i']['index']]['cst_id']): ?>
				<a href="javascript:void(unset_counter_status(<?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['id']; ?>
))">
					<img src="ui/icons/computer_delete.png" title="Delete Counter Status" border="0">
				</a>
			<?php endif; ?>
		</td>
		<td><b><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['network_name']; ?>
</b>
			<?php if (! $this->_tpl_vars['counters'][$this->_sections['i']['index']]['active']): ?> <br><span class=small style="color: red">(inactive)</span>
			<?php else: ?>
				<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['temporary_counter']['allow'] == 1): ?><br><span class=small style="color: blue">(til <?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['temporary_counter']['date_to']; ?>
)</span><?php endif; ?>
			<?php endif; ?>
		</td>
		<td><b><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['location']; ?>
</b></td>
		<td align=center><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['allow_pos']): ?>Allowed<?php endif; ?></td>
		<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['allow_print_receipt_reference_code']): ?>Allowed<?php endif; ?>
		</td>
		<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['allow_do_deposit_payment']): ?>Allowed<?php endif; ?>
		</td>
		<!--<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['allow_do_return_policy']): ?>Allowed<?php endif; ?>
		</td>-->
		<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['allow_do_trade_in']): ?>Allowed<?php endif; ?>
		</td>
		<?php if ($this->_tpl_vars['config']['membership_control_counter_adjust_point']): ?>
		<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['counter_allow_adjust_member_point']): ?>Allowed<?php endif; ?>
		</td>
		<?php endif; ?>
		<td align=center>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['block_goods_return']): ?>Yes<?php else: ?>No<?php endif; ?>
		</td>
		<td align=center>
			<?php if (! $this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['hold_bill_slot']): ?>0<?php else: ?><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['hold_bill_slot']; ?>
<?php endif; ?>
		</td>
		<td align=center><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['membership_settings']['allow_membership']): ?>Allowed<?php endif; ?></td>
		<td align=center><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['current_user']; ?>
</td>
		<td align=center><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['drawer_open_count']; ?>
</td>
		<td align=right><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['inventory']['COH']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['counters'][$this->_sections['i']['index']]['inventory']['COH'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)); ?>
<?php endif; ?></td>
		<td align=center><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['inventory']['CARD_R']; ?>
</td>
		<td align=center><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['inventory']['CARD_G']; ?>
</td>
		<td align=center><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['inventory']['CARD_B']; ?>
</td>
		<?php if ($this->_tpl_vars['mprice']): ?>
			<?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['mprice_settings'] == ""): ?>
				<?php $_from = $this->_tpl_vars['mprice']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
					<td align="center">Allowed</td>
				<?php endforeach; endif; unset($_from); ?>
			<?php else: ?>
				<?php $_from = $this->_tpl_vars['mprice']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
					<td align="center"><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['mprice_settings'][$this->_tpl_vars['val']] && ! $this->_tpl_vars['counters'][$this->_sections['i']['index']]['mprice_settings']['not_allow']): ?>Allowed<?php endif; ?></td>
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
		<?php endif; ?>
		<td><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['sync_weight']): ?><?php echo $this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['sync_weight']; ?>
<?php else: ?>No<?php endif; ?></td>
		<td align="center"><?php if ($this->_tpl_vars['counters'][$this->_sections['i']['index']]['pos_settings']['is_self_checkout']): ?>Yes<?php else: ?>No<?php endif; ?></td>
	</tr>
	<?php endfor; endif; ?>
</table>