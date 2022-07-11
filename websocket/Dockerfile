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
FROM node:14

COPY . /var/www
WORKDIR /var/www/websocket

RUN npm install

EXPOSE 8080

CMD [ "node", "server.js" ]