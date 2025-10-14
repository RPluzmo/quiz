<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get question ID and quiz ID
$question_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($question_id === 0 || $quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// Get question details
$question = $quiz_obj->getQuestionById($question_id);

if (!$question) {
    header('Location: quizzes.php');
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text']);

    $result = $quiz_obj->updateQuestion($question_id, $question_text);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';

    if ($result['success']) {
        // Refresh question data
        $question = $quiz_obj->getQuestionById($question_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                
            </div>
            <nav>
                <ul>
                    <li><a href="../dashboard.php">Sākumlapa</a></li>
                    <li><a href="index.php">Lietotāji</a></li>
                    <li><a href="quizzes.php">Quizzi</a></li>
                    <li><a href="../logout.php">Izlogoties</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <h2>Edit Question</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="question_text">Question Text</label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="4" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Question</button>
                    <a href="manage_answers.php?question_id=<?php echo $question_id; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                       class="btn btn-warning">Manage Answers</a>
                    <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>