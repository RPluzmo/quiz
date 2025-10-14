<?php
require_once 'config/config.php';

// Require login
User::requireLogin();

$database = new Database();
$db = $database->getConnection();
$quiz = new Quiz($db);

// Get all quizzes
$quizzes = $quiz->getAllQuizzes();

// Get user statistics
$user_history = $quiz->getUserHistory($_SESSION['user_id']);
$total_quizzes_taken = count($user_history);
$total_score = 0;
$total_questions = 0;

foreach ($user_history as $result) {
    $total_score += $result['score'];
    $total_questions += $result['total_questions'];
}

$average_score = $total_questions > 0 ? round(($total_score / $total_questions) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Quiz System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>hi</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="history.php">History</a></li>
                    <?php if (User::isAdmin()): ?>
                        <li><a href="admin/index.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h2>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Quizzes Taken</div>
                    <div class="stat-value"><?php echo $total_quizzes_taken; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Average Score</div>
                    <div class="stat-value"><?php echo $average_score; ?>%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Available Quizzes</div>
                    <div class="stat-value"><?php echo count($quizzes); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Available Quizzes</h2>
                <p>Select a quiz to test your knowledge</p>
            </div>

            <?php if (empty($quizzes)): ?>
                <div class="empty-state">
                    <h3>No quizzes available</h3>
                    <p>Check back later for new quizzes</p>
                </div>
            <?php else: ?>
                <div class="quiz-grid">
                    <?php foreach ($quizzes as $q): ?>
                        <div class="quiz-card" data-quiz-id="<?php echo $q['id']; ?>">
                            <h3><?php echo htmlspecialchars($q['name']); ?></h3>
                            <p><?php echo htmlspecialchars($q['description']); ?></p>
                            <div class="quiz-meta">
                                <span>📝 <?php echo $q['question_count']; ?> Questions</span>
                                <span>👥 <?php echo $q['times_taken']; ?> Attempts</span>
                            </div>
                            <a href="take_quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-primary btn-block">
                                Start Quiz
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($user_history)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                </div>
                <?php 
                $recent_history = array_slice($user_history, 0, 5);
                foreach ($recent_history as $result): 
                    $percentage = round(($result['score'] / $result['total_questions']) * 100);
                ?>
                    <div class="history-item">
                        <div class="history-info">
                            <h4><?php echo htmlspecialchars($result['quiz_name']); ?></h4>
                            <p><?php echo date('F j, Y g:i A', strtotime($result['completed_at'])); ?></p>
                        </div>
                        <div class="history-score">
                            <div class="score"><?php echo $result['score']; ?>/<?php echo $result['total_questions']; ?></div>
                            <div class="percentage"><?php echo $percentage; ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="history.php" class="btn btn-secondary btn-block mt-3">View Full History</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>