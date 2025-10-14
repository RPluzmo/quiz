<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$quiz_obj = new Quiz($db);

// Get quiz ID
$quiz_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($quiz_id === 0) {
    header('Location: quizzes.php');
    exit();
}

// Get quiz details
$quiz = $quiz_obj->getQuizById($quiz_id);

if (!$quiz) {
    header('Location: quizzes.php');
    exit();
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $result = $quiz_obj->updateQuiz($quiz_id, $name, $description);
    $message = $result['message'];
    $message_type = $result['success'] ? 'success' : 'danger';

    if ($result['success']) {
        // Refresh quiz data
        $quiz = $quiz_obj->getQuizById($quiz_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - Admin Panel</title>
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
                <h2>Edit Quiz</h2>
                <p>Update quiz information</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Quiz Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($quiz['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Created At</label>
                    <input type="text" class="form-control" 
                           value="<?php echo date('F j, Y g:i A', strtotime($quiz['created_at'])); ?>" 
                           readonly>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Quiz</button>
                    <a href="manage_questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-warning">Manage Questions</a>
                    <a href="quizzes.php" class="btn btn-secondary">Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>