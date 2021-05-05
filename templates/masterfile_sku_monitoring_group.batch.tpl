{*
9/14/2010 2:17:01 PM Andy
- Fix viewing if rows too many will out of div issue.

06/26/2020 1:37 Sheila
- Fixed table height
*}

{config_load file=site.conf}
{if $form.changed}
	<div style="color:red;text-align:center;background:#fcc;">* Batch group is not up to date, please click regenerate.</div>
{/if}
{if !$batch_list}-- No Batch Found --<br />
{else}
{if count($batch_list)>=17}{assign var=batch_show_scroll value=1}{/if}
{if count($item_list)>=17}{assign var=item_show_scroll value=1}{/if}

<br />

<div id="div_batch_container">
	<div class="tab" style="white-space:nowrap;height:21px;">
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:void(list_sel_batch(1))" id="lst_batch_1" class="active a_tab">Show by batch</a>
		<a href="javascript:void(list_sel_batch(2))" id="lst_batch_2" class="a_tab">Show by items</a>
	</div>

	<div style="height:450px;border:0px solid #000 !important; overflow: auto;">
		<!-- Show by date -->
		<table width="100%" id="tbl_batch_1" class="tbl">
			<tr>
			    <th width="40" bgcolor="{#TB_COLHEADER#}">&nbsp;</th>
			    <th bgcolor="{#TB_COLHEADER#}">Date</th>
			    <th bgcolor="{#TB_COLHEADER#}">SKU In batch</th>
			    {if $batch_show_scroll}<th width="13">&nbsp;</th>{/if}
			</tr>
			<tbody style="{if $batch_show_scroll}height:330px;overflow-y:auto;overflow-x:hidden;{/if}">
			{foreach from=$batch_list item=r name=f}
			    <tr>
			        <td nowrap>{$smarty.foreach.f.iteration}.
						<a href="javascript:void(view_batch_item('{$r.sku_monitoring_group_id}','{$r.year}','{$r.month}'));">
						    <img src="ui/view.png" border="0" align="absmiddle" title="View SKU" />
						</a>
					</td>
			        <td class="r">{$r.month|str_month} {$r.year}</td>
					<td class="r">{$r.sku_count|number_format}</td>
			    </tr>
			{/foreach}
			</tbody>
		</table>

		<!-- show by sku -->
		<table width="100%" id="tbl_batch_2" class="tbl" style="display:none;">
            <tr>
			    <th width="40" bgcolor="{#TB_COLHEADER#}">&nbsp;</th>
			    <th bgcolor="{#TB_COLHEADER#}">ARMS Code</th>
			    <th bgcolor="{#TB_COLHEADER#}">Description</th>
			    <th bgcolor="{#TB_COLHEADER#}">Batch Count</th>
			    {if $item_show_scroll}<th width="13">&nbsp;</th>{/if}
			</tr>
			<tbody style="{if $item_show_scroll}height:330px;overflow-y:auto;overflow-x:hidden;{/if}">
			{foreach from=$item_list item=r name=f}
			    <tr>
			        <td nowrap>{$smarty.foreach.f.iteration}.
						<a href="javascript:void(view_item_batch('{$r.sku_monitoring_group_id}','{$r.sku_item_id}'));">
						    <img src="ui/view.png" border="0" align="absmiddle" title="View Batch" />
						</a>
					</td>
			        <td class="r">{$r.sku_item_code}</td>
			        <td class="r">{$r.description}</td>
					<td class="r">{$r.batch_count|number_format}</td>
			    </tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>

<div align="center" style="margin: 10px 0"><input class="btn btn-error" type="button" value="Close" onClick="default_curtain_clicked();" /></div>
{/if}
