# Plugin wordpress utili per lo sviluppo:
* [Github Embed](https://wordpress.org/plugins/github-embed/)
* [GitHub Updater](https://github.com/afragen/git-updater)
* [WP Pusher](https://wppusher.com/) (free for public repos)
* [Versionpress](https://versionpress.com/)


# clean wp-app
```bash
 sudo rm -rf -- !(readme.md); sudo rm .htaccess
```

```bash
#### https://drive.google.com/file/d/10dnK6ybDvnHtezBpUjcrwdQ-oRJQ0pKv/view?usp=sharing
#### AVADA

wget --no-check-certificate 'https://docs.google.com/uc?export=download&id=10dnK6ybDvnHtezBpUjcrwdQ-oRJQ0pKv' -O bandaloco_pagesbuilder.zip


wget --load-cookies /tmp/cookies.txt "https://docs.google.com/uc?export=download&confirm=$(wget --quiet --save-cookies /tmp/cookies.txt --keep-session-cookies --no-check-certificate 'https://docs.google.com/uc?export=download&id=FILEID' -O- | sed -rn 's/.*confirm=([0-9A-Za-z_]+).*/\1\n/p')&id=FILEID" -O FILENAME && rm -rf /tmp/cookies.txt
```
```bash
export FILEID='10dnK6ybDvnHtezBpUjcrwdQ-oRJQ0pKv'
export FILENAME=/tmp/bandaloco_pagesbuilder.zip
wget --no-check-certificate 'https://docs.google.com/uc?export=download&id=FILEID' -O FILENAME

unzip FILENAME -d /tmp/
unzip FILENAME/theme/Avada.zip -d ./theme/
unzip FILENAME/plugins/fusion-core.zip -d ./plugins/
unzip FILENAME/plugins/fusion-builder.zip -d ./plugins/
```
# Plugin
* https://listmonk.app/