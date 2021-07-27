<?php /* Smarty version 2.6.18, created on 2021-07-27 21:28:22
         compiled from adjustment.btm_include.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'adjustment.btm_include.tpl', 1, false),)), $this); ?>
<span class="badge badge-pill badge-primary p-2 tx-12"><?php echo ((is_array($_tmp=$this->_tpl_vars['items_details']['total_item'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
 <?php echo $this->_tpl_vars['LNG']['ITEMS']; ?>
, <?php echo ((is_array($_tmp=$this->_tpl_vars['items_details']['total_pcs'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
 <?php echo $this->_tpl_vars['LNG']['PCS']; ?>
</span>