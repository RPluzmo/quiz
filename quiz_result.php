<?php
require_once 'config/config.php';

// Require login
User::requireLogin();

// Check if results exist in session
if (!isset($_SESSION['quiz_results'])) {
    header('Location: dashboard.php');
    exit();
}

$results = $_SESSION['quiz_results'];
$percentage = round(($results['score'] / $results['total']) * 100);
$details = $results['details'] ?? [];

// Determine message based on score
if ($percentage >= 90) {
    $message = "Tu pārzini šo tēmu, malacis!";
    $message_class = "success";
} elseif ($percentage >= 70) {
    $message = "Labs, par tēmu tu zini daudz.";
    $message_class = "success";
} elseif ($percentage >= 50) {
    $message = "Kaut ko tu zini, ir ok.";
    $message_class = "warning";
} else {
    $message = "Diezgan švaki... varbūt šis nebija priekš tevis :)";
    $message_class = "danger";
}

$quiz_name = $results['quiz_name'];
$score = $results['score'];
$total = $results['total'];

// Pēc lapas ielādes noņemam sesijas rezultātus, lai nevar atkārtoti pārlādēt
unset($_SESSION['quiz_results']);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testu rezultāti</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .question-list {
            list-style: none;
            padding: 0;
        }
        .question-item {
            background: var(--light-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .question-item strong {
            color: var(--text-color);
        }
        .question-item em {
            font-style: normal;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>Rezultāti</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Sākumlapa</a></li>
                    <li><a href="history.php">Iepriekšējās darbības</a></li>
                    <?php if (User::isAdmin()): ?>
                        <li><a href="admin/index.php">Admina opcijas</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Izlogoties (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="result-container">
                <h2>Tests izpildīts</h2>
                <div class="result-message alert alert-<?php echo $message_class; ?>">
                    <?php echo $message; ?>
                </div>

                <div class="result-score">
                    <strong>Rezultāts:</strong> <?php echo $score; ?> / <?php echo $total; ?>
                </div>

                <div class="result-details">
                    <h3><?php echo htmlspecialchars($quiz_name); ?></h3>
                    <p><strong>Pareizās atbildes:</strong> <?php echo $score; ?> no <?php echo $total; ?></p>
                    <p><strong>Procenti:</strong> <?php echo $percentage; ?>%</p>
                    <p><strong>Kad pildīts:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                </div>

                <?php if (!empty($details)): ?>
                    <div class="card mt-4" style="background: var(--lighter-bg, #f9f9f9); padding: 1rem;">
                        <h3>Jautājumi un atbildes:</h3>
                        <ul class="question-list">
                            <?php foreach ($details as $index => $item): ?>
                                <li class="question-item">
                                    <strong><?php echo ($index + 1) . ". " . htmlspecialchars($item['question']); ?></strong><br>
                                    <span style="color: <?php echo $item['is_correct'] ? 'var(--success-color, green)' : 'var(--danger-color, red)'; ?>">
                                        Tavs atbildes variants:
                                        <em><?php echo htmlspecialchars($item['user_answer']); ?></em>
                                        <?php echo $item['is_correct'] ? '✔️' : '❌'; ?>
                                    </span><br>
                                    <?php if (!$item['is_correct']): ?>
                                        <small>Pareizā atbilde:
                                            <strong><?php echo htmlspecialchars($item['correct_answer']); ?></strong>
                                        </small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-between gap-2" style="margin-top: 2rem;">
                    <a href="dashboard.php" class="btn btn-secondary">← Atpakaļ uz sākumlapu</a>
                    <a href="history.php" class="btn btn-primary">Apskatīt iepriekšējās darbības</a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
