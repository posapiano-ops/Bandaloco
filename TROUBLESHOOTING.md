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
run 
```bash
sudo chmod 777 -R wp-app/
```