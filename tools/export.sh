#!/bin/bash
_os="`uname`"
_now=$(date +"%m_%d_%Y")
_file="wp-data/data_$_now.sql"

# Export dump
EXPORT_COMMAND='exec /usr/bin/mysqldump bandaloco -uroot -ppassword'
docker-compose exec db sh -c "$EXPORT_COMMAND" > $_file

if [[ $_os == "Darwin"* ]] ; then
  sed -i '.bak' 1,1d $_file
else
  sed -i 1,1d $_file # Removes the password warning from the file
fi