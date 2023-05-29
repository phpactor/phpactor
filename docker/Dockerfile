FROM php:8.1-cli
RUN apt-get update && apt-get install -y python3-pip wget git inotify-tools libzip-dev
RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer
RUN docker-php-ext-install pcntl zip
RUN wget https://github.com/google/vimdoc/releases/download/v0.7.1/vimdoc_0.7.1-1_all.deb && \
    dpkg -i vimdoc_0.7.1-1_all.deb
COPY requirements.txt ./
COPY docker/php.ini /usr/local/etc/php/conf.d/
RUN pip3 install -r requirements.txt
RUN git config --global init.defaultBranch master
WORKDIR /phpactor
