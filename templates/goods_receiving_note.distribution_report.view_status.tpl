{*
5/21/2019 3:38 PM William
- Enhance "GRN" word to use report_prefix.
*}
{include file='header.tpl'}

<script>

{literal}
function delete_grn_distribution(bid, grn_id){
	var img = $('img_delete_grn_distribution-'+bid+'-'+grn_id);
	
	if(img.src.indexOf('clock')>=0){
		alert('Please wait...');
		return false;
	}
	var ori_src = img.src;
	
	img.src = '/ui/clock.gif';
	
	new Ajax.Request('ajax_autocomplete.php', {
		parameters:{
			a: 'ajax_delete_grn_distribution',
			bid: bid,
			grn_id: grn_id,
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			if(str == 'OK'){
				$('div_grn_distribution-'+bid+'-'+grn_id).remove();
			}else{
				alert(str);
				img.src = ori_src;
			}
		}
	});
}
{/literal}
</script>
<!-- GRN Distribution Status -->
{if $grn_deliver_monitor.grn}
	<h1><img src="/ui/store.png" align="absmiddle" border="0"> GRN Distribution Status</h1>
	<div class="ntc">The following GRN are slow in DO to others branches (below {$grn_deliver_monitor.info.min_do_qty_percent}% after {$grn_deliver_monitor.info.monitor_after_day} days)</div>
	<div style="border:1px solid #ccc;padding:5px;">
	
	{foreach from=$grn_deliver_monitor.grn item=grn}
		<div style="border-bottom:1px solid #eee" id="div_grn_distribution-{$grn.branch_id}-{$grn.id}">
			{if $sessioninfo.level>=9999}
				<a href="javascript:void(delete_grn_distribution('{$grn.branch_id}', '{$grn.id}'))">
					<img src="/ui/del.png" align="absmiddle" border="0" title="Delete this notify" id="img_delete_grn_distribution-{$grn.branch_id}-{$grn.id}" />
				</a>
			{/if} 
			<a href="/goods_receiving_note.php?a=view&id={$grn.id}&branch_id={$grn.branch_id}" target="_blank">{$grn.report_prefix}{$grn.id|string_format:'%05d'}</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<font class="small">
				<a href="goods_receiving_note.distribution_report.php?load_report=1&grn_bid_id={$grn.branch_id}_{$grn.id}" target="_blank">
					Delivered {$grn.do_per|num_format:2}%
				</a>
			</font> 
			<br />
			<font color="#666666" class="small">
				Received Date : {$grn.rcv_date}<br>
			</font>
		</div>
	{/foreach}
	{if $grn_deliver_monitor.have_more}
		<div style="text-align:center;">
			<a href="goods_receiving_note.distribution_report.php?a=view_status">Click here to view more</a>
		</div>
	{/if}
	</div>
{/if}

{include file='footer.tpl'}