#
# Nuber.io
# Copyright 2020 - 2021 Jamiel Sharief
#
# SPDX-License-Identifier: AGPL-3.0
#
# @copyright   Copyright (c) Jamiel Sharief
# @link        https://www.nuber.io
# @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
#
FROM ubuntu:20.04

ENV DATE_TIMEZONE UTC
ENV DEBIAN_FRONTEND=noninteractive

COPY . /var/www
WORKDIR /var/www
RUN scripts/container-setup.sh --docker

CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"] 