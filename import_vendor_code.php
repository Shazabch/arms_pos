<?php
include("include/common.php");

$q1=$con->sql_query("select acct_code from vendor where description='AINTAI CORPORATION SDN BHD'");
$r1 = $con->sql_fetchrow($q1);
$temp1 = unserialize($r1[acct_code]);
echo"<pre>";print_r($temp1);echo"</pre>";

$dir = "vendor_convertion";

if (!is_dir($dir)) {
	mkdir($dir, 0777);  
}
else{
	chmod($dir, 0777); 
}

/*
$dir2 = "templates/cheque_formats";

if (!is_dir($dir2)) {
	mkdir($dir2, 0777);  
}
else{
	chmod($dir2, 0777); 
}
*/

$b['1']='HQ';
$b['2']='BALING';
$b['3']='DUNGUN';
$b['4']='GURUN';
$b['5']='KANGAR';
$b['6']='JITRA';
$b['7']='TMERAH';


for($i=1;$i<8;$i++){
	$branch=$b[$i];

	$incomplete_file="$dir/"."$branch"."_incomplete";
	$file_name_1="$incomplete_file.txt";
	unlink($file_name_1);
		
	echo "$branch<br>";
	echo "######################################################<br>";
	$file_name="$dir/".$branch.'.csv';
	$handle = fopen($file_name, "r");
	$count=0;
	while ($line = fgetcsv($handle,4096,",")){
		$vendor= trim($line[0]);
		$acct_code = trim($line[1]);
		$insert=1;
		
		if($vendor!='' && $acct_code!='' && ($i==5 || $i==6)){
		    $q1=$con->sql_query("select description, acct_code from vendor where description=".ms($vendor));
		    $r1 = $con->sql_fetchrow($q1);
		    if($r1){
			    $temp=array(); 
				$temp = unserialize($r1['acct_code']);
				if($temp){
					foreach($temp[$i] as $k=>$v){
						if(trim($v)==$acct_code){
							//echo "$k==$v<br>";
							$insert=0;
						}
					}				
				}
				else{
					$insert=1;
				}
				if($insert==1){
					$temp[$i][]=$acct_code;
					$new_acct_code = sz($temp);
					
					$con->sql_query("update vendor set acct_code=$new_acct_code where description=".ms($vendor));			
				}			
			}
			else{
				$handle_1 = fopen($file_name_1,"a+");
				
				if (is_writable($file_name_1)) {
					echo "$acct_code,$vendor<br>";
					$content="$acct_code,$vendor\n";	
				    if (fwrite($handle_1, $content) === FALSE) {
				        echo "Cannot write to file ($file_name_1)<br>";
				        exit;
				    }
				}
				else{
				    echo "The file $file_name_1 is not writable<br>";
				}		
			}
		}
	}
}

$q11=$con->sql_query("select acct_code from vendor where description='AINTAI CORPORATION SDN BHD'");
$r11 = $con->sql_fetchrow($q11);
$temp11 = unserialize($r11[acct_code]);
echo"<pre>";print_r($temp11);echo"</pre>";
?>
