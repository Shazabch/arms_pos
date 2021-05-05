<div id="div_multiple_print" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:550px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_multiple_print_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;"><img src="ui/icons/printer.png" align="absmiddle" /> Multiple Printing</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_multiple_print_content" style="padding:2px;">
	    <form name="f_multiple_print" onSubmit="search_inv_no();return false;">
			Invoice No from <input type="text" name="inv_no_from" size="10" /> to <input type="text" name="inv_no_to" size="10" />
			<input type="submit" value="Refresh" id="btn_search_multiple_print" />
		</form>

		<form name="f_multiple_print_list" target="ifprint" method="post">
		    <input type="hidden" name="a" value="multiple_print" />
			<div style="background:white;height:400px;border:1px solid #cfcfcf;overflow-x:hidden;overflow-y:auto;" id="div_multiple_print_list">
			</div>
			<p align="center"><button id="btn_start_multiple_print" disabled><img src="ui/icons/printer.png" align="absmiddle" /> Print</button></p>
		</form>
	</div>
</div>
