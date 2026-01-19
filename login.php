<?php
require_once 'includes/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: public/dashboard.php');
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sports Club Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&family=Noto+Sans+Tamil:wght@400;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Sinhala', 'Noto Sans Tamil', 'Roboto', sans-serif;
        }

        .login-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="login-gradient min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white rounded-full p-4 shadow-lg mb-4">
                <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Sports Club Management</h1>
            <p class="text-purple-100">Southern Province Sports Department</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Admin Login</h2>

            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-600 text-sm">
                        <?php
                        if ($error === 'invalid') {
                            echo 'Invalid username or password';
                        } elseif ($error === 'required') {
                            echo 'Please enter username and password';
                        } elseif ($error === 'inactive') {
                            echo 'This account is inactive';
                        } else {
                            echo 'An error occurred. Please try again';
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <form action="api/login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your username"
                        required
                        autofocus>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your password"
                        required>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition duration-200 shadow-lg">
                    Sign In
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-600 text-center">
                    Default credentials:<br>
                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">admin / admin123</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-white text-sm">
            <p>&copy; 2026 Southern Province Sports Department. All rights reserved.</p>
        </div>
    </div>
</body>

</html>