<?php /* Smarty version 2.6.18, created on 2021-07-16 20:51:13
         compiled from adjustment.btm_include.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'adjustment.btm_include.tpl', 1, false),)), $this); ?>
<font size="3"><?php echo ((is_array($_tmp=$this->_tpl_vars['items_details']['total_item'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
 Item(s), <?php echo ((is_array($_tmp=$this->_tpl_vars['items_details']['total_pcs'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
 pcs</font>