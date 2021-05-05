<?php
/*
12/5/2016 9:19 AM Andy
- Commit and reupload.
*/
$f=$_REQUEST['f'];
//Check file (don't skip it!)
if(strpos($f,"/tmp")===false || !file_exists($f))
{
    die("<script>alert('Incorrect file name $f');</script>");
}
//Handle special IE request if needed
if($HTTP_SERVER_VARS['HTTP_USER_AGENT']=='contype')
{
    Header('Content-Type: application/msexcel');
    exit;
}
//Output PDF
Header('Content-Type: application/msexcel');
Header('Content-Length: '.filesize($f));
Header('Content-Disposition: attachment;filename=arms'.time().'.xls');
readfile($f);
//Remove file
unlink($f);
exit;
?>
