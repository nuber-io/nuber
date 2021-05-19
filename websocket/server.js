/**
 * Nuber.io
 * Copyright 2020 - 2021 Jamiel Sharief.
 *
 * SPDX-License-Identifier: AGPL-3.0
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.nuber.io
 * @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
 */
const WebSocket = require("ws"); // new
const fs = require("fs"); // new
const url = require("url");

const cert = fs.readFileSync("./certs/certificate");
const key = fs.readFileSync("./certs/privateKey");

/**
 * Port to listen on
 */
const listen = 8080;

const server = new WebSocket.Server({ port: listen });

server.on("connection", (client, request) => {
  console.log("> client connected");

  if (request.url.includes("/test")) {
    client.send("test");
  }

  if (request.url.includes("/echo")) {
    echoRequest(client);
  }

  if (request.url.includes("/?server=")) {
    connectTerminal(client, request);
  }
});

server.on("error", function (error) {
  console.log(error);
});

console.log("Server started port:" + listen);

function echoRequest(client) {
  client.on("message", (data) => {
    console.log("received: %s", data);
    client.send(data);
    console.log("sent back");
  });
}

function connectTerminal(client, request) {
  // /?server=192.168.1.150/1.0/operations/e8baee9d-afb3-40cb-9736-ed3e6da8efab/websocket?secret=dabf7ee8c6b15b2a59d833d86a79012c31dd70449b6ba6e3441925d4c9d4f8a9
  var address = request.url.replace("/?server=", "");

  console.log("wss://" + address + "\n");

  const terminal = new WebSocket("wss://" + address, {
    cert: cert,
    key: key,
    rejectUnauthorized: false,
    perMessageDeflate: false,
  });

  terminal.on("error", function (error) {
    console.log(error);
  });

  terminal.on("open", function open() {
    console.log("> connected to LXD");

    client.on("message", (data) => {
      terminal.send(data, { binary: true });
    });

    terminal.on("message", (data) => {
      var dat = Buffer.from(data);
      client.send(dat.toString());
    });
  });

  client.on("close", (client) => {
    console.log(client + " closed connection");

    terminal.send("exit\r\n", { binary: true });
    terminal.close();
  });

  client.on("error", function (error) {
    console.log(error);
  });
}
