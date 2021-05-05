<?php
/*
12/14/2016 10:52 AM Andy
- Enhanced to compatible to php7.
*/
?>
<html>
<style>
body {font:20px Arial; margin:0; padding:0; background:#000; color:#0f0; text-align:center; }
</style>

<body onload="document.forms[0].scode.focus()">
<form action="check.php" target=content onsubmit="this.code.value=this.scode.value;this.scode.value='';this.scode.focus();">
<input type=hidden name=code >
&nbsp;&nbsp;&nbsp; <b>Scan Barcode</b> <input style="font-size:20px" name=scode size=30>
</form>
</body>

</html>


<?php
exit;
?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=iso">

<link rel="stylesheet" href="kiosk.css" type="text/css">

<script language=VBScript>
    Dim Scan2DMode, ScannerOn

    Scan2DMode = false
    ScannerOn = false

    '********************************************
    ' Sets the view frame to the incoming URL.
    ' Similar to window.navigate
    '********************************************
    sub loadURL(URL)
        parent.content.document.getElementById("popup").style.display = ""
		parent.content.location = URL
    end sub

    '********************************************
    ' Gets called when a barcode is decoded
    '********************************************
    Sub ScanControl1_ScanComplete(BarCode, Source, Status, LabelType, DataLength)
        loadURL("check.php?code=" & BarCode)
    End Sub

    '********************************************
    ' Sets the laser mode to either 1d or 2d
    '********************************************
    sub SetLaserMode(mode)
        Dim raster, trigger, timeout, i

        if (ScanControl1.GetLaserParams(raster, trigger, timeout)) then
            raster = mode
	        trigger = 1
            timeout = 900
	        ScanControl1.EnableScanning(0)
            if(raster = 3) then
                ScanControl1.PDF417 = 0
                ScanControl1.MicroPDF = 0
                ScanControl1.MaxiCode = 0
                ScanControl1.DataMatrix = 0
                Scan2DMode = false
            else
                ScanControl1.PDF417 = 1
                ScanControl1.MicroPDF = 1
                ScanControl1.MaxiCode = 1
                ScanControl1.DataMatrix = 1
                Scan2DMode = false
                Scan2DMode = true
            End if
	        i = ScanControl1.SetLaserParams(raster, trigger, timeout)
	        ScanControl1.EnableScanning(1)
        end if
    end sub

    '********************************************
    ' Starts the scanner in cyclone mode
    '********************************************
    sub fSetTriggerMode()
        Dim raster, trigger, timeout, i, min, max, redundancy

        ' Make sure things are in the right state

        ScanControl1.EnableScanning(0)
        ScanControl1.CloseScanner()

        ScanControl1.OpenScanner()
        if (ScanControl1.GetLaserParams(raster, trigger, timeout)) then
            raster = 3
	        trigger = 1
            timeout = 900
	        ScanControl1.EnableScanning(0)
            ScanControl1.PDF417 = 0
            ScanControl1.MicroPDF = 0
            ScanControl1.MaxiCode = 0
            ScanControl1.DataMatrix = 0
            Scan2DMode = false
	        i = ScanControl1.SetLaserParams(raster, trigger, timeout)
	        ' Default Min and Max are set to odd values for I 2 of 5
	        ' so change them to something more reasonable.
			ScanControl1.GetI2of5Params max, min, redundancy
			min = 0
			max = 0
			ScanControl1.SetI2of5Params max, min, redundancy
	        ScanControl1.EnableScanning(1)
            ScannerOn = true
        end if
    end sub

    '********************************************
    ' Disables and turns scanner off
    '********************************************
    sub TurnScannerOff()
        ScanControl1.EnableScanning(0)
        if (ScanControl1.GetLaserParams(raster, trigger, timeout)) then
            raster = 1
	        trigger = 0
	        i = ScanControl1.SetLaserParams(raster, trigger, timeout)
        end if
        ScanControl1.CloseScanner()
        ScannerOn = false
    end sub

    '********************************************
    ' Returns scanner state
    ' true if on and false if off
    '********************************************
    function isScannerOn()
        if(ScannerOn) then
            isScannerOn = true
        else
            isScannerOn = false
        end if
    end function

</script>


<body OnLoad="fSetTriggerMode()">

<table id=nav width=100% border=0 cellpadding=0 cellspacing=0>
<tr height=30>
<td class=btn width=25% onclick='parent.location="index.php"'>Home</td>
<td>&nbsp;&nbsp;</td>
<td width=75%><marquee scrollamount=5 scrolldelay=200><b>Scan product's barcode at the sensor below</b></marquee></td>
</table>

<OBJECT id=ScanControl1 style="LEFT: 0px; WIDTH: 0px; TOP: 0px; HEIGHT: 0px" classid=clsid:5FFAA94A-D9E2-405d-9644-EE2196713A3C VIEWASTEXT>

</body>
</html>
