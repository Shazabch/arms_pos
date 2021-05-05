path_log=~/logs/

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
	
	#if [[ `pwd` == *var/www* ]]
	#then
	#		path_log=~/logs/
	#else
	#		path_log=/var/log/arms/
	#fi
fi

#echo $path_log

if [ ! -d $path_log ]; then 
	mkdir -m777 $path_log;
	
	#even after create still tak ada - email nava
	if [ ! -d $path_log ]; then 
		echo -e "Check log path on $(hostname)" | mail -s "[Backup Analyzer] $(hostname)" nava@arms.my
	fi
else
	chmod 777 $path_log;
fi

mkdir -m777 attch;
mkdir -m777 sku_import;
mkdir -m777 attachments;
chmod 777 api.ewallet/. -fR

# For QR Code
mkdir -m777 include/phpqrcode/cache
chmod 777 include/phpqrcode/cache -fR

path_log=""

$php cron.run_maintenance.php > ${path_log}cron.run_maintenance.log; 

$php admin.config_manager.php generate_config_text  > ${path_log}generate_config_text.log;

$php trigger_maintenance.php > ${path_log}trigger_maintenance.log

$php temp_script.php fix_pos_cn_number -branch=all -date=yesterday -force_update -is_run > ${path_log}fix_pos_cn_number.log

$php cron.pos_finalized_pregen.php all > ${path_log}cron.pos_finalized_pregen.log;

$php cron.check_counter.php -branch=all > ${path_log}cron.check_counter.log

$php cron.acc_export.php > ${path_log}cron.acc_export.log;

$php cron.custom_acc_export.php > ${path_log}cron.custom_acc_export.log;

$php temp_script.php patch_pp_group_type -branch=all -recent_day=7 -is_run > ${path_log}patch_pp_group_type.log;

$php cron.download_paydibs_integrator_list.php > ${path_log}cron.download_paydibs_integrator_list.log;

$php fix_collate.php -is_fix > ${path_log}fix_collate.log

$php cron.cycle_count.php -branch=all -send > ${path_log}cron.cycle_count.log

$php cron.category_sales_trend.php -branch=all > ${path_log}cron.category_sales_trend.log

$php temp_script.php generate_membership_guid > ${path_log}generate_membership_guid.log;

$php temp_script.php update_pos_membership_guid -branch=all > ${path_log}update_pos_membership_guid.log;

$php temp_script.php generate_sku_items_finalised_cache -branch=all > ${path_log}generate_sku_items_finalised_cache.log;

$php report.stock_reorder.php generate_vendor_sku_list -all  > ${path_log}generate_vendor_sku_list.log;

#$php temp_script.php fix_promotion_photo_path -is_run > ${path_log}fix_promotion_photo_path.log

#$php temp_script.php fix_196_member_points > ${path_log}fix_196_member_points.log;

#$php temp_script.php recalc_sa_sales_cache > ${path_log}recalc_sa_sales_cache.log

#$php temp_script.php fix_price_history_fpi_id > ${path_log}fix_price_history_fpi_id.log

#$php temp_script.php june_gst_to_zero > ${path_log}june_gst_to_zero.log

#$php temp_script.php update_all_po_amt_old > ${path_log}update_all_po_amt_old.log;

#$php cron.check_pos.php -is_run > cron.check_pos.log

#$php temp_script.php fix_category_sales_cache -branch=all -is_run > fix_category_sales_cache.log;

#$php temp_script.php fix_gra_items_amount
#echo "cd $path; $php report.stock_reorder.php generate_vendor_sku_list -all" | at 05:00