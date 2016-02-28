#!/bin/sh

id_number=$1
password=$2

#Authentication logic against AISIS
#Simulates a user login to the site and tries to find the string "sign out"
#after
curl --data "userName=$id_number&password=$password&submit=Sign+in&command=login" 'https://d9.aisis.ateneo.edu/j_aisis/login.do' 2> /dev/null|grep -i 'sign out' 2>&1 > /dev/null

#If grep does not find the string, then do
if [ $? -eq 1 ] #not found
then
	echo -n "Reject" #String was not found, did not log in successfully
else
	echo -n "Accept" #String was found, login successful
fi
~
#FreeRADIUS expects Reject or Accept as output.
