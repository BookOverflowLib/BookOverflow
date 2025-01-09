DROP TABLE IF EXISTS Immagine;

DROP TABLE IF EXISTS Follow;

DROP TABLE IF EXISTS Recensione;

DROP TABLE IF EXISTS Scambio;

DROP TABLE IF EXISTS Desiderio;

DROP TABLE IF EXISTS Copia;

DROP TABLE IF EXISTS Libro;

DROP TABLE IF EXISTS Utente;

CREATE TABLE
    Utente (
        email VARCHAR(255) PRIMARY KEY,
        -- l'hashing verrà implementato in PHP, la lunghezza dell'hash dovrebbe essere 60 mentre quella del salt 22
        password_hash CHAR(60) NOT NULL,
        -- non è necessario memorizzare il salt in quanto la funzione password_hash() lo genera automaticamente e poi la funzione password_verify() sarà capace di verificarlo solamente con il risultato di password_hash()
        -- password_salt CHAR(22) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        nome VARCHAR(50),
        cognome VARCHAR(50),
        provincia VARCHAR(50),
        comune VARCHAR(50),
        path_immagine VARCHAR(255),
        generi_preferiti VARCHAR(1300)
    );

CREATE TABLE
    Libro (
        -- la lunghezza dell'ISBN può essere 10 o 13 
        ISBN VARCHAR(13) PRIMARY KEY,
        titolo VARCHAR(255) NOT NULL,
        autore VARCHAR(255),
        editore VARCHAR(255),
        anno YEAR,
        genere VARCHAR(100),
        descrizione TEXT, -- giusto?
        lingua VARCHAR(50),
        path_copertina VARCHAR(255)
    );

CREATE TABLE
    Copia (
        ID INT PRIMARY KEY AUTO_INCREMENT,
        ISBN CHAR(13) NOT NULL,
        proprietario VARCHAR(255) NOT NULL,
        -- utile se un utente vuole mettere tutta la lista dei suoi libri nella piattaforma perché gli altri possano vedere che libri ha, senza però che risultino disponibili per essere scambiati
        disponibile BOOLEAN DEFAULT TRUE,
        condizioni ENUM (
            'nuovo',
            'come nuovo',
            'usato ma ben conservato',
            'usato',
            'danneggiato'
        ),
        FOREIGN KEY (ISBN) REFERENCES Libro (ISBN),
        FOREIGN KEY (proprietario) REFERENCES Utente (email)
    );

CREATE TABLE
    Desiderio (
        email VARCHAR(255),
        ISBN CHAR(13),
        PRIMARY KEY (email, ISBN),
        FOREIGN KEY (email) REFERENCES Utente (email),
        FOREIGN KEY (ISBN) REFERENCES Libro (ISBN)
    );

CREATE TABLE
    Scambio (
        -- ID al posto di pk(emailProponente, emailAccettatore, idCopiaProp, idCopiaAcc, dataProposta)
        ID INT PRIMARY KEY AUTO_INCREMENT,
        emailProponente VARCHAR(255) NOT NULL,
        emailAccettatore VARCHAR(255) NOT NULL,
        idCopiaProp INT NOT NULL,
        idCopiaAcc INT NOT NULL,
        dataProposta DATE DEFAULT CURRENT_DATE,
        dataConclusione DATE,
        FOREIGN KEY (emailProponente) REFERENCES Utente (email),
        FOREIGN KEY (emailAccettatore) REFERENCES Utente (email),
        FOREIGN KEY (idCopiaProp) REFERENCES Copia (ID),
        FOREIGN KEY (idCopiaAcc) REFERENCES Copia (ID)
    );

CREATE TABLE
    Recensione (
        -- email del recensito derivata dalla tabella scambio
        emailRecensore VARCHAR(255),
        idScambio INT,
        dataPubblicazione DATE DEFAULT CURRENT_DATE,
        valutazione TINYINT UNSIGNED CHECK (
            valutazione >= 1
            AND valutazione <= 5
        ),
        contenuto TEXT, -- 65k caratteri
        FOREIGN KEY (emailRecensore) REFERENCES Utente (email),
        FOREIGN KEY (idScambio) REFERENCES Scambio (ID),
        PRIMARY KEY (emailRecensore, idScambio)
    );

CREATE TABLE
    Follow (
        emailSeguace VARCHAR(255),
        emailSeguito VARCHAR(255),
        PRIMARY KEY (emailSeguito, emailSeguace),
        FOREIGN KEY (emailSeguace) REFERENCES Utente (email),
        FOREIGN KEY (emailSeguito) REFERENCES Utente (email)
    );

CREATE TABLE
    Immagine (
        path VARCHAR(255) PRIMARY KEY,
        libro CHAR(13) NOT NULL,
        isCopertina BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (libro) REFERENCES Libro (ISBN)
    );