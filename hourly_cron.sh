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

#echo "ls -l | mail andyloh@wsatp.com" | at now+30 minute

echo $path