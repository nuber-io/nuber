FROM ubuntu:20.04

ENV DATE_TIMEZONE UTC
ENV DEBIAN_FRONTEND=noninteractive

COPY . /var/www
WORKDIR /var/www
RUN scripts/container-setup.sh --docker

CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"] 