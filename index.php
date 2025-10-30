<?php
require_once 'config/config.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $user = new User($db);
        $user->username = trim($_POST['username']);
        $user->password = $_POST['password'];

        $result = $user->login();
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'DB TEVI NĒĒĒGRIB';
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $user = new User($db);
        $user->username = trim($_POST['username']);
        $user->password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if passwords match
        if ($user->password !== $confirm_password) {
            $error = 'Paroles nesakrīt, mby tev CAPS ieslēgts?? idk';
        } else {
            $result = $user->register();
            
            if ($result['success']) {
                $success = $result['message'] . '. Tagad jūst droši varat ielogoties.';
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = 'DB TEVI NĒĒĒGRIB';
    }
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ierakstīšanās</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>quizza</h1>
                
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <div id="login-form" style="<?php echo isset($_POST['register']) ? 'display:none;' : ''; ?>">
                <form method="POST" action="index.php" novalidate>

                    <div class="form-group">
                        <label for="login-username">Lietotājvārds</label>
                        <input type="text" id="login-username" name="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Parole</label>
                        <input type="text" id="login-password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" name="login" class="btn btn-primary btn-block">Ierakstīties</button>
                </form>

                <div class="auth-toggle">
                    <p>Vai vēl nēsi reģistrējies? <a href="javascript:void(0);" onclick="toggleForms();">Reģistrēties</a></p>
                </div>
            </div>

            <!-- Registration Form -->
            <div id="register-form" style="<?php echo isset($_POST['register']) ? '' : 'display:none;'; ?>">
                <form method="POST" action="index.php" novalidate>

                    <div class="form-group">
                        <label for="register-username">Lietotājvārds</label>
                        <input type="text" id="register-username" name="username" class="form-control" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Parole</label>
                        <input type="text" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password">Apstiprini paroli</label>
                        <input type="text" id="confirm-password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" name="register" class="btn btn-primary btn-block">Reģistrēties</button>
                </form>

                <div class="auth-toggle">
                    <p>Mby tev tomēr jau ir lietotājvārds? <a href="#" onclick="toggleForms(); return false;">Ielogoties</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>