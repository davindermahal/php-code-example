PHPCodeExample
=======

In the more recent years I have worked on many PHP applications but unfortunately they are all projects owned by my 
 previous employers.  As a result, I do not have access nor can I share the code from those projects. As part of 
 coding sample, I have built this simple example application written in PHP using the Symfony 2 framework.  I used 
 PHP 5.6 with a mySQL database on MacOS, a Unix based system.

##App description
The app is a basic URL filtering system.  You can add, list, and remove URLs into the database.  Then you can view URLs
via an iFrame.  If the URL you are attempting to go to is in the list above, then you are blocked from doing so. 

One requirement for this project is to allow any type of data store.  It could be mySQL today, but a JSON file tomorrow. 
To accomplish this, I use an Interface for the Data Access Layer.  In this project, I use the ORM Doctrine to interact 
with mySQL. If I wanted to change to a JSON file, I just need to write a class that implements the UrlDalInterface with
the appropriate methods, change the Dependency Injection Container to use this new class instead, and I can easily 
use JSON instead of Doctrine. 

##Composer and setting up database access:

Composer is required to install the vendor directories. To install composer, please see the documentation at 

https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx

Once Composer is installed run composer install. 

`composer install`

During the install, composer will help you setup the database access. 
You may configure your database server. If you are running your mySQL server locally you should be able to use the 
defaults.

`database_name: dm_php_app`

use the username/password for your database server.

Composer will also ask you to configure the mailer. You can ignore this by just typing 'enter' to select the defaults.

##Creating the database, schema and fixtures
We will use the Symfony console to setup the database using the command-line from this directory.

`./bin/console doctrine:database:create`

`./bin/console doctrine:schema:create`

`./bin/console doctrine:fixtures:load`

To load the fixtures you'll be asked if you want to continue, type `y`

##Running the default web server
For ease and the purposes of this exercise, we'll run the server via Symfony.  This will run the application in 
development mode, meaning it will display the Symfony Debug toolbar at the bottom of the page, and will be running
using the `app_dev.php` controller.  In a production environment we would **never** do this. You can also run the 
Symfony Production environment by specifying the app.php controller.

From the terminal type:

`./bin/console server:run`

At this point, the server should be running on localhost for port 8000.

For the dev env, open your browser too: `http://localhost:8000`

For the production env, open your browser too: `http://localhost:8000/app.php/`

##Logging in
Please use the username `user_1` with the password `rabbit` to log into the application. 

##Running Tests
There are some very basic functional tests to check a few pages of the application.
These can be run using phpunit
`phpunit`

If phpunit can not be found, you'll need to install it at https://phpunit.de/getting-started.html

###Note
This project is not meant to run in production, so dev files such as `app_dev.php` and `config.php` have been left here.
