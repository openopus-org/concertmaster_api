# concertmaster_api
Classical music metadata API for Spotify

It's mainly the data provider for the Concertmaster player (https://github.com/adrianosbr/concertmaster_player), but it can be used in any application.

# Steps to install

1. Clone the git repository (for example, in the /var/www/ folder)
2. Install the data (create a database first, for example, dev_concertmaster)

```bash
mysql -u USER -p dev_concertmaster < /var/www/concertmaster_api/db.sql
```

3. Set the environment variables for root:

```bash
vim /etc/environment
```

```bash
export BASEHTMLDIR="/var/www/concertmaster_api/html"
```

4. Update crontab for root

```bash
# m     h       dom     mon     dow     command
0       *       *       *       *       /var/www/concertmaster_api/cln/db.sh
*/30      *       *       *       *       /var/www/concertmaster_api/cln/user.sh
```

5. Give ownership of the public directory to the web server group (e.g., www-data):

```bash
chgrp www-data /var/www/concertmaster_api/html -R
```