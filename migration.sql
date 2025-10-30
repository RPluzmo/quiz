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
('Sports', 'Jautājumi par populāriem sporta veidiem un sportistiem'),
('Mūzika', 'Mūzikas un mākslinieku viktorīna'),
('Programēšana', 'Pārbaudi savas programmēšanas zināšanas'),
('Tehnoloģijas', 'Jautājumi par modernām tehnoloģijām un ierīcēm'),
('Māksla', 'Tests par mākslu, glezniecību un dizainu');

-- ========================================================
-- 🟢 SPORTS
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(1, 'Cik spēlētāju ir futbola komandā uz laukuma?'),
(1, 'Kurš sportists ir pazīstams kā “Bolt”?'),
(1, 'Kādā sportā izmanto raketi un shuttlecock?'),
(1, 'Kur notika 2016. gada vasaras olimpiskās spēles?'),
(1, 'Kas ir “hat-trick” futbolā?'),
(1, 'Kura valsts rīkoja 2018. gada Pasaules kausu futbolā?'),
(1, 'Kā sauc tiesnesi boksa ringā?'),
(1, 'Kurš sporta veids izmanto baseinu un bumbu?'),
(1, 'Cik punktu basketbolā ir tālmetienam?'),
(1, 'Kādā sportā lieto “putter”?'),
(1, 'Kas uzvar F1 sacensībās?'),
(1, 'Cik minūšu ilgst futbola spēle bez papildlaika?'),
(1, 'Kādā sportā sacenšas par “Stanley Cup”?'),
(1, 'Kāds dzīvnieks ir “Chicago Bulls” simbolā?'),
(1, 'Kas ir tenisa “Grand Slam”?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1,'11',TRUE),(1,'10',FALSE),(1,'9',FALSE),(1,'12',FALSE),
(2,'Usain Bolt',TRUE),(2,'Cristiano Ronaldo',FALSE),(2,'Michael Jordan',FALSE),(2,'Tiger Woods',FALSE),
(3,'Badmintons',TRUE),(3,'Teniss',FALSE),(3,'Beisbols',FALSE),(3,'Hokejs',FALSE),
(4,'Riodežaneiro',TRUE),(4,'Tokija',FALSE),(4,'Londona',FALSE),(4,'Pekina',FALSE),
(5,'3 vārti vienā spēlē',TRUE),(5,'3 piespēles',FALSE),(5,'3 soda metieni',FALSE),(5,'3 sitieni',FALSE),
(6,'Krievija',TRUE),(6,'Brazīlija',FALSE),(6,'Vācija',FALSE),(6,'Francija',FALSE),
(7,'Tiesnesis',TRUE),(7,'Treneris',FALSE),(7,'Sekretārs',FALSE),(7,'Cīņas vadītājs',FALSE),
(8,'Ūdens polo',TRUE),(8,'Regbijs',FALSE),(8,'Basketbols',FALSE),(8,'Hokejs',FALSE),
(9,'3 punkti',TRUE),(9,'2 punkti',FALSE),(9,'4 punkti',FALSE),(9,'5 punkti',FALSE),
(10,'Golfs',TRUE),(10,'Krikets',FALSE),(10,'Beisbols',FALSE),(10,'Teniss',FALSE),
(11,'Braucējs ar labāko laiku',TRUE),(11,'Komanda ar visvairāk apļu',FALSE),(11,'Treneris',FALSE),(11,'Tiesnesis',FALSE),
(12,'90',TRUE),(12,'60',FALSE),(12,'100',FALSE),(12,'75',FALSE),
(13,'Hokejs',TRUE),(13,'Basketbols',FALSE),(13,'Futbols',FALSE),(13,'Teniss',FALSE),
(14,'Vērsis',TRUE),(14,'Lauva',FALSE),(14,'Tīģeris',FALSE),(14,'Vilks',FALSE),
(15,'Uzvara visos četros lielajos turnīros',TRUE),(15,'Uzvara vienā turnīrā',FALSE),(15,'Fināla sasniegšana',FALSE),(15,'Divu turnīru uzvara',FALSE);

-- ========================================================
-- 🎵 MŪZIKA
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(2,'Kurš bija The Beatles solists?'),
(2,'Kāds ir Ed Sheeran tautības?'),
(2,'Kura dziedāja “Rolling in the Deep”?'),
(2,'No kuras valsts nāk grupa ABBA?'),
(2,'Kurš instruments ir klavierēm līdzīgs, bet mazāks?'),
(2,'Kas ir DJ?'),
(2,'Kāds ir populārākais mūzikas straumēšanas serviss?'),
(2,'Kas ir Grammy?'),
(2,'Kurš bija “King of Pop”?'),
(2,'Kurš instruments pieder stīgu grupai?'),
(2,'Kura valsts ir pazīstama ar flamenco mūziku?'),
(2,'Kas dziedāja “Shape of You”?'),
(2,'Kura žanrā dominē rīmes un ritms?'),
(2,'Kā sauc cilvēku, kas komponē mūziku filmām?'),
(2,'Kas ir albums?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(16,'John Lennon',TRUE),(16,'Paul McCartney',FALSE),(16,'George Harrison',FALSE),(16,'Ringo Starr',FALSE),
(17,'Brits',TRUE),(17,'Īrs',FALSE),(17,'Amerikānis',FALSE),(17,'Austrālietis',FALSE),
(18,'Adele',TRUE),(18,'Beyoncé',FALSE),(18,'Rihanna',FALSE),(18,'Lady Gaga',FALSE),
(19,'Zviedrija',TRUE),(19,'Norvēģija',FALSE),(19,'Vācija',FALSE),(19,'Dānija',FALSE),
(20,'Sintezators',TRUE),(20,'Trompete',FALSE),(20,'Flauta',FALSE),(20,'Sitaminstruments',FALSE),
(21,'Diskžokejs',TRUE),(21,'Dziedātājs',FALSE),(21,'Producent',FALSE),(21,'Režisors',FALSE),
(22,'Spotify',TRUE),(22,'YouTube',FALSE),(22,'Apple Mail',FALSE),(22,'Deezer',FALSE),
(23,'Balva par mūzikas sasniegumiem',TRUE),(23,'Festivāls',FALSE),(23,'Albums',FALSE),(23,'Koncerts',FALSE),
(24,'Michael Jackson',TRUE),(24,'Elvis Presley',FALSE),(24,'Freddie Mercury',FALSE),(24,'Justin Bieber',FALSE),
(25,'Vijole',TRUE),(25,'Bungas',FALSE),(25,'Trompete',FALSE),(25,'Saksofons',FALSE),
(26,'Spānija',TRUE),(26,'Francija',FALSE),(26,'Itālija',FALSE),(26,'Portugāle',FALSE),
(27,'Ed Sheeran',TRUE),(27,'Sam Smith',FALSE),(27,'Shawn Mendes',FALSE),(27,'Bruno Mars',FALSE),
(28,'Reps',TRUE),(28,'Roks',FALSE),(28,'Džezs',FALSE),(28,'Klasika',FALSE),
(29,'Komponists',TRUE),(29,'Producent',FALSE),(29,'Dziedātājs',FALSE),(29,'Vokālists',FALSE),
(30,'Dziesmu kolekcija',TRUE),(30,'Koncerts',FALSE),(30,'Playlist',FALSE),(30,'Festivāls',FALSE);

-- ========================================================
-- 💻 PROGRAMĒŠANA
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(3,'Kas ir mainīgais programmēšanā?'),
(3,'Kas ir funkcija?'),
(3,'Ko dara if nosacījums?'),
(3,'Ko dara “for” cikls?'),
(3,'Kas ir masīvs?'),
(3,'Kas ir algoritms?'),
(3,'Kas ir sintakse?'),
(3,'Ko dara “return”?'),
(3,'Kas ir bugs?'),
(3,'Kas ir datu tips?'),
(3,'Kas ir koda kompilēšana?'),
(3,'Kas ir IDE?'),
(3,'Kas ir komentārs kodā?'),
(3,'Kas ir objekts OOP?'),
(3,'Ko dara “while” cikls?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(31,'Datu glabāšanas vieta',TRUE),(31,'Kods, kas atkārtojas',FALSE),(31,'Nosacījums',FALSE),(31,'Funkcija',FALSE),
(32,'Koda bloks ar noteiktu darbību',TRUE),(32,'Datu tips',FALSE),(32,'Komentārs',FALSE),(32,'Cikls',FALSE),
(33,'Pārbauda nosacījumu',TRUE),(33,'Atkārto darbības',FALSE),(33,'Izvada tekstu',FALSE),(33,'Saglabā datus',FALSE),
(34,'Atkārto darbības',TRUE),(34,'Beidz programmu',FALSE),(34,'Pārbauda kļūdas',FALSE),(34,'Saglabā failu',FALSE),
(35,'Kolekcija ar elementiem',TRUE),(35,'Funkcija',FALSE),(35,'Fails',FALSE),(35,'Cikls',FALSE),
(36,'Soli pa solim darbību secība',TRUE),(36,'Kļūda',FALSE),(36,'Mainīgais',FALSE),(36,'Komentārs',FALSE),
(37,'Koda uzbūves noteikumi',TRUE),(37,'Mainīgie',FALSE),(37,'Rezultāts',FALSE),(37,'Cikls',FALSE),
(38,'Atgriež rezultātu no funkcijas',TRUE),(38,'Saglabā failu',FALSE),(38,'Atkārto kodu',FALSE),(38,'Sāk jaunu ciklu',FALSE),
(39,'Kļūda kodā',TRUE),(39,'Pareiza funkcija',FALSE),(39,'Izvades rezultāts',FALSE),(39,'Nosacījums',FALSE),
(40,'Mainīgā datu veids',TRUE),(40,'Kļūdas veids',FALSE),(40,'Koda struktūra',FALSE),(40,'Komentārs',FALSE),
(41,'Koda pārvēršana izpildāmā formā',TRUE),(41,'Datu saglabāšana',FALSE),(41,'Komentāru pievienošana',FALSE),(41,'Programmas dzēšana',FALSE),
(42,'Programmēšanas vide',TRUE),(42,'Datu tips',FALSE),(42,'Fails',FALSE),(42,'Masīvs',FALSE),
(43,'Teksts, ko dators ignorē',TRUE),(43,'Koda kļūda',FALSE),(43,'Cikls',FALSE),(43,'Mainīgais',FALSE),
(44,'Objekts ar īpašībām un metodēm',TRUE),(44,'Masīvs',FALSE),(44,'Datu tips',FALSE),(44,'Komentārs',FALSE),
(45,'Atkārto darbības kamēr nosacījums ir patiess',TRUE),(45,'Beidz programmu',FALSE),(45,'Pārbauda kļūdas',FALSE),(45,'Maina datus',FALSE);

-- ========================================================
-- ⚙️ TEHNOLOĢIJAS
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(4,'Kas ir internets?'),
(4,'Kas ir Wi-Fi?'),
(4,'Kas ir viedtālrunis?'),
(4,'Kas ir datorsistēma?'),
(4,'Kas ir mākoņdatošana?'),
(4,'Kas ir robots?'),
(4,'Kas ir mākslīgais intelekts?'),
(4,'Kas ir datubāze?'),
(4,'Kas ir e-pasts?'),
(4,'Kas ir interneta pārlūks?'),
(4,'Kas ir sociālais tīkls?'),
(4,'Kas ir QR kods?'),
(4,'Kas ir GPS?'),
(4,'Kas ir planšete?'),
(4,'Kas ir viedpalīgs?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(46,'Globāls datoru tīkls',TRUE),(46,'Programma',FALSE),(46,'Fails',FALSE),(46,'Ierīce',FALSE),
(47,'Bezvadu interneta savienojums',TRUE),(47,'Mobilais tīkls',FALSE),(47,'Kabelis',FALSE),(47,'Radio',FALSE),
(48,'Telefons ar datorfunkcijām',TRUE),(48,'Stacionārs dators',FALSE),(48,'Planšete',FALSE),(48,'Kalkulators',FALSE),
(49,'Ierīču kopums ar programmatūru',TRUE),(49,'Fails',FALSE),(49,'Internets',FALSE),(49,'Mājas lapa',FALSE),
(50,'Datu glabāšana internetā',TRUE),(50,'Programmu dzēšana',FALSE),(50,'Failu kopēšana',FALSE),(50,'E-pasta sūtīšana',FALSE),
(51,'Automātiska ierīce',TRUE),(51,'Dators',FALSE),(51,'Programma',FALSE),(51,'Cilvēks',FALSE),
(52,'Programmas, kas mācās un analizē',TRUE),(52,'Datorspēle',FALSE),(52,'E-pasta serviss',FALSE),(52,'Kalkulators',FALSE),
(53,'Datu glabāšanas sistēma',TRUE),(53,'Fails',FALSE),(53,'Kods',FALSE),(53,'Pārlūks',FALSE),
(54,'Elektroniskā vēstule',TRUE),(54,'Fails',FALSE),(54,'Ziņa',FALSE),(54,'Datu tips',FALSE),
(55,'Programma, lai pārlūkotu tīmekli',TRUE),(55,'Failu glabātuve',FALSE),(55,'E-pasts',FALSE),(55,'Sociālais tīkls',FALSE),
(56,'Tiešsaistes saziņas platforma',TRUE),(56,'Programma kodam',FALSE),(56,'Skaļrunis',FALSE),(56,'Cietais disks',FALSE),
(57,'Skannējams attēls ar datiem',TRUE),(57,'Foto',FALSE),(57,'Fails',FALSE),(57,'Ikona',FALSE),
(58,'Globālā pozicionēšanas sistēma',TRUE),(58,'Interneta protokols',FALSE),(58,'Datora mikroshēma',FALSE),(58,'Kods',FALSE),
(59,'Pārnēsājams ekrāna dators',TRUE),(59,'Telefons',FALSE),(59,'Printeris',FALSE),(59,'Kamera',FALSE),
(60,'Balss vai lietotņu asistents',TRUE),(60,'Planšete',FALSE),(60,'Robots',FALSE),(60,'Mikrofons',FALSE);

-- ========================================================
-- 🎨 MĀKSLA
-- ========================================================

INSERT INTO questions (quiz_id, question_text) VALUES
(5,'Kas ir glezniecība?'),
(5,'Kas bija Leonardo da Vinči?'),
(5,'Kura glezna attēlo sievieti ar noslēpumainu smaidu?'),
(5,'Kas ir portrets?'),
(5,'Kas ir skulptūra?'),
(5,'Kas ir kompozīcija mākslā?'),
(5,'Kādu materiālu izmanto akvareļglezniecībā?'),
(5,'Kas ir mūsdienu māksla?'),
(5,'Kas ir arhitektūra?'),
(5,'Kas ir perspektīva mākslā?'),
(5,'Kas ir dizains?'),
(5,'Kas ir palete?'),
(5,'Kas ir mozaīka?'),
(5,'Kas ir grafika?'),
(5,'Kas ir mākslas galerija?');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(61,'Zīmējumu un krāsu māksla',TRUE),(61,'Tēlniecība',FALSE),(61,'Fotogrāfija',FALSE),(61,'Arhitektūra',FALSE),
(62,'Mākslinieks un izgudrotājs',TRUE),(62,'Mūziķis',FALSE),(62,'Rakstnieks',FALSE),(62,'Zinātnieks',FALSE),
(63,'Mona Liza',TRUE),(63,'Pēdējās vakariņas',FALSE),(63,'Venēra',FALSE),(63,'Zvaigžņotā nakts',FALSE),
(64,'Cilvēka attēlojums',TRUE),(64,'Ainava',FALSE),(64,'Abstrakcija',FALSE),(64,'Stilizācija',FALSE),
(65,'Trīsdimensionāls mākslas darbs',TRUE),(65,'Zīmējums',FALSE),(65,'Fotogrāfija',FALSE),(65,'Raksts',FALSE),
(66,'Mākslas elementu izvietojums',TRUE),(66,'Krāsu sajaukšana',FALSE),(66,'Līniju zīmēšana',FALSE),(66,'Formu attēlošana',FALSE),
(67,'Ūdens krāsas',TRUE),(67,'Eļļas krāsas',FALSE),(67,'Akrils',FALSE),(67,'Tinte',FALSE),
(68,'20. un 21. gs. māksla',TRUE),(68,'Senā māksla',FALSE),(68,'Romāņu periods',FALSE),(68,'Baroks',FALSE),
(69,'Ēku projektēšana un būvniecība',TRUE),(69,'Zīmēšana',FALSE),(69,'Fotogrāfija',FALSE),(69,'Dizains',FALSE),
(70,'Dziļuma attēlošana gleznā',TRUE),(70,'Krāsu sajaukšana',FALSE),(70,'Līniju izvēle',FALSE),(70,'Gaismas izmantošana',FALSE),
(71,'Formu un funkcijas radīšanas māksla',TRUE),(71,'Fotogrāfija',FALSE),(71,'Krāsošana',FALSE),(71,'Mūzika',FALSE),
(72,'Krāsu jaukšanas plate',TRUE),(72,'Ota',FALSE),(72,'Audekls',FALSE),(72,'Rāmis',FALSE),
(73,'Attēls no sīkiem gabaliņiem',TRUE),(73,'Skulptūra',FALSE),(73,'Fotogrāfija',FALSE),(73,'Zīmējums',FALSE),
(74,'Līniju un kontrastu māksla',TRUE),(74,'Krāsošana',FALSE),(74,'Skulptūra',FALSE),(74,'Mozaīka',FALSE),
(75,'Vieta, kur izstāda mākslas darbus',TRUE),(75,'Skola',FALSE),(75,'Teātris',FALSE),(75,'Kafejnīca',FALSE);

SELECT '✅ Database imported successfully!' AS message;
SELECT 'Admin: admin | Password: admin' AS credentials;
