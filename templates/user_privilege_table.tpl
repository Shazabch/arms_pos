{*
3/25/2011 6:23:13 PM Andy
- Move privilege group name variable to common.php

12/05/2011 2:44:00 PM Kee Kee
- Change "Others" privilege icon from "expand.gif" to "collapse.gif"

05/08/2019 15:35 Liew
- Add clone privilege from one branch to another branch

5/24/2019 10:25 AM Andy
- Fixed clone privilege only available under HQ.
*}

<div class="card mx-3 mt-3">
	<div class="card-body">
		{if $BRANCH_CODE eq 'HQ'}
			<div class="mt-4">
				<div class="">
					<label class="tx-bold">Clone branch privileges</label>
				</div>
				<div class="row mt-2">
					<div class="col-4">
						<label>Source Branch</label>
						<select class="form-control"  name="source_branch" id="sc">
							{section name=i loop=$branches}
							<option value="{$branches[i].id}">{$branches[i].code}</option>
							{/section}
						</select>
					</div>
					<div class="col-4">
						<label>Destination Branch</label>
						<select class="form-control" name="destination_branch" id="dc">
							{section name=i loop=$branches}
							<option value={$branches[i].id}>{$branches[i].code}</option>
							{/section}
						</select>
					</div>
					<div class="col-4">
						<label> &nbsp;</label>
						<button class="btn btn-primary btn-block " onclick="clone_selected_col()">Clone</button>
					</div>
				</div>
			</div>
		{/if}
		<div class="table-responsive mt-5">
			<table class="table table-hover mb-0 text-md-nowrap table-sm">
				<thead>
					<th colspan="2" class="mt-2"><h6><b>User Privileges</b></h6></th>
					{section name=c loop=$branches}
					<th>
						<a href="javascript:void(checkallcol('user_privilege', '{$branches[c].id}', true))"><i class="fas fa-check-square" title="check all"></i></a>
						<a href="javascript:void(checkallcol('user_privilege', '{$branches[c].id}', false))"><i class="far fa-square" title="uncheckall"></i></a><br>
						<p class="text-md" title="{$branches[c].description}">{$branches[c].code}</p>
					</th>
					{/section}
					<th class="mt-2"><h6><b>Description</b></h6></th>
				</thead>
					{foreach from=$privileges key=grp item=pg}
						<tr>
							<td style="cursor:s-resize;" colspan="{count var=$branches offset=3}" onclick="togglediv('group[{$grp}]','exp[{$grp}]')">
								<p> 
									<img src="/ui/{if $grp eq 'Others'}collapse{else}expand{/if}.gif" id="exp[{$grp}]" /> 
									{if $grp eq 'Others'}Others{else}{$privilege_groupname.$grp}{/if}
								</p>
							</td>
						</tr>
						<tbody id="group[{$grp}]" style="background:#fff;{if $grp ne 'Others'}display:none{/if}">
						{foreach from=$pg item=pv}
						<tr>
							<td><a href="javascript:void(checkallrow('user_privilege', '{$pv.code}', true))"><i class="fas fa-check-square" title="Check All"></i></a><br>
							<a href="javascript:void(checkallrow('user_privilege', '{$pv.code}', false))"><i class="far fa-square" title="uncheckall"></i></a></td>
							<th align=left><label class="form-label tx-14" title="{$pv.description}">{$pv.code}</label></th>
							{section name=c loop=$branches}
							<td align=center>
							{if $pv.hq_only && $branches[c].code ne 'HQ'}
							-
							{else}
							<input type=checkbox name="user_privilege[{$branches[c].id}][{$pv.code}]" {get2ditem array=$user_privilege r=$branches[c].id c=$pv.code retval="checked"} class="inp_priv-{$branches[c].id}" priv_code="{$pv.code}" />
							{/if}
							</td>
							{/section}
							<td class=small>
							{$pv.description}
							</td>
						</tr>
						{/foreach}
						</tbody>
					{/foreach}
			</table>
		</div>
	</div>
</div>