<?php
/**
 * Quiz Class - Handles quiz operations
 */

class Quiz {
    private $conn;
    private $quizzes_table = "quizzes";
    private $questions_table = "questions";
    private $answers_table = "answers";
    private $results_table = "results";

    public $id;
    public $name;
    public $description;

    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all quizzes
     */
    public function getAllQuizzes() {
        $query = "SELECT q.*, 
                  COUNT(DISTINCT qu.id) as question_count,
                  COUNT(DISTINCT r.id) as times_taken
                  FROM " . $this->quizzes_table . " q
                  LEFT JOIN " . $this->questions_table . " qu ON q.id = qu.quiz_id
                  LEFT JOIN " . $this->results_table . " r ON q.id = r.quiz_id
                  GROUP BY q.id
                  ORDER BY q.name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get quiz by ID
     */
    public function getQuizById($id) {
        $query = "SELECT * FROM " . $this->quizzes_table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get questions for a quiz (randomized)
     */
    public function getQuizQuestions($quiz_id, $randomize = true) {
        $query = "SELECT * FROM " . $this->questions_table . " 
                  WHERE quiz_id = :quiz_id";
        
        if ($randomize) {
            $query .= " ORDER BY RAND()";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quiz_id', $quiz_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get answers for a question (randomized)
     */
    public function getQuestionAnswers($question_id, $randomize = true) {
        $query = "SELECT * FROM " . $this->answers_table . " 
                  WHERE question_id = :question_id";
        
        if ($randomize) {
            $query .= " ORDER BY RAND()";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':question_id', $question_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get correct answer for a question
     */
    public function getCorrectAnswer($question_id) {
        $query = "SELECT id FROM " . $this->answers_table . " 
                  WHERE question_id = :question_id AND is_correct = 1 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':question_id', $question_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }

    /**
     * Save quiz result
     */
    public function saveResult($user_id, $quiz_id, $score, $total_questions) {
        $query = "INSERT INTO " . $this->results_table . " 
                  (user_id, quiz_id, score, total_questions) 
                  VALUES (:user_id, :quiz_id, :score, :total_questions)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':quiz_id', $quiz_id);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':total_questions', $total_questions);

        return $stmt->execute();
    }

    /**
     * Get user quiz history
     */
    public function getUserHistory($user_id) {
        $query = "SELECT r.*, q.name as quiz_name, q.description
                  FROM " . $this->results_table . " r
                  JOIN " . $this->quizzes_table . " q ON r.quiz_id = q.id
                  WHERE r.user_id = :user_id
                  ORDER BY r.completed_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get high scores for a quiz
     */
    public function getQuizHighScores($quiz_id, $limit = 10) {
        $query = "SELECT r.*, u.username
                  FROM " . $this->results_table . " r
                  JOIN users u ON r.user_id = u.id
                  WHERE r.quiz_id = :quiz_id
                  ORDER BY r.score DESC, r.completed_at ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quiz_id', $quiz_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overall high scores
     */
    public function getOverallHighScores($limit = 10) {
        $query = "SELECT r.*, u.username, q.name as quiz_name
                  FROM " . $this->results_table . " r
                  JOIN users u ON r.user_id = u.id
                  JOIN " . $this->quizzes_table . " q ON r.quiz_id = q.id
                  ORDER BY r.score DESC, r.completed_at ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new quiz
     */
    public function createQuiz($name, $description) {
        $query = "INSERT INTO " . $this->quizzes_table . " 
                  (name, description) 
                  VALUES (:name, :description)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jauns tests veiksmīgi izveidots.',
                'quiz_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās izveidot testu.. my bad;['
        ];
    }

    /**
     * Update quiz
     */
    public function updateQuiz($id, $name, $description) {
        $query = "UPDATE " . $this->quizzes_table . " 
                  SET name = :name, description = :description 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Tests atjaunināts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās atjaunināt testu'
        ];
    }

    /**
     * Delete quiz
     */
    public function deleteQuiz($id) {
        $query = "DELETE FROM " . $this->quizzes_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Tests veiksmīgi izdzēsts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās izdzēst testu'
        ];
    }

    /**
     * Create new question
     */
    public function createQuestion($quiz_id, $question_text) {
        $query = "INSERT INTO " . $this->questions_table . " 
                  (quiz_id, question_text) 
                  VALUES (:quiz_id, :question_text)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quiz_id', $quiz_id);
        $stmt->bindParam(':question_text', $question_text);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jautājums pievienots',
                'question_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās pievienot jautājumu'
        ];
    }

    /**
     * Update question
     */
    public function updateQuestion($id, $question_text) {
        $query = "UPDATE " . $this->questions_table . " 
                  SET question_text = :question_text 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':question_text', $question_text);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jautājums atjaunināts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās atjaunināt jautājumu'
        ];
    }

    /**
     * Delete question
     */
    public function deleteQuestion($id) {
        $query = "DELETE FROM " . $this->questions_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Jautājums dzēsts'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās dzēst jautājumu'
        ];
    }

