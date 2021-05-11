<?php /* Smarty version 2.6.18, created on 2021-05-07 18:20:25
         compiled from user_privilege_table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'count', 'user_privilege_table.tpl', 56, false),array('function', 'get2ditem', 'user_privilege_table.tpl', 74, false),)), $this); ?>

<table border=0 cellspacing=0 cellpadding=4>
<tbody id=header>
<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
	<tr>
		<th align=left>Clone Branch Privileges</th>
		<th align=center width=20%>
		Source Branch<br>
		<select name="source_branch" id="sc"> 
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<option value=<?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['id']; ?>
><?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['code']; ?>
</option>
		<?php endfor; endif; ?>
		</select>
		</th>
		<th align=center width=20%>
		Destination Branch<br>
		<select name="destination_branch" id="dc"> 
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<option value=<?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['id']; ?>
><?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['code']; ?>
</option>
		<?php endfor; endif; ?>
		</select>
		</th>
		<th>
		<button type="button" onclick="clone_selected_col()">
		Clone
		</button>
		</th>
	</tr>
<?php endif; ?>
<tr>
	<td colspan=2><h5>User Privileges</h5></td>
	<?php unset($this->_sections['c']);
$this->_sections['c']['name'] = 'c';
$this->_sections['c']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['c']['show'] = true;
$this->_sections['c']['max'] = $this->_sections['c']['loop'];
$this->_sections['c']['step'] = 1;
$this->_sections['c']['start'] = $this->_sections['c']['step'] > 0 ? 0 : $this->_sections['c']['loop']-1;
if ($this->_sections['c']['show']) {
    $this->_sections['c']['total'] = $this->_sections['c']['loop'];
    if ($this->_sections['c']['total'] == 0)
        $this->_sections['c']['show'] = false;
} else
    $this->_sections['c']['total'] = 0;
if ($this->_sections['c']['show']):

            for ($this->_sections['c']['index'] = $this->_sections['c']['start'], $this->_sections['c']['iteration'] = 1;
                 $this->_sections['c']['iteration'] <= $this->_sections['c']['total'];
                 $this->_sections['c']['index'] += $this->_sections['c']['step'], $this->_sections['c']['iteration']++):
$this->_sections['c']['rownum'] = $this->_sections['c']['iteration'];
$this->_sections['c']['index_prev'] = $this->_sections['c']['index'] - $this->_sections['c']['step'];
$this->_sections['c']['index_next'] = $this->_sections['c']['index'] + $this->_sections['c']['step'];
$this->_sections['c']['first']      = ($this->_sections['c']['iteration'] == 1);
$this->_sections['c']['last']       = ($this->_sections['c']['iteration'] == $this->_sections['c']['total']);
?>
	<th width=50>
		<a href="javascript:void(checkallcol('user_privilege', '<?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['id']; ?>
', true))"><img src=ui/checkall.gif border=0 title="Check all"></a><br>
		<a href="javascript:void(checkallcol('user_privilege', '<?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['id']; ?>
', false))"><img src=ui/uncheckall.gif border=0 title="Uncheck all"></a><br>
		<label title="<?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['description']; ?>
"><?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['code']; ?>
</label>
	</th>
	<?php endfor; endif; ?>
	<th align=left width=100%>Description</th>
</tr>
<?php $_from = $this->_tpl_vars['privileges']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['grp'] => $this->_tpl_vars['pg']):
?>
<tr>
	<td style="cursor:s-resize;border-top:1px solid #fff;border-bottom:1px solid #999;" colspan="<?php echo smarty_function_count(array('var' => $this->_tpl_vars['branches'],'offset' => 3), $this);?>
" onclick="togglediv('group[<?php echo $this->_tpl_vars['grp']; ?>
]','exp[<?php echo $this->_tpl_vars['grp']; ?>
]')">
		<h4 style="margin:0"> 
			<img src="/ui/<?php if ($this->_tpl_vars['grp'] == 'Others'): ?>collapse<?php else: ?>expand<?php endif; ?>.gif" id="exp[<?php echo $this->_tpl_vars['grp']; ?>
]" /> 
			<?php if ($this->_tpl_vars['grp'] == 'Others'): ?>Others<?php else: ?><?php echo $this->_tpl_vars['privilege_groupname'][$this->_tpl_vars['grp']]; ?>
<?php endif; ?>
		</h4>
	</td>
</tr>
<tbody id="group[<?php echo $this->_tpl_vars['grp']; ?>
]" style="background:#fff;<?php if ($this->_tpl_vars['grp'] != 'Others'): ?>display:none<?php endif; ?>">
<?php $_from = $this->_tpl_vars['pg']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pv']):
?>
<tr>
	<td style="border-bottom:1px solid #999"><a href="javascript:void(checkallrow('user_privilege', '<?php echo $this->_tpl_vars['pv']['code']; ?>
', true))"><img src=ui/checkall.gif border=0 title="Check all"></a><br>
	<a href="javascript:void(checkallrow('user_privilege', '<?php echo $this->_tpl_vars['pv']['code']; ?>
', false))"><img src=ui/uncheckall.gif border=0 title="Uncheck all"></a></td>
	<th style="border-bottom:1px solid #999" align=left><label title="<?php echo $this->_tpl_vars['pv']['description']; ?>
"><?php echo $this->_tpl_vars['pv']['code']; ?>
</label></th>
	<?php unset($this->_sections['c']);
$this->_sections['c']['name'] = 'c';
$this->_sections['c']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['c']['show'] = true;
$this->_sections['c']['max'] = $this->_sections['c']['loop'];
$this->_sections['c']['step'] = 1;
$this->_sections['c']['start'] = $this->_sections['c']['step'] > 0 ? 0 : $this->_sections['c']['loop']-1;
if ($this->_sections['c']['show']) {
    $this->_sections['c']['total'] = $this->_sections['c']['loop'];
    if ($this->_sections['c']['total'] == 0)
        $this->_sections['c']['show'] = false;
} else
    $this->_sections['c']['total'] = 0;
if ($this->_sections['c']['show']):

            for ($this->_sections['c']['index'] = $this->_sections['c']['start'], $this->_sections['c']['iteration'] = 1;
                 $this->_sections['c']['iteration'] <= $this->_sections['c']['total'];
                 $this->_sections['c']['index'] += $this->_sections['c']['step'], $this->_sections['c']['iteration']++):
$this->_sections['c']['rownum'] = $this->_sections['c']['iteration'];
$this->_sections['c']['index_prev'] = $this->_sections['c']['index'] - $this->_sections['c']['step'];
$this->_sections['c']['index_next'] = $this->_sections['c']['index'] + $this->_sections['c']['step'];
$this->_sections['c']['first']      = ($this->_sections['c']['iteration'] == 1);
$this->_sections['c']['last']       = ($this->_sections['c']['iteration'] == $this->_sections['c']['total']);
?>
	<td style="border-bottom:1px solid #999" align=center>
	<?php if ($this->_tpl_vars['pv']['hq_only'] && $this->_tpl_vars['branches'][$this->_sections['c']['index']]['code'] != 'HQ'): ?>
	-
	<?php else: ?>
	<input type=checkbox name="user_privilege[<?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['id']; ?>
][<?php echo $this->_tpl_vars['pv']['code']; ?>
]" <?php echo smarty_function_get2ditem(array('array' => $this->_tpl_vars['user_privilege'],'r' => $this->_tpl_vars['branches'][$this->_sections['c']['index']]['id'],'c' => $this->_tpl_vars['pv']['code'],'retval' => 'checked'), $this);?>
 class="inp_priv-<?php echo $this->_tpl_vars['branches'][$this->_sections['c']['index']]['id']; ?>
" priv_code="<?php echo $this->_tpl_vars['pv']['code']; ?>
" />
	<?php endif; ?>
	</td>
	<?php endfor; endif; ?>
	<td style="border-bottom:1px solid #999" class=small>
	<?php echo $this->_tpl_vars['pv']['description']; ?>

	</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</tbody>
<?php endforeach; endif; unset($_from); ?>
</table>