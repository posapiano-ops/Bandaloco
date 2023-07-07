# Documentation Note
```bash
# Init Wordpress
docker-compose run --rm wpcli wp core install --url=http://localhost:3000 --title='BandaLoco' --locale=it_IT --admin_user=admin --admin_password=password --admin_email=bandariello@devmail.com

# init config
docker-compose run --rm wpcli language core install it_IT
docker-compose run --rm wpcli site switch-language it_IT
docker-compose run --rm wpcli option update timezone_string "Europe/Rome"
docker-compose run --rm wpcli option update date_format "j F Y"
docker-compose run --rm wpcli option update time_format "G:i"

#init plugin
docker-compose run --rm wpcli plugin install wp-mail-smtp --activate
docker-compose run --rm wpcli plugin uninstall hello

# init theme
docker-compose run --rm wpcli theme uninstall twentytwentyone
docker-compose run --rm wpcli theme uninstall twentytwentytwo

#docker-compose run --rm wpcli theme uninstall twentytwentythree

```


# Installa theme
copy zip in document root
```bash
cd theme ; zip -r ../wp-app/born-to-give.zip . * ; cd ..
```
install 
```bash
docker-compose run --rm wpcli theme install born-to-give.zip

docker-compose run --rm wpcli plugin install /var/www/html/wp-content/themes/born-to-give/framework/tgm/plugins/btg-core.zip --activate

#docker-compose run --rm wpcli plugin activate btg-core

docker-compose run --rm wpcli theme activate born-to-give

``` 
