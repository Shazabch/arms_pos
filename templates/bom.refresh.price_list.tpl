<ul id=tab>
{foreach item="sp" from=$price}
<li id="selected_type">{$sp.branch}-{$sp.latest_selling|number_format:2}</li>
{/foreach}
</ul>
