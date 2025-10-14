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
                'message' => 'Quiz created successfully',
                'quiz_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create quiz'
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
                'message' => 'Quiz updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update quiz'
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
                'message' => 'Quiz deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete quiz'
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
                'message' => 'Question created successfully',
                'question_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create question'
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
                'message' => 'Question updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update question'
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
                'message' => 'Question deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete question'
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
                'message' => 'Answer created successfully',
                'answer_id' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to create answer'
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
                'message' => 'Answer updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update answer'
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
                'message' => 'Answer deleted successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete answer'
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
}
?>