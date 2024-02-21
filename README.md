# BandaLoco
Bandariello ProLoco website

run (fast)
```bash
make setup
make wordpress
```

run Wordpress CLI
```bash
docker-compose run --rm wpcli --info
docker-compose run --rm wpcli plugin list
```
## Dbeaver (GUI MySQL)
Web Database Admin `http://localhost:3080` per accedere a DBeaver Database Manager dopo aver avviato i containers. 

Il nome utente predefinito è `admin@dbeaver` e la password è la stessa fornita nel file `.env`
## MailDev
Modulo per testare l'e-mail generata dal progetto durante lo sviluppo. Gui Web `http://localhost:3081` dopo aver avviato i containers. 