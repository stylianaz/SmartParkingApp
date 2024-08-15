#!/bin/bash

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "GRANT ALL PRIVILEGES ON ${MYSQL_DB}.* TO 'root'@'%' WITH GRANT OPTION;"
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "FLUSH PRIVILEGES;"

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "CREATE USER ${MYSQL_USER}@localhost IDENTIFIED BY '${MYSQL_PASSWORD}';"
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "GRANT ALL PRIVILEGES ON ${MYSQL_DB}.* TO '${MYSQL_USER}'@'localhost';"
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "CREATE USER ${MYSQL_USER}@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';"
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "GRANT ALL PRIVILEGES ON ${MYSQL_DB}.* TO '${MYSQL_USER}'@'%';"
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "FLUSH PRIVILEGES;"

tables=$(mysql -uroot -p"rootPassword" -e "SELECT count(*) AS TOTALNUMBEROFTABLES FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'smartParking';")
tables="${tables//$'\n'/ }"
read -r a table_num <<< $tables

# This ensures that migrations and database dump loading is only done on the first running
if [ "${table_num}" = "0" ]
then
  mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "USE ${MYSQL_DB}; source ./database/smartParking_07112018.sql;"
  php artisan migrate:fresh --seed
else
  echo "Database already initialised, not using dump."
fi

php artisan serv --host=0.0.0.0 --port=5000