CREATE TABLE Utente (
    email VARCHAR(255) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL, -- Password hashing will be done in PHP
    password_salt VARCHAR(255) NOT NULL, 
    nome VARCHAR(100),
    cognome VARCHAR(100),
    citta VARCHAR(100)
);

CREATE TABLE Libro (
    isbn CHAR(13) PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    autore VARCHAR(255),
    editore VARCHAR(255),
    anno INT,
    genere VARCHAR(100),
    lingua VARCHAR(50)
);

CREATE TABLE Copia (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    ISBN CHAR(13),
    disponibile BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (ISBN) REFERENCES Libro(isbn)
    --condizioni VARCHAR(255),   -- Dobbiamo decidere i valori dell'ENUM
);

CREATE TABLE Desiderati (
    email VARCHAR(255),
    ISBN CHAR(13),

    PRIMARY KEY (email, ISBN),
    FOREIGN KEY (email) REFERENCES Utente(email),
    FOREIGN KEY (ISBN) REFERENCES Libro(isbn)
);

CREATE TABLE Posseduti (
    email VARCHAR(255),
    IdCopia INT,

    PRIMARY KEY (IdCopia),  -- Credo convenga lasciare solo IDcopia come chiave primaria, per evitare che Scambio abbia una chiave primaria ancora più lunga
    FOREIGN KEY (email) REFERENCES Utente(email),
    FOREIGN KEY (IdCopia) REFERENCES Copia(ID)
);

CREATE TABLE Scambio (
    emailProponente VARCHAR(255),
    emailAccettatore VARCHAR(255),
    IdCopia1 INT,
    IdCopia2 INT,
    dataProposta DATE, 
    DataConclusione DATE, 

    PRIMARY KEY (emailProponente, emailAccettatore, IdCopia1, IdCopia2, dataProposta),
    FOREIGN KEY (emailProponente) REFERENCES Utente(email),
    FOREIGN KEY (emailAccettatore) REFERENCES Utente(email),
    FOREIGN KEY (IdCopia1) REFERENCES Copia(ID),
    FOREIGN KEY (IdCopia2) REFERENCES Copia(ID)
);