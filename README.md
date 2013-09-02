DIISH
========================

Welcome to the DIISH!

History
----------------------------------
v1.3.0 - 02/09/2013
v1.2.1 - 02/09/2013
v1.2.0 - 02/09/2013
v1.1.0 - 29/08/2013
v1.0.0 - 26/08/2013


Install
----------------------------------
### Download an Archive File

Download the DIISH application using [Git Clone][1] or [Download ZIP][2] on GitHub and deploy it somewhere under your web server directory.

To install all the necessary dependencies, move to deployed directory and run the following command:

    php composer.phar install

Note: This application is running on Symfony 2.3 Framework. For a more detailed explanation, see the [Installation][3] chapter of the Symfony Documentation.


Configure
----------------------------------
### parameters.yml
Copy `app/config/parameters.yml.dist`, paste it under the same directory and rename it to `parameters.yml`.
Configure all the parameters to match your environment.


### security.yml
Configure the parameters under the `imag_ldap` section to match your LDAP server.


[1]:  https://github.com/toconuts/DIISH.git
[2]:  https://github.com/toconuts/DIISH/archive/develop.zip
[3]:  http://symfony.com/doc/2.3/book/installation.html
