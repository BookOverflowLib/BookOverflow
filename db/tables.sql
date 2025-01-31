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
        -- non è necessario memorizzare il salt in quanto la funzione password_hash() lo genera automaticamente e poi la funzione password_verify() sarà capace di verificarlo partendo dal risultato di password_hash()
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
        -- Google Books API spesso restituisce ISBN malformati, come "PARAMS=MINN:319510008464605"
        -- Motivo per cui abbiamo modificato l'SQL per accettare ISBN più lunghi ed a lunghezza variabile, piuttosto di far fallire gli inserimenti
        ISBN VARCHAR(50) PRIMARY KEY,
        titolo VARCHAR(255) NOT NULL,
        autore VARCHAR(255),
        editore VARCHAR(255),
        anno YEAR,
        genere VARCHAR(100),
        descrizione TEXT,
        lingua VARCHAR(50),
        path_copertina VARCHAR(255)
    );

CREATE TABLE
    Copia (
        ID INT PRIMARY KEY AUTO_INCREMENT,
        ISBN VARCHAR(50) NOT NULL,
        proprietario VARCHAR(255) NOT NULL,
        disponibile BOOLEAN DEFAULT TRUE,
        condizioni ENUM (
            'nuovo',
            'come nuovo',
            'usato ma ben conservato',
            'usato',
            'danneggiato'
        ),
        UNIQUE (ISBN, proprietario, condizioni),
        FOREIGN KEY (ISBN) REFERENCES Libro (ISBN),
        FOREIGN KEY (proprietario) REFERENCES Utente (email) ON DELETE CASCADE
    );

CREATE TABLE
    Desiderio (
        email VARCHAR(255),
        ISBN VARCHAR(50),
        UNIQUE (email, ISBN),
        PRIMARY KEY (email, ISBN),
        FOREIGN KEY (email) REFERENCES Utente (email) ON DELETE CASCADE,
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
        stato ENUM ('in attesa', 'accettato', 'rifiutato') DEFAULT 'in attesa',
        FOREIGN KEY (emailProponente) REFERENCES Utente (email) ON DELETE CASCADE,
        FOREIGN KEY (emailAccettatore) REFERENCES Utente (email) ON DELETE CASCADE,
        FOREIGN KEY (idCopiaProp) REFERENCES Copia (ID) ON DELETE CASCADE,
        FOREIGN KEY (idCopiaAcc) REFERENCES Copia (ID) ON DELETE CASCADE
    );

CREATE TABLE
    Recensione (
        -- email del recensore derivata dalla tabella scambio
        emailRecensito VARCHAR(255),
        idScambio INT,
        dataPubblicazione DATE DEFAULT CURRENT_DATE,
        valutazione TINYINT UNSIGNED CHECK (
            valutazione >= 1
            AND valutazione <= 5
        ),
        contenuto TEXT, -- 65k caratteri
        FOREIGN KEY (emailRecensito) REFERENCES Utente (email) ON DELETE CASCADE,
        FOREIGN KEY (idScambio) REFERENCES Scambio (ID) ON DELETE CASCADE,
        PRIMARY KEY (emailRecensito, idScambio)
    );

-- ==================================================================================
-- ================== DUMP DATA =====================================================
-- ==================================================================================
--
-- Dump dei dati per la tabella `Utente`
--
INSERT INTO
    `Utente` (
        `email`,
        `password_hash`,
        `username`,
        `nome`,
        `cognome`,
        `provincia`,
        `comune`,
        `path_immagine`,
        `generi_preferiti`
    )
VALUES
    (
        'admin@admin.com',
        '$2y$10$5TzwqibmBWvYzA77fWKLMO9v3bEWSMNM7c0Cj0R2Y8J6JkYyEleZ2',
        'admin',
        'admin',
        'admin',
        '28',
        '28060',
        'https://fastly.picsum.photos/id/532/500/500.jpg?hmac=gX2RSOOXKSV31haNz1jMrPzXCsx0bVJ_YV3v5GyuyOU',
        NULL
    ),
    (
        'ale.berna@gmail.com',
        '$2y$10$Z9xb/y0LNXB0KrUkzU0Px.eshf6IswvKFzzDrO4tmEsYfGX053Tma',
        'AleBerna',
        'Alessandro',
        'Bernardello',
        '28',
        '28019',
        'https://fastly.picsum.photos/id/476/500/500.jpg?hmac=E_lHvPgnl3L-QZV8fPpKBbubsR_fOU6B8trnFz_0BsU',
        '[\"pets\",\"antiques & collectibles\",\"computers\",\"games & activities\",\"education\",\"technology & engineering\"]'
    ),
    (
        'ale.math@gmail.com',
        '$2y$10$GlCCnNLkKj9TuFcn3MPszeO0MbB7kQv./.YQLIfE0YBj5MSzsTpCK',
        'Aleena',
        'Aleena',
        'Mathew',
        '26',
        '26056',
        'https://fastly.picsum.photos/id/985/500/500.jpg?hmac=eWDl4YCKkTZg7VCIhqkkovbeNVY1e86vjOJCo-ClLBQ',
        '[\"literary criticism\",\"philosophy\",\"young adult fiction\",\"psychology\",\"technology & engineering\"]'
    ),
    (
        'elena@elena.com',
        '$2y$10$eBiV6fCElL4sGls8VG2hI.PpIr8HBMwCoxqsQj5zTMf1GrBsHNgqG',
        'elena',
        'Elena',
        'Bazzan',
        '28',
        '28103',
        'https://fastly.picsum.photos/id/448/500/500.jpg?hmac=jDlZz301nk1T9BqNuqXq63P3Pc56oXUs9MkN4pU1oQ8',
        '[\"antiques & collectibles\",\"mathematics\",\"bibles\",\"body, mind & spirit\",\"comics & graphic novels\",\"crafts & hobbies\",\"self-help\",\"sports & recreation\"]'
    ),
    (
        'frafra35@pucci.com',
        '$2y$10$q78EI98jS/6zxk5V6ievZuA.UErAgoP2N59cMvmHJGH9bWdfQTWG6',
        'Frafra35',
        'Franchino',
        'Bunella',
        '104',
        '104020',
        'https://fastly.picsum.photos/id/447/500/500.jpg?hmac=uxBsYFIaDBUr4b_bc_5lo0gPE0uy5J0JPAPZuVOCmV0',
        '[\"mathematics\",\"medical\",\"business & economics\",\"computers\",\"photography\",\"humor\",\"travel\"]'
    ),
    (
        'gingin@pucci.com',
        '$2y$10$/qE1hfzZuY7/ixInrn6Ed.CcHuRo3SXdijU7gS.uvRrJ.K/1yN.wq',
        'Gingin',
        'Virginia',
        'Martin ',
        '40',
        '40037',
        'https://fastly.picsum.photos/id/323/500/500.jpg?hmac=b24VENFLL59JsHRUlOIBhRigKfk84EllIDCVfa_alUQ',
        '[\"nature\",\"performing arts\",\"cooking\",\"photography\",\"crafts & hobbies\",\"transportation\",\"travel\",\"language arts & disciplines\"]'
    ),
    (
        'luca.rib@gmail.com',
        '$2y$10$i5u2oOdVfJVoTUdvL7o2kuzunlOilxwTRGvN1OFWWXKzQ7oYUsjOy',
        'LucaRib',
        'Luca',
        'Ribon',
        '27',
        '27035',
        'https://fastly.picsum.photos/id/395/500/500.jpg?hmac=sDyOxcZuKzogaz3HcaBSf2NZAUQNkkSyMLWx5n_31rM',
        '[\"business & economics\",\"photography\",\"education\",\"law\",\"young adult nonfiction\"]'
    ),
    (
        'mari123@gmail.com',
        '$2y$10$PlfGCJbmRjd/GcvKa4c05.9S5r2YZOcTRbxrVW02aVAhWaz5H3xO2',
        'mari',
        'mario',
        'rossa',
        '28',
        '28060',
        'https://fastly.picsum.photos/id/405/500/500.jpg?hmac=kALs-x5LPK5UEWmXyaLbtUhr2eBuZotJlNJdzm0wlXk',
        NULL
    ),
    (
        'mariabranch@pucci.com',
        '$2y$10$A8qSiVItagJhJ/yeXswL.OtmdZ7qqkuUy0iTxaBR5AFUZzKbcvYce',
        'MaryB',
        'Maria',
        'Branchini',
        '103',
        '103017',
        'https://fastly.picsum.photos/id/127/500/500.jpg?hmac=jNxr2TVan5LSjK5AmzUlD8KcmxSLujqpw3om8uUVt04',
        '[\"nature\",\"crafts & hobbies\",\"psychology\",\"fiction\",\"self-help\",\"gardening\",\"young adult fiction\"]'
    ),
    (
        'mariorog123@gmail.com',
        '$2y$10$qmD5HTrzk7UdW02vEORWOOIem30soIUl0GwVtIKv.suRuvrlqEuPy',
        'marior',
        'marco',
        'roggiani',
        '28',
        '28060',
        'https://fastly.picsum.photos/id/547/500/500.jpg?hmac=rhZzPey2iu0a-PB3oKt2rTWHwwSw52CY1038bTQD8JM',
        NULL
    ),
    (
        'mariorossi@gmail.com',
        '$2y$10$93zBR9508aDuno1aVsSVPeBrKclt1ah65PxisPIR0uTKgROylcJm6',
        'adsfdf',
        'mario',
        'rossi',
        '6',
        '6001',
        'https://fastly.picsum.photos/id/83/500/500.jpg?hmac=fGXCjBxs-hII0MnNxmJVTNNzVNo0GE504vLn0o-UThw',
        '[\"fiction\",\"comics & graphic novels\"]'
    ),
    (
        'matteobazzan333@gmail.com',
        '$2y$10$qpc35b9q8tMBK9I.HxD7oe5/j49hy4Rg04KvaubpRdHP5/Y8XXYPm',
        'bazz',
        'Matteo',
        'Bazzan',
        '28',
        '28103',
        'https://fastly.picsum.photos/id/832/500/500.jpg?hmac=SkdEJ2gXPMFuNbkce48FXexYwTHoDb39z2sJQrpfTiY',
        NULL
    ),
    (
        'noah95@pucci.com',
        '$2y$10$RbkBzgaHTWXbMpFw.UpH6OnoprJLmZ8mq8YNdXp5A2ldebeLv26fO',
        'Noahh',
        'Noah',
        'Gordon',
        '72',
        '72027',
        'https://fastly.picsum.photos/id/645/500/500.jpg?hmac=2nNGRjcMTBqW8BiCQk8Jl-ofYXQGNrvr_B4G1QgBKi4',
        '[\"comics & graphic novels\",\"fiction\",\"foreign language study\",\"games & activities\",\"health & fitness\",\"travel\",\"true crime\"]'
    ),
    (
        'pinetto@pino.com',
        '$2y$10$NkBL5IcUTz3SObv2Ei2Iz.FU7DkbGGLdycYsMQC/BI8EOTF8ga7de',
        'piiino',
        'Pino',
        'Fabris',
        '21',
        '21019',
        'https://fastly.picsum.photos/id/1076/500/500.jpg?hmac=CGGoT4Ur1TqLpohrsZ4eMVQkaPxVkHLq2TNwpzojTLc',
        '[\"pets\",\"photography\",\"family & relationships\",\"health & fitness\"]'
    ),
    (
        'sarah@pucci.com',
        '$2y$10$S9sV1GyRu12WNEOWXlsAreEUpCMmeUYREPlXB0c0oxbFBN69vimK2',
        'Saretta',
        'Sarah',
        'Fru',
        '87',
        '87021',
        'https://fastly.picsum.photos/id/2/500/500.jpg?hmac=vAjkLR4Y91mPCNgWRVLc2dF_fuBnLtQTdj9gHrBZz2M',
        '[\"biography & autobiography\",\"business & economics\",\"pets\",\"crafts & hobbies\",\"political science\",\"science\",\"foreign language study\",\"true crime\",\"young adult fiction\"]'
    ),
    (
        'user@user.com',
        '$2y$10$IPlcDJlhRhY/PRAvtSeuDuLyV4FXrUDHNKQhyVNl/Y1CKbDhzxiFW',
        'user',
        'user',
        'user',
        '28',
        '28060',
        'https://fastly.picsum.photos/id/360/500/500.jpg?hmac=3fvPpl_8_Y4RdnU4UJiD3xAYDNpQ4eZnAbtNBUw6d-w',
        '[\"language arts & disciplines\",\"biography & autobiography\",\"literary criticism\",\"cooking\",\"education\",\"social science\"]'
    ),
    (
        'utente@utente.com',
        '$2y$10$mfyfq7sfWJkqD5WEiTylSeKFZkbriQ.l6DFF8gEssiU2xj0xa8Arq',
        'utente',
        'utente',
        'utente',
        '24',
        '24057',
        'https://fastly.picsum.photos/id/277/500/500.jpg?hmac=4iX99jja_8ORqd4fP4h3Zk8VjakiUFEs16c76GP6GhE',
        '[\"self-help\",\"body, mind & spirit\",\"literary criticism\",\"fiction\",\"mathematics\",\"young adult fiction\",\"technology & engineering\"]'
    );

--
-- Dump dei dati per la tabella `Libro`
--
INSERT INTO
    `Libro` (
        `ISBN`,
        `titolo`,
        `autore`,
        `editore`,
        `anno`,
        `genere`,
        `descrizione`,
        `lingua`,
        `path_copertina`
    )
