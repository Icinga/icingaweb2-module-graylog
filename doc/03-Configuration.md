

```
vim /etc/icingaweb2/modules/graylog/instances.ini

[graylog]
uri = "http://192.168.33.9:9000/api"
user = "admin"
password = "admin"
```

```
vim /etc/icingaweb2/modules/graylog/eventtypes.ini

[gelf]
instance = "graylog"
index = "notused"
filter = "host={host.name}"
fields = "*"
```
