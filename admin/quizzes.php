<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz = new Quiz($db);

// Get all quizzes
$quizzes = $quiz->getAllQuizzes();

$message = '';
$message_type = '';

// Handle quiz actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_quiz'])) {
        $result = $quiz->createQuiz($_POST['name'], $_POST['description']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh quiz list
        $quizzes = $quiz->getAllQuizzes();
    } elseif (isset($_POST['delete_quiz'])) {
        $result = $quiz->deleteQuiz($_POST['quiz_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh quiz list
        $quizzes = $quiz->getAllQuizzes();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management - Admin Panel</title>
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
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Create New Quiz -->
        <div class="card">
            <div class="card-header">
                <h2>Create New Quiz</h2>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Quiz Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" name="create_quiz" class="btn btn-success">Create Quiz</button>
            </form>
        </div>

        <!-- Quiz List -->
        <div class="card">
            <div class="card-header">
                <h2>Manage Quizzes</h2>
                <p>Edit quizzes, questions, and answers</p>
            </div>

            <?php if (empty($quizzes)): ?>
                <div class="empty-state">
                    <h3>No quizzes yet</h3>
                    <p>Create your first quiz above</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Questions</th>
                                <th>Times Taken</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $q): ?>
                                <tr>
                                    <td><?php echo $q['id']; ?></td>
                                    <td><?php echo htmlspecialchars($q['name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($q['description'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo $q['question_count']; ?></td>
                                    <td><?php echo $q['times_taken']; ?></td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="edit_quiz.php?id=<?php echo $q['id']; ?>" class="btn btn-sm btn-primary">
                                                Edit
                                            </a>
                                            <a href="manage_questions.php?quiz_id=<?php echo $q['id']; ?>" class="btn btn-sm btn-warning">
                                                Questions
                                            </a>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="quiz_id" value="<?php echo $q['id']; ?>">
                                                <button type="submit" name="delete_quiz" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete quiz: <?php echo htmlspecialchars($q['name']); ?>?')">
                                                    Delete
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