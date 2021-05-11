<?php /* Smarty version 2.6.18, created on 2021-05-10 17:41:10
         compiled from masterfile_vendor_table.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'masterfile_vendor_table.tpl', 34, false),array('modifier', 'escape', 'masterfile_vendor_table.tpl', 78, false),array('modifier', 'nl2br', 'masterfile_vendor_table.tpl', 113, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>


<?php if ($_REQUEST['a'] != 'ajax_reload_table'): ?>
<div id="udiv" class="stdframe">
<?php endif; ?>
<span id="span_loading_vendor_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...<br /><br /></span>

<?php if ($this->_tpl_vars['pagination']): ?>
Page:&nbsp;&nbsp;
<select name="pg" id="pg" onchange="reload_table(true)">
	<?php echo $this->_tpl_vars['pagination']; ?>

</select>&nbsp;of <b><?php echo $this->_tpl_vars['total_page']; ?>
</b>
&nbsp;&nbsp;
<?php endif; ?>
<span style="color:#CE0000;"><b>(Total of <?php echo $this->_tpl_vars['vcount']; ?>
 records)</b></span>
<br /><br />

<table class="sortable" id="vendor_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
<tr>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_CORNER']; ?>
 width=40>&nbsp;</th>
<?php endif; ?>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>&nbsp;</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Code</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Description</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>Company No</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>GST Registration Number</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>Address</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>Phone #1</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>Phone #2</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
 nowrap>Fax No.</th>
<th bgcolor=<?php echo $this->_config[0]['vars']['TB_COLHEADER']; ?>
>Contact</th>
</tr>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['vendors']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>
	<td bgcolor=<?php echo $this->_config[0]['vars']['TB_ROWHEADER']; ?>
 nowrap>
	<a href="javascript:void(ed(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
))"><img src=ui/ed.png title="Edit" border=0></a>
	
	<a href="javascript:void(act(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
,<?php if ($this->_tpl_vars['vendors'][$this->_sections['i']['index']]['active']): ?>0))"><img src=ui/deact.png title="Deactivate" border=0><?php else: ?>1))"><img src=ui/act.png title="Activate" border=0><?php endif; ?></a>
	</td>
<?php endif; ?>

<td align=center nowrap>
	<a href="javascript:void(showtd(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
, '<?php echo ((is_array($_tmp=$this->_tpl_vars['vendors'][$this->_sections['i']['index']]['description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
'))">
	<img src=ui/table.png title="open Trade Discount Table" border=0>
	</a>
	
	<?php if ($this->_tpl_vars['config']['payment_voucher_vendor_maintenance']): ?>
	<img src=ui/report_edit.png title="Payment Voucher Maintenance" border=0 onclick="javascript:void(show_vvc(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
, '<?php echo ((is_array($_tmp=$this->_tpl_vars['vendors'][$this->_sections['i']['index']]['description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
'))">
	<?php endif; ?>
	
	<!--By clicking the vendor on the master vendor list module, able to view all the GRN received - Link to grn summary (new)-->
	<a href="javascript:void(open_grn(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
))">
	<img src=ui/lorry.png title="open Vendor GRN" border=0>
	</a>
	
	<img src="ui/table_delete.png" title="open Branch Block List" border =0 onclick="javascript:void(show_vbb(<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
,'<?php echo ((is_array($_tmp=$this->_tpl_vars['vendors'][$this->_sections['i']['index']]['description'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
'))">
	
	<?php if ($this->_tpl_vars['config']['enable_vendor_portal']): ?>
				<a href="masterfile_vendor.vendor_portal.php?vid=<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
" target="_blank">
			<img src="ui/icons/key.png" border="0" title="Manage Vendor Portal Access Key" />
		</a>
	<?php endif; ?>
	
	<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR_QUOTATION_COST']): ?>
		<a href="masterfile_vendor.quotation_cost.php?vendor_id=<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
" target="_blank">
		<img src="ui/icons/database_edit.png" title="Edit Quotation Cost" border="0" />
		</a>
	<?php endif; ?>
</td>

<td>
<b><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['code']; ?>
</b><?php if (! $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['active']): ?><br><span class=small>(inactive)</span><?php endif; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['description']; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['company_no']; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['gst_register_no']; ?>
</td>
<td><?php echo ((is_array($_tmp=$this->_tpl_vars['vendors'][$this->_sections['i']['index']]['address'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['phone_1']; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['phone_2']; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['phone_3']; ?>
</td>
<td><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['contact_person']; ?>
<br>
<a href="mailto:<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['contact_email']; ?>
"><?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['contact_email']; ?>
</a></td>
</tr>
<?php endfor; endif; ?>
</table>
<?php if ($_REQUEST['a'] != 'ajax_reload_table'): ?>
</div>

<script>
	parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('vendor_tbl'));
</script>
<?php endif; ?>