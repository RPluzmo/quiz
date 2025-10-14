<?php
require_once 'config/config.php';

// Require login
User::requireLogin();

$database = new Database();
$db = $database->getConnection();
$quiz = new Quiz($db);

// Get user history
$history = $quiz->getUserHistory($_SESSION['user_id']);

// Get all quizzes for filter
$quizzes = $quiz->getAllQuizzes();
?>
<!DOCTYPE html>
<html lang="en">
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
                <h1>reocrds</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Sākumlapa</a></li>
                    <li><a href="history.php">Iepriekšējās darbības</a></li>
                    <?php if (User::isAdmin()): ?>
                        <li><a href="admin/index.php">l</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Izlogoties (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Sasniegumi</h2>
            </div>

            <?php if (empty($history)): ?>
                <div class="empty-state">
                    <h3>Neviens quizz nav spēlēts.</h3>
                    <a href="dashboard.php" class="btn btn-primary mt-3">Pildīt quizu</a>
                </div>
            <?php else: ?>
                <!-- Filters -->
                <div class="d-flex justify-between align-center mb-3">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="quiz-filter">Filter by Quiz:</label>
                        <select id="quiz-filter" class="form-control" onchange="filterHistory(this.value)" style="width: auto; display: inline-block; margin-left: 0.5rem;">
                            <option value="all">Visi quizzi</option>
                            <?php foreach ($quizzes as $q): ?>
                                <option value="<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="sort-by">Kārtot pēc:</label>
                        <select id="sort-by" class="form-control" onchange="sortHistory(this.value)" style="width: auto; display: inline-block; margin-left: 0.5rem;">
                            <option value="date">Datuma</option>
                            <option value="score">Rezultāta</option>
                        </select>
                    </div>
                </div>

                <!-- History Items -->
                <div id="history-container">
                    <?php foreach ($history as $result): 
                        $percentage = round(($result['score'] / $result['total_questions']) * 100);
                        $score_class = $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                    ?>
                        <div class="history-item" 
                             data-quiz-id="<?php echo $result['quiz_id']; ?>"
                             data-date="<?php echo $result['completed_at']; ?>"
                             data-score="<?php echo $percentage; ?>">
                            <div class="history-info">
                                <h4><?php echo htmlspecialchars($result['quiz_name']); ?></h4>
                                <p><?php echo htmlspecialchars($result['description']); ?></p>
                                <p style="font-size: 0.875rem; color: var(--light-text);">
                                     <?php echo date('F j, Y g:i A', strtotime($result['completed_at'])); ?>
                                </p>
                            </div>
                            <div class="history-score">
                                <div class="score" style="color: var(--<?php echo $score_class; ?>-color);">
                                    <?php echo $result['score']; ?>/<?php echo $result['total_questions']; ?>
                                </div>
                                <div class="percentage"><?php echo $percentage; ?>%</div>
                                <a href="take_quiz.php?id=<?php echo $result['quiz_id']; ?>" class="btn btn-sm btn-primary mt-2">
                                    Retake Quiz
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Statistics Summary -->
                <div class="card mt-4" style="background: var(--light-bg); padding: 1.5rem;">
                    <h3>Your Statistics</h3>
                    <div class="stats-grid">
                        <div>
                            <strong>Quizi pildī:</strontg> <?php echo count($history); ?>
                        </div>
                        <div>
                            <strong>Vidējais rezultās:</strong> 
                            <?php 
                            $total_score = 0;
                            $total_questions = 0;
                            foreach ($history as $result) {
                                $total_score += $result['score'];
                                $total_questions += $result['total_questions'];
                            }
                            echo $total_questions > 0 ? round(($total_score / $total_questions) * 100) : 0;
                            ?>%
                        </div>
                        <div>
                            <strong>Best Score:</strong>
                            <?php
                            $best_percentage = 0;
                            foreach ($history as $result) {
                                $percentage = round(($result['score'] / $result['total_questions']) * 100);
                                if ($percentage > $best_percentage) {
                                    $best_percentage = $percentage;
                                }
                            }
                            echo $best_percentage;
                            ?>%
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>