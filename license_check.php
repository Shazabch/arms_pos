<h1>Loader API Test</h1>
<pre>
<?
	
print "ioncube_file_is_encoded = " . ioncube_file_is_encoded();
print "\nioncube_file_info = ";
print_r(ioncube_file_info());
print "\nioncube_file_properties = ";
print_r(ioncube_file_properties());
print "\n";
print "ioncube_loader_version = ".ioncube_loader_version();
print "\n";
print "ioncube_loader_iversion = ".ioncube_loader_iversion();
print "\nioncube_license_properties = ";
print_r(ioncube_license_properties());
print "\nioncube_licensed_servers = ";
print_r(ioncube_licensed_servers());
print "\nioncube_server_data = ";
print_r(ioncube_server_data());
print "\n";
print "ioncube_check_license_properties = ";
print_r(ioncube_check_license_properties());
print "\n";
print "ioncube_license_matches_server = ";
print_r(ioncube_license_matches_server());
print "\n";
print "ioncube_license_has_expired = ";
print_r(ioncube_license_has_expired());
print "\n";
print "writing test:-" . ioncube_write_file('enc.txt','this is a test');
print_r(ioncube_file_info()+ioncube_license_properties()+ioncube_file_properties());
?>
</pre>
