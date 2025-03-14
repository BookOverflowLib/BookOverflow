# BookOverflow
[Live Demo](https://bookoverflow.lribon.duckdns.org/)
## Usage
If it is your first time running this docker or if you did changes to the Dockerfile/docker-compose:
```bash
docker compose up --build
```
Then you can just use this: 
```bash
docker compose up
```
If you get _permission denied_ error use _sudo_ before the command.

Connect to the server at `http://localhost:8080/`.

To use phpmyadmin go to `http://localhost:8081/`.
***
To setup ssh keys for uploading the website to Paolotti's server:
```bash
ssh-copy-id USERNAME@paolotti.studenti.math.unipd.it    
ssh paolotti.studenti.math.unipd.it -l USERNAME -L8080:tecweb:80 -L8443:tecweb:443 -L8022:tecweb:22
```
then in a new terminal
```bash
ssh-copy-id -p 8022 USERNAME@127.0.0.1
```

To upload changes to the tecweb server (keys need to have been set up first):
- open tunnel:
    ```bash
    ssh paolotti.studenti.math.unipd.it -l USERNAME -L8080:tecweb:80 -L8443:tecweb:443 -L8022:tecweb:22
    ```
- upload (cd to this repo's dir first):
    ```bash
    rsync -e "ssh -p 8022 " -avrP --exclude-from=.rsyncignore . USERNAME@localhost:public_html 
    ```
- if there are edits in SQL files, upload them from localhost:8080/phpmyadmin
- generate .env file if not already present (execute the following after doing `ssh localhost -p 8022 -l USERNAME`):
    ```bash
    [ "$(basename "$PWD")" != "public_html" ] && cd public_html; username=$(whoami) && DB_HOST="localhost" DB_DATABASE="$username" DB_USERNAME="$username" DB_PASSWORD="$(cat ../pwd_db_2024-25.txt)" PREFIX="/$username" && echo -e "DB_HOST=$DB_HOST\nDB_DATABASE=$DB_DATABASE\nDB_USERNAME=$DB_USERNAME\nDB_PASSWORD=$DB_PASSWORD\nPREFIX=$PREFIX" > .env
    ```

To upload to the CAA server:
- upload files: 
  ```bash
    rsync -avrP --exclude-from=.rsyncignore . abernard@caa.studenti.math.unipd.it:public_html
    ```
- generate `.env`
    ```bash
    [ "$(basename "$PWD")" != "public_html" ] && cd public_html; username=$(whoami) && DB_HOST="localhost" DB_DATABASE="$username" DB_USERNAME="$username" DB_PASSWORD="$(cat ../pwd_db_caa.txt)" PREFIX="/$username" && echo -e "DB_HOST=$DB_HOST\nDB_DATABASE=$DB_DATABASE\nDB_USERNAME=$DB_USERNAME\nDB_PASSWORD=$DB_PASSWORD\nPREFIX=$PREFIX" > .env
    ```
- import db with the script `utils/import_db_tecweb.sh`
