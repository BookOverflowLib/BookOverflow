-- Table: Utente
CREATE TABLE Utente (
    email VARCHAR(255) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100),
    cognome VARCHAR(100),
    citta VARCHAR(100)
);

-- Table: Libri
CREATE TABLE Libri (
    isbn CHAR(13) PRIMARY KEY, -- ISBN is typically 13 characters
    titolo VARCHAR(255) NOT NULL,
    autore VARCHAR(255),
    editore VARCHAR(255),
    anno INT,
    genere VARCHAR(100),
    lingua VARCHAR(50)
);

-- Table: Copia
CREATE TABLE Copia (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    ISBN CHAR(13),
    condizioni VARCHAR(255),
    disponibile BOOLEAN DEFAULT TRUE, -- Derived column for simplicity

    FOREIGN KEY (ISBN) REFERENCES Libri(isbn)
);

-- Table: Desiderati
CREATE TABLE Desiderati (
    email VARCHAR(255),
    ISBN CHAR(13),

    PRIMARY KEY (email, ISBN),
    FOREIGN KEY (email) REFERENCES Utente(email),
    FOREIGN KEY (ISBN) REFERENCES Libri(isbn)
);

-- Table: Posseduti
CREATE TABLE Posseduti (
    email VARCHAR(255),
    IdCopia INT,

    PRIMARY KEY (email, IdCopia),
    FOREIGN KEY (email) REFERENCES Utente(email),
    FOREIGN KEY (IdCopia) REFERENCES Copia(ID)
);

-- Table: Scambio
CREATE TABLE Scambio (
    emailProponente VARCHAR(255),
    emailAccettatore VARCHAR(255),
    IdCopia1 INT,
    IdCopia2 INT,
    data
