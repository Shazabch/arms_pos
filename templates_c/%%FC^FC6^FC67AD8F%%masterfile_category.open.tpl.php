<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:49
         compiled from masterfile_category.open.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'ucwords', 'masterfile_category.open.tpl', 318, false),array('modifier', 'urlencode', 'masterfile_category.open.tpl', 433, false),array('modifier', 'escape', 'masterfile_category.open.tpl', 433, false),)), $this); ?>

<form method="post" name="f_b" onSubmit="return false;">
	<div id=bmsg style="padding:10 0 10 0px;"></div>
	<input type="hidden" name="a" value="save_cat" />
	<input type="hidden" name="id" value="<?php echo $this->_tpl_vars['form']['id']; ?>
" />
	<input type="hidden" name="level" value="<?php echo $this->_tpl_vars['form']['level']; ?>
" />
	<input type="hidden" name="root_id" value="<?php echo $this->_tpl_vars['form']['root_id']; ?>
" />
	<input type="hidden" name="tree_str" value="<?php echo $this->_tpl_vars['form']['tree_str']; ?>
" />
	<input type="hidden" name="got_pos_photo" value="<?php echo $this->_tpl_vars['form']['got_pos_photo']; ?>
" />
	<input type="hidden" name="has_tmp_photo" value="0" />
	<input type="hidden" name="tmp_photo" value="" />
	
	<table id="tb">
		<tr>
			<td><b>Code</b> (Optional)</td>
			<td>
				<input onBlur="uc(this)" id="cat_code_id" name="code" size="17" maxlength="15" value="<?php echo $this->_tpl_vars['form']['code']; ?>
" />
			</td>
		</tr>
		<tr>
			<td><b>Description</b></td>
			<td>
				<input onBlur="uc(this)" name="description" size="50" value="<?php echo $this->_tpl_vars['form']['description']; ?>
" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		<tr>
			<td valign="top"><b>Area</b> (Optional)</td>
			<td>
				<input onBlur="this.value=round2(this.value)" name="area" size="20" value="<?php echo $this->_tpl_vars['form']['area']; ?>
