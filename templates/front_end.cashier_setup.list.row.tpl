{*
10/06/2011 11:32:35 AM Kee Kee
- Add Mprice privilege for each user
*}

<div class="tbody fs-08">
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td bgcolor="{#TB_ROWHEADER#}" nowrap>
			{if $is_tmp}
				<a href="?a={if $user.active}view{else}open{/if}&uid={$uid}&is_tmp={$is_tmp}{if $is_tmp}&branch_id={$user.branch_id}{/if}">
					<img src="/ui/{if $user.active}view{else}ed{/if}.png" title="{if $user.active}View{else}Edit{/if}" border="0" />
				</a>
			{else}
				<a href="?a=open&uid={$uid}"><img src="/ui/ed.png" title="Edit" border="0" /></a>
				<a href="javascript:void(toggle_act('{$uid}'))">
					<img src="/ui/{if $user.active}deact.png{else}act.png{/if}" title="Activate/Deactivate" border="0" id="img_user_act-{$uid}" />
				</a>
			{/if}
			
		</td>
		<td>{$user.u} 
			{if $is_tmp}
				{if $user.active}
					<span style="color:red;">(Waiting Approval)</span>
				{else}
					<span style="color:#060;">(Draft)</span>
				{/if}	
			{/if}
		</td>
		<td>{$user.l}</td>
		<td>{$user.fullname|default:'-'}</td>
		{if $config.user_profile_need_ic}
			<td>{$user.ic_no|default:'-'}</td>
		{/if}
		<td>{$user.email|default:'-'}</td>
		<td class="r">{$user.discount_limit|ifzero:'-':'%'}</td>
		<td align="center">{$branches[$user.default_branch_id].code}</td>
		{if $mprice_list}
			{assign var=mp value=$user.allow_mprice}
			{foreach from=$mprice_list item=val}
			<td>{if $mp.$val && !$mp.not_allow}Allowed{/if}</td>
			{/foreach}
		{/if}
	</tr>
</div>