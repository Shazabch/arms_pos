{*
1/30/2018 1:29 PM Justin
- Enhanced to show 12 or 13 months base on sales requirements.
*}

<script type="text/javascript" language="javascript">
ldata = JSON.parse('{$json_datam}');
var month_loop_count = JSON.parse('{$month_loop_count}');
{literal}
function plot_me(){
	var cvheight = document.getElementById('cv1').height;
	var cvwidth = document.getElementById('cv1').width;
	var ctx = document.getElementById('cv1').getContext('2d');
	var cvstep = parseInt(cvwidth / month_loop_count);

	// fill bkg
	var lingrad = ctx.createLinearGradient(0,0,0,150);
	lingrad.addColorStop(0, '#f7f7f7');
	lingrad.addColorStop(1, '#fff');
	ctx.fillStyle = lingrad;
	ctx.fillRect(0,0,cvwidth,cvheight);

	// find max, sun pien draw lines
	max = 0; total = 0;

	ctx.beginPath();
	for (var m=1;m<=month_loop_count;m++)
	{
		h = parseInt(ldata[m]);
		if (isNaN(h)) h=0;

		total += h;
		if (max<h) max = h;

		ctx.moveTo(m*cvstep+0.5,0);
		ctx.lineTo(m*cvstep+0.5,cvheight);
	}
	ctx.lineWidth = 1;
	ctx.strokeStyle = '#eee';
	ctx.stroke();
	max = max * 1.1 / cvheight;
	total = total * 1.1 / cvheight;


	ctx.lineWidth = 5;
	ctx.lineCap = 'round';
	ctx.lineJoint = 'round';
	// draw monthly
	ctx.beginPath();
	for (var m=1;m<=month_loop_count;m++)
	{
		h = parseInt(ldata[m]);
		if (isNaN(h)) h=0;
		if (m==1)
			ctx.moveTo((m-0.5)*(cvstep),cvheight-h/max);
		else
			ctx.lineTo((m-0.5)*(cvstep),cvheight-h/max);
	}
	ctx.strokeStyle = '#c02';
	ctx.stroke();

	// draw total
	ctx.beginPath();
	t = 0;
	for (var m=1;m<=month_loop_count;m++)
	{
		h = parseInt(ldata[m]);
		if (isNaN(h)) h=0;
		t+=h;
		if (m==1)
			ctx.moveTo((m-0.5)*(cvstep),cvheight-t/total);
		else
			ctx.lineTo((m-0.5)*(cvstep),cvheight-t/total);
	}
	ctx.strokeStyle = '#05a';
	ctx.stroke();

	// frame
	ctx.lineWidth = 1;
	ctx.strokeStyle = '#ccc';
	ctx.strokeRect(0,0,cvwidth,cvheight);
}
if($('cv1') != undefined) plot_me();
{/literal}
</script>

<h2>Sales Trend of item {$item.sku_item_code} ({$branches_codes})</h2>

{if $data}
	<div style="margin-right:10px;float:left;">
		<table cellpadding="0" cellspacing="0" border="0">
			<tr class="small" align="center" style="color:#999;">
				{foreach from=$date_list item=date}
					<td width="30">{$date}</td>
				{/foreach}
			</tr>
			<tr><td colspan="{$month_loop_count}"><canvas id="cv1" width="600" height="120"></canvas></td></tr>
			<tr class="small" align="center">
				{foreach from=$date_list key=mth item=date}
					{assign var=ttl_qty value=$ttl_qty+$datam.qty.$mth}
					<td width="30"><font color="#cc0022">{$datam.qty.$mth|qty_nf}</font><br /><font color="#0055bb">{$ttl_qty}</font></td>
				{/foreach}
			</tr>
		</table>
	</div>
	
	<table cellspacing="1" cellpadding="4" border="0" style="padding:5px;border:1px solid #ccc;float:left;">
		<tr bgcolor="#ffee99">
			<td>&nbsp;</td>
			<th>Total Qty</th>
			<th>Per Mth</th>
		</tr>
		{foreach from=$data.qty key=mth item=qty}
			<tr>
				{assign var=avg value=$qty/$mth}
				<td nowrap>{$mth} month(s) ago</td>
				<td class="r">{$qty|qty_nf}</td>
				<td class="r">{$avg|number_format:2}</td>
			</tr>
		{/foreach}
	</table>
	
	<div style="padding-top:160px;">
	<table width="99%" cellspacing="1" cellpadding="4" border="0" style="padding:5px;border:1px solid #ccc;float:left;">
		<tr bgcolor="#ffee99">
			{foreach from=$date_list item=date}
				<th width="30">{$date}</td>
			{/foreach}
		</tr>
		<tr>
			{foreach from=$date_list key=mth item=date}
				{assign var=ttl_qty value=$ttl_qty+$datam.qty.$mth}
				<td width="30" class="r">{$datam.qty.$mth|qty_nf}</td>
			{/foreach}
		</tr>
	</table>
	</div>
{else}
	-- The item does not have sales for the past 12 months --
{/if}
