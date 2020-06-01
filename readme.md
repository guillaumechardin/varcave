#Welcome to Varcave

Varcave is a web application that can be used for cave data management.
It used on some well known technologies like html/js/php and rely on mysql/maria database backed.
Features a simple i18n system to achieve quick translation to your language. 

## Prerequisites

 * php 7.2
 * mysql/mariaDB 10.3
 * Firefox 75 (fully tested on)
 * your server *must* be set up with https

## Installation 
Varcave has been fully tested on apache 2.4, but it should work on IIS if required.

##Download lastest release or clone git repository.

`git clone https://github.com/guillaumechardin/varcave [targetfolder]

## Configure apache
Change your apache configuration to use this folder as your DocumentRoot. See apache documentation.

## Import database template
Import empty database template 
 ` mysql -u<username> -p<password> <databasename> < doc/defaultVarcave.sql
 
You can also use phpMyadmin or any other tool to import database template.

# Use and update data
Reach the new created website https://server.domain.tld/login.php.
Default account is "admin" and password "password". Keep in mind to change your password as soon as possible to prevent unwanted access to administration panel and data.
 