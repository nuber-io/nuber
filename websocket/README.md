# Websocket Server

This handles the websocket requests between the client and the LXD server


```linux
$ npm install
```

To run 
```linux
$ node server.js
```

To check its working you can do 

```bash
$ curl -i -N -k -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Host: localhost" -H "Origin: http://127.0.0.1:9090" http://127.0.0.1:9090
```

## Dockerimage

The docker container is for development only, you need to copy the privateKey and certificate before building.

To build the docker image

```bash
$ docker build -t websocket .
```

To run the image (use different port)

```bash
$ docker run -p 3000:8080 -d websocket
```

This will output hash,
```bash
$ docker logs e1d1e1fe09afe608c9a395369c9a1886011ad952dff57897f86bfad62fcdade4
```

To get the container ID
```bash
$ docker ps
```

To enter the image

```bash
$ docker exec -it <container id> /bin/bash
```

To tag the image

```bash
$ docker build -t websocket .
$ docker tag websocket:latest websocket:staging
```

To test

```bash
$ curl -i localhost:3000
```