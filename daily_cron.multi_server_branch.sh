if [ -f /opt/lampp/bin/php ]
then
	php=/opt/lampp/bin/php
	path=/home/ARMS/www
else
	php=php
	path=/var/www
	
	if [[ `pwd` == *var/www* ]]
	then
		path=httpdocs
	fi
fi

mkdir -m777 attch;
mkdir -m777 sku_import;
mkdir -m777 attachments;

# Multi Server Mode Branch
$php cron.run_maintenance.php > cron.run_maintenance.log; 
$php admin.config_manager.php generate_config_text > generate_config_text.log;
#$php fix_collate.php -is_fix > fix_collate.log
$php cron.pos_finalized_pregen.php > cron.pos_finalized_pregen.log;
$php cron.custom_acc_export.php > cron.custom_acc_export.log;
$php report.stock_reorder.php generate_vendor_sku_list -all > generate_vendor_sku_list.log;
$php temp_script.php patch_pp_group_type -branch=all -recent_day=7 -is_run > patch_pp_group_type.log;
#$php temp_script.php update_all_po_amt_old > update_all_po_amt_old.log;