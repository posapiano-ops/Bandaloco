# Plugin wordpress utili per lo sviluppo:
* [Github Embed](https://wordpress.org/plugins/github-embed/)
* [GitHub Updater](https://github.com/afragen/git-updater)
* [WP Pusher](https://wppusher.com/) (free for public repos)
* [Versionpress](https://versionpress.com/)


# clean wp-app
```bash
 sudo rm -rf -- !(readme.md); sudo rm .htaccess
```

### NOT WORK
```bash
#### AVADA 7.11.7
#### https://drive.google.com/file/d/114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5/view?usp=sharing

wget --no-check-certificate 'https://docs.google.com/uc?export=download&id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5' -O ./tmp/avada_v7.11.7.zip

wget --load-cookies /tmp/cookies.txt "https://docs.google.com/uc?export=download&confirm=$(wget --quiet --save-cookies /tmp/cookies.txt --keep-session-cookies --no-check-certificate 'https://docs.google.com/uc?export=download&id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5' -O- | sed -rn 's/.*confirm=([0-9A-Za-z_]+).*/\1\n/p')&id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5" -O ./tmp/avada_v7.11.7.zip && rm -rf /tmp/cookies.txt
```

### WORK
Update con [gdown](https://github.com/wkentaro/gdown)
```bash
sudo apt update
sudo apt install python3-pip
pip3 install gdown
gdown https://drive.google.com/uc?id=114Iq6TLocg7c8EKvwDL7Jj9EIAT8miG5 -O ./tmp/avada_v7.11.7.zip

unzip ./tmp/avada_v7.11.7.zip -d ./tmp/
unzip ./tmp/Avada\ Theme/Avada.zip -d ./theme/
unzip ./tmp/Avada\ Theme/fusion-core.zip -d ./plugins/
unzip ./tmp/Avada\ Theme/fusion-builder.zip -d ./plugins/
```

* https://github.com/syltruong/gdown-docker-wrapper/tree/master

# Plugin
* https://listmonk.app/