#!/bin/sh

id_number=$1
password=$2

curl --data "userName=$id_number&password=$password&submit=Sign+in&command=login" 'https://d9.aisis.ateneo.edu/j_aisis/login.do' 2> /dev/null|grep -i 'sign out' 2>&1 > /dev/null

if [ $? -eq 1 ] #not found
then
	echo -n "Reject"
else
	echo -n "Accept"
fi
~
