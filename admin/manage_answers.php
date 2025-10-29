<?php
require_once '../config/config.php';

$message = $_GET['msg'] ?? '';
$message_type = $_GET['type'] ?? '';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get question ID and quiz ID
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;
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

// Get answers
$answers = $quiz_obj->getQuestionAnswers($question_id, false);

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_answer'])) {
        $is_correct = isset($_POST['is_correct']) ? 1 : 0;
        $result = $quiz_obj->createAnswer($question_id, $_POST['answer_text'], $is_correct);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        // ✅ Redirect after POST
        header("Location: manage_answers.php?question_id={$question_id}&quiz_id={$quiz_id}&msg=" . urlencode($message) . "&type={$message_type}");
        exit;
    } elseif (isset($_POST['delete_answer'])) {
        $result = $quiz_obj->deleteAnswer($_POST['answer_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        // ✅ Redirect again
        header("Location: manage_answers.php?question_id={$question_id}&quiz_id={$quiz_id}&msg=" . urlencode($message) . "&type={$message_type}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pārvaldīt atbildes</title>
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
                    <li><a href="quizzes.php">Testi</a></li>
                    <li><a href="../logout.php">Izlogoties</a></li>
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
                <h2>Pārvaldīt atzīmes</h2>
                <p><strong>Jautājums:</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
            </div>

            <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-secondary mb-3"><-- Atgriesties pie jautājumiem</a>

            <!-- Create New Answer -->
            <div style="background: var(--light-bg); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <h3>Pievienot jaunu atbildi</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="answer_text">Atbilde</label>
                        <textarea id="answer_text" name="answer_text" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_correct" value="1">
                            Šī būs pareizā atbilde.
                        </label>
                    </div>
                    <button type="submit" name="create_answer" class="btn btn-success">Pievienot atbildi</button>
                </form>
            </div>

            <!-- Answers List -->
            <?php if (empty($answers)): ?>
                <div class="empty-state">
                    <h3>Nevienas atbildes nav</h3>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Atbilde</th>
                                <th>✅Pareizi✅</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($answers as $answer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($answer['answer_text']); ?></td>
                                    <td>
                                        <?php if ($answer['is_correct']): ?>
                                            <span class="badge badge-admin"> Šī ir pareizā atbilde</span>
                                        <?php else: ?>
                                            <span class="badge badge-user"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="edit_answer.php?id=<?php echo $answer['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                                               class="btn btn-sm btn-primary">Rediģēt</a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                                                <button type="submit" name="delete_answer" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this answer?')">
                                                    Dzēsti
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>