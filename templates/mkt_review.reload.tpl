{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=y value=0}
{assign var=temp_id value=$subcat.$s.id}
<input type=hidden id="dept_default_val_{$temp_id}" name="dept_default_val_{$temp_id}" value="{$dept_default.$temp_id.total_contribute}">
{/section}
