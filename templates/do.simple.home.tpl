{*
*}

{include file=header.tpl}
{literal}
<style>

</style>
<script type="text/javascript">
{/literal}
var phpself = '{$smarty.server.PHP_SELF}';
var page = '{$smarty.request.do_type|default:"open"}';

{literal}
var DO_PREPARATION_MODULE = {
	initialize : function(){
		var THIS = this;
		THIS.tab_sel(1);
	},

	tab_sel: function(n,s){
		var i;
		for(i=0;i<=2;i++){
			if (i==n) $('tab'+i).className='active';
			else $('tab'+i).className='';
		}
		
		$('do_list').update('<img src=ui/clock.gif align=absmiddle> Loading...');

		var pg = '';
		
		// construct params
		var params = {
		    a: 'ajax_load_do_list',
			t: n,
			do_type: page
		};
		
		if (s!=undefined) params['s'] = s;
		if(n==0) params['search'] = $('search').value;
		else if(n==3) params['search'] = $('search_bid').value;
		
		new Ajax.Updater('do_list', phpself, {
			parameters: params,
			evalScripts: true
		});
	},
}

{/literal}
</script>

{assign var=do_type value=$smarty.request.do_type|default:"open"}

{if $smarty.request.msg}
<script>alert('{$smarty.request.msg|escape:javascript}');</script>
{/if}

<h1>
{$do_type_label}&nbsp;{$PAGE_TITLE}</h1>

<div id="show_last">
{if $smarty.request.t eq 'save'}
<img src="/ui/approved.png" align="absmiddle"> DO saved as ID#{$smarty.request.save_id}<br>
{elseif $smarty.request.t eq 'cancel'}
<img src="/ui/cancel.png" align="absmiddle"> DO ID#{$smarty.request.save_id} was cancelled<br>
{/if}
</div>

<div>
	<img src="ui/new.png" align="absmiddle"><a href="{$smarty.server.PHP_SELF}?a=open&do_type={$do_type}"> Create New {$do_type_label} DO</a>
</div>

<br />

<form onsubmit="DO_PREPARATION_MODULE.tab_sel(0,0);return false;">
	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:DO_PREPARATION_MODULE.tab_sel(1)" id="tab1" class="active">Saved DO</a>
		<!--a href="javascript:DO_PREPARATION_MODULE.tab_sel(2)" id="tab2">Cancelled/Terminated</a-->
		<input type="hidden" id="tab2" />
		<a name="find_do" id="tab0">Find DO <input id="search" name="dono"> <input type="submit" value="Search"></a>
		{if $BRANCH_CODE eq 'HQ' && $config.consignment_modules}
			<a id="tab7">
				Branch
				<select name="branch_id" id="search_bid">
					{foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
					{/foreach}
				</select>
				<input type="button" onclick="DO_PREPARATION_MODULE.tab_sel(3);" value="Go">
			</a>
		{/if}
		<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
</form>
<div id="do_list" style="border:1px solid #000"></div>
{include file=footer.tpl}

<script>
{literal}
DO_PREPARATION_MODULE.initialize();
{/literal}
</script>
