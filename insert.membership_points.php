<?php
include("include/common.php");

if (($handle = fopen("akd_pts.csv", "r")) !== FALSE){
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
        $num = count($data);
		
        for ($c=0; $c < $num; $c++){
        	if($c==$num-1 && !$data[$c]) continue;
			$form[] = trim($data[$c]);
        }

        if(trim($form[0]) && trim($form[5]) != 0){
			$con->sql_query("insert into `membership_points` (branch_id, card_no, nric, date, type, remark, points) values 
				(1, \"".join("\",\"", $form)."\")");
			$pass++;
		}else{
			$fail++;
		}
		unset($form);
		$row++;
    }
    print "Total Rows: ".$row."<br />";
    print "Total Success Inserted Rows: ".$pass."<br />";
    print "Total Failed Inserted Rows: ".$fail;
    fclose($handle);
}else{
	print "No CSV file to import for Membership Points";
	exit;
}
?>
