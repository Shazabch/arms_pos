{*
1/30/2018 1:24 PM Justin
- Enhanced to enlarge the misc popup window.
*}

<div id=inv_popup class="curtain_popup" style="width:800px;height:480px;display:none;">
<div style="text-align:right"><img src=/ui/closewin.png onclick="curtain_clicked()"></div>
<div id=inv_list style="height:460px;overflow:auto;">
</div>
</div>

<div id=inv_popup2 class="curtain_popup" style="width:800px;height:480px;display:none;">
<div style="text-align:right"><img src=/ui/closewin.png onclick="if($('inv_popup').style.display=='') hidediv('inv_popup2'); else curtain_clicked();"></div>
<div id=inv_list2 style="height:460px;overflow:auto;">
</div>
</div>

<div id="misc_popup" class="curtain_popup" style="width:850px;height:350px;display:none;">
<div style="text-align:right"><img src=/ui/closewin.png onclick="if($('inv_popup2').style.display=='') hidediv('misc_popup'); else curtain_clicked();"></div>
<div id=misc_list style="height:300px;overflow:auto;">
</div>
</div>
