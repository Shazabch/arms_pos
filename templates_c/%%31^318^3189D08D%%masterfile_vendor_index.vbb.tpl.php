<?php /* Smarty version 2.6.18, created on 2021-05-10 17:41:10
         compiled from masterfile_vendor_index.vbb.tpl */ ?>

<!-- start payment vocher maintenance div -->

<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('vbb_div'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_v target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="vbb_keyin">
<input type=hidden name=vendor_id id=vendor_id value="">
<input type=hidden name=vendor id=vendor value="">
<h4 align=center>Branch Block List<br>
(<?php echo $this->_tpl_vars['vbb']['vendor']; ?>
)</h4>


<table id=tbl_vvc border=0 cellspacing=1 cellpadding=2>
<tr>
<td>&nbsp;</td>
<?php unset($this->_sections['b']);
$this->_sections['b']['name'] = 'b';
$this->_sections['b']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['b']['show'] = true;
$this->_sections['b']['max'] = $this->_sections['b']['loop'];
$this->_sections['b']['step'] = 1;
$this->_sections['b']['start'] = $this->_sections['b']['step'] > 0 ? 0 : $this->_sections['b']['loop']-1;
if ($this->_sections['b']['show']) {
    $this->_sections['b']['total'] = $this->_sections['b']['loop'];
    if ($this->_sections['b']['total'] == 0)
        $this->_sections['b']['show'] = false;
} else
    $this->_sections['b']['total'] = 0;
if ($this->_sections['b']['show']):

            for ($this->_sections['b']['index'] = $this->_sections['b']['start'], $this->_sections['b']['iteration'] = 1;
                 $this->_sections['b']['iteration'] <= $this->_sections['b']['total'];
                 $this->_sections['b']['index'] += $this->_sections['b']['step'], $this->_sections['b']['iteration']++):
$this->_sections['b']['rownum'] = $this->_sections['b']['iteration'];
$this->_sections['b']['index_prev'] = $this->_sections['b']['index'] - $this->_sections['b']['step'];
$this->_sections['b']['index_next'] = $this->_sections['b']['index'] + $this->_sections['b']['step'];
$this->_sections['b']['first']      = ($this->_sections['b']['iteration'] == 1);
$this->_sections['b']['last']       = ($this->_sections['b']['iteration'] == $this->_sections['b']['total']);
?>
<th width="50"><h4><?php echo $this->_tpl_vars['branches'][$this->_sections['b']['index']]['code']; ?>
</h4></th>
<?php endfor; endif; ?>
</tr>

<?php $_from = $this->_tpl_vars['type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tid'] => $this->_tpl_vars['tp']):
?>
	<tr>
		<th align=left ><?php echo $this->_tpl_vars['tp']; ?>
</th>
		<?php unset($this->_sections['b']);
$this->_sections['b']['name'] = 'b';
$this->_sections['b']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['b']['show'] = true;
$this->_sections['b']['max'] = $this->_sections['b']['loop'];
$this->_sections['b']['step'] = 1;
$this->_sections['b']['start'] = $this->_sections['b']['step'] > 0 ? 0 : $this->_sections['b']['loop']-1;
if ($this->_sections['b']['show']) {
    $this->_sections['b']['total'] = $this->_sections['b']['loop'];
    if ($this->_sections['b']['total'] == 0)
        $this->_sections['b']['show'] = false;
} else
    $this->_sections['b']['total'] = 0;
if ($this->_sections['b']['show']):

            for ($this->_sections['b']['index'] = $this->_sections['b']['start'], $this->_sections['b']['iteration'] = 1;
                 $this->_sections['b']['iteration'] <= $this->_sections['b']['total'];
                 $this->_sections['b']['index'] += $this->_sections['b']['step'], $this->_sections['b']['iteration']++):
$this->_sections['b']['rownum'] = $this->_sections['b']['iteration'];
$this->_sections['b']['index_prev'] = $this->_sections['b']['index'] - $this->_sections['b']['step'];
$this->_sections['b']['index_next'] = $this->_sections['b']['index'] + $this->_sections['b']['step'];
$this->_sections['b']['first']      = ($this->_sections['b']['iteration'] == 1);
$this->_sections['b']['last']       = ($this->_sections['b']['iteration'] == $this->_sections['b']['total']);
?>
			<?php $this->assign('bid', $this->_tpl_vars['branches'][$this->_sections['b']['index']]['id']); ?>
			<td align='center'>
				<input type="checkbox" name="block[<?php echo $this->_tpl_vars['tid']; ?>
][<?php echo $this->_tpl_vars['bid']; ?>
]" value="on" <?php if ($this->_tpl_vars['block'][$this->_tpl_vars['tid']][$this->_tpl_vars['bid']]): ?>checked <?php endif; ?> <?php if (! $this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>disabled<?php endif; ?>>
			</td>
		<?php endfor; endif; ?>
	</tr>
<?php endforeach; endif; unset($_from); ?>



</table>

<p align=center>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_VENDOR']): ?>
<input type=button value="Save" onclick="vbb_keyin();">
<?php endif; ?>
<input type=button value="Close" onclick="f_v.reset(); hidediv('vbb_div');">
</p>

</form>
</div></div></div>
<!-- end of div -->