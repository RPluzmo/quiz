<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get quiz ID
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// Get quiz details
$quiz = $quiz_obj->getQuizById($quiz_id);

if (!$quiz) {
    header('Location: quizzes.php');
    exit();
}

// Get questions
$questions = $quiz_obj->getQuizQuestions($quiz_id, false);

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_question'])) {
        $result = $quiz_obj->createQuestion($quiz_id, $_POST['question_text']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh questions
        $questions = $quiz_obj->getQuizQuestions($quiz_id, false);
    } elseif (isset($_POST['delete_question'])) {
        $result = $quiz_obj->deleteQuestion($_POST['question_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh questions
        $questions = $quiz_obj->getQuizQuestions($quiz_id, false);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                
            </div>
            <nav>
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="index.php">Users</a></li>
                    <li><a href="quizzes.php">Quizzes</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($quiz['name']); ?></h2>
                <p>Manage questions and answers</p>
            </div>

            <a href="quizzes.php" class="btn btn-secondary mb-3">← Back to Quizzes</a>

            <!-- Create New Question -->
            <div style="background: var(--light-bg); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3>Add New Question</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="question_text">Question Text</label>
                        <textarea id="question_text" name="question_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="create_question" class="btn btn-success">Add Question</button>
                </form>
            </div>

            <!-- Questions List -->
            <?php if (empty($questions)): ?>
                <div class="empty-state">
                    <h3>No questions yet</h3>
                    <p>Add your first question above</p>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $question): 
                    $answers = $quiz_obj->getQuestionAnswers($question['id'], false);
                ?>
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <div class="card-header">
                            <h4>Question <?php echo $index + 1; ?></h4>
                        </div>
                        
                        <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>

                        <!-- Answers -->
                        <div style="margin: 1rem 0;">
                            <strong>Answers:</strong>
                            <?php if (empty($answers)): ?>
                                <p style="color: var(--danger-color);">No answers yet</p>
                            <?php else: ?>
                                <ul style="margin-top: 0.5rem;">
                                    <?php foreach ($answers as $answer): ?>
                                        <li style="margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                                            <?php if ($answer['is_correct']): ?>
                                                <span class="badge badge-admin">✓ Correct</span>
                                            <?php endif; ?>
                                            <a href="edit_answer.php?id=<?php echo $answer['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                                               class="btn btn-sm btn-primary" style="margin-left: 0.5rem;">Edit</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="edit_question.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                               class="btn btn-sm btn-primary">Edit Question</a>
                            <a href="manage_answers.php?question_id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                               class="btn btn-sm btn-warning">Manage Answers</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <button type="submit" name="delete_question" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this question and all its answers?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>