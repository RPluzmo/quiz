<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get answer ID and quiz ID
$answer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

if ($answer_id === 0 || $quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// Get answer details
$answer = $quiz_obj->getAnswerById($answer_id);

if (!$answer) {
    header('Location: quizzes.php');
    exit();
}

// Get question details
$question = $quiz_obj->getQuestionById($answer['question_id']);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer_text = trim($_POST['answer_text']);
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;

    $result = $quiz_obj->updateAnswer($answer_id, $answer_text, $is_correct);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';

    if ($result['success']) {
        // Refresh answer data
        $answer = $quiz_obj->getAnswerById($answer_id);
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rediģēt adbildes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="../dashboard.php">Sākumlapa</a></li>
                    <li><a href="index.php">Lietotāji</a></li>
                    <li><a href="../logout.php">Izrakstīties</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <h2>Rediģēt atbildi</h2>
                <p><strong>Jautājums:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="answer_text">Atbilde ir</label>
                    <textarea id="answer_text" name="answer_text" class="form-control" rows="3" required><?php echo htmlspecialchars($answer['answer_text']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_correct" value="1" <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                        Šī ir pareizā atbilde
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Atjaunot atbildi</button>
                    <a href="manage_answers.php?question_id=<?php echo $answer['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                       class="btn btn-secondary">Atpakaļ</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>