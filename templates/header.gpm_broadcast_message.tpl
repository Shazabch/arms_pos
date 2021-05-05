{*
10/11/2013 2:59 PM Andy
- Fix the broadcast message sometime will not stick to the top if got error message.
*}

{load_and_show_gpm_broadcast_message var=broadcast_msg}

{if $broadcast_msg}
	<div style="background: none repeat scroll 0 0 #FFFF99;border-bottom: 1px solid #CC0022;clear: both;left: 0;top:0;position: fixed;width: 100%;">
		<marquee onmouseout="this.scrollAmount=3;" onmouseover="this.scrollAmount=0;" scrollamount="3" scrolldelay="10" style="color: #CC0022;font-size: 12px;font-weight: bold;height: 25px;line-height: 25px;">
			{foreach from=$broadcast_msg item=r}
				<span style="color:#666;">{$r.added}: </span>
				{$r.msg|escape:'html'}
				<img src="ui/pixel.gif" width="50" height="1" />
			{/foreach}
		</marquee>
	</div>
	<div style="height:26px;"></div>
{/if}