FROM rustyeddy/nginx-php7-mongo
MAINTAINER Rusty Eddy <rusty@rustyeddy.com>

RUN rm -f /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
COPY etc/nginx-php7.conf /etc/nginx/sites-available/nginx-php7.conf
RUN ln -s /etc/nginx/sites-available/nginx-php7.conf /etc/nginx/sites-enabled/nginx-php7.conf
COPY bin/adduser.sh /root/adduser.sh
RUN chmod +x /root/adduser.sh
RUN /root/adduser.sh

CMD /start.sh
