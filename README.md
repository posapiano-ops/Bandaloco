# BandaLoco
Bandaloco ProLoco website

run (fast)
```bash
cp sample.env .env
make bandaloco
```

run Wordpress CLI
```bash
docker-compose run --rm wpcli --info
docker-compose run --rm wpcli plugin list

# Opzionale: si può definire un alias per ridurre il comando
alias wp="docker-compose run --rm wpcli"
wp --info
wp plugin list
```
## Dbeaver (GUI MySQL)
Web Database Admin `http://localhost:3080/dbeaver` per accedere a DBeaver Database Manager dopo aver avviato i containers. 

Il nome utente predefinito è `admin@dbeaver` e la password è la stessa fornita nel file `.env`
## MailDev
Modulo per testare l'e-mail generata dal progetto durante lo sviluppo. Gui Web `http://localhost:3081` dopo aver avviato i containers. 