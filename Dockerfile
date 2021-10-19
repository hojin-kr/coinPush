FROM ubuntu:20.04

# Supresses unwanted user interaction (like "Please select the geographic area" when installing tzdata)
ENV DEBIAN_FRONTEND=noninteractive

ADD ./ /ccxt
ADD ./ /ccxt/bin
WORKDIR /ccxt

# Update packages (use us.archive.ubuntu.com instead of archive.ubuntu.com — solves the painfully slow apt-get update)
RUN sed -i 's/archive\.ubuntu\.com/us\.archive\.ubuntu\.com/' /etc/apt/sources.list

# Miscellaneous deps
RUN apt-get update && apt-get install -y --no-install-recommends curl gnupg git ca-certificates ruby
# Install twurl
RUN gem install --install-dir /ccxt/bin twurl
# PHP
RUN apt-get update && apt-get install -y --no-install-recommends php php-curl php-iconv php-mbstring php-bcmath php-gmp php-gd

## Install composer and everything else that it needs and manages
RUN /ccxt/composer-install.sh
RUN apt-get update && apt-get install -y --no-install-recommends zip unzip php-zip
RUN mv /ccxt/composer.phar /usr/local/bin/composer
RUN composer install
## Remove apt sources
RUN apt-get -y autoremove && apt-get clean && apt-get autoclean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