    /**
     * Create new answer
     */
    public function createAnswer($question_id, $answer_text, $is_correct) {
        $query = "INSERT INTO " . $this->answers_table . " 
                  (question_id, answer_text, is_correct) 
                  VALUES (:question_id, :answer_text, :is_correct)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':question_id', $question_id);
        $stmt->bindParam(':answer_text', $answer_text);
        $stmt->bindParam(':is_correct', $is_correct);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Atbilde ir izveidota',
                'answer_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās izveidot atbildi'
        ];
    }

    /**
     * Update answer
     */
    public function updateAnswer($id, $answer_text, $is_correct) {
        $query = "UPDATE " . $this->answers_table . " 
                  SET answer_text = :answer_text, is_correct = :is_correct 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':answer_text', $answer_text);
        $stmt->bindParam(':is_correct', $is_correct);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Atbilde ir atjaunota'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās atjaunot atbildi'
        ];
    }

    /**
     * Delete answer
     */
    public function deleteAnswer($id) {
        $query = "DELETE FROM " . $this->answers_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Atbilde ir dzēsta'
            ];
        }

        return [
            'success' => false,
            'message' => 'Neizdevās dzēst atbildi'
        ];
    }

    /**
     * Get question by ID
     */
    public function getQuestionById($id) {
        $query = "SELECT * FROM " . $this->questions_table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get answer by ID
     */
    public function getAnswerById($id) {
        $query = "SELECT * FROM " . $this->answers_table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function saveResultDetails($result_id, $details) {
        $query = "INSERT INTO result_details 
                    (result_id, question_id, user_answer_text, correct_answer_text, is_correct) 
                    VALUES (:result_id, :question_id, :user_answer_text, :correct_answer_text, :is_correct)";

        $stmt = $this->conn->prepare($query);

        foreach ($details as $item) {
            $stmt->bindParam(':result_id', $result_id);
            $stmt->bindParam(':question_id', $item['question_id']);
            $stmt->bindParam(':user_answer_text', $item['user_answer']);
            $stmt->bindParam(':correct_answer_text', $item['correct_answer']);
            $stmt->bindParam(':is_correct', $item['is_correct']);
            
            if (!$stmt->execute()) {
                // Kļūdas gadījumā, pārtraucam saglabāšanu
                return false; 
            }
        }
        return true;
    }

public function getHighScoresForAllUsers($quiz_id = 'all') {
        
        $where_clause = "";
        if ($quiz_id !== 'all' && is_numeric($quiz_id)) {
            $where_clause = " WHERE h.quiz_id = :quiz_id ";
        }

        // Tiek izmantots "Greatest N Per Group" modelis, lai atrastu MAX rezultātu katram user_id/quiz_id pārim.
        $query = "
            SELECT 
                u.username,
                q.name AS quiz_name,
                q.description,
                h.quiz_id,
                h.score,
                h.total_questions,
                h.completed_at
            FROM 
                results h
            INNER JOIN 
                users u ON h.user_id = u.id
            INNER JOIN 
                quizzes q ON h.quiz_id = q.id
            INNER JOIN (
                SELECT 
                    user_id, 
                    quiz_id, 
                    MAX(CAST(score AS DECIMAL) / total_questions) AS max_percentage
                FROM 
                    results
                GROUP BY 
                    user_id, quiz_id
            ) AS best_scores ON h.user_id = best_scores.user_id 
                               AND h.quiz_id = best_scores.quiz_id
                               -- KRITISKĀ IZMAIŅA: Nodrošina, ka tiek atlasīta rinda ar faktisko MAX procentu
                               AND (CAST(h.score AS DECIMAL) / h.total_questions) = best_scores.max_percentage
            " . $where_clause . "
            ORDER BY 
                (CAST(h.score AS DECIMAL) / h.total_questions) DESC, 
                h.completed_at ASC
        ";

        $stmt = $this->conn->prepare($query);

        if ($quiz_id !== 'all' && is_numeric($quiz_id)) {
            $stmt->bindParam(':quiz_id', $quiz_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get quiz result details for review (from DB)
     */
    public function getResultDetailsForReview($result_id, $user_id) {
        // 1. Ielādējam galveno rezultātu un pārbaudām lietotāja ID
        $query_main = "SELECT r.*, q.name as quiz_name, q.description
                       FROM " . $this->results_table . " r
                       JOIN " . $this->quizzes_table . " q ON r.quiz_id = q.id
                       WHERE r.id = :result_id AND r.user_id = :user_id LIMIT 1";
        
        $stmt_main = $this->conn->prepare($query_main);
        $stmt_main->bindParam(':result_id', $result_id);
        $stmt_main->bindParam(':user_id', $user_id);
        $stmt_main->execute();
        $main_result = $stmt_main->fetch(PDO::FETCH_ASSOC);

        if (!$main_result) {
            return false; // Rezultāts nav atrasts vai pieder citam lietotājam
        }

        // 2. Ielādējam detalizēto informāciju
        $query_details = "SELECT rd.user_answer_text, rd.correct_answer_text, rd.is_correct, q.question_text
                          FROM result_details rd
                          JOIN questions q ON rd.question_id = q.id
                          WHERE rd.result_id = :result_id
                          ORDER BY rd.id ASC";

        $stmt_details = $this->conn->prepare($query_details);
        $stmt_details->bindParam(':result_id', $result_id);
        $stmt_details->execute();
        $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

        // Pārformatējam datus, lai tie atbilstu sesijas struktūrai
        $formatted_details = array_map(function($item) {
            return [
                'question' => $item['question_text'],
                'user_answer' => $item['user_answer_text'] ?? 'Nav atbildēts', // Iespējams NULL
                'correct_answer' => $item['correct_answer_text'],
                'is_correct' => (bool)$item['is_correct']
            ];
        }, $details);

        return [
            'quiz_name' => $main_result['quiz_name'],
            'score' => $main_result['score'],
            'total' => $main_result['total_questions'],
            'completed_at' => $main_result['completed_at'],
            'details' => $formatted_details
        ];
    }

}
?>