" />
			</td>
		</tr>
		<?php if ($this->_tpl_vars['form']['level'] < 4): ?>
			<tbody id="category_options">
								<tr>
					<td valign="top">
						<b>Discount</b>
						<a href="javascript:void(alert('Note: Branches and member type Settings only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All)\n\nRequire privilege CATEGORY_DISCOUNT_EDIT to use this.'));">
							<img src="/ui/icons/information.png" align="absmiddle" />
						</a>
						<br />
					</td>
					<td>
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.tpl', 'smarty_include_vars' => array('is_edit' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					</td>
				</tr>
				
								<tr>
					<td valign="top">
						<b>Reward Point</b>
						<a href="javascript:void(alert('Note: Branches and member type Settings only available at counter BETA v168.\n\nInherit: Member Type (Branch) -> Member Type (All) -> Member (Branch) -> Member (All) \n\nRequire privilege MEMBER_POINT_REWARD_EDIT to use this.'));">
							<img src="/ui/icons/information.png" align="absmiddle" />
						</a>
						<br />
					</td>
					<td>
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.point.tpl', 'smarty_include_vars' => array('is_edit' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					</td>
				</tr>
				
								<?php if ($this->_tpl_vars['config']['membership_enable_staff_card'] && $this->_tpl_vars['config']['membership_staff_type']): ?>
					<tr>
						<td valign="top">
							<b>Staff Discount</b>
							<a href="javascript:void(alert('Note: Branches and Staff type Settings only available at counter alpha version.\n\nInherit: Staff Type (Branch) -> Staff Type (All) -> Staff (Branch) -> Staff (All)\n\nRequire privilege CATEGORY_STAFF_DISCOUNT_EDIT to use this.'));">
								<img src="/ui/icons/information.png" align="absmiddle" />
							</a>
							<br />
						</td>
						<td>
							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.staff_discount.tpl', 'smarty_include_vars' => array('is_edit' => 1)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		<?php endif; ?>
				<?php if ($this->_tpl_vars['form']['level'] == 2): ?>
			<tbody id="grn_options">
				<tr>
					<td valign="top"><b>Show PO Qty<br>in GRN Worksheet</b></td>
					<td>
						<select name="grn_po_qty">
							<option value="0">No</option>
							<option value="1" <?php if ($this->_tpl_vars['form']['grn_po_qty'] == 1): ?>selected <?php endif; ?>>Yes</option>
						</select>
					</td>
				</tr>
				<tr>
					<td valign="top"><b>GRN with Weight</b></td>
					<td>
						<select name="grn_get_weight">
							<option value="0">No</option>
							<option value="1" <?php if ($this->_tpl_vars['form']['grn_get_weight'] == 1): ?>selected <?php endif; ?>>Yes</option>
						</select>
					</td>
				</tr>
			</tbody>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['form']['level'] <= 2): ?>
			<tr>
				<td colspan="2">
					<div id="photo_tb">
						<br>
						<b>Photos required for SKU Application</b>
						<table>
							<?php $_from = $this->_tpl_vars['sku_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['t']):
?>
								<tr>
									<td><?php echo $this->_tpl_vars['t']['description']; ?>
</td>
									<td>
										<select name="min_sku_photo[<?php echo $this->_tpl_vars['k']; ?>
]">
											<option value="-1" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == -1): ?>selected <?php endif; ?>>Inherit (Follow parent category)</option>
											<option value="0" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 0): ?>selected <?php endif; ?>>Not required</option>
											<option value="1" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 1): ?>selected <?php endif; ?>>At least 1</option>
											<option value="2" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 2): ?>selected <?php endif; ?>>At least 2</option>
											<option value="3" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 3): ?>selected <?php endif; ?>>At least 3</option>
											<option value="4" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 4): ?>selected <?php endif; ?>>At least 4</option>
											<option value="5" <?php if ($this->_tpl_vars['form']['min_sku_photo'][$this->_tpl_vars['k']] == 5): ?>selected <?php endif; ?>>At least 5</option>
										</select>
									</td>
								</tr>
							<?php endforeach; endif; unset($_from); ?>
						</table>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['config']['enable_no_inventory_sku']): ?>
			<!-- No Inventory-->
			<tr>
				<td><b>SKU Without Inventory</b></td>
				<td>
				    <select name="no_inventory">
						<?php if ($this->_tpl_vars['form']['level'] != 1): ?>
							<option value="inherit" <?php if (! $this->_tpl_vars['form']['no_inventory'] || $this->_tpl_vars['form']['no_inventory'] == 'inherit'): ?>selected <?php endif; ?>>Inherit</option>
						<?php endif; ?>
				        <?php $_from = $this->_tpl_vars['inherit_options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
							<?php if ($this->_tpl_vars['form']['level'] == 1 && $this->_tpl_vars['k'] == 'inherit'): ?>
							<?php else: ?>
								<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['form']['no_inventory'] == $this->_tpl_vars['k']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']; ?>
</option>
							<?php endif; ?>
				        <?php endforeach; endif; unset($_from); ?>
				    </select>
				</td>
			</tr>
		<?php else: ?>
			<input type="hidden" name="no_inventory" value="<?php if ($this->_tpl_vars['form']['level'] != 1): ?>inherit<?php else: ?>no<?php endif; ?>" />
		<?php endif; ?>
	
		<?php if ($this->_tpl_vars['config']['enable_fresh_market_sku']): ?>
			<!-- Is Fresh Market SKU-->
			<tr>
				<td><b>Is Fresh Market SKU</b></td>
				<td>
				    <select name="is_fresh_market">
						<?php if ($this->_tpl_vars['form']['level'] != 1): ?>
							<option value="inherit" <?php if (! $this->_tpl_vars['form']['is_fresh_market'] || $this->_tpl_vars['form']['is_fresh_market'] == 'inherit'): ?>selected <?php endif; ?>>Inherit</option>
						<?php endif; ?>
				        <?php $_from = $this->_tpl_vars['inherit_options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
							<?php if ($this->_tpl_vars['form']['level'] == 1 && $this->_tpl_vars['k'] == 'inherit'): ?>
							<?php else: ?>
								<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if (( $this->_tpl_vars['form']['is_fresh_market'] == $this->_tpl_vars['k'] ) || ( ! $this->_tpl_vars['form']['id'] && $this->_tpl_vars['form']['level'] == 1 && $this->_tpl_vars['k'] == 'no' )): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']; ?>
</option>
							<?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
				    </select>
				</td>
			</tr>
		<?php else: ?>
			<input type="hidden" name="is_fresh_market" value="inherit" />
		<?php endif; ?>

		<?php if ($this->_tpl_vars['is_gst']): ?>
		<tr>
			<td><b>Input Tax</b></td>
			<td>
				<select name="input_tax">
					<?php if ($this->_tpl_vars['form']['level'] == 1): ?>
						<option value="-1">Inherit (Follow GST Setting: <?php echo $this->_tpl_vars['root_info']['input_tax']['code']; ?>
 [<?php echo $this->_tpl_vars['root_info']['input_tax']['rate']; ?>
%])</option>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['form']['level'] > 1 || $this->_tpl_vars['form']['level'] > 10): ?>
						<option value="-1">Inherit (Follow Parent Category: <?php echo $this->_tpl_vars['root_info']['input_tax']['code']; ?>
 [<?php echo $this->_tpl_vars['root_info']['input_tax']['rate']; ?>
%])</option>
					<?php endif; ?>
					<?php $_from = $this->_tpl_vars['input_tax']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
						<option value="<?php echo $this->_tpl_vars['val']['id']; ?>
" <?php if ($this->_tpl_vars['form']['input_tax'] == $this->_tpl_vars['val']['id']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']['code']; ?>
 - <?php echo $this->_tpl_vars['val']['description']; ?>
</option>
					<?php endforeach; endif; unset($_from); ?>
				</select>
			</td>
		</tr>

		<tr>
			<td><b>Output Tax</b></td>
			<td>
				<select name="output_tax">
					<?php if ($this->_tpl_vars['form']['level'] == 1): ?>
						<option value="-1">Inherit (Follow GST Setting: <?php echo $this->_tpl_vars['root_info']['output_tax']['code']; ?>
 [<?php echo $this->_tpl_vars['root_info']['output_tax']['rate']; ?>
%])</option>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['form']['level'] > 1 || $this->_tpl_vars['form']['level'] > 10): ?>
						<option value="-1">Inherit (Follow Parent Category: <?php echo $this->_tpl_vars['root_info']['output_tax']['code']; ?>
 [<?php echo $this->_tpl_vars['root_info']['output_tax']['rate']; ?>
%])</option>
					<?php endif; ?>
					<?php $_from = $this->_tpl_vars['output_tax']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
						<option value="<?php echo $this->_tpl_vars['val']['id']; ?>
