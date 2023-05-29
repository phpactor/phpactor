FROM php:8.1-cli
RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer
RUN docker-php-ext-install pcntl
RUN apt-get update && apt-get install -y python3-pip wget git
RUN wget https://github.com/google/vimdoc/releases/download/v0.7.1/vimdoc_0.7.1-1_all.deb && \
    dpkg -i vimdoc_0.7.1-1_all.deb
COPY requirements.txt ./
RUN pip3 install -r requirements.txt
WORKDIR /app
