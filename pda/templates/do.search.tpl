{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/11/2011 10:42:12 AM Justin
- Modified the edit image to use gif instead of png extension.

3/20/2014 2:41 PM Justin
- Enhanced to have access to DO Checklist.

5/22/2014 11:46 AM Justin
- Enhanced to remove the function that always change DO No into numberic.

8/3/2017 2:03 PM Justin
- Enhanced to disable user for editting Transfer DO that contains multiple delivery branch.

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['do_no'].value=='')  return false;
	
	return true;
}
{/literal}
</script>

<h1>
Open DO {if $is_checklist}Checklist{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=do">DO</a>
<div style="margin-bottom:10px;"></div>
<div class="stdframe" style="background:#fff">

<form name="f_a" method="post" onSubmit="return check_form();">
<p>
	DO No.
	<input type="text" name="do_no" class="txt-width-50" value="{$smarty.request.do_no}" />
	<input type="submit" class="btn btn-primary" value="Enter" />
	<br />
</p>
	<span style="color:red;">
	    {if $err}
	        <ul>
	        {foreach from=$err item=e}
	            <li>{$e}</li>
	        {/foreach}
	        </ul>
	    {/if}
	</span>

</form>


{if $do_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>DO No.</th>
	        <th>Deliver To</th>
	        <th>Type</th>
	    </tr>
	    {foreach from=$do_list item=do}
	        <tr>
	            <td width="20">
					{if $do.do_type eq 'transfer' && $do.deliver_branch}
						{* do nth because cannot let user to edit multiple branches DO *}
					{else}
						<a href="do.php?a={if $is_checklist}scan_checklist_item{else}change_do{/if}&id={$do.id}&branch_id={$do.branch_id}"><img src="/ui/ed.gif" border="0" title="Open" /></a>
					{/if}
				</td>
	            <td>DO#{$do.id}</td>
	            <td>
				{if $do.do_type eq 'open'}
				    {$do.open_info.name}
				{elseif $do.do_type eq 'credit_sales'}
				    Debtor: {$do.debtor_code}
				{else}
					{if $do.deliver_branch}
						<font color="red">DO consist of Multiple branches, <br />disabled for editing</font>
					{else}
						{$do.do_branch_code}
					{/if}
				{/if}
				</td>
	            <td>
                    {if $do.do_type eq 'open'}Cash Sales
					{elseif $do.do_type eq 'credit_sales'}Credit Sales
					{else}Transfer {/if}
				</td>
	        </tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['do_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
