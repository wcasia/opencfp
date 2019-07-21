This is [OpenCFP](https://github.com/opencfp/opencfp) setup for [WordCamp Asia 2020](https://2020.asia.wordcamp.org/)

## Updating This Site

Most updates are performed via direct code changes. Once changes are done, run followin commad on the server from:

```
cd /var/www/speak.wpasia.org/htdocs &&
composer run update-env
```

## Clearing cache

```
cd /var/www/speak.wpasia.org/htdocs &&
bin/console cache:clear
```

## Development Setup

Setup a local PHP enviornemt with a mysql database and configure web server following instructions in [original OpenCFP readme here](https://github.com/opencfp/opencfp#readme-contents).

### Using EasyEngine

**Create Site**

Create a site with PHP and MySQL. Also switch it's public dir to `web` subdir inside `htdocs` using following command:

```
ee site create opencfp --mysql --public-dir=web
```

**PDO Support**

OpenCFP rquires PDO extension.
```
ee shell opencfp.test --command='docker-php-ext-install pdo_mysql' --user=root
```

**Development environment bug fix**

There is a bug in OpenCFP because of which even default `development` environment choice is not respected when accessing application via browser.

Open `~//easyengine/sites/opencfp.test/config/nginx/conf.d/main.conf`

In `location ~ \.php$ {` block, add following line after `try_files` line:

```
fastcgi_param CFP_ENV development;
```

Save changed and run follwing to restart nginx:

```
ee site restart opencfp.test --nginx
```

**Clone git repo**

Fork [this repo](https://github.com/wcasia/opencfp) and clone forked repo into correct folder.

Change repo path in follwing code. Don't forget to switch to `wcasia` branch.

```
cd ~/easyengine/sites/opencfp.test
rm -rf htdocs
git clone git@github.com:wcasia/opencfp.git htdocs
git checkout wcasia
```

**OpenCFP config file**

Use sample config file to set development config
```
cp config/development.yml.dist config/development.yml
```

Edit `config/development.yml` with database info. You can use `ee site info opencfp.test` to find database credentials created for this site.

**Run Setup**

Next, run OpenCFP installer script to finish setup

```
composer run setup-env
```

**Optional**
If you need PhpMyAdmin or Mailhog to debug stuff:

```
ee admin-tools enable opencfp.test
ee mailhog enable opencfp.test
```

You may need mailhog to access signup confirmation email in dev enviornment.
Mailhog url: http://opencfp.test/ee-admin/mailhog/


To get HTTP Auth i.e. username &amp; password, please use:

```
ee auth list global
```
