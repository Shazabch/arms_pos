{*
11/04/2020 10:12 AM Sheila
- Fixed title, table and form css

11/05/2020 11:51 AM Sheila
- Fixed breadcrumbs

*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){

	if(document.f_a['owner_id'].value==''){
		alert('Please select Vendor.');
		return false;
	}
	
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

{/literal}
</script>

<h1>
NEW PROMOTION
&nbsp;
</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id=promotion">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>

{if $form.id}{include file='promotion.top_include.tpl'}<br /><br />{/if}

<div class="stdframe" style="background:#fff">

<h2>Setting - {if $form.promotion_no}(Promotion/{$form.promotion_no}){else}{if $form.id}(Promotion#{$form.id}){else}New Promotion{/if}{/if}
</h2>
{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$promo_id}" />
<table cellspacing="0" cellpadding="4" border="0">
    <tr>
	    <th align="left">Title</th>
	    <td><input type="text" size="30" name="title" value="{$form.title}" /></td>
	</tr>
    <tr>
	    <th align="left">Owner</th>
	    <td>
	        <select name="owner_id" >
	            <option value="">-- Please Select --</option>
	            {foreach from=$owners item=r}
	                <option value="{$r.id}" {if $form.owner_id eq $r.id}selected {/if}>{$r.u}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
</table>
<p align="center">
	<input type="button" name="submit_btn" value="Save" onClick="submit_form();" />
</p>
</form>
</div>
{include file='footer.tpl'}
