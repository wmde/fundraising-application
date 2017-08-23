FROM php:7.1-fpm

RUN apt-get update \
    # for intl
    && apt-get install -y libicu-dev \
    # for curl
    && apt-get install -y libcurl3-dev \
    # for xml
    && apt-get install -y libxml2-dev \
    # for konto_check
    && apt-get install -y unzip patch libz-dev \
    # compare vagrant/install_packages.sh
    #&& docker-php-ext-install -j$(nproc) pdo_sqlite \
    && docker-php-ext-install -j$(nproc) intl curl xml pdo_mysql mbstring

COPY ./build/installKontoCheck_docker.sh /tmp/installKontoCheck_docker.sh

RUN chmod +x /tmp/installKontoCheck_docker.sh && /tmp/installKontoCheck_docker.sh
