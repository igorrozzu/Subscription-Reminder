FROM php:8.2-fpm

ARG DEPS="bash vim git wget npm"
RUN apt-get update && apt-get install -y $DEPS

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip

RUN apt-get -y install cron

# Add the cron job
RUN crontab -l | { cat; echo "* * * * * /usr/local/bin/php /var/www/html/public/subscription_reminder.php"; } | crontab -
RUN crontab -l | { cat; echo "* * * * * /usr/local/bin/php /var/www/html/public/email_sender.php"; } | crontab -
RUN crontab -l | { cat; echo "* * * * * /usr/local/bin/php /var/www/html/handlers/jobs_handler.php email_sender"; } | crontab -
RUN crontab -l | { cat; echo "* * * * * /usr/local/bin/php /var/www/html/handlers/jobs_handler.php subscription_reminder"; } | crontab -


RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip
    
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php 
RUN php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www