FROM richarvey/nginx-php-fpm:php71
RUN sed -i -e 's/try_files $uri $uri\/ =404/try_files $uri $uri\/ \/index.php$is_args$args/g' /etc/nginx/sites-enabled/default.conf
RUN sed -i -e 's/root \/var\/www\/html;/root \/app\/web;/g' /etc/nginx/sites-enabled/default.conf
