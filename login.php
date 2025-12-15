<?php
/**
 * login.php - Đăng nhập + Remember Me
 */
// Lưu ý: config.php đã có session_start và connection
include 'config.php'; 
// include 'connection.php'; // Bỏ dòng này vì config.php đã kết nối rồi

// 1. Nếu đã đăng nhập -> Chuyển hướng
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'Donor') header('Location: donor-home.php');
    elseif ($role === 'Doctor') header('Location: doctor-home.php');
    else header('Location: home.php');
    exit();
}

$error = '';

// 2. Xử lý Form Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = mysqli_real_escape_string($link, $_POST['username']);
    $password_input = $_POST['password'];
    $remember = isset($_POST['remember']); // Kiểm tra checkbox

    // Query tìm user (check cả is_deleted)
    $query = "SELECT * FROM users WHERE (username = '$username_input' OR email = '$username_input') AND is_deleted = 0";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Kiểm tra mật khẩu
        $check_pass = false;
        if (password_verify($password_input, $user['password_hash'])) {
            $check_pass = true;
        } elseif (md5($password_input) === $user['password_hash']) {
            $check_pass = true; 
        }

        if ($check_pass) {
            // A. LƯU SESSION
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $user['avatarUrl'] ?? '';

            // B. XỬ LÝ REMEMBER ME (COOKIE)
            if ($remember) {
                // 1. Tạo token ngẫu nhiên
                $token = bin2hex(random_bytes(32)); 
                $uid = $user['id'];

                // 2. Lưu token vào Database
                mysqli_query($link, "UPDATE users SET remember_token = '$token' WHERE id = $uid");

                // 3. Lưu Cookie: "ID:Token" (30 ngày)
                $cookie_value = "$uid:$token";
                setcookie('remember_me', $cookie_value, time() + COOKIE_EXPIRY, "/");
            }

            // C. CHUYỂN HƯỚNG
            if ($user['role'] === 'Donor') header('Location: donor-home.php');
            elseif ($user['role'] === 'Doctor') header('Location: doctor-home.php');
            else header('Location: home.php');
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found or deactivated.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - B-DONOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: { 500: '#ef4444', 600: '#dc2626', 700: '#b91c1c', 800: '#991b1b' }
                    }
                }
            }
        }
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white transition-colors duration-300">
    <div class="min-h-screen flex w-full">
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 lg:p-16 xl:p-24 bg-white dark:bg-gray-900 z-10">
            <div>
                <a href="#" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-brand-600 transition-colors dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Home
                </a>
            </div>
            <div class="max-w-md w-full mx-auto">
                <div class="mb-10">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-3">
                        Welcome Back! <span class="text-3xl">👋</span>
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-base">Please sign in to your account to continue.</p>
                </div>
                <?php if($error): ?>
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Username / Email</label>
                        <div class="relative">
                            <input type="text" id="username" name="username" placeholder="Enter your username" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:placeholder-gray-500 text-sm pr-10">
                            <button type="button" id="togglePass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="remember" class="peer h-5 w-5 cursor-pointer appearance-none rounded border border-gray-300 shadow-sm checked:border-brand-600 checked:bg-brand-600 hover:border-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:checked:bg-brand-600">
                                <svg class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-400 font-medium select-none">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-500">Forgot Password?</a>
                    </div>
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3.5 px-4 rounded-lg transition-colors shadow-lg shadow-brand-600/30 text-sm tracking-wide">
                        Sign In
                    </button>
                    <div class="text-center mt-8">
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Don't have an account? <a href="register.php" class="text-brand-600 font-bold hover:underline dark:text-brand-500 ml-1">Create Account</a></p>
                    </div>
                </form>
            </div>
            <div class="text-center lg:text-left mt-auto pt-8">
                <p class="text-xs text-gray-400 dark:text-gray-500">&copy; 2025 B-Donor. All rights reserved.</p>
            </div>
        </div>
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-brand-800 to-[#7f1d1d] relative items-center justify-center overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <svg class="absolute top-10 left-10 w-32 h-32 text-white transform -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                <svg class="absolute bottom-20 right-20 w-64 h-64 text-white opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
            </div>
            <div class="relative z-10 text-center px-12 max-w-lg">
                <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white drop-shadow-md"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/></svg>
                </div>
                <h2 class="text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight tracking-tight">Give Blood, <br> Save Lives</h2>
                <p class="text-red-100 text-lg font-light leading-relaxed">Join our community of heroes. Your donation can make a difference in someone's life today.</p>
            </div>
        </div>
        <button id="theme-toggle" class="fixed bottom-6 right-6 z-50 p-3 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-300 focus:outline-none ring-2 ring-white/50">
            <svg id="theme-toggle-light-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
            <svg id="theme-toggle-dark-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
        </button>
    </div>
    <script>
        const togglePass = document.getElementById('togglePass');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        togglePass.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            if (type === 'text') {
                eyeIcon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            } else {
                eyeIcon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
            }
        });
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        var themeToggleBtn = document.getElementById('theme-toggle');
        if (document.documentElement.classList.contains('dark')) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }
        themeToggleBtn.addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });
    </script>
</body>
</html>