VALUES
    (
        '0760319545',
        'The Complete Book of the World Rally Championship',
        'Henry Hope-Frost,John Davenport',
        'Motorbooks',
        '2004',
        'Automobile racing drivers',
        'For 30 years some of the most talented and bravest drivers have battled across the continents of the world to claim what is arguably motorsport\'s toughest prize: the World Rally Championship. Now a multi-million dollar, global technology battle and terrestrial television phenomenon played out over the frozen wastes of Finland, the dusty plains of Australia and the sun-kissed mountain roads of Corsica, the WRC has reached its 30th birthday. This book celebrates that important milestone and paints an exhaustively detailed picture of the people and personalities who have shaped this great sport. The Complete Book of the World Rally Champions provides a biographical account of the 65 men who have won at least one World Championship Rally since 1973. The biographies are compiled by the sport\'s leading writers and historians and complemented by stunning photography. The book includes a detailed and accurate statistical career record of each driver, plus highlights of all the significant cars.',
        'en',
        'https://books.google.com/books/content?id=gXm5qXLnF1oC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1451685556',
        'Anna Karenina',
        'Leo Tolstoy',
        'Simon and Schuster',
        '2016',
        'Fiction',
        'Anna Karenina is a powerful meditation on love and marriage, envy and retribution, and the desire for happiness. Considered one of the greatest novels ever written, Anna Karenina is the story of Anna, her marriage to Karenin, a high-ranking government minister, and her affair with Vronsky, a wealthy and charismatic military officer. This impossible and destructive triangle is set against the courtship and marriage of Levin, a melancholy landowner, and Kitty, a beautiful young woman was also initially sought after by Vronsky. While Anna looks for happiness through love—rashly defying the conventions of Russian society by leaving her husband and son to live with her lover, which finds her condemned and ostracized by her peers and prone to fits of jealousy that alienate Vronsky—Levin embarks on his own search for spiritual fulfillment through marriage, family, and hard work. Surrounding these two central plot threads are dozens of characters whom Tolstoy seamlessly weaves together, making Anna Karenina a breathtaking overview of nineteenth-century Russian society. This edition includes: -A concise introduction that gives the reader important background information -A chronology of the author’s life and work -A timeline of significant events that provides the book’s historical context -An outline of key themes and plot points to guide the reader’s own interpretations -Detailed explanatory notes -Critical analysis and modern perspectives on the work -Discussion questions to promote lively classroom and book group interaction -A list of recommended related books and films to broaden the reader’s experience Simon & Schuster Enriched Classics offer readers affordable editions of great works of literature enhanced by helpful notes and insightful commentary. The scholarship provided in Enriched Classics enables readers to appreciate, understand, and enjoy the world’s finest books to their full potential.',
        'en',
        'https://books.google.com/books/content?id=jqWDCgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1452182175',
        'Good for You',
        'Akhtar Nawab',
        'Chronicle Books',
        '2020',
        'Cooking',
        'Good for You: Bold Flavors with Benefits is a cookbook that infuses clean eating with rich flavor. Award-winning chef Akhtar Nawab presents 100 healthful and hearty recipes that satisfy every appetite. Inspired by his Indian heritage, Kentucky upbringing, and professional experience cooking in Mexican and Italian restaurants, these recipes are as unique as they are delicious. • Great for gluten-free, dairy-free, vegetarian, and vegan diets • Wholesome, accessible recipes that pack serious flavor into every bite • Covers basic building blocks—like vegan soubise and gluten-free bread—as well as more advanced recipes and techniques With bright, enticing photography, Good for You is a delicious pick for both amateur and seasoned home cooks. Recipes include Blueberry Ginger Smoothie, Gazpacho with Poached Shrimp, Fish Tacos with Pistachio Mole, and Dark Chocolate Almond Butter Cups with Sea Salt. • This book is for anyone who wants to eat well and feel good. • Akhtar Nawab is the chef behind Alta Calidad and Alta Calidad Taqueria in New York, and Otra Vez in New Orleans • Perfect for home cooks who want to take their clean eating to the next level with interesting spices, marinades, and methods • Add it to the shelf with books like The Skinnytaste Cookbook: Light on Calories, Big on Flavor by Gina Homolka; Salt, Fat, Acid, Heat: Mastering the Elements of Good Cooking by Samin Nosrat; and The Flavor Bible: The Essential Guide to Culinary Creativity, Based on the Wisdom of America\'s Most Imaginative Chefs by Karen Page and Andrew Dornenburg.',
        'en',
        'https://books.google.com/books/content?id=_VfZDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1477730141',
        'Guess!',
        'Emma Carlson-Berne',
        'The Rosen Publishing Group, Inc',
        '2014',
        'Juvenile Nonfiction',
        'A hypothesis is an educated guess, and this volume breaks down the necessary steps to forming a good one. Chapters focus on showing kids how to make scientific observations, how to find good sources for research, and tips for staying organized. Students will learn how to test and revise a hypothesis for a science project, and how this part of the process leads to scientific discovery.',
        'en',
        'https://books.google.com/books/content?id=qXlhDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1498531911',
        'The Moral Rights of Animals',
        'Mylan Engel,Gary Lynn Comstock',
        'Lexington Books',
        '2016',
        'Philosophy',
        'Edited by Mylan Engel Jr. and Gary Lynn Comstock, this book employs different ethical lenses, including classical deontology, libertarianism, commonsense morality, virtue ethics, utilitarianism, and the capabilities approach, to explore the philosophical basis for the strong animal rights view, which holds that animals have moral rights equal in strength to the rights of humans, while also addressing what are undoubtedly the most serious challenges to the strong animal rights stance, including the challenges posed by rights nihilism, the “kind” argument against animal rights, the problem of predation, and the comparative value of lives. In addition, contributors explore the practical import of animal rights both from a social policy standpoint and from the standpoint of personal ethical decisions concerning what to eat and whether to hunt animals. Unlike other volumes on animal rights, which focus primarily on the legal rights of animals, and unlike other anthologies on animal ethics, which tend to cover a wide variety of topics but only devote a few articles to each topic, this volume focuses exclusively on the question of whether animals have moral rights and the practical import of such rights. The Moral Rights of Animals will be an indispensable resource for scholars, teachers, and students in the fields of animal ethics, applied ethics, ethical theory, and human-animal studies, as well as animal rights advocates and policy makers interested in improving the treatment of animals.',
        'en',
        'https://books.google.com/books/content?id=vCelCwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1647573076',
        'Master He’s Overbearing Love',
        'Jiang MoXi',
        'Funstory',
        '2019',
        'Fiction',
        '\"I\'ll marry you.\" At the party where her boyfriend mocked her, he took her hand.At that moment, he slipped into the deepest recesses of her heart.Half a month after his marriage, however, his \"glamour\" with the actress hit the headlines. She flew into a rage, and he beamed.Half a year after their marriage, Little San came to their door with a big belly. She had an extraordinary skill, so he happily watched from the sidelines ...A year after his marriage, his ex-wife suddenly came back from abroad to fight him.He had strayed unscrupulously among the flowers, treating her as air.\"You were just married on a whim, and now I\'m tired of it.\" In front of his lover, he smiled elegantly. The spring wind was warm, but cold and ruthless.Having trampled on her dignity, she became the greatest joke in the world.Where does love belong when it is full of love and ends up with a drop of cinnabar tears? When her figure faded away from his gaze, at that moment, he knew that this was not the end he wanted!',
        'en',
        'https://books.google.com/books/content?id=R16-DwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1665904968',
        'Five Feet Apart',
        'Rachael Lippincott,Mikki Daughtry,Tobias Iaconis',
        'Simon and Schuster',
        '2022',
        'Juvenile Fiction',
        'Seventeen-year-olds Stella and Will, both suffering from cystic fibrosis, realize the only way to stay alive is to stay apart, but their love for each other is slowly pushing the boundaries of physical and emotional safety.',
        'en',
        'https://books.google.com/books/content?id=FTNcEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1781101582',
        'Harry Potter e la Pietra Filosofale',
        'J.K. Rowling',
        'Pottermore Publishing',
        '2015',
        'Juvenile Fiction',
        'Harry Potter è un ragazzo normale, o quantomeno è convinto di esserlo, anche se a volte provoca strani fenomeni, come farsi ricrescere i capelli inesorabilmente tagliati dai perfidi zii. Vive con loro al numero 4 di Privet Drive: una strada di periferia come tante, dove non succede mai nulla fuori dall’ordinario. Finché un giorno, poco prima del suo undicesimo compleanno, riceve una misteriosa lettera che gli rivela la sua vera natura: Harry è un mago e la Scuola di Magia e Stregoneria di Hogwarts è pronta ad accoglierlo...',
        'it',
        'https://books.google.com/books/content?id=9CJWTbd-RYoC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '1781102120',
        'Harry Potter e la Camera dei Segreti',
        'J.K. Rowling',
        'Pottermore Publishing',
        '2015',
        'Juvenile Fiction',
        'A Hogwarts il nuovo anno scolastico s’inaugura all’insegna di fatti inquietanti: strane voci riecheggiano nei corridoi e Ginny sparisce nel nulla. Un antico mistero si nasconde nelle profondità del castello e incombe ora sulla scuola, toccherà a Harry, Ron e Hermione risolvere l’enigma che si cela nella tenebrosa Camera dei Segreti...',
        'it',
        'https://books.google.com/books/content?id=H1RCT73xWlsC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '232239209X',
        'La Formula Uno è condannata a morte',
        'Noël Cavey',
        'BoD - Books on Demand',
        '2022',
        'Fiction',
        'È la storia di un\'esistenza immersa nel mondo eroico della Formula Uno. Il libro decifra il gioco drammatico. All\'inizio si tace per ricordare. Si medita, si reagisce. Le parole sono lì, cesellate, esemplari. Dicono le vertigini del tempo asseditato nello spazio dove il passato e il futuro si scontrano nell\'angoscia per formare uno strano mosaico, la Formula Uno è condannata a morte. L\'analisi è utile per meditare sugli effetti delle nostre azioni perché abbiamo una buona scusa, quella di lasciar fare. Al di là del racconto, un giorno, la verità si pone a ciascuno: sono cieco o colpevole?',
        'it',
        'https://books.google.com/books/content?id=5gRgEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '3319672207',
        'Information Systems Architecture and Technology: Proceedings of 38th International Conference on Information Systems Architecture and Technology – ISAT 2017',
        'Leszek Borzemski,Jerzy Świątek,Zofia Wilimowska',
        'Springer',
        '2017',
        'Technology & Engineering',
        'This three-volume set of books presents advances in the development of concepts and techniques in the area of new technologies and contemporary information system architectures. It guides readers through solving specific research and analytical problems to obtain useful knowledge and business value from the data. Each chapter provides an analysis of a specific technical problem, followed by the numerical analysis, simulation and implementation of the solution to the problem. The books constitute the refereed proceedings of the 2017 38th International Conference “Information Systems Architecture and Technology,” or ISAT 2017, held on September 17–19, 2017 in Szklarska Poręba, Poland. The conference was organized by the Computer Science and Management Systems Departments, Faculty of Computer Science and Management, Wroclaw University of Technology, Poland. The papers have been organized into topical parts: Part I— includes discourses on topics including, but not limited to, Artificial Intelligence Methods, Knowledge Discovery and Data Mining, Big Data, Knowledge Discovery and Data Mining, Knowledge Based Management, Internet of Things, Cloud Computing and High Performance Computing, Distributed Computer Systems, Content Delivery Networks, and Service Oriented Computing. Part II—addresses topics including, but not limited to, System Modelling for Control, Recognition and Decision Support, Mathematical Modelling in Computer System Design, Service Oriented Systems and Cloud Computing and Complex Process Modeling. Part III—deals with topics including, but not limited to, Modeling of Manufacturing Processes, Modeling an Investment Decision Process, Management of Innovation, Management of Organization.',
        'en',
        'https://books.google.com/books/content?id=g7szDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8804591382',
        'Assassinio sull\'Orient-Express',
        'Agatha Christie',
        'Edizioni Mondadori',
        '2009',
        'Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=AnyejIR0XW0C&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8809754530',
        'Piccole donne',
        'Louisa May Alcott',
        'Giunti Editore',
        '2010',
        'Juvenile Fiction',
        'Piccole donne è il primo dei romanzi che, sullo sfondo di un\'America ottocentesca, racconta la storia delle sorelle March, bambine e poi fanciulle e spose, fra cui emerge Jo, la sensibile protagonista di questo libro. Il romanzo scritto da Louisa Alcott ha passato i cento anni, ma conserva tuttora una sua fresca vitalità. Titolo originale: Little women.',
        'it',
        'https://books.google.com/books/content?id=URRtDoBczZIC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8811815290',
        'Finché il caffè è caldo',
        'Toshikazu Kawaguchi',
        'Garzanti',
        '2020',
        'Fiction',
        '«Oltre un milione di copie vendute.» The Bookseller «Un libro tenero, che incita a superare le difficoltà con l\'anima aperta. » La Lettura - Corriere della Sera - Annachiara Sacchi «Un balsamo per le nostre anime ferite. » Panorama «La sorpresa del passaparola: ha venduto un milione di copie vendute in Giappone e 100.000 in Italia. » Donna Moderna - Alessandra Cipelli «Un romanzo magico quasi come un film di Hayao Miyazaki.» Cosmopolitan «Una meravigliosa lettura su una caffetteria in cui tutto è possibile.» Publishers Weekly Un tavolino, un caffè, una scelta. Basta solo questo per essere felici. ECCO LE 5 REGOLE DA SEGUIRE: 1. Sei in una caffetteria speciale. C’è un unico tavolino e aspetta solo te. 2. Siediti e attendi che il caffè ti venga servito. 3. Tieniti pronto a rivivere un momento importante della tua vita. 4. Mentre lo fai ricordati di gustare il caffè a piccoli sorsi. 5. Non dimenticarti la regola fondamentale: non lasciare per alcuna ragione che il caffè si raffreddi. In Giappone c’è una caffetteria speciale. È aperta da più di cento anni e, su di essa, circolano mille leggende. Si narra che dopo esserci entrati non si sia più gli stessi. Si narra che bevendo il caffè sia possibile rivivere il momento della propria vita in cui si è fatta la scelta sbagliata, si è detta l’unica parola che era meglio non pronunciare, si è lasciata andare via la persona che non bisognava perdere. Si narra che con un semplice gesto tutto possa cambiare. Ma c’è una regola da rispettare, una regola fondamentale: bisogna assolutamente finire il caffè prima che si sia raffreddato. Non tutti hanno il coraggio di entrare nella caffetteria, ma qualcuno decide di sfidare il destino e scoprire che cosa può accadere. Qualcuno si siede su una sedia con davanti una tazza fumante. Fumiko, che non è riuscita a trattenere accanto a sé il ragazzo che amava. Kotake, che insieme ai ricordi di suo marito crede di aver perso anche sé stessa. Hirai, che non è mai stata sincera fino in fondo con la sorella. Infine Kei, che cerca di raccogliere tutta la forza che ha dentro per essere una buona madre. Ognuna di loro ha un rimpianto. Ognuna di loro sente riaffiorare un ricordo doloroso. Ma tutti scoprono che il passato non è importante, perché non si può cambiare. Quello che conta è il presente che abbiamo tra le mani. Quando si può ancora decidere ogni cosa e farla nel modo giusto. La vita, come il caffè, va gustata sorso dopo sorso, cogliendone ogni attimo. Finché il caffè è caldo è diventato un caso editoriale in Giappone, dove ha venduto oltre un milione di copie. Poi ha conquistato tutto il mondo e le classifiche europee a pochi giorni dall’uscita. Un romanzo pieno di fascino e mistero sulle occasioni perdute e sull’importanza di quelle ancora da vivere.',
        'it',
        'https://books.google.com/books/content?id=BobUDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8820093839',
        'Il mio gatto lo sa',
        'Helga Hofmann',
        'SPERLING & KUPFER',
        '2015',
        'Pets',
        'In questa preziosa guida troverai tutte le risposte ai tuoi dubbi e le soluzioni agli eventuali problemi. Le schede con i concetti di base, i consigli sull\'alimentazione e la salute dei gatti e il breve corso di lingua felina ne fanno uno strumento di informazione completo per abitare con un gatto, curarlo, capirlo e agire nel modo migliore per lui e per tutti gli altri abitanti della casa.',
        'it',
        'https://books.google.com/books/content?id=BeCqCgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8822762576',
        'Il segreto di Medusa',
        'Hannah Lynn',
        'Newton Compton Editori',
        '2021',
        'Fiction',
        'Tutta un’altra storia Radiosa, innocente, la più pura tra le sacerdotesse di Atena. La bellezza di Medusa va ben oltre quella dei semplici mortali. Per questo, quando lo sguardo colmo di lussuria del dio Poseidone cade su di lei, l’unico luogo in cui spera di trovare rifugio è il sacro tempio della protettrice dei greci. Ma nessuno può sfuggire a un dio. E la divina Atena, signora delle arti e della guerra, non avrà pietà per colei che ha profanato la sua casa. Poco importa che Medusa, violata nel corpo e nello spirito contro la propria volontà, implori il suo perdono. Da questo momento il male che le è stato inflitto diventerà la sua corazza e abbraccerà l’oscurità, in esilio, perché chiunque altro le ha voltato le spalle. Si trasformerà nel mostro che gli altri hanno deciso che doveva essere. Nel frattempo, un giovane di nome Perseo si appresta a partire con la missione di uccidere Medusa. La storia dell’eroe Perseo e del mostro Medusa è stata raccontata molte volte. Questa è un’altra storia. In un tempo in cui gli dèi camminano tra i mortali, il confine tra la gloria e l’infamia è estremamente labile. Ma ogni mito ha bisogno di eroi e di mostri. Bestseller in Inghilterra La leggenda vuole fosse un mostro, ma la verità è un’altra La storia arriva distorta. Quella di Medusa è rimasta sepolta per lungo tempo. È arrivato il momento di sapere la verità. Colei che pietrifica con un solo sguardo nasconde un segreto che nessuno conosce «Che splendido personaggio questa Medusa! La scrittrice ci offre una donna dalla bellezza radiosa, tuttavia la vera bellezza della sacerdotessa non è nel suo aspetto, ma nel suo cuore.» «Il mito dell’orrenda Gorgone è arrivato a noi senza rivelare l’origine del Male, finalmente la verità trionfa! Povera Medusa, evviva Medusa!» Hannah Lynn È una scrittrice di successo internazionale. È nata e cresciuta nelle Cotswold, le splendide colline al centro dell’Inghilterra, prima di laurearsi e diventare un’insegnante. Attualmente vive sulle Alpi austriache.',
        'it',
        'https://books.google.com/books/content?id=IxlOEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8827225250',
        'I Tarocchi',
        'Oswald Wirth',
        'Edizioni Mediterranee',
        '2014',
        'Body, Mind & Spirit',
        'Contiene un meraviglioso mazzo di 22 carte (gli Arcani Maggiori) stampato a 6 colori Wirth esamina e spiega tutti gli aspetti occulti ed esoterici dei Tarocchi, risalendo alle origini della loro complessa simbologia e presentandone il lato alchemico, quello astrologico, quello magico-religioso e quello esoterico moderno.Certamente, i Tarocchi possono venire usati anche come strumento divinatorio, e l’autore infatti illustra anche questo loro aspetto, chiarendo i molteplici significati di ciascun Arcano e il modo in cui farne uso, tuttavia questa non è che una faccia, forse la più popolare, di un poliedro. L’opera di Oswald Wirth dedicata ai Tarocchi è la più famosa mai pubblicata su questo argomento; ed è anche la più importante, la più seria e la più completa. Questo spiega come essa abbia mantenuto inalterata nel tempo la sua validità, nonostante che numerosi autori abbiano cercato di emularla.La si può, in realtà, definire un’opera ispirata. È noto infatti come Oswald Wirth, vissuto tra la seconda metà dell’Ottocento e i primi del Novecento, fosse un iniziato.Affiliato alle principali società segrete, Wirth ebbe il merito di saper recepire e sintetizzare il pensiero e i principi delle più importanti correnti iniziatiche, servendosene per l’interpretazione dei segreti della Grande Opera, e dedicandosi allo studio dell’Alchimia, della Cabala e dei Tarocchi.Conscio del valore universale del simbolo, Oswald Wirth riteneva di poter ricondurre l’insegnamento delle varie scuole esoteriche ad una matrice comune, mediante l’impiego di una simbologia generalizzata, derivata direttamente dai concetti archetipici del pensiero magico.Egli, pertanto, esamina i Tarocchi come un libro muto, potenzialmente in grado di rispondere a tutte le domande. Non per nulla gli occultisti affermano che – a saper cercare – nei Tarocchi si possono trovare i segreti dell’universo, il ritmo nascosco che guida la danza della vita.Approfondendo lo studio dei Tarocchi si troveranno significati sempre nuovi e sempre, comunque, adeguati al livello iniziatico ed evolutivo di colui il quale vi si dedica.',
        'it',
        'https://books.google.com/books/content?id=wPWXCwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8828103019',
        'I tre moschettieri',
        'Alexandre Dumas',
        'E-text',
        '2022',
        'Fiction',
        'I tre moschettieri (Les trois mousquetaires) è un romanzo d\'appendice scritto dal francese Alexandre Dumas con la collaborazione di Auguste Maquet nel 1844 e pubblicato originariamente a puntate sul giornale \"Le Siècle\". È uno dei romanzi più famosi e tradotti della letteratura francese e ha dato inizio ad una trilogia, che comprende Vent\'anni dopo (1845) e Il visconte di Bragelonne (1850).',
        'it',
        'https://books.google.com/books/content?id=DutmEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8830448036',
        'Enzo Ferrari',
        'Leo Turrini',
        'Longanesi',
        '2017',
        'Biography & Autobiography',
        'Enzo Ferrari è l’uomo che con le sue automobili ha creato un vero e proprio mito, un protagonista assoluto di quella stagione irripetibile che ha segnato il passaggio dell’Italia da Paese contadino a potenza industriale. Sulla leggenda del creatore del «cavallino rampante» c’è molto da raccontare e ancora tanto da scoprire. I trionfi e le sconfitte, i drammi, la politica e soprattutto gli amori. Leo Turrini ricostruisce l’avventura pubblica e privata di un uomo che ha segnato un’epoca, svelandone i sogni e i tormenti: le speranze della giovinezza, la tragedia di un figlio perso troppo presto, il segreto di un erede amatissimo, il difficile rapporto con la fede, le polemiche con il Vaticano, fino all’ultimo amore. Ma anche gli incontri con le star dello spettacolo, i rapporti con i leggendari campioni, da Nuvolari a Niki Lauda, e le relazioni privilegiate con personaggi che hanno fatto la storia d’Italia, da Mussolini a Togliatti, da Pertini a Gianni Agnelli.',
        'it',
        'https://books.google.com/books/content?id=ul21DQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8831804499',
        'Come le cicale',
        'Fiore Manni',
        'Rizzoli',
        '2021',
        'Juvenile Fiction',
        'Il primo anno delle medie è terminato, eppure Teresa si sente ancora una bambina, con i capelli sempre arruffati e le ginocchia perennemente sbucciate. È come se fosse rimasta indietro. E quando arriva alla casa al mare dove ogni anno trascorre le vacanze, scopre con stupore e tristezza che anche lì è destinata a sentirsi quella fuori posto, inadeguata: le compagne di gioco di una vita ora sono interessate solo allo smalto e ai ragazzi, il rapporto con il suo migliore amico è improvvisamente complicato, e tutti sembrano essere cresciuti tranne lei. Dov\'è la sfavillante Terry, la versione di sé sicura e matura, che aspetta da tempo?, si chiede ogni giorno guardandosi allo specchio. Ma proprio quando Teresa si è ormai rassegnata a trascorrere un\'estate terribile, ecco comparire Agata. Dolce e forte al tempo stesso, sincera, gentile e bellissima, conquista subito l\'affetto e la simpatia di Teresa. E molto più: le fa battere forte il cuore, sudare le mani, sognare il primo bacio... Un romanzo che con delicatezza e sincerità cattura la protagonista proprio mentre si trasforma e si cerca, divisa tra la paura di conoscersi e il desiderio fortissimo di emozionarsi.',
        'it',
        'https://books.google.com/books/content?id=wHorEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8832104849',
        'Macchina e uomo nella società industriale',
        'Franco Ferrarotti',
        'Bibliotheka Edizioni',
        '2024',
        'Social Science',
        'La macchina funziona. L\'uomo pensa, esiste, dubita. La Macchina la si può accendere, spegnere, riaccendere. Gli esseri umani sono vivi o morti.',
        'it',
        'https://books.google.com/books/content?id=rY3pEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8834741587',
        '1984',
        'George Orwell',
        'Fanucci Editore',
        '2021',
        'Fiction',
        '1984. Il mondo è diviso in tre grandi Stati che si fronteggiano, minacciando la Terza guerra mondiale: Oceania, Eurasia ed Estasia. L’Oceania, la cui capitale è Londra, è governata dal Grande Fratello, che ogni cosa conosce e controlla. Con telecamere appostate ovunque, spia continuamente nelle case di ogni abitante e il suo braccio armato, la Psicopolizia, interviene al minimo sospetto. Ogni cosa è permessa, non c’è legge scritta che tenga. Niente, apparentemente, sembrerebbe proibito a parte pensare, amare, godersi le cose... ossia vivere. Questo è possibile solo secondo le regole del Grande Fratello. Dal loro rifugio, in uno scenario deprimente, solo Winston Smith e Julia lottano con tutte le forze per mantenere un poco di umanità...',
        'it',
        'https://books.google.com/books/content?id=SZgQEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8835700159',
        'Tutto chiede salvezza',
        'Daniele Mencarelli',
        'Edizioni Mondadori',
        '2020',
        'Fiction',
        'Ha vent\'anni Daniele quando, in seguito a una violenta esplosione di rabbia, viene sottoposto a un TSO: trattamento sanitario obbligatorio. È il giugno del 1994, un\'estate di Mondiali. Al suo fianco, i compagni di stanza del reparto psichiatria che passeranno con lui la settimana di internamento coatto: cinque uomini ai margini del mondo. Personaggi inquietanti e teneri, sconclusionati eppure saggi, travolti dalla vita esattamente come lui. Come lui incapaci di non soffrire, e di non amare a dismisura. Dagli occhi senza pace di Madonnina alla foto in bianco e nero della madre di Giorgio, dalla gioia feroce di Gianluca all\'uccellino resuscitato di Mario. Sino al nulla spinto a forza dentro Alessandro. Accomunati dal ricovero e dal caldo asfissiante, interrogati da medici indifferenti, maneggiati da infermieri spaventati, Daniele e gli altri sentono nascere giorno dopo giorno un senso di fratellanza e un bisogno di sostegno reciproco mai provati. Nei precipizi della follia brilla un\'umanità creaturale, a cui Mencarelli sa dare voce con una delicatezza e una potenza uniche. Dopo l\'eccezionale vicenda editoriale del suo libro di esordio - otto edizioni e una straordinaria accoglienza critica (premio Volponi, premio Severino Cesari opera prima, premio John Fante opera prima) -, Daniele Mencarelli torna con una intensa storia di sofferenza e speranza, interrogativi brucianti e luminosa scoperta. E mette in scena la disperata, rabbiosa ricerca di senso di un ragazzo che implora salvezza: \"Salvezza. Per me. Per mia madre all\'altro capo del telefono. Per tutti i figli e tutte le madri. E i padri. E tutti i fratelli di tutti i tempi passati e futuri. La mia malattia si chiama salvezza\".',
        'it',
        'https://books.google.com/books/content?id=K6vMDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8835703891',
        'Bridgerton - 1. Il duca e io',
        'Julia Quinn',
        'Edizioni Mondadori',
        '2020',
        'Fiction',
        'Londra, 1813. Simon Arthur Henry Fitzranulph Basset, nuovo duca di Hastings ed erede di uno dei titoli più antichi e prestigiosi d\'Inghilterra, è uno scapolo assai desiderato. A dire il vero, è letteralmente perseguitato da schiere di madri dell\'alta società che farebbero di tutto pur di combinare un buon matrimonio per le loro fanciulle in età da marito. E Simon, sempre alquanto riluttante, è in cima alla lista dei loro interessi. Anche la madre di Daphne Bridgerton è indaffaratissima e intende trovare il marito perfetto per la maggiore delle sue figlie femmine, che ha già debuttato in società da un paio d\'anni e che rischia di rimanere - Dio non voglia! - zitella. Assillati ciascuno a suo modo dalle ferree leggi del \"mercato matrimoniale\", Daphne e Simon, vecchio amico di suo fratello Anthony, escogitano un piano: si fingeranno fidanzati e così saranno lasciati finalmente in pace. Ciò che non hanno messo in conto è che, ballo dopo ballo, conversazione dopo conversazione, ricordarsi che quanto li lega è solo finzione diventerà sempre più difficile. Quella che era iniziata come una recita sembra proprio trasformarsi in realtà. Una realtà tremendamente ricca di passione e coinvolgimento...',
        'it',
        'https://books.google.com/books/content?id=j1vyDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8835711509',
        'Il libro della scienza',
        'Isaac Asimov',
        'Edizioni Mondadori',
        '2021',
        'Computers',
        'All\'inizio c\'è la curiosità. Una volta risolte le esigenze pratiche, l\'uomo ha cominciato a cercare risposte \"inutili\": quanto è alto il cielo? Perché una pietra cade? È nata la scienza. Sarebbe bello poter dire che da allora la scienza e gli esseri umani vissero insieme felici e contenti. Ma non è andata così, e anche oggi sono proprio gli straordinari progressi della conoscenza e della tecnologia ad allontanare le persone dalla scienza, vista spesso come qualcosa di misterioso, lontano, ostile. Convinto che non serva essere un poeta per amare Shakespeare, in queste pagine Asimov offre ai lettori non specialisti uno strumento di \"iniziazione\" per apprezzare gli sviluppi della scienza contemporanea. Con stile brillante e straordinaria competenza, nei due celebri testi riuniti in questo volume - \"Il libro di fisica\" e \"Il libro di biologia\", cui si aggiunge l\'inedito saggio \"La matematica nella scienza\" - costruisce una vera e propria \"biografia\" delle scienze che ci introduce, in forma semplice ma rigorosa, ai segreti dei buchi neri e degli acceleratori di particelle, delle miriadi di forme viventi e dell\'intelligenza artificiale, dei numeri e della logica.',
        'it',
        'https://books.google.com/books/content?id=mRw0EAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8835728657',
        'Percy Jackson e gli dei dell\'Olimpo - 6. Il calice degli dei',
        'Rick Riordan',
        'Edizioni Mondadori',
        '2023',
        'Juvenile Fiction',
        'Dopo aver salvato il mondo innumerevoli volte, Percy spera solo di finire il liceo per poter andare all\'università con Annabeth, godendosi un anno tranquillo. Sfortunatamente gli dei dell\'Olimpo non hanno ancora finito con lui, e anche l\'ammissione al college diventa una vera tortura: per ottenerla il figlio di Poseidone dovrà procurarsi tre lettere di presentazione da parte di tre divinità diverse, affrontando le loro imprese. Prima fra tutte quella affidatagli da Ganimede, il coppiere di Zeus, che ha perduto il prezioso calice degli dei. Chi lo ha rubato, e perché? Insieme alla sua ragazza Annabeth e all\'amico satiro Grover, Percy si lancerà nella missione. Deve impedire che Ganimede venga umiliato pubblicamente, ma soprattutto deve sventare una ben più terribile minaccia, visto che un solo sorso dal calice garantirebbe a chi se ne impossessa l\'immortalità... Riuscirà lo storico trio a recuperarlo prima che finisca nelle mani sbagliate?',
        'it',
        'https://books.google.com/books/content?id=-QPZEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '883573469X',
        'Il satiro scientifico. I belli hanno rotto il cazzo',
        'Barbascura X,',
        'Edizioni Mondadori',
        '2024',
        'Science',
        'A un certo punto, il signor Mondadori mi chiede di diventare il curatore di una rivista scientifica. «Signor Mondadori, sono lusingato, ma mi concede un azzardo?» «Quale, o villoso Barbascura?» «Mi faccia fare una rivista a modo mio.» «Intendi a cazzo di cane?» «Esatto. Una rivista scientifica più pop, dove possiamo parlare anche di roba sconcia, che sia tanto scientifica quanto spassosa, e che se ci scappa faccia venire pure una paresi facciale.» «Intendi \"ridere\"?» «Non esageriamo.» Quel babbeo del signor Mondadori ha accettato, ed eccoci qui. L\'idea è semplice: 1. Scegliere l\'argomento del volume. 2. Chiedere a varati e ben noti divulgatori scientifici di scrivere pezzi su tale argomento nello stile più smaliziato e pop possibile. 3. Chiedere a stand-up comedian di gettarla in caciara in inserti a loro dedicati. 4. Mischiare il tutto con una mannaia. 5. Ingollare crudo. Con TikTok, Instagram e le foto pucciose ormai pare che se non sei un panda tenero puoi crepare male. Mentre degli aracnidi schifosi o dei viscidi molluschi chissenefrega. Ma vaffanculo, allora. Questo è ingiustificato specismo. Io non ci sto, e ho deciso che per il terzo numero del Satiro scientifico ci renderemo portavoce dei deboli, dei cessi, dei bistrattati, degli schifomadò, dei freaks, degli inguardabili, dei belli dentro e dei brutti sempre. Parleremo del ruolo della bellezza in natura e di quanto ci influenzi, di tutte quelle bestiacce che ci fanno ingiustamente schifo, del bello nel brutto e soprattutto del brutto nel bello. Perché non è bello ciò che è bello, ma è vero che i belli hanno rotto il cazzo. Se siete qui state per unirvi alla lotta.',
        'it',
        'https://books.google.com/books/content?id=eugJEQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8836005195',
        'La psicologia dei soldi',
        'Morgan Housel',
        'HOEPLI EDITORE',
        '2021',
        'Business & Economics',
        'Saperci fare con i soldi non dipende soltanto dalle informazioni a nostra disposizione ma anche, e soprattutto, da come ci comportiamo. E il comportamento è difficile da insegnare, anche alle persone più intelligenti. Spesso pensiamo al denaro – agli investimenti, alla finanza personale, alle decisioni d’affari – come a una questione matematica: un campo di studi in cui i dati e le formule ci dicono esattamente cosa dobbiamo fare. Nel mondo reale, però, non prendiamo le decisioni in materia economica consultando un foglio di calcolo. Le prendiamo la sera a cena o in una sala riunioni, dove si mescolano la storia personale, la visione del mondo propria di ciascuno, l’ego, l’orgoglio, il marketing... e i motivi più imprevedibili. In questo libro, l’autore pluripremiato Morgan Housel condivide 19 brevi narrazioni sugli strani modi in cui pensiamo ai soldi, aiutandoci a comprendere meglio uno degli argomenti più importanti nella vita di tutti e spiegando, nel contempo, come risparmiare, investire e far fruttare i nostri risparmi. Contiene il Capitolo bonus \"La storia infinita\".',
        'it',
        'https://books.google.com/books/content?id=okEvEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8836011098',
        'Trading pratico e investimenti',
        'Giacomo Probo',
        'HOEPLI EDITORE',
        '2022',
        'Business & Economics',
        'Il libro è indirizzato all’investitore che vuole operare con successo sui diversi mercati finanziari (Forex, Future, Azionari, Materie prime e Crypto). Nella prima parte l’autore descrive le varie situazioni mentali (paura, stress, immobilismo, euforia, esaltazione), suggerendo come affrontare le emozioni di trading per raggiungere un vantaggio strategico. L’ampia parte centrale affronta in modo approfondito l’operatività da adottare nei mercati finanziari tramite un numero elevato di esempi pratici di trading e di investimenti reali. In ogni situazione pratica si mostra come applicare concretamente la strategia: come individuare il miglior momento di entrata, la gestione del take profit e il posizionamento di un accurato stop-loss. L’obiettivo è quello di portare l’investitore direttamente nella sala operativa dell’autore, per spiegargli come gestire la posizione nelle varie fasi di mercato riducendo i rischi e massimizzando i profitti. Nella parte finale sono presenti tre test di autovalutazione su tre distinti livelli (principiante, privato, avanzato), che costituiscono un valido strumento per comprendere il proprio livello di conoscenze e di competenze nella materia. Il volume, ricco di grafici di operatività reale, costituisce un aiuto importante sia per il trader esperto alla ricerca di nuove soluzioni per migliorare i propri guadagni, sia per il neofita che tramite esempi pratici può acquisire una metodologia utile ad avviare una profittevole attività di trading.',
        'it',
        'https://books.google.com/books/content?id=OwyYEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8841216301',
        '342 Cani di razza',
        'Valeria Rossi',
        'De Vecchi Editore',
        '2018',
        'Pets',
        'NON DISPONIBILE PER KINDLE E-INK, PAPERWHITE, OASIS. La piccola grande enciclopedia da consultare per conoscere origini, caratteristiche fisiche, attitudini e particolarità di tutte le razze canine ufficialmente riconosciute dalla Federazione Cinologica Internazionale.',
        'it',
        'https://books.google.com/books/content?id=vsR1DwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8841253169',
        'Alberi d’Italia e d’Europa',
        'Bernardo Ticli',
        'De Vecchi',
        '2022',
        'Nature',
        'NON DISPONIBILE PER KINDLE E-INK, PAPERWHITE, OASIS. La bellezza delle forme e dei colori, la grande varietà, la capacità di resistere al trascorrere del tempo hanno sempre esercitato grande fascino sull’uomo che da secoli studia le caratteristiche degli alberi. Non finiamo mai di stupirci di fronte alla profonda diversità di ciascuna specie rispetto a tutte le altre: foglie, fiori e frutti sono solo alcune manifestazioni della straordinaria varietà di queste magnifiche creature vegetali. Il libro contiene le informazioni necessarie per individuare tutte le specie europee e tanti alberi esotici presenti stabilmente nel nostro territorio.',
        'it',
        'https://books.google.com/books/content?id=FQJlEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8845975819',
        'Forse Esther',
        'Katja Petrowskaja',
        'Adelphi Edizioni spa',
        '2014',
        'Biography & Autobiography',
        'Si sarà proprio chiamata Esther quella bis­­nonna che, nella Kiev del 1941, chiese fi­duciosa a due soldati tedeschi la strada per Babij Jar, la fossa comune degli e­brei, ricevendone come risposta un distratta rivoltellata? Forse. E dell\'intera famiglia, dispersa fra Polonia, Russia e Austria, che cosa ne è stato? Il monolite sovietico conosceva l\'avvenire, non la memoria. Per ricostruire quella ramificata genealogia, quel vivace intreccio di culture e di lingue – yiddish, polacco, ucraino, ebraico, russo, tedesco –, Katja Petrowskaja intraprende, sulle tracce degli scomparsi, un intenso viaggio a ritroso nella storia di un Novecento sul quale incombono la stella gialla e quella rossa, e in cui si incrociano i destini di memorabili figure: la babuška Rosa, incantevole logopedista di Varsavia, che salva duecento bambini sopravvissuti all\'assedio di Leningrado; il nonno ucraino, prigioniero di guerra a Mauthausen e riemerso da un gulag dopo decenni; il prozio Judas Stern, che spara a un diplomatico tedesco nella Mosca del 1932, e dopo un processo-farsa viene spedito «nel mondo della ma­teria disorganizzata»; il fratello Semën, il rivoluzionario di Odessa, che passando ai bolscevichi cambia in Petrovskij un cognome troppo ebraico... Ma indimenticabili protagonisti sono anche i paesaggi: l\'im­mane pianura russa invasa dai tedeschi e le città della vecchia Europa: Kiev, Mosca, Varsavia, Berlino. E i ghetti, i gulag e i lager nazisti. In questo romanzo vero, vibrante, venato di ironia – il migliore che la letteratura tedesca ci abbia dato dopo \"Austerlitz\" di Sebald –, mondi inabissati risorgono vividi, rapinosi, e più che mai contemporanei.',
        'it',
        'https://books.google.com/books/content?id=iAjqBAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8845984672',
        'La lettera uccide',
        'Carlo Ginzburg',
        'Adelphi Edizioni spa',
        '2021',
        'History',
        'Di fronte alla varietà dei temi discussi in questi saggi ci si potrà chiedere se esista un filo che li leghi. Il titolo del libro ne offre uno. «La lettera uccide, lo spirito dà vita» disse Paolo di Tarso, contrapponendo alla legge giudaica in cui era nato la nuova fede – il cristianesimo – di cui fu il fondatore. «Uccide», «dà vita» sono metafore, che non vanno prese alla lettera. Ad esse si può rispondere con un’altra metafora: la lettera uccide chi la ignora. Dall’analisi ravvicinata di casi specifici emerge una versione della microstoria, qui presentata in una prospettiva inedita. Al centro di questi casi ci sono personaggi famosi (Machiavelli, Michelangelo, Montaigne) o semisconosciuti (Jean-Pierre Purry, La C.***); un testo o un’immagine; un tema (la rivelazione) o una lettera dell’alfabeto. E un elemento ricorrente: la riflessione sul metodo, sugli intrecci tra «caso» e «caso» – tra studi di caso ed elementi casuali, spesso prodotti deliberatamente. «Il libro di cui hai bisogno si trova accanto a quello che cerchi»: chi legge potrà scoprire i risultati, spesso imprevedibili, di questa affermazione di Aby Warburg.',
        'it',
        'https://books.google.com/books/content?id=Xg1IEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8845985466',
        'L’insostenibile leggerezza dell’essere',
        'Milan Kundera',
        'Adelphi Edizioni spa',
        '2022',
        'Fiction',
        'Il suo romanzo ci dimostra come nella vita tutto quello che scegliamo e apprezziamo come leggero non tarda a rivelare il proprio peso insostenibile. Forse solo la vivacità e la mobilità dell’intelligenza sfuggono a questa condanna: le qualità con cui è scritto il romanzo, che appartengono a un altro universo da quello del vivere. ITALO CALVINO Chi è pesante non può fare a meno di innamorarsi perdutamente di chi vola lievemente nell’aria, tra il fantastico e il possibile: mentre i leggeri sono respinti dai loro simili e trascinati dalla «com-passione» verso i corpi e le anime possedute dalla pesantezza. Così accade nel romanzo: Tomáš ama Tereza, Tereza ama Tomáš: Franz ama Sabina, Sabina (almeno per qualche mese) ama Franz; quasi come nelle «Affinità elettive» si forma il perfetto quadrato delle affinità amorose. PIETRO CITATI',
        'it',
        'https://books.google.com/books/content?id=GNVuEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8850322089',
        'Storia dell\'informatica. Dai primi computer digitali all\'era di internet',
        'Paul E. Ceruzzi',
        'Apogeo Editore',
        '2005',
        'undefined',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=ZnooDozpRF4C&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8851130892',
        'Four',
        'Veronica Roth',
        'De Agostini',
        '2015',
        'Juvenile Fiction',
        'Quando per Tobias Eaton arriva il Giorno della Scelta, il ragazzo non ha dubbi: vuole lasciare la fazione che per sedici anni è stata la sua prigione e scappare dalla furia del padre violento. Per il suo nuovo inizio sceglie gli Intrepidi, perché desidera imparare da solo a sconfiggere le proprie paure e a essere coraggioso. Con un nuovo nome, \"Quattro\" comincia l\'addestramento che lo porta a scalare la classifica degli iniziati e ad attirare su di sé l\'interesse delle più alte sfere dirigenziali, che lo vorrebbero trasformare nel più giovane capofazione che negli Intrepidi abbiano mai avuto. Ma è davvero così... oppure c\'è qualcosa di più inquietante dietro gli intrighi dei leader Intrepidi? Due anni dopo, Quattro - disgustato dalle trame della sua fazione - è pronto a fare la propria mossa e a lasciarsi tutto alle spalle, ma l\'arrivo di una giovane iniziata cambia ogni cosa. Perché, grazie a lei, Quattro scopre un lato di sé che non credeva di possedere. Grazie a lei, potrebbe tornare a essere semplicemente Tobias. Veronica Roth ci riporta nella Chicago distopica di Tris, raccontando i momenti fondamentali della vita di Quattro e regalandoci così un viaggio nella mente del tenebroso e tormentato protagonista maschile di Divergent.',
        'it',
        'https://books.google.com/books/content?id=PgYqBgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8851800413',
        'Se l\'universo brulica di alieni... dove sono tutti quanti? Cinquanta soluzioni al paradosso di Fermi e al problema della vita extraterrestre',
        'Stephen Webb',
        'Alpha Test',
        '2004',
        'Science',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=PKmAXzCMOu0C&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8852024182',
        'Principessa Laurentina',
        'Bianca Pitzorno',
        'Edizioni Mondadori',
        '2012',
        'Juvenile Fiction',
        'Il secondo matrimonio di sua madre ha costretto Barbara a trasferirsi a Milano. I suoi rapporti con la madre diventano sempre più difficili e il disastro è completo quando arriva una sorellina, ossia una rivale, amata e coccolata. Ma una imprevista tragedia obbliga Barbara ad affrontare la realtà, invece di subirla.',
        'it',
        'https://books.google.com/books/content?id=Grz3sjDDdcwC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8852035826',
        'Inferno',
        'Dan Brown',
        'Edizioni Mondadori',
        '2013',
        'Fiction',
        'Il profilo inconfondibile di Dante che ci guarda dalla copertina è il motore mobile di un thriller che di \"infernale\" ha molto. Il ritmo, prima di tutto, e poi il simbolismo acceso, e infine la complessità dei personaggi che conducono a un esito raro per i romanzi d\'azione: instillare nel lettore il fascino del male, addirittura la sua salvifica necessità. Non è affatto sorprendente che lo studioso di simbologia Robert Langdon sia un esperto di Dante, anzi. È naturale che al poeta fiorentino e alla visionarietà con cui tradusse in forme solenni e oscure la temperie della sua epoca tormentata il professore americano abbia dedicato studi e corsi universitari ad Harvard. E quindi è normale che a Firenze Robert Langdon sia di casa, che il David e piazza della Signoria, il giardino di Boboli e Palazzo Vecchio siano per lui uno sfondo familiare, una costellazione culturale e affettiva ben diversa dal palcoscenico turistico percorso in tutti i sensi di marcia da legioni di visitatori. Ma ora è tutto diverso, non c\'è niente di normale, nulla che possa rievocare una dolce abitudine. Questa volta è un incubo e la sua conoscenza della città fin nei labirinti delle stradine, dei corridoi dei palazzi, dei passaggi segreti può aiutarlo a salvarsi la vita. Il Robert Langdon che si sveglia in una stanza d\'ospedale, stordito, sedato, ferito alla testa, gli abiti insanguinati su una sedia, ricorda infatti a stento il proprio nome, non capisce come sia arrivato a Firenze, chi abbia tentato di ucciderlo e perché i suoi inseguitori non sembrino affatto intenzionati a mollare il colpo. Barcollante, la mente invasa da apparizioni mostruose che ricordano la Morte Nera che flagellò l\'Europa medievale e simboli criptici connessi alla prima cantica del Divino poema, le labbra capaci di articolare, nel delirio dell\'anestetico, soltanto un incongruo \"very sorry\", il professore deve scappare. E, aiutato solo dalla giovane dottoressa Sienna Brooks, soccorrevole ma misteriosa come troppe persone e cose intorno a lui, deve scappare da tutti. Comincia una caccia all\'uomo in cui schieramenti avversi si potrebbero ritrovare dalla stessa parte, in cui niente è quel che sembra: un\'organizzazione chiamata Consortium è ambigua tanto quanto un movimento detto Transumanesimo e uno scienziato come Bertrand Zobrist può elaborare teorie che oscillano tra utopia e aberrazione. Alla fine di un\'avventura che raggiunge momenti di insostenibile tensione, Dan Brown ci rivela come nel nostro mondo la distanza tra il bene e il male sia breve in maniera davvero inquietante, catastrofe e salvezza possano essere questione di punti di vista e anche da una laguna a cielo coperto si possa uscire a riveder le stelle.',
        'it',
        'https://books.google.com/books/content?id=JYyPqYjhKSIC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    );

INSERT INTO
    `Libro` (
        `ISBN`,
        `titolo`,
        `autore`,
        `editore`,
        `anno`,
        `genere`,
        `descrizione`,
        `lingua`,
        `path_copertina`
    )
VALUES
    (
        '885205720X',
        'Pandora e altri mondi (Urania)',
        'Frank Herbert,Bill Ransom',
        'Edizioni Mondadori',
        '2014',
        'Fiction',
        'PROGETTO COSCIENZA (Destination Void, 1966) Le ricerche per sviluppare l\'intelligenza artificiale si svolgono sulla Luna, dove le condizioni sembrano più sicure. Qui viene allestita la prima astronave interstellare guidata da un OMC (Organic Mental Core, Centro di controllo organico) che sarà popolata da cloni e diretta verso un pianeta della stella Tau Ceti. Questo, almeno, stando alle versioni ufficiali... SALTO NEL VUOTO (The Jesus Incident, 1979) Il Progetto è ormai realtà. Per portare a termine quest\'impresa gigantesca sono state progettate astronavi così complesse che possono essere governate solo da una simbiosi fra uomini e macchine, fra l\'intelligenza biologica e quella artificiale. Ma le navi intelligenti hanno un loro punto di vista sull\'argomento, e non è detto che coincida con quello dell\'uomo. Il brusco risveglio di Raja Flattery, ben prima dell\'arrivo nel sistema di Tau Ceti, è perciò l\'inizio di una drammatica odissea fra il vuoto dello spazio e l\'ambitissimo pianeta Pandora.',
        'it',
        'https://books.google.com/books/content?id=mgPaBAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8852220828',
        'Wall-E',
        'Disney',
        'Giunti Editore',
        '2015',
        'Juvenile Fiction',
        'WALL-E (sigla di Waste Allocation Load Lifters – Earth Class) è un piccolo robot creato per ripulire il pianeta Terra, ormai disabitato, dai rifiuti lasciati dall\'uomo. Rimasto l\'unico robot funzionante (i suoi fratelli si sono disattivati per un difetto di programmazione), WALL-E soffre di solitudine, finché incontra EVE (Extraterrestrial Vegetation Evaluator), una femmina robot inviata sulla Terra alla ricerca di tracce di vita. Wall-E si innamora di lei e, quando gli uomini la conducono via su un\'astronave, decide di seguirla in una straordinaria avventura. Una storia splendidamente illustrata per sognare in compagnia dei personaggi Disney Pixar più amati.',
        'it',
        'https://books.google.com/books/content?id=3DHMCQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8854175382',
        'Il Piccolo Principe',
        'Antoine de Saint-Exupéry',
        'Newton Compton Editori',
        '2015',
        'Fiction',
        'Traduzione e cura di Emanuele Trevi Ecco il mio segreto. È molto semplice: si vede bene solo con il cuore. L\'essenziale è invisibile agli occhi. Il Piccolo Principe è la storia dell’incontro in mezzo al deserto tra un aviatore e un buffo ometto vestito da principe che è arrivato sulla Terra dallo spazio. Ma c’è molto di più di una semplice amicizia in questo libro surreale, filosofico e magico. C’è la saggezza di chi guarda le cose con occhi puri, la voce dei sentimenti che parla la lingua universale, e una sincera e naturale voglia di autenticità. Perché la bellezza, quando non è filtrata dai pregiudizi, riesce ad arrivare fino al cuore dei bambini, ma anche a quello degli adulti che hanno perso la capacità di ascoltare davvero. Antoine de Saint-Exupéry (Lione, 29 giugno 1900 – mar Mediterraneo, 31 luglio 1944), è stato uno scrittore e aviatore francese. Oltre a Il Piccolo Principe, uno dei libri più letti e amati nel mondo, è anche autore di Volo di notte e L’aviatore pubblicati in volume unico da Newton Compton editori.',
        'it',
        'https://books.google.com/books/content?id=2_XMBQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8854505935',
        'L\'ultimo inverno',
        'Paul Harding',
        'Neri Pozza Editore',
        '2012',
        'Fiction',
        'Chi non è rimasto almeno una volta incantato dal mondo misterioso dei venditori ambulanti, dai loro carretti così carichi di oggetti semplici, ingegnosi, meccanici, che essi sapevano riparare grazie a un\'arte tramandata da tempo immemorabile? È questo il mondo da cui proviene George Washington Crosby; è questo il mondo a cui ritorna mentre, steso su un letto d\'ospedale al centro del soggiorno della sua casa nel New England, si prepara a concludere la sua vita circondato dai famigliari e accompagnato dal tintinnio dei suoi orologi cui per anni si è dedicato come meticoloso restauratore. Meravigliosi meccanismi di tutte le epoche e fogge che sono stati a lungo il legame, negato ma indissolubile, con il mondo della sua infanzia e di suo padre Howard, un uomo silenzioso, sognante, poetico, il quale stentatamente manteneva quattro figli e una moglie insoddisfatta girovagando con il suo carro pieno di mercanzie tra i boschi del Maine. Il carro arrivava insieme al suo solitario guidatore a offrire spazzole, polvere dentifricia, calze di nylon, crema da barba, lucido da scarpe, manici di scopa, pentole, e persino gioielli da quattro soldi a donne rudi e senza più sogni, a uomini che finivano troppo presto le loro scorte di gin e sigarette, a eremiti in un mondo dominato dalle stagioni, dal sole e dal gelo, dagli alberi, dai laghi e dai ruscelli, da leggende e da poche parole. Mentre le funzioni vitali lo abbandonano, George ritrova Howard così come non aveva mai permesso a se stesso di immaginarlo, e di quell\'uomo simile a un veggente rivede anche i segni dell\'incurabile e misteriosa malattia: l\'epilessia. Un dramma che era quasi in sintonia con quella natura imprevedibile e spesso impetuosa in cui il venditore ambulante viveva immerso: un eccesso di energia, di elettricità lo colpiva così come il fulmine colpisce il bosco, e lo lasciava stordito e sanguinante dopo angosciosi minuti trascorsi sul pavimento a scalciare, rovesciare le lampade, sbattere la testa sulle assi, mentre i denti mordevano un bastoncino. Oppure le dita del figlio, di George adolescente che in seguito, per anni, non aveva più saputo se odiare o amare quel padre folle, ma che ora finalmente riesce a incontrare, e solamente ad amare. L\'ultimo inverno è un romanzo d\'esordio di rara potenza espressiva, dominato da un linguaggio plasmato dalla penna di un grande scrittore, un romanzo sull\'America di ieri e di oggi che parla dell\'amore tra un padre e un figlio, della fierezza della natura, del ricordo e della fantasia.',
        'it',
        'https://books.google.com/books/content?id=gsvbCgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8854529486',
        'L\'idiota',
        'Fëdor Dostoevskij',
        'Neri Pozza Editore',
        '2024',
        'Fiction',
        'Due giovani uomini, seduti l’uno di fronte all’altro, con le ginocchia che si toccano, nello spazio limitato di un vagone in corsa. Uno biondo, mite, luminoso; l’altro bruno, cupo, febbricitante. Comincia così, in uno spazio ristretto e in movimento, con due personaggi che si rispecchiano l’uno nell’altro, uno dei testi più misteriosi, inattesi, davvero enigmatici della letteratura mondiale, l’«esperimento» di Dostoevskij, un’opera che disgrega i limiti del romanzo della sua epoca e si proietta in avanti a testa bassa, senza freni, verso la modernità, andando però nel contempo a prendere energia e struttura in un passato letterario antichissimo. «L’idea principale del romanzo è raffigurare un uomo positivamente bello. Al mondo non c’è nulla di più difficile» scriveva Dostoevskij. Il principe Myškin, l’«idiota», diventa dunque una sorta di simbolo vivente capace di evocare la figura di Cristo, l’unica positivamente bella per Dostoevskij, che tuttavia quasi non è nominata nel romanzo, pur pervadendolo. Il mondo in cui si muovono il protagonista, le figure comprimarie e le complesse relazioni d’amore che li intrecciano è privo di ogni salvifica bellezza, è un mondo feroce, che gronda sangue, dove chi è indifeso (Nastas’ja bambina, in balia di un adulto depravato; Aglaja giovanetta, prigioniera di convenzioni sociali che le fanno orrore; persino Rogožin, travolto da una passione priva di limiti), o chi si porta nel cuore la mitezza, la grazia, la compassione, si muove a tentoni, cercando di indovinare le regole della sopravvivenza, sempre fallendo, sino al colpo di coltello finale. S.P. l possessore del mantello col cappuccio era un giovane tra i ventisei e i ventisette anni d’età, d’altezza un poco superiore alla media, molto biondo, con capelli folti, guance incavate e una barbetta appena accennata, a punta, di un biondo quasi bianco. Gli occhi erano grandi, azzurri e fissi; nel loro sguardo c’era qualcosa di quieto ma anche di meditabondo, erano colmi di quella strana espressione dalla quale alcuni intuiscono fin dalla prima occhiata la presenza, nel soggetto, del mal caduco. «I romanzi di Dostoevskij sono vortici ribollenti, mulinelli di sabbia in una tempesta, trombe d’acqua che sibilano e gorgogliano e ci risucchiano». Virginia Woolf «L’idiota è un capolavoro, il cui vero tema è l’imminenza e l’immanenza della morte». A.S. Byatt',
        'it',
        'https://books.google.com/books/content?id=C3v3EAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8854980579',
        'Orgoglio e pregiudizio',
        'Jane Austen',
        'Edizioni Theoria',
        '2019',
        'Fiction',
        'Orgoglio e pregiudizio è sicuramente il romanzo più popolare di Jane Austen, quello in cui si realizza un equilibrio perfetto tra struttura e stile, e quello che esalta in maniera più distintiva lo smalto della sua arte. Il libro fu pubblicato nel 1813 ottenendo già all’epoca un ottimo riscontro da parte dei lettori. Un romanzo che ancora oggi è considerato tra i massimi di tutta la letteratura inglese.',
        'it',
        'https://books.google.com/books/content?id=RsvREAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8855057014',
        'In cucina con Benedetta',
        'Benedetta Parodi',
        'Vallardi',
        '2021',
        'Cooking',
        'Contro la noia dei soliti piatti, ricette originali per esperti e principianti Riscopri con Benedetta il piacere dei sapori semplici e genuini, i profumi di una cucina casalinga a partire dalla spesa di ogni giorno. Un ricettario tutto da provare contro la routine di piatti e padelle, dove la tradizione italiana incontra il gusto unico e avventuroso di un viaggio gastronomico tra i Paesi del mondo. Così quando le idee sembrano esaurite e lo spettro dei soliti piatti bussa alla tua porta, ti basteranno un pizzico di creatività e due cucchiai di fantasia per colorare di nuovi sapori i tuoi momenti in cucina. Con Benedetta sperimentare nuovi piatti e ricette stuzzicanti diventa un’occasione per liberare la tua fantasia culinaria, accendendo la fiamma della creatività senza sprechi e senza spendere una fortuna. Una cucina veloce e saporita per principianti ed esperti e per chiunque voglia spezzare la routine in cucina, riscoprendo ogni giorno la semplicità del mangiare bene. 150 NUOVE RICETTE ALLA SCOPERTA DEL PIACERE DEI SAPORI SEMPLICI PER I PIATTI DI OGNI GIORNO E LE DELIZIE DELLE OCCASIONI SPECIALI',
        'it',
        'https://books.google.com/books/content?id=83hMEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858407148',
        'After Dark (versione italiana)',
        'Murakami Haruki,',
        'Giulio Einaudi Editore',
        '2012',
        'Fiction',
        '«Librandosi al di sopra della generale tristezza, Murakami riesce a captare le fosforescenze, in ogni luogo e in particolare nell\'aura che avvolge le persone: di notte e nella comunanza degli esseri umani, essa raggiunge l\'apice della luminosità». «The New York Times Book Review»',
        'it',
        'https://books.google.com/books/content?id=NHHzp4YrduMC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858407245',
        'Norwegian Wood. Tokyo Blues',
        'Murakami Haruki,',
        'Giulio Einaudi Editore',
        '2012',
        'Fiction',
        'Con una nota dell\'autore. *** «La costruzione della scrittura di Murakami è cosí impalpabile e squisita che ogni cosa egli scelga di descrivere vibra di potenzialità simbolica: una camicia stesa ad asciugare, dei ritagli di carta, un fermaglio a forma di farfalla». «The Guardian»',
        'it',
        'https://books.google.com/books/content?id=HU5O_l8XGyMC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858407865',
        'La ragazza dello Sputnik',
        'Murakami Haruki,',
        'Giulio Einaudi Editore',
        '2013',
        'Fiction',
        '«Un romanzo intriso di suggestioni della pop art urbana e della vita metropolitana che s\'innalza fin dalle prime pagine sopra il grigiore della quotidianità per descrivere personaggi ed emozioni». «L\'Indice»',
        'it',
        'https://books.google.com/books/content?id=GyS1vt9Wci0C&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858415833',
        'Guerra e pace',
        'Lev Tolstoj',
        'Giulio Einaudi Editore',
        '2014',
        'Fiction',
        '« Guerra è il mondo storico, pace il mondo umano. Il mondo umano interessa ed attrae particolarmente Tolstoj soprattutto perché egli è convinto che ogni uomo - di ieri, di oggi, di domani - valga un altro uomo...» Leone Ginzburg «La nuova traduzione di Emanuela Guercetti è un evento editoriale. È di fronte a imprese del genere che comprendi quanto diceva Iosif Brodskij a proposito della traduzione: è la madre di ogni civiltà». Alessandro Piperno',
        'it',
        'https://books.google.com/books/content?id=tgDaBAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '885842431X',
        'Le otto montagne',
        'Paolo Cognetti',
        'Giulio Einaudi Editore',
        '2016',
        'Fiction',
        'Romanzo vincitore del Premio Strega 2017 . «Qualunque cosa sia il destino, abita nelle montagne che abbiamo sopra la testa».',
        'it',
        'https://books.google.com/books/content?id=cbZ0DQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858529413',
        'I girasoli di Kiev',
        'Erin Litteken',
        'Edizioni Piemme',
        '2022',
        'Fiction',
        'Una grande saga familiare che racconta il destino di una nazione e i destini dei singoli contro il nemico più grande: la storia. C\'è un posto a Kiev dove crescono ancora i girasoli. E nulla, neanche i venti tempestosi della Storia, potrà spezzarli. È il 1934 e per Katya, ora che la fattoria di famiglia vicino Kiev non esiste più, c\'è un solo posto dove trovare pace: il fazzoletto di terra miracolosamente scampato alla collettivizzazione sovietica, dove con la sorella Alina amava coltivare i girasoli. Era il loro gioco e il loro piccolo segreto. Sembrano passati secoli, ma solo pochi anni prima, quel giorno di primavera al matrimonio della cugina, il mondo di Katya era perfetto: sedici anni, il vestito più bello e le trecce legate in testa alla maniera tradizionale, la mamma, il papà, Alina... e Pavlo, l\'amico di sempre che proprio quel giorno le aveva confessato il suo amore, e con cui Katya aveva sognato mille cose. Ora non esiste più nulla: i suoi genitori, Pavlo, Alina, non c\'è più nessuno, l\'Ucraina è in mano ai sovietici, e per piegare la nazione al suo volere Stalin ha escogitato un piano crudele, passato alla storia come Holodomor, il genocidio per fame che uccise milioni di ucraini, e che il mondo ha dimenticato. Solo i girasoli, oggi, sono ancora lì, pronti a seguire il sole e a indicarle la strada... Girasoli. È di questo che continua a parlare Bobby, l\'amata nonna di Cassie, con quella strana lucidità delle persone ormai molto, molto anziane. Da quando Cassie ha perso suo marito, insieme alla piccola Birdie si è trasferita dal Wisconsin in Illinois, per stare con Bobby - così l\'ha sempre chiamata, da babuška, la parola ucraina per \"nonna\". È da lì che arriva Bobby, emigrata in America dopo la guerra, ma di quel mondo e di quel passato non ha mai raccontato nulla. Ma quando Cassie trova per caso un diario dalle pagine fitte scritte in ucraino, capisce che è arrivato il momento. Il momento di scoprire che cosa c\'è nel passato doloroso di sua nonna, qual è il segreto che si porta dietro, perché nel sonno continua a ripetere un nome - Alina - e quella parola: girasoli... \"Un debutto potente e commovente. La tragica storia dell\'Ucraina raccontata in questo romanzo riecheggia dolorosamente ciò che accade oggi, e a ogni pagina lo spirito di questo popolo illumina la narrazione, fiero e indistruttibile. I girasoli di Kiev è una lettura trascinante e, ancor più in questo momento, importante.\"- Kate Quinn',
        'it',
        'https://books.google.com/books/content?id=oDh4EAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858611446',
        'I tre moschettieri',
        'Alexandre Dumas',
        'Bur',
        '2011',
        'Fiction',
        'Il giovane d\'Artagnan va a Parigi in cerca di fortuna. Divenuto amico dei moschettieri Porthos, Athos e Aramis, entra con loro al servizio del re. I quattro devono combattere le trame del cardinale Richelieu e della perfida Milady de Winter. Salveranno l\'onore della regina che imprudentemente aveva regalato al duca di Buckingham, come pegno d\'amore, una collana di diamanti avuta in dono dal marito Luigi XIII. Giustizieranno Milady, che aveva fatto uccidere il duca e una cameriera amata da d\'Artagnan. Questi, riconciliatosi col cardinale, verrà promosso luogotenente, Athos si ritirerà in campagna, Porthos si sposerà e Aramis si farà abate.',
        'it',
        'https://books.google.com/books/content?id=utT5qmal_gIC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858693361',
        'Jack Bennet e la chiave di tutte le cose',
        'Fiore Manni',
        'Rizzoli',
        '2018',
        'Juvenile Fiction',
        'Jack Bennet è un bambino di dieci anni come tanti altri, forse solo un pochino più basso e più magro della media. Ogni mattina si alza, si avvolge intorno al collo la lunga sciarpa a righe azzurre che gli ha lasciato suo padre ed esce per le fumose vie di Londra. Come molti ragazzi del suo tempo lavora in fabbrica, perché la mamma è malata, e in famiglia non c\'è nessun altro che possa provvedere a loro. Una mattina, sulla strada del lavoro, Jack incontra un curioso personaggio che pare sbucato dal nulla; un uomo del tutto fuori luogo, con il suo elegante completo viola nel bel mezzo della grigia città. Jack lo osserva incuriosito e lo saluta educato, poi lo ascolta con attenzione. E fa bene, perché la più grande delle avventure può cominciare in un giorno qualunque. L\'uomo gli consegna una chiave, e con quella Jack inizia a viaggiare per mondi sconosciuti e bislacchi, dove incontra pappagalli tipografi, libri magici per tutte le occasioni, navi pirata, una ragazzina spavalda ma non troppo, un drago che sputa vapore e colleziona tesori. E molto, molto altro. La storia di Jack ci risucchia e non ci molla; ci porta su mari senz\'acqua e davanti a misteriose creature, facendoci palpitare, emozionare, e ridere anche, fino alla fine.',
        'it',
        'https://books.google.com/books/content?id=PWtZDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858775880',
        'La peste',
        'Albert Camus',
        'Bompiani',
        '2017',
        'Fiction',
        'Scritto da Albert Camus secondo una dimensione corale e con una scrittura che sfiora e supera la confessione, La peste è un romanzo attuale e vivo, una metafora in cui il presente continua a riconoscersi. Oggi da leggere e rileggere in una nuova brillante traduzione. Orano è colpita da un\'epidemia inesorabile e tremenda. Isolata, affamata, incapace di fermare la pestilenza, la città diventa il palcoscenico e il vetrino da laboratorio per le passioni di un\'umanità al limite tra disgregazione e solidarietà. La fede religiosa, l\'edonismo di chi non crede alle astrazioni né è capace di \'\'essere felice da solo\'\', il semplice sentimento del proprio dovere sono i protagonisti della vicenda; l\'indifferenza, il panico, lo spirito burocratico e l\'egoismo gretto gli alleati del morbo.',
        'it',
        'https://books.google.com/books/content?id=IZ6HDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858814274',
        'Il Principe',
        'Niccolò Machiavelli',
        'Feltrinelli Editore',
        '2013',
        'Philosophy',
        'Questa edizione del Principe intende riproporre l\'opera maggiore di Machiavelli da una angolazione critica che si avvale delle pagine che Hegel dedicò al Segretario fiorentino nella Costituzione della Germania. A parere del filosofo di Stoccarda, il celebre libro è da leggersi in modo piuttosto diverso da quello cui è corriva una tradizione interpretativa ormai secolare: per Hegel l\'opera di Machiavelli è piuttosto l\'intervento appassionato e lucido di un patriota che cerca di individuare e di additare i mezzi più idonei alla costruzione di uno Stato nazionale italiano. E con ciò la riflessione machiavelliana, ampiamente illustrata nella premessa del noto italianista Ugo Dotti, viene ricondotta alla dimensione e alla problematica di una ben precisa epoca storica.',
        'it',
        'https://books.google.com/books/content?id=uiV1AgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858818962',
        'Cime tempestose',
        'Emily Brontë',
        'Feltrinelli Editore',
        '2014',
        'Fiction',
        '“Se tutto il resto scomparisse e restasse solo lui, continuerei a esistere” “Un romanzo in cui domina la violenza sugli uomini, sugli animali, sulle cose, scandito da scatti di crudeltà sia fisica sia, soprattutto, morale. Un romanzo brutale e rozzo – sono gli aggettivi utilizzati dalla critica dell’epoca – che scuoteva gli animi per la sua potenza e la sua tetraggine e che narra il consumarsi di un’inesorabile (sino a un certo punto) vendetta portata avanti con fredda meticolosità dal disumano Heathcliff. Cime tempestose è un romanzo selvaggio, originale, possente,’ si leggeva in una recensione della ‘North American Review’, apparsa nel dicembre del 1848, e se la riuscita di un romanzo dovesse essere misurata unicamente sulla sua capacità evocativa, allora Wuthering Heights può essere considerata una delle migliori opere mai scritte in inglese e, come affermava Charlotte Brontë in una lettera a William Smith, Ellis Bell (lo pseudonimo di Emily) era un ‘uomo dal talento non comune, ma caparbio, brutale e cupo’”. [...] Tomasi di Lampedusa esprimeva il suo entusiastico e ammirato giudizio su Cime tempestose: ‘Un romanzo come non ne sono mai stati scritti prima, come non saranno mai più scritti dopo. Lo si è voluto paragonare a Re Lear. Ma, veramente, non a Shakespeare fa pensare Emily, ma a Freud; un Freud che alla propria spregiudicatezza e al proprio tragico disinganno unisse le più alte, le più pure doti artistiche. Si tratta di una fosca vicenda di odi, di sadismo e di represse passioni, narrate con uno stile teso e corrusco spirante, fra i tragici fatti, una selvaggia purezza.” (dall’introduzione di Frédéric Ieva)',
        'it',
        'https://books.google.com/books/content?id=2WtuBAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858819551',
        'Il giocatore',
        'Fëdor Dostoevskij',
        'Feltrinelli Editore',
        '2024',
        'Fiction',
        'Quella che Dostoevskij tratteggia nel Giocatore è una vera e propria radiografia letteraria del vizio del gioco, un’istantanea dei modi in cui il demone dell’azzardo può possedere uomini e donne di ogni età ed estrazione sociale. Un’istantanea così vivida da spingere Sergej Prokofiev a trasporla in musica, dando vita a un caposaldo della lirica novecentesca. Nella fittizia cittadina tedesca di Roulettenburg va in scena, attorno a un totem fatto di fiches e casinò, un vero e proprio carosello di figure, dal giovane precettore Aleksej al vecchio generale, dall’anziana, ricchissima nonnina al cialtronesco marchese des Grieux, dalla graziosa Polina alla misteriosa mademoiselle Blanche. Succede di tutto, eppure nulla cambia e chi, come Aleksej, è posseduto dal gioco potrà guarire e redimersi, sì, ma solo “da domani”.',
        'it',
        'https://books.google.com/books/content?id=xSgWEQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858820746',
        'Le notti bianche - La cronaca di Pietroburgo',
        'Fëdor Dostoevskij',
        'Feltrinelli Editore',
        '2015',
        'Fiction',
        '“Era una notte meravigliosa, una di quelle notti che forse possono esistere solo quando si è giovani” Un giovane sognatore, nella magia vagamente inquieta delle nordiche notti bianche, incontra una misteriosa fanciulla e vive la sua “educazione sentimentale”, segnata da un brusco risveglio con conseguente ritorno alla realtà. Un Dostoevskij lirico, ispirato, comincia a riflettere sulle disillusioni dell’esistenza e dell’amore nell’ultima opera pubblicata prima dell’arresto e della deportazione, esperienze che modificheranno in maniera radicale e definitiva la sua concezione dell’uomo e dell’arte. In questa edizione, al celebre racconto viene affiancata la visione “diurna” di Pietroburgo contenuta nei feuilletons che compongono la Cronaca di Pietroburgo, vero e proprio laboratorio per la scrittura dostoevskiana. Lo stretto legame tra pubblicistica e letteratura, che accompagnerà Dostoevskij negli anni della maturità, viene così a manifestarsi fin quasi dal suo esordio. Il racconto Le notti bianche ha ispirato il film omonimo di Luchino Visconti (1957), con Marcello Mastroianni e Maria Schell, e il film Quattro notti di un sognatore di Robert Bresson (1971).',
        'it',
        'https://books.google.com/books/content?id=z_6rCQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858821815',
        'Guerra e rivoluzione',
        'Lev Nikolaevic Tolstoj',
        'Feltrinelli Editore',
        '2015',
        'History',
        'In questo saggio sostanzialmente inedito del 1906 – ritrovato per questa edizione italiana – Tolstoj delinea il suo programma d’azione politico e denuncia la tirannia degli stati e la cecità morale della società. Egli si fa profeta di una nuova era e con grande lucidità invita alla insubordinazione verso ogni forma di governo. Lo spunto è offerto dalla convulsa dinamica della Rivoluzione russa del 1905, emersa sulla scorta della sconfitta della Russia nel conflitto con il Giappone. è tra l’ottobre e il novembre del 1905 difatti che Tolstoj scrive quest’opera, che non riuscì poi a vedere la luce in patria per la feroce censura zarista. Tolstoj indica qui la strada verso una “vera concezione della vita”. Per liberarsi da tutti i mali di cui soffrono gli uomini c’è un unico mezzo: il lavoro interiore che ognuno deve fare per essere l’architetto del proprio miglioramento morale. Nel delegare il loro potere gli individui realizzano invece una sorta di schiavitù volontaria. Il testo, sofferta orazione che riflette sugli assetti politici del primo Novecento, fu poi pubblicato non senza difficoltà a Parigi nel 1906. Il volume diventerà presto introvabile anche in Francia, fino a questa edizione italiana.',
        'it',
        'https://books.google.com/books/content?id=n0FACQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858840941',
        'Hello America',
        'James G. Ballard',
        'Feltrinelli Editore',
        '2020',
        'Fiction',
        'Nel 2114 il mondo è cambiato a causa di un disastro ecologico senza precedenti e della conseguente crisi energetica. Parte dell’Asia è ghiacciata, il Circolo polare artico è divenuto fertile e l’America del Nord è quasi del tutto disabitata e desertica. Quando alcuni picchi radioattivi inspiegabili impensieriscono l’Europa, un piccolo gruppo di esploratori viene inviato a bordo del piroscafo Apollo alla ricerca delle cause dell’anomalia. La missione è composta da discendenti degli americani espatriati più di cento anni prima: ognuno si sente legato in qualche modo alla terra natìa dei propri avi, ognuno ha un secondo fine per essere lì. Costretti a viaggiare da costa a costa, scopriranno le tracce di un mondo ormai scomparso, incontreranno “nativi” che vivono in modo primitivo, prede di una cultura ormai disintegrata. I segnali indicano che da qualche parte al di là delle distese desertiche di Manhattan si annida un’allettante, ma pericoloso, mistero e i viaggiatori dell’\"Apollo\" abbandoneranno la loro missione scientifica alla ricerca di un nuovo “sogno americano”. Precedendo di diversi decenni gli attuali dibattiti ecologici, in questo romanzo J.G. Ballard viviseziona con lucidità tagliente e ironia il mito del “sogno americano” e costruisce un mondo devastato che pare nascere dai nostri peggiori incubi, ma così familiare che non faremo fatica a riconoscerlo.',
        'it',
        'https://books.google.com/books/content?id=UCYGEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858845145',
        'Lettera a una ragazza del futuro',
        'Concita De Gregorio',
        'Feltrinelli Editore',
        '2021',
        'Juvenile Fiction',
        '\"Vivi come se il mondo fosse già quello che vuoi.\" Concita De Gregorio scrive una lettera alla sé stessa del passato e alle ragazze che diventeranno donne. Le sue parole sono un filo potente e prezioso che unisce le generazioni. Concita sa che i consigli, di solito, restano inascoltati e che si impara solo dall’esperienza così, in questa \"Lettera a una ragazza del futuro\", parla innanzitutto a se stessa, alla ragazza che è stata nel passato. Sii gentile, dice. Appassionata e gentile. Ribellati, ma scegli tu a che cosa. Ignora le convenzioni e l’arroganza. Resta intatta e diventa tu stessa il mondo che vorresti. Non avere paura di avere paura. Piangi ogni volta che puoi. E poi ridi, ogni volta che puoi. Impara a dire grazie e scusa (ma ricorda che grazie vale cento volte dire scusa). Non importa se dimenticherai queste parole, quando tra molti anni le ritroverai, magari per caso, ti accorgerai di averle conservate da qualche parte dentro di te.',
        'it',
        'https://books.google.com/books/content?id=9RhNEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8858862104',
        'Il coccodrillo',
        'Fëdor Dostoevskij',
        'Feltrinelli Editore',
        '2024',
        'Fiction',
        '“Quindi si tratta sempre di quello stupido pettegolezzo a proposito del Coccodrillo?” Nella nuova galleria commerciale di Pietroburgo, il Passage, ha da poco aperto un’esotica meraviglia: una via di mezzo tra una Wunderkammer e uno zoo in miniatura dove, insieme ad altri animali provenienti da luoghi lontani, è esposto un vero coccodrillo vivo. Il funzionario amministrativo Ivan Matveič, incuriosito da tutto ciò che è nuovo e alla moda, decide di visitare il luogo insieme alla giovane moglie e a un amico. Convinto della propria superiorità, esempio perfetto di piccolo borghese semicolto e pieno di sé, frustrato per la mancanza di attenzione nei suoi confronti, Ivan Matveič non resiste alla tentazione di stuzzicare il grande rettile che se ne sta pacificamente disteso nella sua vasca. Infastidito, l’animale spalanca le fauci e lo inghiotte tutto intero. In poco tempo si capisce che l’impiegato non ha subìto danni, ma è ancora vivo e in perfetta salute. Anzi, in quella inusuale e scomoda posizione, comprende di aver finalmente conquistato la rilevanza e il prestigio che fin lì gli sono mancati: un’occasione imperdibile per lasciare il proprio segno nel mondo, dispensando a chiunque voglia sentirle le sue opinioni sugli argomenti più diversi. Partendo da questa situazione, che nulla ha da invidiare alla letteratura fantastica, Dostoevskij sviluppa una serie di spunti satirici e argomentazioni polemiche, che non mancarono di far discutere critici e lettori suoi contemporanei.',
        'it',
        'https://books.google.com/books/content?id=9FErEQAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8860404401',
        'Croazia',
        'Vesna Maric,Anja Mutic',
        'EDT srl',
        '2009',
        'Travel',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=-ni23TwXW7sC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8861375596',
        'La fabbrica dei giochi',
        'Maurizio Parente',
        'Edizioni Erickson',
        '2010',
        'Education',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=RN_pv8y1EOcC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8868301385',
        'Senna',
        'Leo Turrini',
        'Imprimatur editore',
        '2014',
        'Biography & Autobiography',
        'Nel ventennale dalla scomparsa del più talentuoso tra i piloti di F1, arriva in libreria il tributo di Leo Turrini, giornalista e amico personale di Ayrton Senna. «Non sono una macchina, non sono imbattibile; semplicemente l\'automobilismo fa parte di me, del mio corpo. Quattro ruote, un sedile, un volante. E questa è la mia vita da sempre» Ayrton Senna non era solo il Campionissimo della Formula Uno. Dentro gli autodromi ha lasciato un vuoto incolmabile, perché il suo talento non era replicabile. Ma la dimensione del personaggio valicava i confini dell\'automobilismo: le cronache del suo funerale, al quale parteciparono oltre cinque milioni di brasiliani, furono il sigillo di un vita dedicata sì alla passione per le corse, ma anche all\'impegno in favore delle masse più umili del suo popolo. Di Ayrton, tre volte iridato nel 1988, nel 1990 e nel 1991, Leo Turrini ha raccontato la carriera in presa diretta, tra grandi trionfi e cocenti sconfitte, tra gesti di maestosa nobiltà agonistica e rovinose cadute di stile. Sempre in bilico sul crinale dell\'emozione, Senna era come un supereroe dei fumetti sulle piste e un uomo dalla fragile sensibilità nelle esperienze quotidiane. La gente, non soltanto nel suo amatissimo Brasile, aveva imparato a comprenderne la doppia identità: per questo, l\'1 maggio 1994, il lutto per la tragedia di Imola fu collettivo, enorme, non consolabile. Questo libro, che comincia dalla fine, con l\'ultimo viaggio sull\'aereo che ospita in business class la salma del Campionissimo, non è e non vuole essere una biografia. E\' un tributo figlio della gratitudine. Perché chi ha conosciuto almeno un po\' Ayrton Senna ha un debito con il destino. Leo Turrini racconta le avventure della F1 per i quotidiani del gruppo Poligrafici («Resto del Carlino», «Nazione» e «Giorno») dai primi anni Ottanta. Ha seguito dal vivo quasi 400 Gran Premi di Formula Uno. Opinionista di Sky, considera l\'incontro con Ayrton Senna una delle sue più grandi fortune umani e professionali.',
        'it',
        'https://books.google.com/books/content?id=MDhQAwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8868572982',
        'Dolore minimo',
        'GIovanna Cristina Vivinetto',
        'Interlinea',
        '2019',
        'Poetry',
        'Il «dolore minimo» del titolo esprime la complessa condizione transessuale pronunciata con grande potenza poetica, volta a infrangere, per la prima volta in Italia, il muro del silenzioso tabù culturale. La giovane autrice racconta la sua rinascita luminosa con versi, delicati e profondissimi al tempo stesso, che hanno fatto parlare Dacia Maraini e Alessandro Fo di caso letterario dell’anno. «Quando nacqui mia madre / mi fece un dono antichissimo. / Il dono dell’indovino Tiresia: / mutare sesso una volta nella vita», narra Giovanna Cristina Vivinetto, che, in questo dirompente diario in versi, confessa: «non mi sono mai conosciuta / se non nel dolore bambino / di avvertirmi a un tratto / così divisa. Così tanto parziale».',
        'it',
        'https://books.google.com/books/content?id=ix2hDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8873395007',
        'Tutto comincia dalle stelle',
        'Margherita Hack,Gianluca Ranzini',
        'SPERLING & KUPFER',
        '2011',
        'Juvenile Nonfiction',
        'Nel racconto della grande astrofisica e di un divulgatore esperto, l\'evoluzione del cosmo diventa una storia affascinante dedicata ai ragazzi, ma capace di appassionare i lettori di tutte le età.',
        'it',
        'https://books.google.com/books/content?id=j3D2Dx14amgC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8876383697',
        'L\'isola dei pinguini',
        'Anatole France',
        'Isbn Edizioni',
        '2013',
        'Fiction',
        'Anno mille, più o meno. Un vecchio monaco quasi cieco sbarca su un’isola bretone popolata da pinguini. Scambiandoli per esseri umani, li battezza tutti. Per rimediare all’errore, Dio e i santi decidono di concedere ai volatili «un’anima, però di piccola taglia». Peccato che dalla conversione in poi, i pinguini sviluppino avidità e invidia, prepotenza e conformismo, ambizioni e pudori (il primo pinguino vestito viene violentato da un diavolo travestito da prete). A partire da questo antefatto, Anatole France traccia la storia di Pinguinia come controcanto amaro, rivelatore e irresistibilmente comico, dell’evoluzione dell’Europa dal Medioevo fino alla Rivoluzione industriale. Uno dei migliori romanzi satirici del Novecento, capace di fare arrabbiare i cattolici e infuriare i borghesi, amato da Conrad, Benjamin e Jung. Pubblicato per la prima volta nel 1908, è stato per lungo tempo considerato il capolavoro di Anatole France e paragonato a classici come La fattoria degli animali di Orwell e Il mondo nuovo di Huxley.',
        'it',
        'https://books.google.com/books/content?id=FPPoMHYJQ4cC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8890185074',
        'Le notti bianche',
        'Fëdor Dostoevskij',
        'La Riflessione',
        '2006',
        'Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=3uY8X-4XD8YC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8892742027',
        'My Policeman: Storia di un amore impossibile',
        'Bethan Roberts',
        'FRASSINELLI',
        '2021',
        'Fiction',
        '*** Ora un film originale Prime Video *** Dopo appena due giorni a godere delle possibilità offerte da Venezia, dissi: «Dovremmo venire a vivere qui». E la risposta di Tom fu: «Dovremmo andare sulla luna». Ma sulle sue labbra c\'era un sorriso. Marion e Patrick: così simili nella loro presunzione romantica di ottenere ciò che desiderano. Per entrambi, l\'oggetto del desiderio si chiama Tom Burgess, un giovane uomo dal fascino irresistibile e imperscrutabile. Tom è il fratello maggiore della migliore amica di Marion. Si conoscono da adolescenti e per lei è amore a prima vista. Di lì a poco, Tom parte per il servizio militare e poi per l\'accademia di polizia, ma Marion è decisa ad aspettarlo, a conquistarlo, sperando in una proposta di matrimonio. Quando finalmente arriva, lei è al settimo cielo, incurante dei segnali che dovrebbero metterla in guardia. Convinta che il suo amore basterà per entrambi. Ignara che Tom ha un\'altra vita. Patrick è un artista e lavora come curatore al museo di Brighton. Anche per lui Tom è stato un colpo di fulmine, una folle beatitudine per cui sarebbe disposto a rischiare tutto. Ma nell\'Inghilterra di fine anni Cinquanta, in cui l\'omosessualità è condannata dalla società e dalla legge, il matrimonio resta per Tom un nascondiglio sicuro. E così, Marion e Patrick dovranno dividersi l\'amore di Tom, fino a quando uno di loro non resisterà più. E le loro tre vite saranno spezzate.',
        'it',
        'https://books.google.com/books/content?id=IkNIEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8893429713',
        'La prigione di carta',
        'Marco Onnembo',
        'SPERLING & KUPFER',
        '2020',
        'Fiction',
        'Un professore appassionato e idealista in lotta per salvare la scrittura e i libri dall\'oblio. Un romanzo distopico di grande attualità nell\'era del digitale. Malcolm King è professore di scrittura creativa al college di Brownsville, dove vive con la moglie Lynette e il figlio Buddy. Idealista dalla solida cultura umanistica, insegna alla prima generazione di studenti che non sa scrivere a mano. La digitalizzazione ha vinto: il governo ha imposto che ogni tipo di contenuto esistesse solo in formato elettronico, mettendo al bando i libri cartacei dal sistema scolastico e abolendo l\'uso della scrittura con inchiostro. King temeva che la conoscenza potesse essere manipolata. Che i giovani potessero essere manipolati. Che gli uomini, e la loro coscienza, potessero essere manipolati. Credendo di poter contrastare quella legge e cambiare il mondo con il dialogo e la resistenza pacifica, il professore sarà invece condannato all\'ergastolo in un carcere di massima sicurezza. Dalla sua prigione, di nascosto, e con la complicità di un criminale e di un secondino, riuscirà però a recuperare fogli, penne e matite: materiale proibito, armi di libertà. Per raccontare la sua vita. Compiendo l\'atto più sovversivo che ci sia concesso dalla scrittura: scegliere il nostro destino.',
        'it',
        'https://books.google.com/books/content?id=RJrnDwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8893679833',
        'In cucina con Ciccio',
        'CiccioGamer89',
        'Salani',
        '2020',
        'Cooking',
        '50 ricette per ogni occasione e alla portata di tutti! I burger più gustosi ma non solo: dai mini sandwich avocado e salmone alla pagnotta all’amatriciana, dai supplì con stracciata di bufala ai funghi ripieni, per finire con un bel maritozzo alla panna. Un viaggio tra i fornelli della mia cucina con tutte le mie ricette preferite, pronte a sorprendervi grazie allo speciale tocco alla Ciccio. Che tu sia un principiante o un esperto, che tu voglia seguire le mie ricette o personalizzarle con un tocco di fantasia, tutto ciò che devi fare è indossare un grembiule e… iniziare a spadellare! E che fai te ne privi?',
        'it',
        'https://books.google.com/books/content?id=VZAIEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '8893903008',
        'Il mare e il silenzio',
        'Peter Cunningham',
        'SEM',
        '2020',
        'Fiction',
        'Irlanda, anni Quaranta. Giovane e bellissima, Iz vive sulla costa sudorientale con suo marito Ronnie, vicino a un mare spesso inquieto che ama contemplare. Le circostanze che l’hanno portata ad abitare non lontano da Monument, una città portuale, sono però avvolte nel mistero. L’emergenza mondiale si è infatti riflettuta nel microcosmo di Iz che, dopo aver conosciuto il vero amore, è stata costretta da ragioni più grandi di lei a fidanzarsi con un uomo che non amava in nome della sicurezza finanziaria e poi a sposare Ronnie per il bene della sua famiglia. Inizialmente le cose con il marito sembrano funzionare, ma poi precipitano man mano che le debolezze di Ronnie vengono alla luce. Ripercorrendo i decenni successivi alla conquista dell’indipendenza da parte dell’Irlanda, tra le brucianti questioni di classe e gli scontri fra protestanti e cattolici, Il mare e il silenzio è un’epica storia d’amore ambientata tra gli sfarzi del ceto privilegiato anglo-irlandese ormai in declino, che Cunningham ritrae con sapiente maestria. Un romanzo potente da uno dei migliori scrittori irlandesi sulla turbolenta nascita di una nazione e sugli amanti che ha diviso.',
        'it',
        'https://books.google.com/books/content?id=AdWVEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9780192835956',
        'Faust',
        'Johann Wolfgang von Goethe',
        'Oxford University Press, USA',
        '1998',
        'Drama',
        'The legend of Faust grew up in the sixteenth century, a time of transition between medieval and modern culture in Germany. Johann Wolfgang von Goethe (1749-1832) adopted the story of the wandering conjuror who accepts Mephistopheles\'s offer of a pact, selling his soul for the devil\'s greaterknowledge; over a period of 60 years he produced one of the greatest dramatic and poetic masterpieces of European literature.David Luke\'s recent translation, specially commissioned for The World\'s Classics series, has all the virtues of previous classic translations of Faust, and none of their shortcomings. Cast in rhymed verse, following the original, it preserves the essence of Goethe\'s meaning without sacrifice toarchaism or over-modern idiom. It is as near an \'equivalent\' rendering of the German as has been achieved.',
        'en',
        'https://books.google.com/books/content?id=_Sbju4F0AVAC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788804611516',
        'Il leone',
        'Nelson DeMille',
        'undefined',
        '2011',
        'Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=pHAJtwAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788804639718',
        'Il signore delle mosche',
        'William Golding',
        'undefined',
        '2014',
        'Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=IY08MwEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788804646846',
        'Le cronache di Narnia',
        'Clive S. Lewis',
        'undefined',
        '2014',
        'Juvenile Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=F2iOoAEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788807820885',
        'Romeo e Giulietta',
        'William Shakespeare',
        'Feltrinelli Editore',
        '1998',
        'Drama',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=L3um5uXCmUkC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788807821097',
        'Il processo',
        'Franz Kafka',
        'Feltrinelli Editore',
        '2002',
        'Fiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=NDtEUZ0QWiEC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788808170033',
        'Fondamenti di basi di dati',
        'Antonio Albano,Giorgio Ghelli,Renzo Orsini',
        'undefined',
        '2005',
        'Computers',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=pVPCAAAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788833246598',
        'Senna. Le verità',
        'Franco Nugnes',
        'undefined',
        '2024',
        'Biography & Autobiography',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=zOGu0AEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788834009635',
        'Amore, sesso e cuore',
        'Alexander Lowen',
        'undefined',
        '1989',
        'Health & Fitness',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=J3dLAAAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788842498551',
        'L\'arte di Bill Viola',
        'Chris Townsend',
        'Pearson Italia S.p.a.',
        '2005',
        'Art',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=USJskmBoLHYC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788846208002',
        'La peste',
        'Albert Camus',
        'undefined',
        '2005',
        'Fiction',
        'undefined',
        'en',
        'https://books.google.com/books/content?id=MpMbAAAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788871923420',
        'Sistemi embedded. Sviluppo hardware e software per sistemi dedicati',
        'William Fornaciari,Carlo Brandolese',
        'Pearson Italia S.p.a.',
        '2007',
        'Computers',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=3FfZYF9vLY0C&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788883423178',
        'Storie di rally. Quarant\'anni di uomini e avventure raccontati dal poeta delle corse',
        'Guido Rancati',
        'Edizioni Pendragon',
        '2004',
        'Sports & Recreation',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=T18sKhMG7bIC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788891802699',
        'Sotto le cuffie',
        'Favij',
        'undefined',
        '2015',
        'Biography & Autobiography',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=_bQIogEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9788891808394',
        'Conosciamoci meglio',
        'Virginia De Giglio',
        'undefined',
        '2017',
        'Juvenile Nonfiction',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=ZgNEMQAACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9791041969258',
        'La lettera scarlatta',
        'Nathaniel Hawthorne',
        'BoD - Books on Demand',
        '2023',
        'Fiction',
        'Una folla d\'uomini barbuti, dagli abiti scuri e dai grigi cappelloni a punta, e di donne in cappuccio o a testa nuda, stava raccolta davanti a un edificio di legno, la cui porta di quercia massiccia era guarnita con bulloni di ferro. I fondatori d\'una colonia, qualunque Utopia di virtù e felicità umana possano aver divisato in origine, hanno sempre riconosciuto tra le prime necessità pratiche quella di destinare una parte del suolo vergine a cimitero, ed un\'altra a prigione...',
        'it',
        'https://books.google.com/books/content?id=sOTWEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9791220225007',
        'Ragazza luna',
        'Leonard Robert',
        'New-Book Edizioni',
        '2020',
        'Poetry',
        'Sei a casa, ti va di entrare? Questo libro vuole essere un rifugio, quel piccolo posto in cui sentirti bene, riflettere, comprendere quanto valore hai. Una raccolta di poesie in versi liberi che spero possano rendere più libera anche te, perché la tua libertà è importante ai miei occhi e mi auguro, anzi ti auguro, sia la tua priorità. Questo libro vuole essere tuo amico, una persona con la quale sentirti trasparente, con la quale non devi avere timore di essere te stessa. Togli la maschera, poggiala sul comodino; tira giù la cerniera del petto e ammira la luna all\'interno, le stelle, i pianeti, l\'universo tutto, che solo tu sai essere. Qui sei a casa, l\'ho costruita notte dopo notte, adesso è pronta e tu non devi fare altro che entrare, perché è il posto sicuro tirato su per te, ma all\'arredamento pensaci tu: arredala come più ti piace, ora è tua e questo libro è la chiave, entra.',
        'it',
        'https://books.google.com/books/content?id=xdpJEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        '9791254620243',
        'Il manifesto di Unabomber. La società industriale e il suo futuro',
        'Theodore John Kaczynski',
        'undefined',
        '2022',
        'Business & Economics',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=jL7vzgEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        'BML:37001102649147',
        'I promessi sposi',
        'Alessandro Manzoni',
        'undefined',
        '0000',
        'undefined',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=LbHAsCsSD6YC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        'HARVARD:32044',
        'La visione di Alberico',
        'Alberico (da Montecassino.),Catello De Vivo',
        'undefined',
        '0000',
        'undefined',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=4Yc_wRdHbwkC&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        'IND:30000100662224',
        'Harry Potter e il prigioniero di Azkaban',
        'J. K. Rowling',
        'Distribooks',
        '2000',
        'Juvenile Fiction',
        'Tra colpi di scena, mappe stregate e ippogrifi scontrosi, zie volanti e libri che mordono, Harry Potter conduce il lettore nel terzo capitolo delle sue avventure. Harry, giovane studente della prestigiosa Scuola di Magia e Stregoneria di Hogwarts, è questa volta alle prese con un famigerato assassino che, evaso dalla terribile prigione di Azkaban, gli sta dando la caccia per ucciderlo. Forse questa volta nemmeno la Scuola di Magia, nemmeno gli amici più cari potranno aiutarlo, almeno fino a quando si nasconderà tra di loro un traditore... Età di lettura: da 10 anni.',
        'it',
        'https://books.google.com/books/content?id=89QHAQAAMAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    ),
    (
        'UOM:39015053109289',
        'Storia della Formula uno',
        'Guido Staderini',
        'Mondadori Electa',
        '2001',
        'Sports & Recreation',
        'undefined',
        'it',
        'https://books.google.com/books/content?id=B_dOAAAAMAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api&fife=w328'
    );

--
-- Dump dei dati per la tabella `Copia`
--
INSERT INTO
    `Copia` (
        `ID`,
        `ISBN`,
        `proprietario`,
        `disponibile`,
        `condizioni`
    )
VALUES
    (1, '8893679833', 'admin@admin.com', 1, 'nuovo'),
    (
        2,
        '9788891808394',
        'admin@admin.com',
        1,
        'danneggiato'
    ),
    (
        3,
        '8841253169',
        'admin@admin.com',
        1,
        'come nuovo'
    ),
    (
        4,
        '9788834009635',
        'admin@admin.com',
        1,
        'danneggiato'
    ),
    (5, '8841216301', 'admin@admin.com', 1, 'nuovo'),
    (6, '9788804646846', 'user@user.com', 0, 'nuovo'),
    (
        9,
        '8858407148',
        'matteobazzan333@gmail.com',
        1,
        'nuovo'
    ),
    (
        10,
        '8876383697',
        'matteobazzan333@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        11,
        '8835700159',
        'frafra35@pucci.com',
        1,
        'usato'
    ),
    (
        12,
        '883573469X',
        'frafra35@pucci.com',
        1,
        'come nuovo'
    ),
    (
        13,
        '8858820746',
        'frafra35@pucci.com',
        1,
        'usato ma ben conservato'
    ),
    (
        14,
        '8858693361',
        'mariabranch@pucci.com',
        1,
        'come nuovo'
    ),
    (
        15,
        '1781101582',
        'mariabranch@pucci.com',
        1,
        'usato'
    ),
    (
        16,
        '8809754530',
        'pinetto@pino.com',
        1,
        'usato ma ben conservato'
    ),
    (
        17,
        '9791220225007',
        'mariabranch@pucci.com',
        1,
        'usato'
    ),
    (
        18,
        '8811815290',
        'mariabranch@pucci.com',
        1,
        'nuovo'
    ),
    (
        19,
        '8835700159',
        'gingin@pucci.com',
        1,
        'danneggiato'
    ),
    (
        20,
        '883573469X',
        'gingin@pucci.com',
        1,
        'usato ma ben conservato'
    ),
    (
        21,
        '8873395007',
        'gingin@pucci.com',
        1,
        'usato ma ben conservato'
    ),
    (
        22,
        '8827225250',
        'gingin@pucci.com',
        1,
        'come nuovo'
    ),
    (
        23,
        '8858407865',
        'noah95@pucci.com',
        1,
        'come nuovo'
    ),
    (24, '8835711509', 'noah95@pucci.com', 1, 'usato'),
    (
        25,
        '8868572982',
        'noah95@pucci.com',
        1,
        'usato ma ben conservato'
    ),
    (
        26,
        '8831804499',
        'noah95@pucci.com',
        1,
        'come nuovo'
    ),
    (
        27,
        '1452182175',
        'mariorossi@gmail.com',
        1,
        'come nuovo'
    ),
    (
        28,
        '8890185074',
        'utente@utente.com',
        0,
        'come nuovo'
    ),
    (
        29,
        '9788807821097',
        'utente@utente.com',
        1,
        'usato'
    ),
    (
        30,
        '8822762576',
        'utente@utente.com',
        1,
        'usato ma ben conservato'
    ),
    (31, '1451685556', 'utente@utente.com', 1, 'nuovo'),
    (
        32,
        '8845985466',
        'user@user.com',
        0,
        'come nuovo'
    ),
    (
        34,
        '8858819551',
        'user@user.com',
        0,
        'come nuovo'
    ),
    (
        35,
        '8854980579',
        'user@user.com',
        0,
        'usato ma ben conservato'
    ),
    (
        36,
        '8858818962',
        'utente@utente.com',
        1,
        'come nuovo'
    ),
    (
        37,
        '1781101582',
        'utente@utente.com',
        1,
        'usato ma ben conservato'
    ),
    (
        38,
        '8858814274',
        'utente@utente.com',
        1,
        'usato ma ben conservato'
    ),
    (
        39,
        'BML:37001102649147',
        'utente@utente.com',
        1,
        'danneggiato'
    ),
    (40, '8892742027', 'utente@utente.com', 1, 'usato'),
    (
        41,
        '8828103019',
        'user@user.com',
        1,
        'come nuovo'
    ),
    (42, '8858845145', 'user@user.com', 1, 'usato'),
    (
        43,
        '9788842498551',
        'user@user.com',
        1,
        'danneggiato'
    ),
    (
        44,
        '8854529486',
        'user@user.com',
        1,
        'come nuovo'
    ),
    (
        45,
        '9788807821097',
        'noah95@pucci.com',
        1,
        'danneggiato'
    ),
    (
        46,
        '8835700159',
        'utente@utente.com',
        1,
        'usato ma ben conservato'
    ),
    (
        47,
        '8845985466',
        'elena@elena.com',
        1,
        'usato ma ben conservato'
    ),
    (
        48,
        '8845985466',
        'sarah@pucci.com',
        1,
        'come nuovo'
    ),
    (51, '8845985466', 'pinetto@pino.com', 1, NULL),
    (52, '8845985466', 'frafra35@pucci.com', 1, NULL),
    (
        53,
        '8858407865',
        'mariabranch@pucci.com',
        1,
        NULL
    ),
    (54, '8858407865', 'mariorossi@gmail.com', 1, NULL),
    (55, '8858407865', 'utente@utente.com', 1, 'usato'),
    (
        56,
        '1647573076',
        'utente@utente.com',
        1,
        'usato ma ben conservato'
    ),
    (
        57,
        '8858407865',
        'mari123@gmail.com',
        1,
        'come nuovo'
    ),
    (58, '8854175382', 'mari123@gmail.com', 1, 'usato'),
    (
        59,
        '8852024182',
        'mariorog123@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        60,
        '9788807821097',
        'mari123@gmail.com',
        1,
        'come nuovo'
    ),
    (
        61,
        '3319672207',
        'mariorog123@gmail.com',
        0,
        'come nuovo'
    ),
    (
        62,
        '8854175382',
        'user@user.com',
        0,
        'come nuovo'
    ),
    (
        63,
        '1477730141',
        'mariorossi@gmail.com',
        0,
        'usato ma ben conservato'
    ),
    (64, '9788846208002', 'user@user.com', 0, 'nuovo'),
    (66, '8852220828', 'mari123@gmail.com', 0, 'usato'),
    (
        67,
        '8861375596',
        'user@user.com',
        1,
        'usato ma ben conservato'
    ),
    (
        68,
        '8851800413',
        'mariorossi@gmail.com',
        1,
        'come nuovo'
    ),
    (
        69,
        'IND:30000100662224',
        'user@user.com',
        1,
        'usato'
    ),
    (
        70,
        '8835728657',
        'user@user.com',
        1,
        'usato ma ben conservato'
    ),
    (
        71,
        '8851130892',
        'user@user.com',
        1,
        'come nuovo'
    ),
    (
        72,
        '8832104849',
        'luca.rib@gmail.com',
        1,
        'danneggiato'
    ),
    (
        74,
        '1781102120',
        'luca.rib@gmail.com',
        1,
        'nuovo'
    ),
    (
        75,
        '8893429713',
        'luca.rib@gmail.com',
        1,
        'danneggiato'
    ),
    (
        76,
        '8834741587',
        'luca.rib@gmail.com',
        1,
        'come nuovo'
    ),
    (
        77,
        '8893903008',
        'luca.rib@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        78,
        '8858407865',
        'luca.rib@gmail.com',
        0,
        'usato'
    ),
    (
        79,
        '8850322089',
        'ale.berna@gmail.com',
        1,
        'come nuovo'
    ),
    (
        80,
        '9788871923420',
        'ale.berna@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        81,
        '885842431X',
        'ale.berna@gmail.com',
        0,
        'usato'
    ),
    (
        82,
        '9780192835956',
        'ale.berna@gmail.com',
        1,
        'nuovo'
    ),
    (
        83,
        '1498531911',
        'ale.berna@gmail.com',
        0,
        'come nuovo'
    ),
    (
        85,
        '232239209X',
        'ale.berna@gmail.com',
        1,
        'danneggiato'
    ),
    (
        86,
        '8858529413',
        'ale.math@gmail.com',
        1,
        'come nuovo'
    ),
    (
        87,
        '8858407865',
        'ale.math@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        88,
        '885842431X',
        'ale.math@gmail.com',
        1,
        'usato'
    ),
    (
        89,
        '8893903008',
        'ale.math@gmail.com',
        1,
        'usato'
    ),
    (
        90,
        '9788807821097',
        'ale.math@gmail.com',
        1,
        'danneggiato'
    ),
    (
        91,
        '8890185074',
        'ale.math@gmail.com',
        0,
        'come nuovo'
    ),
    (92, '8868301385', 'user@user.com', 1, 'usato'),
    (
        93,
        '8858821815',
        'user@user.com',
        0,
        'usato ma ben conservato'
    ),
    (
        94,
        '8832104849',
        'user@user.com',
        1,
        'danneggiato'
    ),
    (
        95,
        '8834741587',
        'utente@utente.com',
        1,
        'come nuovo'
    ),
    (
        96,
        '8854980579',
        'luca.rib@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        97,
        '1498531911',
        'luca.rib@gmail.com',
        0,
        'danneggiato'
    ),
    (
        98,
        '8851800413',
        'ale.math@gmail.com',
        1,
        'danneggiato'
    ),
    (
        99,
        '8858693361',
        'luca.rib@gmail.com',
        1,
        'usato ma ben conservato'
    ),
    (
        100,
        '8831804499',
        'luca.rib@gmail.com',
        1,
        'come nuovo'
    ),
    (
        101,
        '8831804499',
        'ale.berna@gmail.com',
        1,
        'usato'
    );

--
-- Dump dei dati per la tabella `Desiderio`
--
INSERT INTO
    `Desiderio` (`email`, `ISBN`)
VALUES
    ('ale.berna@gmail.com', '8820093839'),
    ('ale.berna@gmail.com', '8832104849'),
    ('ale.berna@gmail.com', '8858407865'),
    ('ale.berna@gmail.com', '8858819551'),
    ('ale.berna@gmail.com', '8858821815'),
    ('ale.berna@gmail.com', '8893903008'),
    ('ale.berna@gmail.com', '9788804611516'),
    ('ale.math@gmail.com', '8832104849'),
    ('ale.math@gmail.com', '8854980579'),
    ('ale.math@gmail.com', '8858407245'),
    ('ale.math@gmail.com', '8858415833'),
    ('ale.math@gmail.com', '8858819551'),
    ('ale.math@gmail.com', '8868301385'),
    ('ale.math@gmail.com', '9780192835956'),
    ('ale.math@gmail.com', '9788807820885'),
    ('elena@elena.com', '8822762576'),
    ('frafra35@pucci.com', '1781101582'),
    ('frafra35@pucci.com', '8811815290'),
    ('frafra35@pucci.com', '8858693361'),
    ('frafra35@pucci.com', '8860404401'),
    ('frafra35@pucci.com', '9791220225007'),
    ('gingin@pucci.com', '8858407865'),
    ('gingin@pucci.com', '8858693361'),
    ('gingin@pucci.com', '8858818962'),
    ('gingin@pucci.com', '8868572982'),
    ('gingin@pucci.com', '9791220225007'),
    ('luca.rib@gmail.com', '8830448036'),
    ('luca.rib@gmail.com', '8836005195'),
    ('luca.rib@gmail.com', '8836011098'),
    ('luca.rib@gmail.com', '885842431X'),
    ('luca.rib@gmail.com', '8858819551'),
    ('luca.rib@gmail.com', '8858821815'),
    ('luca.rib@gmail.com', '8868301385'),
    ('luca.rib@gmail.com', '9788807821097'),
    ('luca.rib@gmail.com', '9788883423178'),
    ('mari123@gmail.com', '8845985466'),
    ('mari123@gmail.com', '8852024182'),
    ('mari123@gmail.com', '8858775880'),
    ('mariabranch@pucci.com', '8831804499'),
    ('mariabranch@pucci.com', '8835700159'),
    ('mariabranch@pucci.com', '8852035826'),
    ('mariabranch@pucci.com', '8858820746'),
    ('mariorog123@gmail.com', '8854175382'),
    ('mariorog123@gmail.com', '9788804646846'),
    ('mariorossi@gmail.com', '1647573076'),
    ('mariorossi@gmail.com', '8854175382'),
    ('mariorossi@gmail.com', '8861375596'),
    ('matteobazzan333@gmail.com', '8809754530'),
    ('noah95@pucci.com', '1665904968'),
    ('noah95@pucci.com', '883573469X'),
    ('noah95@pucci.com', '8854505935'),
    ('noah95@pucci.com', '8854980579'),
    ('noah95@pucci.com', '8873395007'),
    ('pinetto@pino.com', '8858407148'),
    ('sarah@pucci.com', '1451685556'),
    ('user@user.com', '1477730141'),
    ('user@user.com', '1498531911'),
    ('user@user.com', '3319672207'),
    ('user@user.com', '8834741587'),
    ('user@user.com', '8835703891'),
    ('user@user.com', '8845975819'),
    ('user@user.com', '8851800413'),
    ('user@user.com', '885205720X'),
    ('user@user.com', '8852220828'),
    ('user@user.com', '8855057014'),
    ('user@user.com', '8858840941'),
    ('user@user.com', '8890185074'),
    ('user@user.com', '8893679833'),
    ('user@user.com', '9788804639718'),
    ('user@user.com', '9788807821097'),
    ('user@user.com', '9788891802699'),
    ('utente@utente.com', '8804591382'),
    ('utente@utente.com', '8831804499'),
    ('utente@utente.com', '8845984672'),
    ('utente@utente.com', '8845985466'),
    ('utente@utente.com', '8850322089'),
    ('utente@utente.com', '8854980579'),
    ('utente@utente.com', '8858407245'),
    ('utente@utente.com', '8858611446'),
    ('utente@utente.com', '8858693361'),
    ('utente@utente.com', '8858819551'),
    ('utente@utente.com', '8858862104'),
    ('utente@utente.com', '8893429713'),
    ('utente@utente.com', '9788808170033'),
    ('utente@utente.com', '9791041969258');

--
-- Dump dei dati per la tabella `Scambio`
--
INSERT INTO
    `Scambio` (
        `ID`,
        `emailProponente`,
        `emailAccettatore`,
        `idCopiaProp`,
        `idCopiaAcc`,
        `dataProposta`,
        `dataConclusione`,
        `stato`
    )
VALUES
    (
        1,
        'pinetto@pino.com',
        'matteobazzan333@gmail.com',
        16,
        9,
        '2025-01-15',
        NULL,
        'in attesa'
    ),
    (
        3,
        'matteobazzan333@gmail.com',
        'pinetto@pino.com',
        9,
        16,
        '2025-01-20',
        NULL,
        'in attesa'
    ),
    (
        4,
        'frafra35@pucci.com',
        'mariabranch@pucci.com',
        11,
        14,
        '2025-01-13',
        '2025-01-15',
        'accettato'
    ),
    (
        6,
        'mariabranch@pucci.com',
        'frafra35@pucci.com',
        18,
        13,
        '2025-01-22',
        NULL,
        'in attesa'
    ),
    (
        7,
        'gingin@pucci.com',
        'noah95@pucci.com',
        20,
        23,
        '2025-01-23',
        '2025-01-24',
        'accettato'
    ),
    (
        8,
        'mariabranch@pucci.com',
        'gingin@pucci.com',
        17,
        21,
        '2025-01-27',
        '2025-01-28',
        'rifiutato'
    ),
    (
        9,
        'user@user.com',
        'utente@utente.com',
        32,
        28,
        '2025-01-29',
        NULL,
        'accettato'
    ),
    (
        10,
        'user@user.com',
        'utente@utente.com',
        35,
        30,
        '2025-01-29',
        NULL,
        'rifiutato'
    ),
    (
        11,
        'mariorog123@gmail.com',
        'mari123@gmail.com',
        59,
        58,
        '2025-01-29',
        '2025-01-29',
        'accettato'
    ),
    (
        12,
        'mariorog123@gmail.com',
        'user@user.com',
        61,
        6,
        '2025-01-29',
        NULL,
        'accettato'
    ),
    (
        13,
        'mariorossi@gmail.com',
        'user@user.com',
        63,
        62,
        '2025-01-29',
        NULL,
        'accettato'
    ),
    (
        15,
        'mari123@gmail.com',
        'user@user.com',
        66,
        64,
        '2025-01-29',
        NULL,
        'accettato'
    ),
    (
        17,
        'utente@utente.com',
        'mariabranch@pucci.com',
        46,
        14,
        '2025-01-30',
        NULL,
        'in attesa'
    ),
    (
        18,
        'user@user.com',
        'ale.berna@gmail.com',
        34,
        83,
        '2025-01-30',
        NULL,
        'accettato'
    ),
    (
        19,
        'user@user.com',
        'ale.math@gmail.com',
        35,
        91,
        '2025-01-30',
        NULL,
        'accettato'
    ),
    (
        21,
        'ale.berna@gmail.com',
        'luca.rib@gmail.com',
        81,
        78,
        '2025-01-30',
        NULL,
        'accettato'
    ),
    (
        22,
        'luca.rib@gmail.com',
        'user@user.com',
        97,
        93,
        '2025-01-30',
        NULL,
        'accettato'
    ),
    (
        23,
        'luca.rib@gmail.com',
        'utente@utente.com',
        75,
        29,
        '2025-01-30',
        NULL,
        'in attesa'
    );

--
-- Dump dei dati per la tabella `Recensione`
--
INSERT INTO
    `Recensione` (
        `emailRecensito`,
        `idScambio`,
        `dataPubblicazione`,
        `valutazione`,
        `contenuto`
    )
VALUES
    (
        'ale.berna@gmail.com',
        21,
        '2025-01-30',
        5,
        'libro tenuto bene e consegna veloce'
    ),
    (
        'ale.math@gmail.com',
        19,
        '2025-01-30',
        4,
        'Libro in ottime condizioni'
    ),
    (
        'luca.rib@gmail.com',
        22,
        '2025-01-30',
        5,
        'Molto veloce a rispondere e a spedire il libro'
    ),
    (
        'mari123@gmail.com',
        15,
        '2025-01-29',
        2,
        'lento a inviare il libro ma tenuto bene'
    ),
    (
        'mariorog123@gmail.com',
        11,
        '2025-01-29',
        2,
        'Consegna veloce ma alcune pagine erano rovinate'
    ),
    (
        'mariorog123@gmail.com',
        12,
        '2025-01-29',
        4,
        'consegna un pochino lenta ma libro in ottimi stati'
    ),
    (
        'mariorossi@gmail.com',
        13,
        '2025-01-29',
        5,
        'Consegna veloce e libro tenuto molto bene'
    ),
    (
        'user@user.com',
        9,
        '2025-01-29',
        5,
        'Libro tenuto molto bene e consegna veloce'
    ),
    (
        'user@user.com',
        18,
        '2025-01-30',
        3,
        'molto veloce a spedire ma alcune pagine erano piegate'
    ),
    (
        'user@user.com',
        19,
        '2025-01-30',
        5,
        'Libro tenuto molto bene'
    );