GOES Getter application using Slim framework
============================================



External Dependencies
---------------------

* php7-fpm & php7-cli
  * pecl mongodb & mongo/mongo php library
  * php7-redis
* nginx
* redis
* mongodb

### NGINX

__ngxin__ must be configured to understand REST style URLs.

``` bash
% sudo cp etc/nginx-php7.conf /etc/nginx/sites-available/default
% sudo service nginx restart
```



# SLIM Framework 3 Skeleton Application

Use this skeleton application to quickly setup and start working on a
new Slim Framework 3 application. This application uses the latest
Slim 3 with the PHP-View template renderer. It also uses the Monolog
logger.

This skeleton application was built for Composer. This makes setting
up a new Slim Framework application quick and easy.

## Install the Application

Run this command from the directory in which you want to install your
new Slim Framework application.

    php composer.phar create-project slim/slim-skeleton [my-app-name]

Replace `[my-app-name]` with the desired directory name for your new
application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writeable.

To run the application in development, you can also run this command.

	php composer.phar start

Run this command to run the test suite

	php composer.phar test

That's it! Now go build something cool.
