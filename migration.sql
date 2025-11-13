DROP DATABASE IF EXISTS quiz_system;
CREATE DATABASE quiz_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quiz_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz_id (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question_id (question_id),
    INDEX idx_is_correct (is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_quiz_id (quiz_id),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password, role) VALUES ('admin', 'admin', 'admin');

INSERT INTO quizzes (name, description) VALUES
('Formula 1', 'Jautājumi par modernās Formaulas 1 sacīkstēm, braucējiem un vēsturi.'),
('Mūzika', 'Mūzikas un mākslinieku viktorīna'),
('VTDT', 'Vai tu zini par vietiņu kur mācies vai strādā?(pārsvarā Cēsu VTDT)'),
('Latvija', 'Vispārīgi jautājumi par Latviju.'),
('Auto / Moto Latvijā', 'Tests par sportistiem un motorizētiem sporta veidiem Latvijā');


INSERT INTO questions (quiz_id, question_text) VALUES
(1, 'Vai Latvijā ir rīkots F1 posms?'),
(1, 'Kā sauc sacīkšu posmu, kas parasti notiek svētdienā?'),
(1, 'Kurš no šobrīd startējošiem bruacējiem ir 7kārtējs F1 pasaules čempions?'),
(1, 'Kura komanda ir visvairāk uzvarējusi F1 konstruktora čempionātā?'),
(1, 'Cik moderā F1 formulā ir pārnesumi (ātrumkārbā) neieskaitot N un Reversu?'),
(1, 'Kuru starta nummuru drīkst izmantot iepriekšējā gada F1 čempions sezonā?'),
(1, 'Cik punktus var iegūt par uzvaru F1 Grand Prix sacīkstēs?'),
(1, 'Kurš vinēja 2024. gada F1 Pasaules čempionātā?'),
(1, 'Kādas krāsas karogs tiek rādīt braucējiem, ja posms tiek apturēts?'),
(1, 'Kura komanda ieguva F1 konstruktora čempionātu 2025. gadā?'),
(1, 'Cik uzvaras ieguva Max Verstappen(s) 2023. gada F1 sezonā?(no 22 posmiem)'),
(1, 'Kuram bijušam F1 čempionam bija iesauka "Iceman"?'),
(1, 'Kur tiks aizvadīts nākamnedēļas F1 posms (ja es šo atrādu 13.11.2025)?'),
(1, 'Kurš šobrīd (13.11.2025) kopvērtējumā ir līderis pēc punktu summas?'),
(1, 'Cik (Drivers Championships) ieguva Sebastians Vetels ar Redbull komandu?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1,'Nē.',TRUE),(1,'Jā, es pat tur biju.',FALSE),
(2,'Grand Prix (Lielā balva)',TRUE),(2,'Kvalifikācija',FALSE),(2,'Brīvais treniņš',FALSE),(2,'Atbildes uz žurnālistu jautājumiem..',FALSE),
(3,'Lewis Hamilton',TRUE),(3,'Max Verstappen',FALSE),(3,'Fernando Alonso',FALSE),(3,'Michael Schumacher',FALSE),
(4,'Ferrari',TRUE),(4,'BrawnGP',FALSE),(4,'Haas',FALSE),(4,'BMW Sauber',FALSE),
(5,'8',TRUE),(5,'Kādi 6-7',FALSE),(5,'Automāts',FALSE),(5,'3',FALSE),
(6,'1',TRUE),(6,'100',FALSE),(6,'69',FALSE),(6,'93',FALSE),
(7,'25',TRUE),(7,'26',FALSE),(7,'10',FALSE),(7,'5',FALSE),
(8,'Max Verstappen',TRUE),(8,'Fernando Alonso',FALSE),(8,'Charles Leclerc',FALSE),(8,'Sebastian Vetel',FALSE),
(9,'Sarkans',TRUE),(9,'Zaļš',FALSE),(9,'Melns ar baltu(Finiša)',FALSE),(9,'Dzeltens',FALSE),
(10,'Mclaren',TRUE),(10,'Rebull',FALSE),(10,'Ferrari',FALSE),(10,'Mercedes',FALSE),
(11,'19',TRUE),(11,'0',FALSE),(11,'Visas',FALSE),(11,'7',FALSE),
(12,'Kimi Raikkonen',TRUE),(12,'Fernando Alonso',FALSE),(12,'Oscar Piastri',FALSE),(12,'Jack Doohan',FALSE),
(13,'Las Vegas(USA)',TRUE),(13,'Biķernieki(LV)',FALSE),(13,'Abu Dhabi(UAE)',FALSE),(13,'Sao Paulo (BR)',FALSE),
(14,'Lando Noriss',TRUE),(14,'Max Verstappen',FALSE),(14,'Oscar Piastri',FALSE),(14,'Fernando Alonso',FALSE),
(15,'4',TRUE),(15,'1',FALSE),(15,'7',FALSE),(15,'2',FALSE);


INSERT INTO questions (quiz_id, question_text) VALUES
(2,'Kura latviešu grupa ir pazīstama ar dziesmu "Ujā ujā nikni vilki"?'),
(2,'Kurā pilsētā ik pēc pieciem gadiem notiek Vispārējie latviešu Dziesmu un Deju svētki?'),
(2,'"Un es skrienu, skrienu vēl man vēl jāpaspēj..." autors ir?'),
(2,'Kādu tautas mūzikas instrumentu latvieši mēdz uzskatīt par nacionālo simbolu (līdzīgu citārai)?'),
(2,'Kādu slavenu dziesmu festivālu katru vasaru rīko Jūrmalā (lai gan nosaukumi mainās)?'),
(2,'Kurš latviešu mūziķis ir galvenais dziedātājs grupā "Prāta Vētra"?'),
(2,'Kura no šīm grupām NAV latviešu mūzikas grupa?'),
(2,'Kuras "grupas" galveno dziedātāju bieži dzird SWH radio stacijā un it īpaši Jāņu laikā?'),
(2,'Kā sauc tradicionālo latviešu deju, kas tiek dejota pāros un ir ļoti populāra Dziesmu svētkos?'),
(2,'Kurš sarakstija un izpildīja dziesmu "Lācītis"?'),
(2,'Kāds ir Latvijas populārākais radio, kas atskaņo latviešu mūziku?'),
(2,'Kurš mūziķis sacerēja tādas dziesmas kā "Ģenoveva" un "Muļķe sirds"?'),
(2,'Kāds ir vārds, ar kuru latvieši mēdz dēvēt svētkus, kad līgo un ēd sieru?'),
(2,'Vai "Labvēlīgā Tipa" dziesmas ir īsti "bangeri"?'),
(2,'Kāds latviešu tradicionālais ēdiens ir cieši saistīts ar Jāņu svinēšanu?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(16,'Prāta Vētra (Brainstorm)',FALSE),(16,'Dzels vilks',TRUE),(16,'Jumprava',FALSE),(16,'Instrumenti',FALSE),
(17,'Rīgā',TRUE),(17,'Siguldā',FALSE),(17,'Cēsīs',FALSE),(17,'Valmierā',FALSE),
(18,'Fredis',FALSE),(18,'Juris Kaukulis',FALSE),(18,'Lauris Reiniks',TRUE),(18,'Fredis Muša',FALSE),
(19,'Bungas',FALSE),(19,'Dūdas',FALSE),(19,'Kokle',TRUE),(19,'Vijole',FALSE),
(20,'Jaunais vilnis (New Wave)',TRUE),(20,'Zvaigžņu balle',FALSE),(20,'Vasaras ritmi',FALSE),(20,'Rīgas festivāls',FALSE),
(21,'Reinis Sējāns',FALSE),(21,'Renārs Kaupers',TRUE),(21,'Lauris Reiniks',FALSE),(21,'Intars Busulis',FALSE),
(22,'The Sound Poets',FALSE),(22,'Carnival Youth',FALSE),(22,'The Beatles',TRUE),(22,'Musiqq',FALSE),
(23,'Otra puse',FALSE),(23,'Eolika',FALSE),(23,'Labvēlīgais Tips',TRUE),(23,'Menuets',FALSE),
(24,'Uguns zīme',FALSE),(24,'Saule',FALSE),(24,'Laimas slotiņa',TRUE),(24,'Zalktis',FALSE),
(25,'Raimonds Pauls',FALSE),(25,'Edgars Liepiņš',TRUE),(25,'Zigmars Liepiņš',FALSE),(25,'Mārtiņš Brauns',FALSE),
(26,'Latvijas Radio 2',TRUE),(26,'Radio SWH',FALSE),(26,'European Hit Radio',FALSE),(26,'Latvijas Radio 1',FALSE),
(27,'Žoržs Siksna',TRUE),(27,'Raimonds Pauls',FALSE),(27,'Intars Busuilis',FALSE),(27,'Lauris Reiniks',FALSE),
(28,'Līgo',TRUE),(28,'Rudens saulgrieži',FALSE),(28,'Ziemassvētki',FALSE),(28,'Mārtiņdiena',FALSE),
(29,'Nē',FALSE),(29,'Kā dziesmas?',FALSE),(29,'Jā (šī ir pareizā atbilde btw)',TRUE),(29,'Nezinu',FALSE),
(30,'Jāņu siers',TRUE),(30,'Pelēkie zirņi ar speķi',FALSE),(30,'Sklandrausis',FALSE),(30,'Rupjmaize ar sviestu',FALSE);

-- ========================================================
-- VTDT
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(3,'Ko apzīmē VTDT?'),
(3,'Kurā pilsētā atrodas.. baltā VTDT skola?'),
(3,'Kurā pilsētā, jeb lielciemā atrodas... dzeltenā VTDT skola?'),
(3,'Kā sauc skolas direktoru?'),
(3,'VTDT piedāvā izglītību pēc kuras klases(pārsvarā)?'),
(3,'Kādu izglītību galvenokārt iegūst apsolvējot VTDT?'),
(3,'Cik ilgi jāmācās VTDT, lai iegūtu izglītību?'),
(3,'Kura no specialitātēm NAV viena no VTDT piedāvātajām nozarēm?'),
(3,'Kas tehnikumā pārstāv audzēkņu intereses un rīko pasākumus(palielam)?'),
(3,'Kas ir viena no VTDT prioritātēm attiecībā uz sadarbību ar uzņēmumiem Vidzemes reģionā?'),
(3,'Cikos piektdienās sākas stundas?'),
(3,'Cik ilgs ir pusdienu pārtaukums?'),
(3,'Cik ilga starp skolotājiem ir pieņemta Dinamiskā pauzīte?'),
(3,'Kāda OS ir 301. kabineta Cēsu datoros?'),
(3,'Uz kurieni dodas lielākā daļa skolnieku brokastu pauzītē?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(31,'Vidzemes Tehnoloģiju un Dizaina Tehnikums',TRUE),(31,'Ventspils Traktoristu un Direktoru Tusiņš ',FALSE),(31,'Vista, Trusis, Dinozaurs, Taurenis',FALSE),
(32,'Cēsīs',TRUE),(32,'Priekuļos',FALSE),(32,'Valmierā',FALSE),(32,'Jāņmuižā',FALSE),
(33,'Priekuļos',TRUE),(33,'Cēsīs',FALSE),(33,'Preiļos',FALSE),(33,'Iemetējos',FALSE),
(34,'Artūrs Sņegovičs',TRUE),(34,'Joe Bidens',FALSE),(34,'Labubu',FALSE),(34,'direktors',FALSE),
(35,'Devītās(9)',TRUE),(35,'Divpadsvitās (12)',FALSE),(35,'Sestās (6)',FALSE),(35,'Trešās (3)',FALSE),
(36,'Profesionālo vidējo izglītību',TRUE),(36,'Augstāko akadēmisko izglītību',FALSE),(36,'Profesionālo bakalaura grādu',FALSE),(36,'Pamatizglītību',FALSE),
(37,'4 gadi.',TRUE),(37,'3 gadi.',FALSE),(37,'Visa vasara.',FALSE),(37,'1 gads.',FALSE),
(38,'Medicīna un farmācija',TRUE),(38,'Apģērbu izains un māksla',FALSE),(38,'Programmēšana',FALSE),(38,'Galdniecība',FALSE),
(39,'Audzēkņu pašpārvalde',TRUE),(39,'Vecāku komiteja',FALSE),(39,'Direktors',FALSE),(39,'Klases vecākais',FALSE),
(40,'Nodrošināt audzēkņiem prakses un darba vietas',TRUE),(40,'Iegūt finansējumu jaunu sporta zāļu celtniecībai',FALSE),(40,'Organizēt ekskursijas uz Rīgu',FALSE),(40,'Rīkot vasaras nometnes skolēniem',FALSE),
(41,'8:10',TRUE),(41,'No rīta',FALSE),(41,'8:30',FALSE),(41,'7:30',FALSE),
(42,'60min',TRUE),(42,'20min',FALSE),(42,'10min',FALSE),(42,'5min',FALSE),
(43,'5min',TRUE),(43,'10min',FALSE),
(44,'MacOS',TRUE),(44,'Windows11',FALSE),(44,'Michaelsoft Binbows',FALSE),(44,'Linux',FALSE),
(45,'Uz maximu',TRUE),(45,'Ārā pasēdēt',FALSE),(45,'Augšā uz sporta zāli',FALSE),(45,'Uz ēdamzāli',FALSE);

-- ========================================================
-- Latvija
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(4,'Kura no šīm valstīm robežojas ar Latviju?'),
(4,'Kāds ir Latvijas nacionālais putns?'),
(4,'Kad Latvija proklamēja savu neatkarību (datums)?'),
(4,'Kāds ir Latvijas ģerbonī attēlotais simbols, kas simbolizē senās zemes – Kurzemi un Vidzemi?'),
(4,'Kurš ir Latvijas augstākais kalns?'),
(4,'Kas tiek atzīmēts 11. novembrī?'),
(4,'Kāda ir Latvijas valsts valoda?'),
(4,'Kura no šīm pilsētām ir viena no deviņām Latvijas republikas pilsētām?'),
(4,'Kā sauc populāro latviešu dzejnieku, kura portrets redzams uz 10 latu banknotes?'),
(4,'Cik krāsas ir attēlotas Latvijas karogā?'),
(4,'Kāds ir Latvijas Republikas pašreizējais himnas nosaukums?'),
(4,'Kura ir Latvijas populārākā nacionālā sporta spēle?'),
(4,'Kā sauc tradicionālo latviešu maizi, kas tiek cepta no rudzu miltiem?'),
(4,'Kura Latvijas pilsēta ir pazīstama kā "Latgales sirds"?'),
(4,'Kāds ir visbiežāk sastopamais koks Latvijas mežos?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(46,'Somija',FALSE),(46,'Lietuva',TRUE),(46,'Polija',FALSE),(46,'Zviedrija',FALSE),
(47,'Baltā cielava',TRUE),(47,'Stārķis',FALSE),(47,'Lielais ērglis',FALSE),(47,'Lakstīgala',FALSE),
(48,'1918. gada 18. novembris',TRUE),(48,'1991. gada 4. maijs',FALSE),(48,'1917. gada 1. decembris',FALSE),(48,'1940. gada 17. jūnijs',FALSE),
(49,'Lauva',TRUE),(49,'Grifs (Plēsīgs putns)',FALSE),(49,'Ozols',FALSE),(49,'Zvaigzne',FALSE),
(50,'Gaiziņkalns',TRUE),(50,'Lielais Kangaru paugurs',FALSE),(50,'Zilais kalns',FALSE),(50,'Mākoņkalns',FALSE),
(51,'Lāčplēša diena',TRUE),(51,'Latvijas dzimšanasdiena',FALSE),(51,'Jāņi',FALSE),(51,'Skolnieku brīvlaiks',FALSE),
(52,'Latviešu',TRUE),(52,'Krievu',FALSE),(52,'Lībiešu',FALSE),(52,'Angļu',FALSE),
(53,'Jelgava',TRUE),(53,'Sigulda',FALSE),(53,'Tukums',FALSE),(53,'Bauska',FALSE),
(54,'Rainis',TRUE),(54,'Aspazija',FALSE),(54,'Jānis Poruks',FALSE),(54,'Vizma Belševica',FALSE),
(55,'Divas',TRUE),(55,'Trīs',FALSE),(55,'Četras',FALSE),(55,'Viena',FALSE),
(56,'Dievs, svētī Latviju!',TRUE),(56,'Saule, Pērkons, Daugava',FALSE),(56,'Latvija, mana tēvzeme',FALSE),(56,'Cīruļa rīts',FALSE),
(57,'Basketbols',TRUE),(57,'Hokejs',FALSE),(57,'Futbols',FALSE),(57,'Bobslejs',FALSE),
(58,'Rudzu maize',TRUE),(58,'Baltmaize',FALSE),(58,'Saldskābmaize',FALSE),(58,'Sklandrausis',FALSE),
(59,'Daugavpils',FALSE),(59,'Rēzekne',TRUE),(59,'Jēkabpils',FALSE),(59,'Ludza',FALSE),
(60,'Priede',TRUE),(60,'Egle',FALSE),(60,'Bērzs',FALSE),(60,'Ozols',FALSE);

-- ========================================================
--  Auto / Moto Lativja
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(5,'Kura Latvijas trase uzņem pasaules rallijkrosa čempionāta (World RX) posmu?'),
(5,'Kura ir viena no slavenākajām Latvijas rallija sacensībām, kas notiek Kurzemē?'),
(5,'Kā sauc latviešu motokrosa braucēju, kurš guvis panākumus pasaules čempionātā MXGP klasē?'),
(5,'Kuru sporta veidu pasaulē pārstāv Pauls Jonass?'),
(5,'Kāds ir Latvijas motokrosa posma nosaukums, kas notiek Ķegumā?'),
(5,'Kura pilsēta Latvijā ir populāra ar savu spīdveja komandu?'),
(5,'Kura latviešu autosportiste ir pazīstama ar saviem panākumiem rallijā un rallijkrosā?'),
(5,'Kas ir galvenais sporta veids, kas saistīts ar Mārtiņu Sesku?'),
(5,'Kā sauc trasi, kurā notiek populāras dragreisa un autošosejas sacensības?'),
(5,'Kā sauc sacensības, kurās galvenokārt izmanto vecus un klasiskus žiguļus (VAZ)?'),
(5,'Kura organizācija Latvijā pārvalda un regulē auto sporta disciplīnas?'),
(5,'Kura no šīm vietām nav pazīstama ar motokrosa trasēm?(licencētām un aktuālām)'),
(5,'Kāds ir populārs ziemas moto sporta veids Latvijā, kurā nepieciešami 2 cilvēki komandā?'),
(5,'Kurš latviešu braucējs ir izcīnījis titulus Eiropas rallijkrosa čempionātā (Euro RX)?'),
(5,'Kāda ir galvenā atšķirība starp Ralliju un Rallijkrosu?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(61,'Biķernieku trase',TRUE),(61,'333 trase',FALSE),(61,'Liepājas osta',FALSE),(61,'Mūsa Raceway',FALSE),
(62,'Rally Talsi',TRUE),(62,'Rally Daugavpils',FALSE),(62,'Rally Cēsis',FALSE),(62,'Rally Rīga',FALSE),
(63,'Pauls Jonass',TRUE),(63,'Andris Dzenis',FALSE),(63,'Oskars Bārmanis',FALSE),(63,'Mārtiņš Sesks',FALSE),
(64,'Spīdvejs',FALSE),(64,'Bobslejs',FALSE),(64,'Motokross',TRUE),(64,'Formula 1',FALSE),
(65,'Zelta Zirgs',TRUE),(65,'Lielais Ķegums',FALSE),(65,'Garais posms',FALSE),(65,'Race of Latvia',FALSE),
(66,'Daugavpils',TRUE),(66,'Ventspils',FALSE),(66,'Jēkabpils',FALSE),(66,'Valmiera',FALSE),
(67,'Reinis Nitišs',FALSE),(67,'Lāsma Ozola',FALSE),(67,'Beate Klipa',TRUE),(67,'Elīna Dambe',FALSE),
(68,'WRC',TRUE),(68,'Drifts',FALSE),(68,'Motokross',FALSE),(68,'Krūmu gonka',FALSE),
(69,'Mūsa Raceway',TRUE),(69,'Pļaviņu aplis',FALSE),(69,'Ozolnieku aplis',FALSE),(69,'Cēsu aplis',FALSE),
(70,'VAZ Lada kauss',TRUE),(70,'Retro rallijs',FALSE),(70,'Vecās mašīnas',FALSE),(70,'Volga kauss',FALSE),
(71,'Latvijas Automobiļu federācija (LAF)',TRUE),(71,'Latvijas Olimpiskā komiteja',FALSE),(71,'Satiksmes ministrija',FALSE),(71,'Latvijas Motosporta federācija',FALSE),
(72,'Stende',FALSE),(72,'Apē',FALSE),(72,'Staicele',FALSE),(72,'Valmiera',TRUE),
(73,'Moto skijorings',TRUE),(73,'Ledus hokejs ar auto',FALSE),(73,'Kērlings',FALSE),(73,'Ledus drifta sacensības',FALSE),
(74,'Reinis Nitišs',TRUE),(74,'Haralds Šlēgelmilhs',FALSE),(74,'Artūrs Priednieks',FALSE),(74,'Kristers Serģis',FALSE),
(75,'Rallijs notiek uz slēgtiem ceļiem, rallijkross – noslēgtā trasē',TRUE),(75,'Rallijā izmanto motociklus, rallijkrosā – auto',FALSE),(75,'Rallijs notiek tikai vasarā, rallijkross – ziemā',FALSE),(75,'Rallijs ir ātrāks par rallijkrosu',FALSE);

SELECT '✅ Database imported successfully!' AS message;
SELECT 'Admin: admin | Password: admin' AS credentials;
