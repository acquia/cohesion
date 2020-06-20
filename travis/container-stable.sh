#!/bin/bash

supervisorctl restart apache:apached
/etc/init.d/mysql restart

while ! mysqladmin ping --silent; do
    sleep 1
done
