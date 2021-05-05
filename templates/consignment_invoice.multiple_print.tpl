{*
5/8/2015 5:37 PM Andy
- Remove invoice type filter on multiple print.
- Change print multiple to open a new screen for preview.
- Enhanced to allow select printing type for multiple print.
*}

<div id="div_multiple_print" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:550px;display:none;border:2px solid #CE0000;">
	<div id="div_multiple_print_header"><span style="float:left;"><img src="ui/icons/printer.png" align="absmiddle" /> Multiple Printing</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_multiple_print_content">
	    <form name="f_multiple_print" onSubmit="search_inv_no();return false;">
			Invoice No from <input type="text" name="inv_no_from" size="10" /> to <input type="text" name="inv_no_to" size="10" />
			<input type="submit" value="Refresh" id="btn_search_multiple_print" />
			{*<input type="checkbox" name="sales" align="absmiddle" value="1" checked /> Sales
			<input type="checkbox" name="lost" align="absmiddle" value="1" checked /> Lost
			<input type="checkbox" name="over" align="absmiddle" value="1" checked /> Over*}
		</form>
		
		<form name="f_multiple_print_list" target="_blank" method="post">
		    <input type="hidden" name="a" value="multiple_print" />
		    
			<div style="background:white;height:400px;border:1px solid #cfcfcf;overflow-x:hidden;overflow-y:auto;" id="div_multiple_print_list">
			</div>
			<div>
				<input type="checkbox" name="print_ci" value="1" checked /> Print Invoice &nbsp;&nbsp;&nbsp;
				<input type="checkbox" name="print_summary" value="1" /> Print Summary &nbsp;&nbsp;&nbsp;
			</div>
			<p align="center"><button id="btn_start_multiple_print" disabled><img src="ui/icons/printer.png" align="absmiddle" /> Print</button></p>
		</form>
	</div>
</div>
