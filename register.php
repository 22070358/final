<?php
/**
 * register.php - Đăng ký tài khoản mới
 */
session_start();
include 'config.php';
include 'connection.php';

// Nếu đã đăng nhập thì chuyển hướng
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'Doctor' ? 'doctor-home.php' : 'donor-home.php'));
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu
    $firstname = mysqli_real_escape_string($link, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($link, $_POST['lastname']);
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']); // Giả sử form có email để reset pass sau này
    $password = $_POST['password'];
    
    // Ghép tên
    $fullname = $firstname . ' ' . $lastname;

    // Kiểm tra username hoặc email đã tồn tại chưa
    $check = mysqli_query($link, "SELECT id FROM users WHERE username = '$username' OR email = '$email'"); // Cần đảm bảo bảng users có cột email
    
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or Email already exists.";
    } else {
        // Mã hóa mật khẩu
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Mặc định role là Donor, avatar ngẫu nhiên
        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($fullname) . "&background=random&color=fff";
        
    $sql = "INSERT INTO users (username, email, password_hash, name, role, avatarUrl) 
        VALUES ('$username', '$email', '$password_hash', '$fullname', 'Donor', '$avatar')";
        
        if (mysqli_query($link, $sql)) {
            // Lấy ID vừa tạo để tạo hồ sơ donor rỗng (tùy chọn)
            $new_user_id = mysqli_insert_id($link);
            mysqli_query($link, "INSERT INTO donor_profiles (userId) VALUES ($new_user_id)");

            $success = "Account created successfully! Redirecting to login...";
            echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
        } else {
            $error = "Error: " . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - B-DONOR</title>
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
        
        <div class="w-full lg:w-1/2 flex flex-col justify-between p-8 lg:p-12 xl:p-16 bg-white dark:bg-gray-900 z-10 overflow-y-auto">
            <div>
                <a href="login.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-brand-600 transition-colors dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="m15 18-6-6 6-6"/></svg>
                    Back to Home
                </a>
            </div>

            <div class="max-w-md w-full mx-auto mt-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        Create Account <span class="text-2xl">🚀</span>
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Join B-Donor to start your journey of saving lives.</p>
                </div>

                <?php if($error): ?>
                    <div class="mb-6 p-3 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-400"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="mb-6 p-3 rounded-lg bg-green-50 border border-green-200 text-green-600 text-sm dark:bg-green-900/20 dark:border-green-800 dark:text-green-400"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">First Name</label>
                            <input type="text" name="firstname" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm" placeholder="John">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Last Name</label>
                            <input type="text" name="lastname" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm" placeholder="Doe">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm" placeholder="johndoe123">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm" placeholder="name@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                        <div class="relative">
                            <input type="password" id="reg_password" name="password" required class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20 outline-none transition-all bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-white text-sm pr-10" placeholder="Min 8 characters">
                            <button type="button" id="toggleRegPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg id="eyeIconReg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" required id="terms" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800">
                        <label for="terms" class="text-xs text-gray-600 dark:text-gray-400">I agree to the <a href="#" class="text-brand-600 hover:underline font-medium">Terms of Service</a> and <a href="#" class="text-brand-600 hover:underline font-medium">Privacy Policy</a>.</label>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors shadow-lg shadow-brand-600/30 text-sm tracking-wide">
                        Sign Up
                    </button>

                    <div class="text-center mt-6">
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Already have an account? <a href="login.php" class="text-brand-600 font-bold hover:underline dark:text-brand-500 ml-1">Sign In</a></p>
                    </div>
                </form>
            </div>
            
            <div class="text-center lg:text-left mt-auto pt-4">
                <p class="text-xs text-gray-400 dark:text-gray-500">&copy; 2025 B-Donor. All rights reserved.</p>
            </div>
        </div>

        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-brand-800 to-[#7f1d1d] relative items-center justify-center overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <svg class="absolute top-10 left-10 w-32 h-32 text-white transform -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
            </div>
            <div class="relative z-10 text-center px-12 max-w-lg">
                <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white drop-shadow-md"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/></svg>
                </div>
                <h2 class="text-4xl font-extrabold text-white mb-4 leading-tight">Give Blood, <br> Save Lives</h2>
                <p class="text-red-100 text-base font-light">Join our community of heroes. Your donation can make a difference in someone's life today.</p>
            </div>
            
            <div class="absolute -bottom-10 -right-10 text-white opacity-5">
                <svg width="300" height="300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
        </div>

        <button id="theme-toggle" class="fixed bottom-6 right-6 z-50 p-3 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition-all focus:outline-none">
            <svg id="theme-toggle-light-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
            <svg id="theme-toggle-dark-icon" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
        </button>
    </div>

    <script>
        // Password toggle
        const toggleRegPass = document.getElementById('toggleRegPass');
        const regPassword = document.getElementById('reg_password');
        const eyeIconReg = document.getElementById('eyeIconReg');

        toggleRegPass.addEventListener('click', () => {
            const type = regPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            regPassword.setAttribute('type', type);
            if(type === 'text') {
                eyeIconReg.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            } else {
                eyeIconReg.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
            }
        });

        // Theme Toggle Logic
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
