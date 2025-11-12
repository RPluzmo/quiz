<?php
require_once 'config/config.php';

// Require login
User::requireLogin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get quiz ID
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($quiz_id === 0) {
    header('Location: dashboard.php');
    exit();
}

// Get quiz details
$quiz = $quiz_obj->getQuizById($quiz_id);
if (!$quiz) {
    header('Location: dashboard.php');
    exit();
}

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
    $user_answers = json_decode($_POST['answers'], true);
    $questions = $quiz_obj->getQuizQuestions($quiz_id, false);

    $score = 0;
    $details = [];

    foreach ($questions as $question) {
        $correct_answer_id = $quiz_obj->getCorrectAnswer($question['id']);
        $user_answer_id = $user_answers[$question['id']] ?? null;
        $is_correct = ($user_answer_id == $correct_answer_id);

        // Iegūstam teksta vērtības no DB
        $answers = $quiz_obj->getQuestionAnswers($question['id'], false);
        $user_answer_text = '';
        $correct_answer_text = '';

        foreach ($answers as $a) {
            if ($a['id'] == $user_answer_id) $user_answer_text = $a['answer_text'];
            if ($a['id'] == $correct_answer_id) $correct_answer_text = $a['answer_text'];
        }

        if ($is_correct) $score++;

        $details[] = [
            'question' => $question['question_text'],
            'user_answer' => $user_answer_text ?: 'Nav atbildes',
            'correct_answer' => $correct_answer_text,
            'is_correct' => $is_correct
        ];
    }

    // Saglabājam rezultātu DB
    $quiz_obj->saveResult($_SESSION['user_id'], $quiz_id, $score, count($questions));

    // Saglabājam sesijā visus datus priekš quiz_result.php
    $_SESSION['quiz_results'] = [
        'quiz_name' => $quiz['name'],
        'score' => $score,
        'total' => count($questions),
        'details' => $details
    ];

    header('Location: quiz_result.php');
    exit();
}

// Get questions with randomized order
$questions = $quiz_obj->getQuizQuestions($quiz_id, true);
foreach ($questions as &$question) {
    $question['answers'] = $quiz_obj->getQuestionAnswers($question['id'], true);
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['name']); ?> Tests</title>
    <link rel="stylesheet" href="assets/css/style.css">
    </head>
<body>
<header>
    <div class="header-content">
        <div class="logo">
            <h1>Tests</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Sākumlapa</a></li>
                <li><a href="history.php">Iepriekšējās darbības</a></li>
                <?php if (User::isAdmin()): ?>
                    <li><a href="admin/index.php">Admina panelis</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Izlogoties (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($quiz['name']); ?></h2>
            <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        </div>

        <div class="progress-container">
            <div class="progress-info">
                <span id="progress-text">1. jautājums no <?php echo count($questions); ?></span> 
                <span class="text-right">Kopā: <?php echo count($questions); ?> jaut.</span>
            </div>
            <div class="progress-bar-container">
                <div id="progress-bar" class="progress-bar" style="width: <?php echo (1 / count($questions)) * 100; ?>%">
                </div>
            </div>
        </div>

        <form id="quiz-form" method="POST" action="">
            <div class="question-container" id="question-container"></div>

            <div class="quiz-navigation">
                <button type="button" id="prev-btn" class="btn btn-secondary" disabled>
                    <- Iepriekšējais
                </button>
                <button type="button" id="next-btn" class="btn btn-primary" disabled>
                    Nākamais ->
                </button>
                <button type="button" id="submit-btn" class="btn btn-success" style="display:none;" disabled>
                    Pabeigt testu
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
    const questionsData = <?php echo json_encode(array_map(function($q) {
        return [
            'id' => $q['id'],
            'text' => $q['question_text'],
            'answers' => array_map(function($a) {
                return [
                    'id' => $a['id'],
                    'text' => $a['answer_text']
                ];
            }, $q['answers'])
        ];
    }, $questions)); ?>;

    // Uzsākam QuizManager ar datiem no PHP
    quizManager.init(questionsData);
</script>
</body>
</html>