" <?php if ($this->_tpl_vars['form']['output_tax'] == $this->_tpl_vars['val']['id']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']['code']; ?>
 - <?php echo $this->_tpl_vars['val']['description']; ?>
</option>
					<?php endforeach; endif; unset($_from); ?>
				</select>
			</td>
		</tr>

		<?php if ($this->_tpl_vars['form']['inclusive_tax'] == 'no' || $this->_tpl_vars['root_info']['inclusive_tax'] == 'no'): ?>
			<tr>
				<td><b>Selling Price Inclusive Tax</b></td>
				<td>
					<select name="inclusive_tax">
						<option value="inherit" <?php if ($this->_tpl_vars['form']['inclusive_tax'] == 'inherit'): ?>selected <?php endif; ?>>
							Inherit (Follow <?php if ($this->_tpl_vars['form']['level'] == 1): ?>GST Settings<?php else: ?>Parent Category<?php endif; ?>: <?php echo ucwords($this->_tpl_vars['root_info']['inclusive_tax']); ?>
)
						</option>
						<?php $_from = $this->_tpl_vars['inherit_options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
							<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if ($this->_tpl_vars['form']['inclusive_tax'] == $this->_tpl_vars['k']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']; ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
					</select>
				</td>
			</tr>
		<?php endif; ?>
		
		<?php endif; ?>
		<?php if ($this->_tpl_vars['form']['level'] > 1): ?>
		<tr>
			<td><b>Can Auto load all po items for GRN</b></td>
			<td>
				<select name="grn_auto_load_po_items">
					<option value="0" <?php if ($this->_tpl_vars['form']['grn_auto_load_po_items'] == '0'): ?>selected <?php endif; ?>>No</option>
					<option value="1" <?php if ($this->_tpl_vars['form']['grn_auto_load_po_items'] == '1'): ?>selected <?php endif; ?>>Yes</option>
				</select>
			</td>
		</tr>
		<?php endif; ?>
		
		<?php if ($this->_tpl_vars['config']['enable_one_color_matrix_ibt'] && $this->_tpl_vars['form']['level'] >= 2): ?>
			<tr>
				<td><b>Use Matrix Settings</b></td>
				<td>
					<select name="use_matrix" onchange="matrix_changed();">
						<?php if ($this->_tpl_vars['form']['level'] >= 3): ?>
							<option value="inherit" <?php if (! $this->_tpl_vars['form']['use_matrix'] || $this->_tpl_vars['form']['use_matrix'] == 'inherit'): ?>selected <?php endif; ?>>Inherit</option>
						<?php endif; ?>
						<?php $_from = $this->_tpl_vars['inherit_options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['val']):
?>
							<option value="<?php echo $this->_tpl_vars['k']; ?>
" <?php if (( $this->_tpl_vars['form']['use_matrix'] == $this->_tpl_vars['k'] ) || ( ! $this->_tpl_vars['form']['use_matrix'] && $this->_tpl_vars['form']['level'] == 2 && $this->_tpl_vars['k'] == 'no' )): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['val']; ?>
</option>
						<?php endforeach; endif; unset($_from); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.matrix.tpl', 'smarty_include_vars' => array('is_edit' => 1,'use_matrix' => $this->_tpl_vars['form']['use_matrix'],'cat_matrix' => $this->_tpl_vars['form']['matrix'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</td>
			</tr>
		<?php endif; ?>
		
				
		<?php if ($this->_tpl_vars['user_selection']): ?>
		<tr>
			<td colspan="2"><br /><b>Allowed user</b></td>
		</tr>
		<tr>
			<td colspan="2">
				<div style="height:200px;overflow:auto;">
				<?php $_from = $this->_tpl_vars['user_selection']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['brcode'] => $this->_tpl_vars['userlist']):
?>
					<table width="100%">
						<tr><td width="100%" style="border:1px solid black;border-collapse:collapse;background-color:#b0c4de;">
							&nbsp;&nbsp;&nbsp;<?php echo $this->_tpl_vars['brcode']; ?>

							<img style="float:right;cursor:pointer;" src="ui/uncheckall.gif" onclick="checkall_user('cb_user-<?php echo $this->_tpl_vars['brcode']; ?>
',false)" />
							<span style="float:right;">&nbsp;&nbsp;</span>
							<img style="float:right;cursor:pointer;" src="ui/checkall.gif" onclick="checkall_user('cb_user-<?php echo $this->_tpl_vars['brcode']; ?>
',true)" />
						</td></tr>
						<tr>
							<td width="100%" style="">
								<table width="100%" border="0">
								
								<?php $this->assign('ct', 0); ?>
								<?php $_from = $this->_tpl_vars['userlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['ul']):
?>
									<?php if ($this->_tpl_vars['ul']['is_arms_user']): ?>
										<td style="display:none;"><label><input name="allowed_user[<?php echo $this->_tpl_vars['ul']['id']; ?>
]" type="checkbox" checked /><?php echo $this->_tpl_vars['ul']['u']; ?>
</label></td>
									<?php else: ?>
										<?php if ($this->_tpl_vars['ct'] == 0): ?><tr><?php endif; ?>
										<td><label><input name="allowed_user[<?php echo $this->_tpl_vars['ul']['id']; ?>
]" class="cb_user-<?php echo $this->_tpl_vars['brcode']; ?>
" type="checkbox" <?php if ($this->_tpl_vars['allowed_user'][$this->_tpl_vars['ul']['id']]): ?>checked<?php endif; ?>/><?php echo $this->_tpl_vars['ul']['u']; ?>
</label></td>
										<?php if ($this->_tpl_vars['ct'] == 4): ?></tr><?php endif; ?>
										
										<?php $this->assign('ct', $this->_tpl_vars['ct']+1); ?>
										
										<?php if ($this->_tpl_vars['ct'] == 4): ?>
										<?php $this->assign('ct', 0); ?>
									<?php endif; ?>
								<?php endif; ?>
								
								<?php endforeach; endif; unset($_from); ?>
								
								</table>
							</td>
						</tr>
					</table>&nbsp;
				<?php endforeach; endif; unset($_from); ?>
				</div>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<td><b>Hide category at POS</b></td>
			<td>
			    <select name="hide_at_pos">
					<option value="1" <?php if ($this->_tpl_vars['form']['hide_at_pos']): ?>selected <?php endif; ?>>Yes</option>
					<option value="0" <?php if (! $this->_tpl_vars['form']['hide_at_pos']): ?>selected <?php endif; ?>>No</option>
			    </select>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<h5>PROMOTION / POS IMAGE <img src="/ui/add.png" align=absmiddle onclick="add_image(<?php echo $this->_tpl_vars['form']['id']; ?>
)"></h5>
				<div id=cat_photo>
				<?php if ($this->_tpl_vars['form']['cat_photo']): ?>
				<div id="pos_img" class=imgrollover>
					<img width=110 height=100 align=absmiddle id="promotion_img" vspace=4 hspace=4 alt="Photo #<?php echo $this->_foreach['i']['iteration']; ?>
