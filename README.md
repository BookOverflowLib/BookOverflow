# BookOverflow

## Usage
Run the following commands to start the server inside the project folder:
```bash
docker compose up
```
Connect to the server at `http://localhost:8080/public/index.php`.

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
- [ ] nel form di registrazione, implementare l'inserimento della città con suggerimenti
- [ ] alleggerire foto

## Note:

-   Riconsiderare più avanti come gestire le immagini (DB vs filepaths vs google as CDN)
-   [suggerimenti citta](https://www.html.it/script/creazione-menu-a-discesa-con-lista-di-tutti-i-comuni-italiani/)
-   [lista formale dei generi libri](https://www.bisg.org/complete-bisac-subject-headings-list)
-   Requisito: deve essere presente una forma di controllo dell’input inserito dall’utente, sia lato client che lato server → per lato server si potrebbe controllare se un utente che ha provato a registrarsi non abbia già un account
-   L'ideale è max 3 css, uno per screen, uno per print e uno handheld
