<?php /* Smarty version 2.6.18, created on 2021-06-18 21:21:03
         compiled from notifications_pm.tpl */ ?>

<?php if ($this->_tpl_vars['pm']): ?>
	<?php $this->assign('lastpm', ""); ?>
	<?php $this->assign('pm_count', 0); ?>
	<h5><font color=#999999><img src=/ui/notify_pm.png align=absmiddle border=0> For Your Information</font></h5>
	<div class=ntc>Click message to see detail.</div><br />
	<div style="width:100%;height:10px">
		<div style="float:left;width:80%;">
			<form>
				<?php if ($this->_tpl_vars['pagination']): ?>
					<b>Go To Page</b>
					<select id="s" name="s" onchange="ajax_get_pm(this.value)">
						<?php $_from = $this->_tpl_vars['pagination']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['paging']):
?>
							<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['selected_page'] == $this->_tpl_vars['k']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['paging']; ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
					</select>
				<?php endif; ?>
				<span class=ntc>Total <?php echo $this->_tpl_vars['total_pm']; ?>
 messages</span>
			</form>
		</div>
		<div style="float:right;text-align:right;width:20%;">
			<a href="javascript:void(clear_all_pm());">Clear All</a>
		</div>
	</div>
	<br>
	<div style="width:100%;height:400px;border:2px inset black;overflow-x:hidden;overflow-y:auto;position:relative;" class="ui-corner-all" id="div_pm_notification">
	<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<tbody class="tbody_container" id="tbody_pm_list">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['pm']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<!-- <?php echo $this->_tpl_vars['pm_count']++; ?>
 -->
	<tr id="pm-<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['branch_id']; ?>
-<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['id']; ?>
" style="<?php if (! $this->_tpl_vars['pm'][$this->_sections['i']['index']]['opened']): ?>background-color:FFFFE0 ;<?php endif; ?>">
		<td width="20">
			<img src="/ui/closewin.png" align="absmiddle" class="clickable img_delete_pm_row" title="Mark as read and close" onclick="pm_delete(<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['branch_id']; ?>
,<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['id']; ?>
);" />
		</td>
		<td>
			<a href="/pm.php?a=view_pm&branch_id=<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['branch_id']; ?>
&id=<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['id']; ?>
" target=_blank  style="<?php if (! $this->_tpl_vars['pm'][$this->_sections['i']['index']]['opened']): ?>font-weight: bold;<?php endif; ?>" onclick="change_style(this,<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['branch_id']; ?>
,<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['id']; ?>
);"><?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['msg']; ?>
</a>
			<br><font color=#666666 class=small ><?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['u']; ?>
 (<?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['branch']; ?>
 - <?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['position']; ?>
) @ <?php echo $this->_tpl_vars['pm'][$this->_sections['i']['index']]['timestamp']; ?>
</font>
		<td>
	</tr>
	<?php endfor; endif; ?>
	</tbody>
	</table>
	</div>
	<script>
	pm_count = <?php echo $this->_tpl_vars['pm_count']; ?>
;
	</script>
<?php endif; ?>