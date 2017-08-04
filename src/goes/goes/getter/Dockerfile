FROM rustyeddy/nginx-php7-mongo
MAINTAINER Rusty Eddy <rusty@rustyeddy.com>

COPY etc/nginx-php7.conf /etc/nginx/sites-enabled/default
ADD bin/adduser.sh /root/adduser.sh
RUN chmod +x /root/adduser.sh; /root/adduser.sh

