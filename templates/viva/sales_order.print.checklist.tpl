{*
8/19/2013 9:31 AM Fithri
- assign document no as filename when print
*}

{include file='header.print.tpl'}

<style>

{literal}
.grey_bg{
	background-color: #ccc;
}

.black_bg{
	background-color: black;
	color: #fff;
}
{/literal}
</style>

<script type="text/javascript">
var doc_no = '{$form.order_no}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">

<div class="printarea">
	<table width="100%">
		<tr>
			<td width="33%"><img src="{get_logo_url}" height="60" hspace="5" vspace="5" /></td>
			<td nowrap><h1>CUSTOMER CHECKLIST</h1></td>
			<td align="right"><h2>Sales Order No<br />{$form.order_no}</h2></td>
		</tr>
	</table>
	
	<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">
		<tr>
			<th width="60" class="grey_bg">Plate No.</th>
			<td width="80">&nbsp;</td>
			
			<th width="60" class="grey_bg">Make</th>
			<td width="80">&nbsp;</td>
			
			<th width="100" class="grey_bg">Date</th>
			<td nowrap align="center"><div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;M &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div></td>
			
			<th width="60" class="grey_bg">Time</th>
			<td width="60">&nbsp;</td>
		</tr>
		
		<tr>
			<th class="grey_bg">Mileage</th>
			<td>&nbsp;</td>
			
			<th class="grey_bg">Model</th>
			<td>&nbsp;</td>
			
			<th class="grey_bg">Car Size</th>
			<td colspan="3" nowrap align="center">
				<div>
					[&nbsp;&nbsp;&nbsp;] Small
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					[&nbsp;&nbsp;&nbsp;] Large
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					[&nbsp;&nbsp;&nbsp;] LL/X
				</div>
			</td>
		</tr>
		
		<tr>
			<th class="grey_bg">Name</th>
			<td colspan="3">
				<div style="float:right">
					[&nbsp;&nbsp;&nbsp;] Mr
					[&nbsp;&nbsp;&nbsp;] Ms
				</div>
			</td>
			
			<th class="grey_bg" nowrap>Contact No.</th>
			<td colspan="3">&nbsp;</td>
		</tr>
		
		<tr>
			<th class="grey_bg">Company</th>
			<td colspan="3">&nbsp;</td>
			
			<th class="grey_bg" nowrap>Chasis / Vin No.</th>
			<td colspan="3">&nbsp;</td>
		</tr>
		
		<tr>
			<th class="grey_bg">Address</th>
			<td colspan="3">&nbsp;</td>
			
			<th class="grey_bg">Email</th>
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>
	
	<table width="100%" border=0 cellspacing=0 cellpadding="1" class="tb">
		<tr>
			<th class="black_bg" colspan="4">Exterior Inspection</th>
			
		</tr>
		<tr>
			<td align="center" rowspan="16">
				<img src="templates/viva/car.png" width="80%" />
			</td>
			<td width="5">&nbsp;</td>
			<td width="10%" align="center">Fe (&micro;m)</td>
			<td width="10%" align="center">NFe (&micro;m)</td>
		</tr>

		{section loop=15 name=s}
			<tr>
				<td align="right">{$smarty.section.s.iteration}.</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		{/section}		
	</table>
	
	<table width="100%" border=0 cellspacing=0 cellpadding=4 class="tb">
		<tr>
			<th class="black_bg" colspan="2">Fuel Level Reminders</th>
					
			<th class="black_bg" colspan="2">Mobile Unit</th>
		</tr>
		<tr>
			<td nowrap valign="top" rowspan="3">
				[&nbsp;&nbsp;&nbsp;] E <br />
				[&nbsp;&nbsp;&nbsp;] 25% <br />
				[&nbsp;&nbsp;&nbsp;] 50% <br />
				[&nbsp;&nbsp;&nbsp;] 75% <br />
				[&nbsp;&nbsp;&nbsp;] F <br />
			</td>
			
			<td valign="top" class="small" rowspan="3">
				1. Any damage on the interior & exterior<br />
				2. Remind customer to remove valuables<br />
				3. Is there any hidden kill switch or lock<br />
				4. Exterior paint condition<br />
				5. Reconfirm price & payment
			</td>
			
			<td colspan="2"> [&nbsp;&nbsp;&nbsp;] Show Room</td>
		</tr>
		
		<tr>
			<td>Time Start</td>
			<td width="80">&nbsp;</td>
		</tr>
		<tr>
			<td>Time Finish</td>
			<td>&nbsp;</td>
		</tr>
	</table>
				
	<table width="100%" border=0 cellspacing=0 cellpadding=4 class="tb small">
		<tr>
			<th class="grey_bg" width="80">Items in Car</th>
			<td>
				[&nbsp;&nbsp;&nbsp;] Smart Tag/Touch n' Go
				&nbsp;&nbsp;
				[&nbsp;&nbsp;&nbsp;] Laptop
				&nbsp;&nbsp;
				[&nbsp;&nbsp;&nbsp;] Handsfree Set
				&nbsp;&nbsp;
				[&nbsp;&nbsp;&nbsp;] Steering Lock
				&nbsp;&nbsp;
				[&nbsp;&nbsp;&nbsp;] Sunglasses
				&nbsp;&nbsp;
				[&nbsp;&nbsp;&nbsp;] Others:
			</td>
		</tr>
	</table>
	
	
	<table width="100%" border=0 cellspacing=0 cellpadding=4 class="tb small">
		<tr>
			<th class="black_bg">Treatment</th>
			<th class="black_bg" width="60">Price</th>
			<th class="black_bg">Treatment</th>
			<th class="black_bg" width="60">Price</th>
			<th class="black_bg">Treatment</th>
			<th class="black_bg" width="60">Price</th>
		</tr>
		
		<tr>
			<td nowrap>[&nbsp;&nbsp;&nbsp;] Premium Car Wash - [&nbsp;&nbsp;&nbsp;] Dealer</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Hi-MOHS Coat</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Wheel Coating</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>[&nbsp;&nbsp;&nbsp;] Engine Wash</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Maintenance 1</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Tyre Coat</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>[&nbsp;&nbsp;&nbsp;] Mild Polish - Fluorine</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Maintenance 2</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] 180 Hyper View</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>[&nbsp;&nbsp;&nbsp;] Deep Polish - Fluorine</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Resin Coat</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Leather and Cushion</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>[&nbsp;&nbsp;&nbsp;] Sparkling Coat</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Hyper View (Front)</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Nano Titanium</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td>[&nbsp;&nbsp;&nbsp;] Real Glass Coat</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Hyper View (All)</td>
			<td>&nbsp;</td>
			
			<td>[&nbsp;&nbsp;&nbsp;] Others</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td nowrap>[&nbsp;&nbsp;&nbsp;] Quartz 7</td>
			<td>&nbsp;</td>
			
			<td nowrap>[&nbsp;&nbsp;&nbsp;] Headlight Coat</td>
			<td>&nbsp;</td>
			
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	
	<table width="100%" border=0 cellspacing=0 cellpadding=4 class="tb">
		<tr class="small">
			<td class="grey_bg" width="25%" align="center">Remarks</td>
			<td class="grey_bg" width="25%" align="center" nowrap>[&nbsp;&nbsp;&nbsp;] Cash [&nbsp;&nbsp;&nbsp;] Card [&nbsp;&nbsp;&nbsp;] Discount</td>
			<td class="grey_bg" width="25%" align="center" nowrap>Person in Charge (Technician)</td>
			<td class="grey_bg" width="25%" align="center">Customer</td>
		</tr>
		<tr>
			<td>&nbsp;<br />&nbsp;<br />&nbsp;<br /></td>
			<td valign="top">Total</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	
	<br />
	<table width="100%" border=0 cellspacing=0 cellpadding=4 class="tb small">
		<tr>
			<td class="black_bg">Terms and Conditions</td>
		</tr>
		<tr>
			<td>
				1. The company's liability for the customer's vehicle is for the period commencing when the key to the vehicle is delivered to the company and terminating at the time when the key to the vehicle is handed back to the customer who present this checklist or invoice or any proof of ownership of the vehicle which may deem acceptable by the company.<br />
				2. The company will be responsible for any damage to the paintwork of the customer 's vehicle in the form of repairing at the company only when it is proved to be caused by negligence, willful act or default or breach of statutory duty of the company's staff.<br />
				3. The company shall not be responsible to any theft, loss, damage to any personal property or loose items left within the vehicle whilst in the company's premises.<br />
				4. Full Payment is required for commencing of service.
			</td>
		</tr>
	</table>
</div>

</body>