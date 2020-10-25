# INSTALLING

This project was originally developed to run on versions of FreeBSD or Ubuntu
Linux that have reached or is nearing end-of-life status. The information
provided in this document refers to the most recent platform that the
application had been deployed on: Ubuntu Linux 16.04 LTS (amd64).

## System Package Requirements

The following packages, along with any required dependencies, will need to
be installed using the `apt-get` utility on Ubuntu:

- apache2
- libapache2-mod-php7.0
- libmysqlclient-dev
- libmysqlclient20
- php-cli
- php-common
- php-mbstring
- php-mysql
- php-pear
- php-zip

## Apache Module Requirements

The following Apache modules need to be installed and enabled:

- alias
- rewrite

## Pear Package Requirements

The following PHP Pear packages need to be installed using the `pear install`
command:

- MDB2-2.5.0b5
- MDB2_Driver_mysql-1.5.0b4

Both of the packages listed are beta versions and, depending on the version of
`pear` installed on the system and how it is configured, you may need to allow
installation of packages in `beta` state.

## PHP Composer

In order to install the required libraries referenced in the project, you will
need to download and install the Composer tool as documented at:
https://getcomposer.org/doc/00-intro.md.

Once the tool has been installed, run `composer install` in the project
directory. The project does depend on an abandoned package, `silex/silex`,
that is no longer maintained and there has been no attempt to convert the
project to use `symfony/flex`.

## Apache Configuration

Depending on how you want to serve the web application through Apache 2.x,
either as a dedicated site or a virtual directory, the following should be
included in the appropriate Apache configuration file:

```
RewriteEngine On
DirectoryIndex index.php

<Directory "/path/to/wwdt.me_v3">
    Options -Indexes +FollowSymLinks -MultiViews -Includes
    AllowOverride all
    Order allow,deny
    Allow from all
</Directory>
```

## Application Configuration

Before the application can be used, the are several files that need to be
copied or renamed, and modified with the correct path and database information.

### _includes/Templates/Header.dist.php

This file needs to be copied or renamed to `_includes/Templates/Header.php`
and the Facebook and/or Google integration values will need to be changed if
they are to be used.

### _includes/WWDTM/Config.dist.php

This file need to be copied or renamed to `_includes/WWDTM/config.php` and the
database connection information needs to be filled out and the `SITE_PATH`
value needs to point to the fully-qualified path of the appliation.

### s/r.dist.php

This file needs to be copied or renamed to `s/r.php` and the database
connection information needs to be filled out.
