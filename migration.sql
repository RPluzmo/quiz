DROP DATABASE IF EXISTS quiz_system;
CREATE DATABASE quiz_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quiz_system;

-- Users table (simplified - no email, plain text passwords)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quizzes/Topics table
CREATE TABLE quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Questions table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    INDEX idx_quiz_id (quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Answers table
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX idx_question_id (question_id),
    INDEX idx_is_correct (is_correct)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Results/History table
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

-- Insert default admin user (plain text password)
INSERT INTO users (username, password, role) VALUES 
('admin', 'admin', 'admin');

-- Insert 5 quiz topics
INSERT INTO quizzes (name, description) VALUES
('Sports', 'itz olny game y you have to b mad?'),
('Programmēšana', 'aka kodēšanās'),
('Spēles', 'itz onļy game'),
('B', 'b'),
('C', 'c');

-- Insert questions and answers for Quiz 1: PHP Programming
INSERT INTO questions (quiz_id, question_text) VALUES
(1, 'basketola?'),
(1, 'hoķis?')
;

-- Answers for PHP Programming questions
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
-- Question 1
(1, 'basketbols', FALSE),
(1, 'futbols', TRUE)
;

-- Insert questions for Quiz 2: JavaScript Fundamentals
INSERT INTO questions (quiz_id, question_text) VALUES
(2, 'Which keyword is used to declare a variable in JavaScript?'),
(2, 'What is the output of: typeof null?')
;

-- Answers for JavaScript Fundamentals
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
-- Question 16
(16, 'var, let, or const', TRUE),
(16, 'variable', FALSE)
;

-- Insert questions for Quiz 3: Database Management
INSERT INTO questions (quiz_id, question_text) VALUES
(3, 'What does SQL stand for?'),
(3, 'Which SQL statement is used to retrieve data from a database?')
;

-- Answers for Database Management
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
-- Question 31
(31, 'Structured Query Language', TRUE),
(31, 'Simple Query Language', FALSE)
;

-- Answers for Web Development
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
-- Question 46
(46, 'HyperText Markup Language', TRUE),
(46, 'High Text Markup Language', FALSE)
;


-- Insert questions for Quiz 5: Object-Oriented Programming
INSERT INTO questions (quiz_id, question_text) VALUES
(5, 'What is encapsulation in OOP?'),
(5, 'What is inheritance?')
;

-- Answers for Object-Oriented Programming
INSERT INTO answers (question_id, answer_text, is_correct) VALUES
-- Question 61
(61, 'Bundling data and methods that work on that data', TRUE),
(61, 'Hiding implementation details', FALSE)
;

-- Success message
SELECT 'Database migration completed successfully!' as message;
SELECT 'Default admin user created - Username: admin, Password: admin' as info;