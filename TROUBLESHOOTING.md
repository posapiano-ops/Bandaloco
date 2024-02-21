# TROUBLESHOOTING
1. fix permiss wp-app folder
```bash
Warning: Unable to create directory wp-content/uploads/2023/07. Is its parent directory writable by the server?
Success: WordPress installed successfully.
```
oppure
```bash
Warning: Could not create directory. "/var/www/html/wp-content/languages/"
Language 'it_IT' not installed.
Error: No languages installed (1 failed).
make: *** [/home/pippo/workspaceU/BandaLoco/libs/common.mk:24: wordpress] Error 1
```
***SOLUZIONE***: esegui 
```bash
sudo chmod 775 -R wp-app/
```

2. fix database connection
```bash
Error: Error establishing a database connection. This either means that the username and password information in your `wp-config.php` file is incorrect or that contact with the database server at `db` could not be established. This could mean your host’s database server is down.
make: *** [/home/rfiorito/workspaceU/BandaLoco/libs/common.mk:22: wordpress] Error 1
```
***SOLUZIONE***: attendere che il database mysql si avvii.. verificare i log del database