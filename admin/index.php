<?php
require_once '../config/config.php';

// Require admin access
User::requireAdmin();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$quiz = new Quiz($db);

// Get statistics
$all_users = $user->getAllUsers();
$all_quizzes = $quiz->getAllQuizzes();

$total_users = count($all_users);
$total_admins = count(array_filter($all_users, function($u) { return $u['role'] === 'admin'; }));
$total_quizzes = count($all_quizzes);

// Handle user actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $result = $user->deleteUser($_POST['user_id']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh user list
        $all_users = $user->getAllUsers();
    } elseif (isset($_POST['change_role'])) {
        $result = $user->changeRole($_POST['user_id'], $_POST['new_role']);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
        // Refresh user list
        $all_users = $user->getAllUsers();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Quiz System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                
            </div>
            <nav>
                <ul>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="index.php">Users</a></li>
                    <li><a href="quizzes.php">Quizzes</a></li>
                    <li><a href="../logout.php">Logout</a></li>
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

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Admin Users</div>
                <div class="stat-value"><?php echo $total_admins; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Quizzes</div>
                <div class="stat-value"><?php echo $total_quizzes; ?></div>
            </div>
        </div>

        <!-- User Management -->
        <div class="card">
            <div class="card-header">
                <h2>User Management</h2>
                <p>Manage user accounts and permissions</p>
            </div>

            <div class="form-group">
                <input type="text" id="user-search" class="form-control" placeholder="Search users...">
            </div>

            <div style="overflow-x: auto;">
                <table class="table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $u['role']; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                        
                                        <!-- Change Role -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <input type="hidden" name="new_role" value="<?php echo $u['role'] === 'admin' ? 'user' : 'admin'; ?>">
                                            <button type="submit" name="change_role" class="btn btn-sm btn-warning"
                                                    onclick="return confirm('Change role for <?php echo htmlspecialchars($u['username']); ?>?')">
                                                <?php echo $u['role'] === 'admin' ? '→ User' : '→ Admin'; ?>
                                            </button>
                                        </form>

                                        <!-- Delete User -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($u['username']); ?>?')">
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
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>