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
<html lang="si">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="page.login_title">Login - ක්‍රීඩා සමාජ කළමනාකරණ පද්ධතිය</title>
    <?php if (file_exists(__DIR__ . '/assets/css/tailwind.css')): ?>
        <link href="assets/css/tailwind.css?t=<?php echo filemtime(__DIR__ . '/assets/css/tailwind.css'); ?>" rel="stylesheet">
    <?php else: ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Noto+Sans+Sinhala:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="assets/js/i18n.js"></script>
    <style>
        body {
            font-family: 'Poppins', 'Noto Sans Sinhala', 'Iskoola Pota', sans-serif;
            transition: font-size 0.3s ease;
        }

        .login-gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
        }

        .login-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Improved layout for larger screens */
        .auth-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        @media (min-width: 900px) {
            .auth-grid {
                grid-template-columns: 1fr 1fr;
                align-items: stretch;
            }
        }

        .login-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .input-field {
            font-family: 'Poppins', 'Noto Sans Sinhala', 'Iskoola Pota', sans-serif;
            font-size: 14px;
            transition: all 0.2s;
        }

        .password-toggle {
            cursor: pointer;
            color: #6b7280;
        }

        .login-card .help-row a {
            color: #2563eb;
            text-decoration: underline;
        }


        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-login {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -3px rgba(59, 130, 246, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .lang-btn {
            transition: all 0.2s;
        }

        .lang-btn.active {
            background: white;
            color: #2563eb;
            font-weight: 600;
        }

        .role-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .left-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
    </style>
</head>

<body class="login-gradient flex items-center justify-center px-4 py-8">
    <div class="max-w-4xl w-full auth-grid">
        <div class="left-card">
            <div class="login-card p-6 flex flex-col items-center justify-center">
                <!-- Language Switcher -->
                <div class="mb-6 w-full flex justify-center">
                    <div class="flex space-x-1 bg-blue-100 rounded-lg p-1">
                        <button data-language="en" class="lang-btn px-3 py-1.5 rounded text-xs font-medium text-gray-700 hover:bg-blue-200">
                            EN
                        </button>
                        <button data-language="si" class="lang-btn active px-3 py-1.5 rounded text-xs font-medium text-white bg-blue-500 hover:bg-blue-600">
                            සිං
                        </button>
                        <button data-language="ta" class="lang-btn px-3 py-1.5 rounded text-xs font-medium text-gray-700 hover:bg-blue-200">
                            தமிழ்
                        </button>
                    </div>
                </div>

                <!-- Branding -->
                <img src="assets/img/logo.svg" alt="Southern Province Sports" class="w-24 h-24 mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-2 text-center" data-i18n="page.login_system_title"></h3>
                <p class="text-sm text-gray-600 text-center">Department of Sports Southern Province</p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="login-card p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-800 text-center mb-1" data-i18n="form.login_title"></h2>
                <div class="flex items-center justify-center gap-2 mt-2">
                    <span class="role-badge bg-blue-100 text-blue-700" data-i18n="role.admin">Admin</span>
                    <span class="text-gray-400 text-xs">/</span>
                    <span class="role-badge bg-gray-100 text-gray-600" data-i18n="role.viewer">Viewer</span>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-50 border-l-3 border-red-500 rounded">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-red-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-red-700 text-xs font-medium" id="errorMessage"
                            data-i18n="<?php
                                        if ($error === 'invalid') {
                                            echo 'login.error_invalid';
                                        } elseif ($error === 'required') {
                                            echo 'login.error_required';
                                        } elseif ($error === 'inactive') {
                                            echo 'login.error_inactive';
                                        } else {
                                            echo 'login.error_generic';
                                        }
                                        ?>">
                            <?php
                            if ($error === 'invalid') {
                                echo 'වලංගු නොවන පරිශීලක නාමය හෝ මුරපදය';
                            } elseif ($error === 'required') {
                                echo 'කරුණාකර පරිශීලක නාමය සහ මුරපදය ඇතුළත් කරන්න';
                            } elseif ($error === 'inactive') {
                                echo 'මෙම ගිණුම අක්‍රිය කර ඇත';
                            } else {
                                echo 'දෝෂයක් සිදු විය. කරුණාකර නැවත උත්සාහ කරන්න';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="api/login.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        <span data-i18n="form.username">පරිශීලක නාමය</span>
                        <span class="text-gray-400 font-normal"> (Username)</span>
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="input-field w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        data-i18n-placeholder="placeholder.enter_username"
                        required
                        autofocus>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">
                        <span data-i18n="form.password">මුරපදය</span>
                        <span class="text-gray-400 font-normal"> (Password)</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input-field w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            data-i18n-placeholder="placeholder.enter_password"
                            required>
                        <span id="togglePassword" class="absolute right-3 top-1/2 -translate-y-1/2 password-toggle" title="Show / hide password">
                            <!-- eye icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remember" class="w-4 h-4" />
                        <span class="text-gray-600 text-sm" data-i18n="login.remember">Remember me</span>
                    </label>
                    <!-- <a href="#" class="text-sm help-row text-gray-600" data-i18n="login.forgot">Forgot password?</a> -->
                </div>

                <button
                    type="submit"
                    class="btn-login w-full text-white py-2.5 rounded-lg font-semibold shadow-md">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span data-i18n="button.login">පිවිසීම</span>
                    </span>
                </button>
            </form>

            <!-- <div class="mt-5 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center">
                    <span data-i18n="login.default_credentials">පෙරනිමි අක්තපත්‍ර:</span>
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700 ml-1">admin / admin123</span>
                </p>
            </div> -->
        </div>

    </div>

    <!-- Footer -->
    <div class="mt-6 text-center text-white text-xs absolute bottom-4 left-0 right-0">
        <p>&copy; 2026 <span data-i18n="footer.department_name"></span>. <span data-i18n="footer.all_rights">All rights reserved.</span></p>
    </div>

    <script>
        // Update language switcher active state
        function updateLanguageSwitcher(lang) {
            document.querySelectorAll("[data-language]").forEach((button) => {
                if (button.getAttribute("data-language") === lang) {
                    button.classList.add("active");
                } else {
                    button.classList.remove("active");
                }
            });
        }

        // Setup language switcher
        document.querySelectorAll("[data-language]").forEach((button) => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const lang = button.getAttribute("data-language");
                if (window.i18n) {
                    window.i18n.loadTranslations(lang);
                    updateLanguageSwitcher(lang);
                }
            });
        });

        // Error message will be automatically translated via data-i18n attribute

        // Initialize language on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedLang = localStorage.getItem('language') || 'si';
            updateLanguageSwitcher(savedLang);
        });

        // Toggle password visibility (click the eye icon)
        document.addEventListener('click', function(e) {
            if (e.target.closest && e.target.closest('#togglePassword')) {
                const pwd = document.getElementById('password');
                if (!pwd) return;
                pwd.type = pwd.type === 'password' ? 'text' : 'password';
            }
        });
    </script>
</body>

</html>