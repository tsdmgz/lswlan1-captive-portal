# LSAuth

LSWLAN1 authentication system using student AISIS credentials with
WPA2-Enterprise

# Contains

* hostapd sample config
* dnsmasq sample config
* FreeRADIUS sample config
* AISIS authentication script

# Implementation

The prototype environment was made with an openSUSE Tumbleweed host, running
hostapd and dnsmasq. freeradius-server was installed in an LXC container running
openSUSE Leap 42.1. Configuration files for this setup are provided in this
repository.

## Packages

* freeradius-server
* dnsmasq
* hostapd
* bridge-utils
* iptables

# Client settings

SSID: LSWLAN1-EAP\
Encryption: WPA2-Enterprise or 802.1x\
Encapsulation: TTLS\
Second layer: PAP\
Identity/Username: AISIS username\
Password: AISIS password

# Specifics

## PAP

While PAP sends the password in plaintext, it is secured in a TLS tunnel, the
same technology powering HTTPS. As long as the server keeps its certificate safe
and even better secured with certificate pinning, the credentials will be safe.

The password is required to be in plaintext so that the authentication logic can
send the credentials to AISIS itself for verification.

## Unsupported platforms

* Windows Phone 8 (possibly WP7)
* Windows Mobile =< 6
* Maemo 5 (/sad)
* BBos has not been tested

## Tested platforms

* Windows > 8 desktop
* Android > 4.4

## Not tested

* OS X
* Linux (it *should* work)
