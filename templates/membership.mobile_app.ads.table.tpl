
<div id="div_banner_prev">
	{foreach from=$banner_list key=banner_name item=banner}
		<div class="stdframe div_banner_prev">
			<a href="?a=open&banner_name={$banner_name}" target="_blank">
			<h4>{$banner.banner_description}</h4>
			<img src="{$banner.wireframe_url}" style="max-height:500px;" />
			</a>
		</div>
	{/foreach}
<div>

<br style=" clear:both;"/>