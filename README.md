# **bearsoft**

Bearsoft is first open source accounting software in persian language with web interface.

for see full project Demo see main website at [https://bearsoft.ir](https://bearsoft.ir)

## Before Installation

For install bearsoftCore you need this tools

* Web server : Apache,NginX,...
* Database: Mysql, mariaDB,SqlServer,....
* PHP: php : +8.1
* php extentions: php-Intl, php-mbstring, php-http, php-raphf
* composer

## Installation

* Copy or clone project in web server directory . if you use shared hosting panels like cpanel or directadmin copy files in root directory and public_html folder will be rewrited.
* create database in your DBMS and edit .env file in root of project

* Install dependencies with run this command

```
composer install
```

* edit .env file and set database connection string with your username and password and name of database

* create local env file with run this command

```
composer dump-end prod
```

* login to your database managment like phpmyadmin and import file located in bearsoftBackup/databaseFiles/bearsoft-db-default.sql

* go to bearsoftCore folder in cli and update database with this command

```
php bin/console doctrine:schema:update --force
```
open root domain address in browser you should see bearsoft api main page.

## Donation
for help developers please use this link
[https://zarinp.al/bearsoft.ir](https://zarinp.al/bearsoft.ir)