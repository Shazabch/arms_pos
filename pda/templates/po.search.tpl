{*
23/9/2019 11:38 AM William 
- Added new module Purchase Order.

 11/04/2020 10:28 AM Sheila
- Fixed title, table and form css

11/05/2020 11:57 AM Sheila
- Fixed breadcrumbs

*}
{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['po_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<h1>
Open Purchase Order
&nbsp;
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=po">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>


<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p align="center">
	<span><h2>Open PO</h2></span>
	<p>PO No.
	<input class="txt-width-50" type="text" name="po_no" value="{$smarty.request.po_no}" />
	<input type="submit" class="btn btn-primary" value="Enter" />
	</p>
	<br />
	<span style="color:red;">
	    {if $err}
	        <ul>
	        {foreach from=$err item=e}
	            <li>{$e}</li>
	        {/foreach}
	        </ul>
	    {/if}
	</span>
</p>
</form>


{if $po_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>PO No.</th>
	        <th>Delivery Branch</th>
	    </tr>
	    {foreach from=$po_list item=po}
			<tr>
	            <td width="20">
					{if $po.deliver_to}
						<a href="po.php?a=change_po&id={$po.id}&branch_id={$po.branch_id}"><img src="/ui/ed.gif" border="0" title="Open" /></a>
					{else}
						<a href="po.php?a=change_po&id={$po.id}&branch_id={$po.branch_id}"><img src="/ui/ed.gif" border="0" title="Open" /></a>
					{/if}
				</td>
				<td>PO#{$po.id}</td>
	            <td>
					{if $po.deliver_to}
						{$po.deliver_to}
					{else}
						{$po.branch_code}
					{/if}
				</td>
			</tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['po_no'].select();
{/literal}
</script>
{include file='footer.tpl'}