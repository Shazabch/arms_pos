{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MARKETING_PLAN_PROMOTION_ACTIVITY_MAIN_MODULE = {
	initialize: function(){
		$('#div_data_accordion').accordion({ header: "h3" });
	}
};

{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{if !$form}
	** You don't have any assigned activity yet.
{else}
    <div id="div_data_accordion">
		{foreach from=$form.data item=data}
		    <div>
				<h3><a>{$data.mar_p_title} > {$data.promo_title} > {$data.act_title}</a></h3>
				<div>
					<table width="100%" class="report_table">
					    <thead>
					        <tr>
					            <th>#</th>
					        </tr>
					    </thead>
					</table>
				</div>
			</div>
		{/foreach}
	</div>
{/if}

{include file='footer.tpl'}

<script>
{literal}
	$(function(){
        MARKETING_PLAN_PROMOTION_ACTIVITY_MAIN_MODULE.initialize(); // initial main module
	});
{/literal}
</script>
