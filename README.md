# concertmaster_api
Classical music metadata API for Spotify

It's mainly the data provider for the Concertmaster player (https://github.com/adrianosbr/concertmaster_player), but it can be used in any application.

# Steps to install

1. Clone the git repository
2. Set the environment variables for root:

```bash
vim /etc/environment
```

```bash
export BASEHTMLDIR="/var/www/concertmaster_api/html"
```

3. Update crontab for root

```bash
# m     h       dom     mon     dow     command
0       *       *       *       *       /var/www/concertmaster_api/cln/db.sh
*/30      *       *       *       *       /var/www/concertmaster_api/cln/user.sh
```

4. Change ownership of the HTML directory to group www-data:

```bash
cd /var/www/concertmaster_api/
chgrp www-data html -R
```