{include file=header.tpl}{if $type eq 'save'}<h1>PO Saved as #{$id}</h1><p>You can review the PO and made changes to it from the Purchase Order page.<br><span class="small">Note that your PO is not yet submitted. To send out the PO, please click the "Submit" button in the PO screen.</span></p>{elseif $type eq 'delete'}<h1>PO Deleted</h1>{/if}<ul class="card"><li style="height:30px;width:200px;border:1px solid #ccc;" onmouseover="this.style.backgroundColor='#ffc';" onmouseout="this.style.backgroundColor='';"> <img src=ui/po_home.png align=left vspace=4 hspace=4> <a href="/purchase_order.php">Back to PO Index</a>{if $id}<li style="height:30px;width:200px;border:1px solid #ccc;" onmouseover="this.style.backgroundColor='#ffc';" onmouseout="this.style.backgroundColor='';"> <img src=ui/notify_po_new.png align=left vspace=4 hspace=4> <a href="/purchase_order.php?a=open&id=0&copy_id={$id}">Create New PO for same vendor and department</a>{/if}<li style="height:30px;width:200px;border:1px solid #ccc;" onmouseover="this.style.backgroundColor='#ffc';" onmouseout="this.style.backgroundColor='';"> <img src=ui/notify_po_new.png align=left vspace=4 hspace=4> <a href="/purchase_order.php?a=open&id=0">Create New PO</a><li style="height:30px;width:200px;border:1px solid #ccc;" onmouseover="this.style.backgroundColor='#ffc';" onmouseout="this.style.backgroundColor='';"> <img src=ui/home.png align=left vspace=4 hspace=4> <a href="/home.php">Back to Home</a></ul>{include file=footer.tpl}