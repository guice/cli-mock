#!/bin/bash

DIR=/var/data/pp/files/dataOSC/export

shopt -s nullglob
cd $DIR;
for f in *.csv
do
    echo "Zipping File - $f"
    zip_file=${f/csv/zip}
    zip -P expose-hillside-liven $zip_file $f
    #rm $f
done

d=`date +%B_%d -d "yesterday"`

lftp -u defuser,34r4\$m94M%@ sftp://secure.dataOSC.org << EOT
  cd /Secure_Messages/QAPending
  mput DataOSC__MESSAGES_*.zip && !mv DataOSC__MESSAGES_*.zip sent/
  cd /OrderFiles/QAPending
  mput DataOSC__*.zip && !mv DataOSC__*.zip sent/
  bye
EOT

FAILED_FILES=$DIR/*.zip
echo "FAILED_FILES: " $FAILED_FILES;

if [ "$FILED_FILES" != "" ]; then
    mail -s "(`hostname`) DataOSC Push Error" philip@lemonaid.com << EOT

The following files FAILED to push to PPSOSBC:

`for f in $FAILED_FILES; do echo "   • " $f; done`

Manual Verification Required to correct issue.
    
EOT

fi
