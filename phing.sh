#!/bin/sh
phing -Dapacheconfdir="/etc/apache2/conf-enabled" -Dapachectl="sudo /etc/init.d/apache2" -Dmysqlrootpass=Pd2@1105 -Dextra_shutdown_cleanup="sudo chown -R ubuntu:www-data local/cache" $*

