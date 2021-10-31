## FakeAuth

### Minecraft Yggrasil <sub><sup>(circa 2017)</sup></sub> in PHP

This is a small project I did back in 2017 for running a fully authenticated minecraft server in a completly isolated LAN.

It requires Apache and MySQL to run, and OpenSSL to generate an SSL key. `ssl_key/fakeauth.openssl.conf` is an OpenSSL config file for generating the key.

You'll need to add that SSL key to the trusted storage on every device that'll be playing, and configure your DNS server to redirect the 5 domains listed in `ssl_key/fakeauth.openssl.conf` to the server hosting this.

There used to be a dashboard for account, cape, & skin management, but it wasn't recovered with the rest of this code

**this code is horibly inefficient and not worth the effort of setting up. please don't try to use this**
