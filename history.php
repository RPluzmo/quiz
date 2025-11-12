<?php
require_once 'config/config.php';

// Require login
User::requireLogin();

$database = new Database();
$db = $database->getConnection();
$quiz = new Quiz($db);

// Filters
$filter_quiz = $_GET['quiz'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'date';
$order = $_GET['order'] ?? 'desc';

$history = [];
$highscores_mode = false;

if ($sort_by === 'highscore') {
    // Īpašs režīms: Iegūst labākos rezultātus starp visiem lietotājiem
    $highscores_mode = true;
    
    // Šī funkcija JĀIZVEIDO/JĀLABO klasē Quiz.php (skat. piezīmes zemāk!)
    $history = $quiz->getHighScoresForAllUsers($filter_quiz);
    
    // Pārslēdz kārtošanu uz dilstošu, jo highscore vienmēr ir 'desc'
    $order = 'desc';

} else {
    // Parastais režīms: Iegūst tikai ielogotā lietotāja vēsturi
    $history = $quiz->getUserHistory($_SESSION['user_id']);

    // Filtrēšana pēc testa
    if ($filter_quiz !== 'all') {
        $history = array_filter($history, fn($item) => $item['quiz_id'] == $filter_quiz);
    }
    
    // Kārtošana (tikai lietotāja vēsturei)
    usort($history, function($a, $b) use ($sort_by, $order) {
        if ($sort_by === 'score') {
            $pa = ($a['score'] / $a['total_questions']) * 100;
            $pb = ($b['score'] / $b['total_questions']) * 100;
        } else {
            $pa = strtotime($a['completed_at']);
            $pb = strtotime($b['completed_at']);
        }
        $result = $pb <=> $pa; // Default descending
        return $order === 'asc' ? -$result : $result;
    });
}

// Get all quizzes
$quizzes = $quiz->getAllQuizzes();
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iepriekšējās darbības</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <div class="header-content">
        <div class="logo">
            <h1>Visi iepriekš pildītie testi</h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Sākumlapa</a></li>
                <?php if (User::isAdmin()): ?>
                    <li><a href="admin/index.php">Admina opcijas</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Izlogoties (<?= htmlspecialchars($_SESSION['username']); ?>)</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Sasniegumi</h2>
        </div>

        <form method="GET" class="d-flex justify-between align-center mb-3" style="gap: 1rem; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="quiz-filter">Tests:</label>
                <select id="quiz-filter" name="quiz" class="form-control" style="width: auto; margin-left: 0.5rem;">
                    <option value="all" <?= $filter_quiz === 'all' ? 'selected' : '' ?>>Visi testi</option>
                    <?php foreach ($quizzes as $q): ?>
                        <option value="<?= $q['id'] ?>" <?= $filter_quiz == $q['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($q['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="sort-by">Kārtot pēc:</label>
                <select id="sort-by" name="sort" class="form-control" style="width: auto; margin-left: 0.5rem;">
                    <option value="date" <?= $sort_by === 'date' ? 'selected' : '' ?>>Datuma</option>
                    <option value="score" <?= $sort_by === 'score' ? 'selected' : '' ?>>Mana rezultāta</option>
                    <option value="highscore" <?= $sort_by === 'highscore' ? 'selected' : '' ?>>Highscore (Visi)</option>
                    </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="order">Secība:</label>
                <select id="order" name="order" class="form-control" style="width: auto; margin-left: 0.5rem;">
                    <option value="desc" <?= $order === 'desc' ? 'selected' : '' ?>>Dilstoši</option>
                    <option value="asc" <?= $order === 'asc' ? 'selected' : '' ?>>Augoši</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrēt</button>
        </form>

        <?php if (empty($history)): ?>
            <div class="empty-state">
                <h3>Nav rezultātu atbilstoši izvēlētajiem filtriem.</h3>
                <p>Izmēģini mainīt filtru vai pildīt kādu testu.</p>
                <a href="dashboard.php" class="btn btn-primary mt-3">Uz sākumlapu</a>
            </div>
        <?php else: ?>
            <div id="history-container">
                <?php foreach ($history as $result): 
                    // Pārliecināmies, ka lauki eksistē un nav tukši, lai novērstu kļūdas
                    $score = $result['score'] ?? 0;
                    $total_questions = $result['total_questions'] ?? 1;
                    
                    // Pārbauda, vai $total_questions nav 0, lai izvairītos no dalīšanas ar nulli
                    $total_questions = $total_questions > 0 ? $total_questions : 1;
                    
                    $percentage = round(($score / $total_questions) * 100);
                    $score_class = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                ?>
                    <div class="history-item" 
                         data-quiz-id="<?= $result['quiz_id'] ?>"
                         data-date="<?= $result['completed_at'] ?>"
                         data-score="<?= $percentage ?>">
                        <div class="history-info">
                            <h4><?= htmlspecialchars($result['quiz_name']) ?></h4>
                            
                            <?php if ($highscores_mode): ?>
                                <p style="font-weight: bold; margin-bottom: 0.25rem;">Lietotājs: <?= htmlspecialchars($result['username']); ?></p>
                            <?php else: ?>
                                <p><?= htmlspecialchars($result['description'] ?? 'Nav apraksta'); ?></p>
                            <?php endif; ?>

                            <p style="font-size: 0.875rem; color: var(--light-text);">
                                <?= date('Y-m-d H:i', strtotime($result['completed_at'])) ?>
                            </p>
                        </div>
                        <div class="history-score">
                            <div class="score" style="color: var(--<?= $score_class ?>-color);">
                                <?= $score; ?>/<?= $total_questions; ?>
                            </div>
                            <div class="percentage"><?= $percentage; ?>%</div>
                            <a href="take_quiz.php?id=<?= $result['quiz_id']; ?>" class="btn btn-sm btn-primary mt-2">
                                Pildīt testu vēlreiz
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$highscores_mode): ?>
            <div class="card mt-4" style="background: var(--light-bg); padding: 1.5rem;">
                <h3>Jūsu rezultāti</h3>
                <div class="stats-grid">
                    <div><strong>Izpildīti testi:</strong> <?= count($history); ?></div>
                    <div>
                        <strong>Vidējais rezultāts:</strong>
                        <?php
                        // Šeit lietojam tikai to vēsturi, kas pieder ielogotajam lietotājam.
                        $total_score = array_sum(array_column($history, 'score'));
                        $total_questions_sum = array_sum(array_column($history, 'total_questions'));
                        echo $total_questions_sum > 0 ? round(($total_score / $total_questions_sum) * 100) : 0;
                        ?>%
                    </div>
                    <div>
                        <strong>Labākais rezultāts:</strong>
                        <?php
                        $best = 0;
                        foreach ($history as $r) {
                            $q_count = $r['total_questions'] > 0 ? $r['total_questions'] : 1;
                            $p = round(($r['score'] / $q_count) * 100);
                            if ($p > $best) $best = $p;
                        }
                        echo $best; ?>%
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>