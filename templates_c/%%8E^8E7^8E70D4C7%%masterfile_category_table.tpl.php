<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:32
         compiled from masterfile_category_table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'masterfile_category_table.tpl', 6, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>

<table  border=0 width=100% cellpadding=0 cellspacing=0>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['categories']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<tr id="r<?php echo $this->_tpl_vars['categories'][$this->_sections['i']['index']]['id']; ?>
" height=24 onmouseover="this.bgColor='<?php echo $this->_config[0]['vars']['TB_ROWHIGHLIGHT']; ?>
';" onmouseout="this.bgColor='';" class="tr_child_of-<?php echo $this->_tpl_vars['categories'][$this->_sections['i']['index']]['root_id']; ?>
">
<?php $this->assign('category_row', ($this->_tpl_vars['categories'][$this->_sections['i']['index']])); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_category_row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</tr>
<tr>
<td colspan=9 id="sc<?php echo $this->_tpl_vars['categories'][$this->_sections['i']['index']]['id']; ?>
" style="display:none">
</td>
</tr>
<?php endfor; endif; ?>
</table>
