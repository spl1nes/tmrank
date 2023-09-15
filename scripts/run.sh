#!/bin/bash

sudo -u www-data git pull /var/www/html/tmrank

echo "#################################################"
echo "# Remove"
echo "#################################################"
php /var/www/html/tmrank/scripts/remove.php

echo "#################################################"
echo "# Maps"
echo "#################################################"
php /var/www/html/tmrank/scripts/maps.php

echo "#################################################"
echo "# Drivers and Finnishes"
echo "#################################################"
php /var/www/html/tmrank/scripts/drivers.php

echo "#################################################"
echo "# Names"
echo "#################################################"
php /var/www/html/tmrank/scripts/names.php

