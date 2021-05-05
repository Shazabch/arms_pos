{*
5/10/2017 11:40 AM Andy
- Add "Reorder by Branch" feature.

3/30/2018 4:13PM HockLee
- Export PO to csv format.
- Send email with CSV attachment.

*}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
	send_mail = function(){
		var modal = document.getElementById('myModal');
		var to = document.getElementById('to').value;
		var cc = document.getElementById('cc').value;
		var subject = document.getElementById('subject').value;
		var path = document.getElementById('path').value;
		var message = document.getElementById('message').value;
		message = message.replace(/\n\r?/g, '<br />');

		new Ajax.Request(phpself, {
		  method:'post',
		  parameters: 'a=sr_send_email&to='+to+'&cc='+cc+'&subject='+subject+'&path='+path+'&message='+message,

		  onSuccess: function(transport) {
		    var response = transport.responseText || "The email has been sent successfully.";
		    //alert("Success! \n\n" + response);
		    alert(response);
		    modal.style.display = "none";
		  },
		  onFailure: function() { alert('Error, please check your input again.'); }
		});
	}

	// Get the modal
	var modal = document.getElementById('myModal');

	// Get the button that opens the modal
	var btn = document.getElementById("myBtn");

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName("close")[0];

	// When the user clicks on the button, open the modal
	btn.onclick = function() {
	    modal.style.display = "block";
	}

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
	    modal.style.display = "none";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	    if (event.target == modal) {
	        modal.style.display = "none";
	    }
} 
{/literal}
</script>

<style type="text/css">
{literal}
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 60%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
{/literal}
</style>

{if !$data}
	Error, no PO was generated.<br />
{else}
	{if $data.data}
		<b> Generated PO:</b>
		<ul>
		{foreach from=$data.data key=vid item=po}
			<li><a href='po.php?a=open&branch_id={$po.branch_id}&id={$po.po_id}' target='_blank'>PO#{$po.po_id}</a></li>
		{/foreach}
		</ul>
	{/if}

	{if $data.path}
		<b> Download CSV:</b>
		<ul>		
			<li><a href='{$data.path}' target='_blank' onclick="close_curtain2();">{$data.file_name}</a></li>		
		</ul>
		<b> Send CSV as:</b>
		<ul>	
			<li><a href="javascript:void(0)" id="myBtn">Email</a></li>
		</ul>
	{/if}
	
	{if $data.pa_data}
		<b> <img src="ui/messages.gif" align="absmiddle" /> Purchase Agreement Require Your Action:</b>
		<ul>
		{assign var=pa_no value=0}
		{foreach from=$data.pa_data key=vid item=vendor_pa}
			{foreach from=$vendor_pa.pa_list item=pa}
				{assign var=pa_no value=$pa_no+1}
				<li><a href='po.po_agreement.php?a=create_from_tmp&branch_id={$pa.branch_id}&tmp_pa_id={$pa.id}' target='_blank'>Purchase Agreement #{$pa_no}</a></li>
			{/foreach}
		{/foreach}
		</ul>
	{/if}
{/if}

<div id="myModal" class="modal">
  <!-- Modal content -->
  <div class="modal-content">
   <span style="color:white;background-color:#CE0000;padding:3px;">New email message</span>
   <span class="close">&times;</span>
    <table>
		<form name="f_mail" id="mail" method="post" action="{$smarty.server.PHP_SELF}">
			<tr>
				<td>To: </td>
				<td>
					<input type="text" id="to" name="to" value="" size="40" />
				</td>
			</tr>
			<tr>
				<td>CC: </td>
				<td>
					<input type="text" id="cc" name="cc" value="" size="40" />
				</td>
			</tr>
			<tr>
				<td>Subject: </td>
				<td>
					<input type="text" id="subject" name="subject" value="Stock Reorder {$data.date}" size="40" />
				</td>
			</tr>
			<tr>
				<td>Attachment: </td>
				<td>{if $data.path}<a href="{$data.path}"><img src="/ui/icons/page_excel.png"> {$data.file_name}{/if}</a>
					<input type="hidden" id="path" name="path" value="{if $data.path}{$data.path}{/if}" />
				</td>
			</tr>
			<tr>
				<td>Message: </td>
				<td>
					<textarea rows="5" cols="80" id="message" name="message"></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type="button" value="Send" onclick="send_mail()" />
				</td>
			</tr>
		</form>
	</table>
  </div>
</div>