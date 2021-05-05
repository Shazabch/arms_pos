<?php
declare(ticks = 1);

pcntl_signal(SIGINT , "sig_handler");
while(1) { sleep(10); }
function sig_handler($signo)
{
print "oh no,,,\n";exit;
}

?>