" src="/thumb.php?w=110&h=100&cache=<?php if ($this->_tpl_vars['form']['cat_photo_time']): ?><?php echo $this->_tpl_vars['form']['cat_photo_time']; ?>
<?php else: ?>1<?php endif; ?>&img=<?php echo ((is_array($_tmp=$this->_tpl_vars['form']['cat_photo'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
" border=0 style="cursor:pointer" onClick="show_sku_image_div('<?php echo ((is_array($_tmp=$this->_tpl_vars['form']['cat_photo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'javascript') : smarty_modifier_escape($_tmp, 'javascript')); ?>
', <?php echo $this->_tpl_vars['form']['cat_photo_time']; ?>
, 10001);" title="View"><br>
					<img src="/ui/del.png" align=absmiddle onclick="if (confirm('Are you sure?'))del_image(this.parentNode,'<?php echo ((is_array($_tmp=$this->_tpl_vars['form']['cat_photo'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
', <?php echo $this->_tpl_vars['form']['id']; ?>
)"> Delete
				</div>
				<?php endif; ?>
				</div>
			</td>
		</tr>
	
		<tr id="tr_btn_row">
			<td align="center" colspan="2">
				<br>
				<input class="btn btn-success" type="button" value="Save" onClick="save_category();" id="btn_save_cat" />  
				<input class="btn btn-error" type="button" value="Close" onClick="default_curtain_clicked();" />
			</td>
		</tr>
	</table>
</form>

<!-- popup div -->
<div id=upload_popup style="display:none;">
<form onsubmit="return upload_check()" name=upl target=_ifs enctype="multipart/form-data" method=post>
<h4>Select an image</h4>
<input type=hidden name=a value="add_photo">
<input type=hidden name=id value="0"> 
<input name=fnew type=file><br>
<ul>
	<li>Photo must be a valid JPEG image or JPG file.</li>
</ul>
<br><input type=submit value="Upload"> <input type=button value="Cancel" onclick="curtain_clicked2()">
</form>
<iframe name=_ifs width=1 height=1 style="visibility:hidden"></iframe>
</div>