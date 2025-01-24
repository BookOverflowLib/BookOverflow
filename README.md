# BookOverflow

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

Connect to the server at `http://localhost:8080/public/index.php`.

To use phpmyadmin go to `http://localhost:8081/`.
***
To setup ssh keys for uploading the website to paoltti's server:
```bash
ssh-copy-id USERNAME@paolotti.studenti.math.unipd.it    
ssh paolotti.studenti.math.unipd.it -l USERNAME -L8080:tecweb:80 -L8443:tecweb:443 -L8022:tecweb:22
```
then in a new terminal
```bash
ssh-copy-id -p 8022 USERNAME@127.0.0.1
```

Then to upload changes to the server:
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

## Idee funzionalità

### Gestione Libri:

-   [ ] Ricerca dei libri tramite api per aggiungerli alle lista
-   [ ] Ricerca generica dei libri nel database
-   [ ] Ricerca tra i match
-   [ ] Ricerca case insensitive e parziale che aggiorna i risultati dopo tot tempo che non scrivo
-   [ ] Includere condizioni del libro

### Sistema di Matching:

-   [ ] Algoritmo di matching diretto (A ha libro che B vuole e viceversa)
-   [ ] Matching libri posseduti --> libri non posseduti ma dei generi preferiti
-   [ ] ??? Prioritizzazione della visualizzazione della lista degli utenti basata su:
    -   [ ] Rating utenti
    -   [ ] Scambi andati a buon fine

### Features di Sicurezza:

-   [ ] Password salvata in hash
-   [ ] Usare regex per verificare che la password abbia A-z, 0-9, simbolo e lunghezza minima

### Features di Accessibilità

-   [ ] Attenzione al numero di tab
-   [ ] **Link per saltare al contenuto**
-   [ ] Modalità high-contrast?

### Aspetti Social:

-   [ ] Profili utente con storico scambi
-   [ ] Recensioni

### Funzionalità Extra:

-   [ ] Rete di follower/followed
-   [ ] Chat integrata per organizzare lo scambio
-   [ ] Suggerimenti automatici basati su libri precedentemente scambiati
-   [ ] Notifiche push/email per nuovi match
-   [ ] Visualizzare quanto si ha risparmiato con gli scambi fatti

## TODO
- [x] nel form di registrazione, implementare l'inserimento della città con suggerimenti
- [x] alleggerire foto

## Note:

-   Riconsiderare più avanti come gestire le immagini (DB vs filepaths vs google as CDN)
-   [suggerimenti citta](https://www.html.it/script/creazione-menu-a-discesa-con-lista-di-tutti-i-comuni-italiani/)
-   [lista formale dei generi libri](https://www.bisg.org/complete-bisac-subject-headings-list)
-   Requisito: deve essere presente una forma di controllo dell’input inserito dall’utente, sia lato client che lato server → per lato server si potrebbe controllare se un utente che ha provato a registrarsi non abbia già un account
-   L'ideale è max 3 css, uno per screen, uno per print e uno handheld
