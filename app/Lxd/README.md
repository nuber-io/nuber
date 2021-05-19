# LXD Endpoint

## Guidelines

These are the new guidelines, eventually libraries should be refactored to represent this

1. Methods should represent the lxc commands for organziation, with exception `show` since we are not showing, however
this might conflict with `get`, e.g. volume. Can use info. 
2. Just because there are options in LXD does not mean need to accept them, if in doubt see 1
3. options keys, auto_update becomes autoUpdate.
4. lxc <command> --help to see list of items 


## Troubleshooting

```bash
curl -s -k --cert config/certs/certificate --key config/certs/privateKey https://192.168.1.150:8443/1.0 | jq .metadata.auth 
```


## Resources

- https://linuxcontainers.org/lxd/docs/master/index
- https://linuxcontainers.org/lxd/docs/master/rest-api