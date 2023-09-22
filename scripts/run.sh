#!/bin/bash

echo "#################################################"
echo "# Git"
echo "#################################################"
sudo -u www-data git -C /var/www/html/tmrank pull

echo "#################################################"
echo "# Remove"
echo "#################################################"
php /var/www/html/tmrank/scripts/remove.php

echo "#################################################"
echo "# Maps"
echo "#################################################"
php /var/www/html/tmrank/scripts/maps.php

echo "#################################################"
echo "# Drivers and Finishes"
echo "#################################################"
php /var/www/html/tmrank/scripts/drivers.php

echo "#################################################"
echo "# Names"
echo "#################################################"
php /var/www/html/tmrank/scripts/names.php

