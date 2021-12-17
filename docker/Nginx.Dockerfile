FROM nginx
ENV TZ=Europe/Moscow
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
ADD docker/conf/vhost.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/messanger

