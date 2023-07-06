# BandaLoco Demo
Bandariello ProLoco  Experience

run (fast)
```bash
make setup
make up
```

run Wordpress CLI
```bash
docker-compose run --rm wpcli --info
docker-compose run --rm wpcli plugin list
```
## Dbeaver
Web Database Admin `http://localhost:3080` per accedere a DBeaver Database Manager dopo aver avviato i containers. 

Il nome utente predefinito è `admin@dbeaver` e la password è la stessa fornita nel file `.env`