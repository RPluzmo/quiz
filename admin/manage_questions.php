<?php
require_once '../config/config.php';

// ✅ Nolasām ziņojumu no URL (ja ir)
$message = $_GET['msg'] ?? '';
$message_type = $_GET['type'] ?? '';

// ✅ Tikai adminiem
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// ✅ Nolasām quiz_id
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if ($quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// ✅ Iegūstam testa datus
$quiz = $quiz_obj->getQuizById($quiz_id);
if (!$quiz) {
    header('Location: quizzes.php');
    exit();
}

// ✅ Iegūstam jautājumus
$questions = $quiz_obj->getQuizQuestions($quiz_id, false);

// ✅ Apstrādājam POST (pievienošana / dzēšana)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['create_question'])) {
        $result = $quiz_obj->createQuestion($quiz_id, $_POST['question_text']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        // ✅ Redirect – novērš dublikātus pēc refreša
        header("Location: manage_questions.php?quiz_id={$quiz_id}&msg=" . urlencode($message) . "&type={$message_type}");
        exit;
    }

    if (isset($_POST['delete_question'])) {
        $result = $quiz_obj->deleteQuestion($_POST['question_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';

        // ✅ Redirect – arī pēc dzēšanas
        header("Location: manage_questions.php?quiz_id={$quiz_id}&msg=" . urlencode($message) . "&type={$message_type}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pārvaldīt testu jautājumus</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo"></div>
        <nav>
            <ul>
                <li><a href="../dashboard.php">Sākumlapa</a></li>
                <li><a href="index.php">Lietotāji</a></li>
                <li><a href="quizzes.php">Testi</a></li>
                <li><a href="../logout.php">Izrakstīties</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">

    <!-- ✅ Ziņojums pēc darbības -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($quiz['name']); ?></h2>
            <p>Rediģē testu jautājumus un atbildes.</p>
        </div>

        <a href="quizzes.php" class="btn btn-secondary mb-3"><-- Atpakaļ pie testiem</a>

        <!-- ✅ Jauna jautājuma pievienošana -->
        <div style="background: var(--light-bg); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3>Pievienot jaunu jautājumu</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="question_text">Jautājumu teksts</label>
                    <textarea id="question_text" name="question_text" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" name="create_question" class="btn btn-success">Pievienot jautājumu</button>
            </form>
        </div>

        <!-- ✅ Esošie jautājumi -->
        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <h3>Testam nav neviena jautājuma..</h3>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $index => $question): 
                $answers = $quiz_obj->getQuestionAnswers($question['id'], false);
            ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h4>Jautājums <?php echo $index + 1; ?></h4>
                    </div>

                    <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>

                    <!-- ✅ Atbildes -->
                    <div style="margin: 1rem 0;">
                        <strong>Atbildes:</strong>
                        <?php if (empty($answers)): ?>
                            <p style="color: var(--danger-color);">Nav nevienas atbildes</p>
                        <?php else: ?>
                            <ul style="margin-top: 0.5rem;">
                                <?php foreach ($answers as $answer): ?>
                                    <li style="margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                        <?php if ($answer['is_correct']): ?>
                                            <span class="badge badge-admin">Pareizā atbilde</span>
                                        <?php endif; ?>
                                        <a href="edit_answer.php?id=<?php echo $answer['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                                           class="btn btn-sm btn-primary" style="margin-left: 0.5rem;">Rediģēt</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="edit_question.php?id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                           class="btn btn-sm btn-primary">Rediģēt jautājumu</a>
                        <a href="manage_answers.php?question_id=<?php echo $question['id']; ?>&quiz_id=<?php echo $quiz_id; ?>" 
                           class="btn btn-sm btn-warning">Pārvaldīt atbildes</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                            <button type="submit" name="delete_question" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Dzēst šo jautājumu un visas tā atbildes?')">
                                Dzēst
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
