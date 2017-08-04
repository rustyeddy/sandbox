GOES Getter Web Interface
=========================

The GOES Getter web interface is written in PHP (shocker) with the [SLIM Framework](http://slimframework.com) to serve up a _restful API_.  We are also using the __nginx__ webserver.

In order for this to work __nginx__ needs to have URL rewriting configured properly, as well as user permissions.  This will cause all URLs to be passed through the index.php file.

```config
server {
    listen 80;
    server_name goes.dev;
    index index.php;
    root /srv/goesgetter/www;

    try_files $uri /index.php;

    location /index.php {

        # with php5-fpm
    fastcgi_index index.php;
    fastcgi_pass unix:/var/run/php5-fpm.sock;
    include fastcgi_params;
    }
}

```
