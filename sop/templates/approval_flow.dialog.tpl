<form name="f_approval_flow" onSubmit="return false;">
	<input type="hidden" name="a" value="save_approval_flow" />
	<input type="hidden" name="approval_flow_id" value="{$form.id}" />
	
	<table width="100%" border="1">
		<tr>
		    <td><b>Branch</b></td>
		    <td>
		        <select name="branch_id" class="required" title="Branch ID">
				    <option value="">-- Please Select --</option>
					{foreach from=$branches key=bid item=r}
						<option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$r.code}</option>
					{/foreach}
				</select>
				<img align="absbottom" title="Required Field" src="/ui/rq.gif">
		    </td>
		</tr>
		<tr>
		    <td><b>Flow Type</b></td>
		    <td>
		        <select name="flow_type" class="required" title="Flow Type">
		            <option value="">-- Please Select --</option>
					{foreach from=$flow_type key=t item=r}
					    <option value="{$t}" {if $form.type eq $t}selected {/if}>{$r.label}</option>
					{/foreach}
				</select>
				<img align="absbottom" title="Required Field" src="/ui/rq.gif">
		    </td>
		</tr>
		<tr>
		    <td><b>Department</b></td>
		    <td>
		        <select name="dept_id" disabled>
				    <option value="">-- Please Select --</option>
				    {foreach from=$depts item=r}
				        <option value="{$r.id}" {if $form.sku_category_id eq $r.id}selected {/if}>{$r.description}</option>
				    {/foreach}
				</select>
		    </td>
		</tr>
		<tr>
		    <td><b>SKU Type</b></td>
		    <td>
		        <select name="sku_type" disabled>
				    <option value="">-- Please Select --</option>
				    {foreach from=$sku_types item=r}
				        <option value="{$r.code}" {if $form.sku_type eq $r.code}selected {/if}>{$r.code}</option>
				    {/foreach}
				</select>
		    </td>
		</tr>
		<tr>
		    <td><b>Approval Order</b></td>
		    <td>
		        <select name="approval_order" disabled class="required" title="Approval Order">
		            <option value="">-- Please Select --</option>
		            {foreach from=$approval_order item=r}
				        <option value="{$r.id}" {if $form.aorder eq $r.id}selected {/if}>{$r.description}</option>
				    {/foreach}
		        </select>
		        <img align="absbottom" title="Required Field" src="/ui/rq.gif">
		    </td>
		</tr>
	</table>
	
	<table width="100%">
	    <tr>
	        <th>Approvals</th>
	        <th width="80">&nbsp;</th>
	        <th>User Pool</th>
	        <th width="80">&nbsp;</th>
	        <th>Notify Users</th>
	    </tr>
	    <tr>
	        <td>
	            <select name="approvals[]" multiple size="10" style="width:100%;" class="sel_approvals">
	                {foreach from=$form.approvals item=uid}
	                    <option value="{$uid}">{$users.$uid.u}</option>
	                {/foreach}
				</select>
	        </td>
	        <td class="c">
				<button id="btn_approvals_up" class="btn1">Up</button>
				<br /><br />
				<button id="btn_approvals_down" class="btn1">Down</button>
				<br /><br />
				<button id="btn_approvals_add" class="btn1">&lt;&lt;</button>
				<br /><br />
				<button id="btn_approvals_remove" class="btn1">&gt;&gt;</button>
			</td>
	        <td>
	            <select name="user_id_list[]" multiple size="10" style="width:100%;">
	                {foreach from=$form.available_users key=uid item=r}
	                    {if $r.active}
	                    	<option value="{$uid}">{$r.u}</option>
	                    {/if}
	                {/foreach}
				</select>
	        </td>
	        <td>
                <button id="btn_notify_users_add" class="btn1">&gt;&gt;</button>
				<br /><br />
				<button id="btn_notify_users_remove" class="btn1">&lt;&lt;</button>
			</td>
	        <td>
	            <select name="notify_users[]" multiple size="10" style="width:100%;">
	                {foreach from=$form.notify_users item=uid}
	                    <option value="{$uid}">{$users.$uid.u}</option>
	                {/foreach}
				</select>
	        </td>
	    </tr>
	</table>
</form>
