<?php
// user-management.php - Quản lý User (Full Logic: Add, Edit, Delete, Soft Delete)
include 'config.php';
include 'connection.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_username = $_SESSION['full_name'] ?? $_SESSION['username'];
$current_avatar = "https://ui-avatars.com/api/?name=" . urlencode($current_username) . "&background=random&color=fff";
$user_role = $_SESSION['role'] ?? 'Admin User';
$user_sub_role = 'Super Admin';

// --- 1. XỬ LÝ LOGIC BACKEND ---

// A. Xử lý THÊM User Mới (ADD NEW)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $add_name = mysqli_real_escape_string($link, $_POST['full_name']);
    $add_username = mysqli_real_escape_string($link, $_POST['username']);
    $add_email = mysqli_real_escape_string($link, $_POST['email']);
    $add_role = mysqli_real_escape_string($link, $_POST['role']);
    $add_pass = $_POST['password'];

    // 1. Kiểm tra trùng lặp
    $check_query = "SELECT id FROM users WHERE username = '$add_username' OR email = '$add_email'";
    $check_result = mysqli_query($link, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Error: Username or Email already exists!'); window.location.href='user-management.php';</script>";
    } else {
        // 2. Mã hóa mật khẩu (MD5 cho đồng bộ với data mẫu)
        $pass_hash = md5($add_pass);
        $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($add_name) . "&background=random&color=fff";

        // 3. Insert vào bảng users
        $sql_insert = "INSERT INTO users (username, password_hash, name, email, role, avatarUrl, status, createdAt) 
                       VALUES ('$add_username', '$pass_hash', '$add_name', '$add_email', '$add_role', '$avatar_url', 'active', NOW())";
        
        if (mysqli_query($link, $sql_insert)) {
            $new_user_id = mysqli_insert_id($link);

            // 4. Nếu là Donor, tạo luôn profile rỗng để tránh lỗi foreign key sau này
            if ($add_role == 'Donor') {
                mysqli_query($link, "INSERT INTO donor_profiles (userId) VALUES ($new_user_id)");
            }

            echo "<script>alert('New user added successfully!'); window.location.href='user-management.php';</script>";
        } else {
            echo "<script>alert('Database Error: " . mysqli_error($link) . "');</script>";
        }
    }
}

// B. Xử lý CẬP NHẬT User (UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $edit_id = intval($_POST['user_id']);
    $edit_name = mysqli_real_escape_string($link, $_POST['full_name']);
    $edit_username = mysqli_real_escape_string($link, $_POST['username']);
    $edit_role = mysqli_real_escape_string($link, $_POST['role']);
    $edit_pass = $_POST['password'];

    $sql_update = "UPDATE users SET name = '$edit_name', username = '$edit_username', role = '$edit_role'";

    if (!empty($edit_pass)) {
        $pass_hash = md5($edit_pass); 
        $sql_update .= ", password_hash = '$pass_hash'";
    }

    $sql_update .= " WHERE id = $edit_id";

    if (mysqli_query($link, $sql_update)) {
        echo "<script>alert('User updated successfully!'); window.location.href='user-management.php';</script>";
    } else {
        echo "<script>alert('Error updating user: " . mysqli_error($link) . "');</script>";
    }
}

// C. Xử lý XÓA User (SOFT DELETE)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($delete_id != $_SESSION['user_id']) {
        // Soft delete: Chỉ đánh dấu là đã xóa (is_deleted = 1)
        // Cần đảm bảo bảng users có cột is_deleted (INT 1, Default 0)
        // Nếu chưa có cột này, bạn có thể dùng DELETE cứng: "DELETE FROM users WHERE id = $delete_id"
        
        // Kiểm tra xem cột is_deleted có tồn tại không, nếu không thì DELETE cứng
        $check_col = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'is_deleted'");
        if(mysqli_num_rows($check_col) > 0) {
            $sql_del = "UPDATE users SET is_deleted = 1 WHERE id = $delete_id";
        } else {
            // Xóa dữ liệu bảng phụ trước khi xóa cứng
            mysqli_query($link, "DELETE FROM donor_profiles WHERE userId = $delete_id");
            mysqli_query($link, "DELETE FROM appointments WHERE userId = $delete_id");
            $sql_del = "DELETE FROM users WHERE id = $delete_id";
        }

        if (mysqli_query($link, $sql_del)) {
            echo "<script>alert('User deleted successfully!'); window.location.href='user-management.php';</script>";
        } else {
            echo "<script>alert('Error deleting user: " . mysqli_error($link) . "');</script>";
        }
    } else {
        echo "<script>alert('You cannot delete your own account!'); window.location.href='user-management.php';</script>";
    }
}

// --- 2. LẤY DỮ LIỆU HIỂN THỊ ---
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE 1=1";

// Nếu có cột is_deleted thì chỉ lấy user chưa xóa
$check_col = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'is_deleted'");
if(mysqli_num_rows($check_col) > 0) {
    $sql .= " AND is_deleted = 0";
}

if (!empty($search)) {
    $search_e = mysqli_real_escape_string($link, $search);
    $sql .= " AND (username LIKE '%$search_e%' OR name LIKE '%$search_e%' OR role LIKE '%$search_e%')";
}
$sql .= " ORDER BY id ASC";
$result = mysqli_query($link, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - B-DONOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: '#B91C1C',
                        sidebar: '#FFFFFF',
                        dark: '#1a2231',
                        'gray-dark': '#24303f',
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
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="bg-[#F3F4F6] dark:bg-dark text-gray-800 dark:text-gray-200 font-sans transition-colors duration-300">
    
    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-white dark:bg-gray-dark border-r border-gray-200 dark:border-gray-700 flex flex-col z-20 shadow-sm hidden lg:flex">
            <div class="h-20 flex items-center px-8 border-b border-transparent">
                <a href="home.php" class="flex items-center gap-3">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                        <path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>
                    </svg>
                    <span class="text-2xl font-extrabold text-[#B91C1C] dark:text-red-500 uppercase tracking-wide">B-DONOR</span>
                </a>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="home.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="appointments.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="font-medium">Appointment</span>
                </a>
                <a href="bloodinvent.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>
                    <span class="font-medium">Blood inventory</span>
                </a>
                <a href="user-management.php" class="flex items-center gap-3 px-4 py-3 bg-primary text-white rounded-lg shadow-md shadow-red-200 dark:shadow-none transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="font-semibold">User management</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-20 bg-[#B91C1C] dark:bg-gray-800 flex items-center justify-between px-8 shadow-sm shrink-0 z-30">
                <h1 class="text-2xl font-bold text-white tracking-tight">User Management</h1>
                <div class="flex items-center gap-6">
                    <div class="relative hidden md:block">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                        <input type="text" placeholder="Search here..." class="w-80 pl-10 pr-4 py-2.5 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                    <button class="relative p-2 bg-[#A01818] rounded-full text-white hover:bg-red-800 transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="absolute top-2 right-2.5 w-2 h-2 bg-yellow-400 rounded-full border border-[#B91C1C]"></span>
                    </button>
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-3 pl-4 border-l border-red-800/50 focus:outline-none group">
                            <div class="text-right hidden md:block"><div class="text-sm font-bold text-white leading-tight"><?php echo htmlspecialchars($current_username); ?></div><div class="text-xs text-red-200 font-medium"><?php echo $user_sub_role; ?></div></div>
                            <img src="<?php echo $current_avatar; ?>" class="w-10 h-10 rounded-full border-2 border-white/20 bg-white object-cover">
                            <svg id="user-menu-arrow" class="text-red-200 group-hover:text-white transition-transform duration-200" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 border border-gray-100 dark:border-gray-700">
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-gray-700 hover:text-red-600 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 bg-[#F3F4F6] dark:bg-dark">
                
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">User Management</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage Doctors, Donors, and Admins accounts.</p>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <form method="GET" class="w-full md:w-96 relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, username or role..." class="w-full h-11 rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm text-gray-700 outline-none focus:border-[#B91C1C] focus:ring-1 focus:ring-[#B91C1C] transition shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                        <button type="submit" hidden></button>
                    </form>
                    <div class="flex items-center gap-3">
                        <a href="user-management.php" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white transition" title="Refresh List"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path><path d="M3 22v-6h6"></path><path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path></svg></a>
                        <button onclick="openAddModal()" class="flex items-center gap-2 bg-[#B91C1C] hover:bg-[#991B1B] text-white px-5 py-2.5 rounded-lg font-semibold shadow-sm transition text-sm">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg> Add User
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-dark rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">No.</th>
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Username</th>
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Full Name</th>
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Role</th>
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase">Created At</th>
                                    <th class="py-4 px-6 text-xs font-bold text-gray-500 uppercase text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $index => $u): 
                                        $roleClass = match($u['role']) {
                                            'Admin' => 'bg-purple-100 text-purple-700',
                                            'Doctor' => 'bg-blue-100 text-blue-700',
                                            'Donor' => 'bg-green-100 text-green-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                        $created = $u['createdAt'] ? date('d/m/Y', strtotime($u['createdAt'])) : '—';
                                        $name = $u['name'] ? htmlspecialchars($u['name']) : '—';
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="py-5 px-6 text-sm text-gray-500 font-medium"><?php echo $index + 1; ?>.</td>
                                        <td class="py-5 px-6 text-sm text-gray-700 dark:text-gray-300 font-medium"><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td class="py-5 px-6 text-sm text-gray-700 dark:text-gray-300"><?php echo $name; ?></td>
                                        <td class="py-5 px-6"><span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold <?php echo $roleClass; ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
                                        <td class="py-5 px-6 text-sm text-gray-500"><?php echo $created; ?></td>
                                        <td class="py-5 px-6 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button onclick='openEditModal(<?php echo json_encode($u); ?>)' class="text-blue-500 hover:bg-blue-50 p-1.5 rounded-full transition dark:hover:bg-gray-700" title="Edit"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                                <a href="user-management.php?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')" class="text-[#CF2222] hover:bg-red-50 p-1.5 rounded-full transition dark:hover:bg-gray-700" title="Delete"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="py-10 text-center text-gray-500">No users found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>

        <button id="theme-toggle" class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full bg-blue-600 text-white shadow-lg flex items-center justify-center hover:bg-blue-700 transition-transform hover:scale-110 focus:outline-none">
            <svg id="moon-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </button>
    </div>

    <div id="updateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-[#1a1f2e] w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
            <div class="flex justify-between items-center px-6 py-5 border-b border-gray-700">
                <h3 class="text-xl font-bold text-gray-200">Update User</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-white bg-gray-800 p-1.5 rounded-full"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" class="p-6 space-y-5">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" id="modal_user_id">
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Full Name</label><input type="text" name="full_name" id="modal_full_name" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" required></div>
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Username <span class="text-red-500">*</span></label><input type="text" name="username" id="modal_username" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" required></div>
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Role <span class="text-red-500">*</span></label><select name="role" id="modal_role" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none"><option value="Admin">Admin</option><option value="Doctor">Doctor</option><option value="Donor">Donor</option></select></div>
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Password (Leave blank to keep current)</label><input type="password" name="password" placeholder="********" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none"></div>
                <div class="flex justify-end gap-3 mt-8"><button type="button" onclick="closeEditModal()" class="px-5 py-2.5 rounded-lg bg-gray-700 text-gray-300 font-medium hover:bg-gray-600 transition">Cancel</button><button type="submit" class="px-5 py-2.5 rounded-lg bg-[#4F46E5] text-white font-medium hover:bg-[#4338CA] shadow-lg transition">Save Changes</button></div>
            </form>
        </div>
    </div>

    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-[#1a1f2e] w-full max-w-lg rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="addModalContent">
            <div class="flex justify-between items-center px-6 py-5 border-b border-gray-700">
                <h3 class="text-xl font-bold text-gray-200">Add New User</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-white bg-gray-800 p-1.5 rounded-full"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" class="p-6 space-y-5">
                <input type="hidden" name="action" value="add">
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Full Name</label><input type="text" name="full_name" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Username <span class="text-red-500">*</span></label><input type="text" name="username" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" required></div>
                    <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Email</label><input type="email" name="email" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Role <span class="text-red-500">*</span></label><select name="role" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none"><option value="Admin">Admin</option><option value="Doctor">Doctor</option><option value="Donor">Donor</option></select></div>
                <div><label class="block text-sm font-medium text-gray-400 mb-1.5">Password <span class="text-red-500">*</span></label><input type="password" name="password" class="w-full px-4 py-3 rounded-lg bg-white border border-gray-200 text-gray-900 focus:ring-2 focus:ring-blue-500 outline-none" required></div>
                <div class="flex justify-end gap-3 mt-8"><button type="button" onclick="closeAddModal()" class="px-5 py-2.5 rounded-lg bg-gray-700 text-gray-300 font-medium hover:bg-gray-600 transition">Cancel</button><button type="submit" class="px-5 py-2.5 rounded-lg bg-[#4F46E5] text-white font-medium hover:bg-[#4338CA] shadow-lg transition">Create User</button></div>
            </form>
        </div>
    </div>

    <script>
        const updateModal = document.getElementById('updateModal');
        const updateContent = document.getElementById('modalContent');
        const addModal = document.getElementById('addModal');
        const addContent = document.getElementById('addModalContent');

        function openEditModal(user) {
            document.getElementById('modal_user_id').value = user.id;
            document.getElementById('modal_full_name').value = user.name || '';
            document.getElementById('modal_username').value = user.username;
            document.getElementById('modal_role').value = user.role;
            updateModal.classList.remove('hidden');
            setTimeout(() => { updateContent.classList.remove('opacity-0', 'scale-95'); updateContent.classList.add('opacity-100', 'scale-100'); }, 10);
        }
        function closeEditModal() { updateContent.classList.remove('opacity-100', 'scale-100'); updateContent.classList.add('opacity-0', 'scale-95'); setTimeout(() => { updateModal.classList.add('hidden'); }, 200); }

        function openAddModal() {
            addModal.classList.remove('hidden');
            setTimeout(() => { addContent.classList.remove('opacity-0', 'scale-95'); addContent.classList.add('opacity-100', 'scale-100'); }, 10);
        }
        function closeAddModal() {
            addContent.classList.remove('opacity-100', 'scale-100'); addContent.classList.add('opacity-0', 'scale-95'); setTimeout(() => { addModal.classList.add('hidden'); }, 200);
        }

        const userBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-menu-dropdown');
        const userArrow = document.getElementById('user-menu-arrow');
        userBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('hidden'); userArrow.classList.toggle('rotate-180'); });
        document.addEventListener('click', (e) => { if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) { userDropdown.classList.add('hidden'); userArrow.classList.remove('rotate-180'); } });

        const btn = document.getElementById('theme-toggle'); const moon = document.getElementById('moon-icon'); const sun = document.getElementById('sun-icon'); const html = document.documentElement;
        function updateIcon() { if (html.classList.contains('dark')) { moon.classList.add('hidden'); sun.classList.remove('hidden'); } else { moon.classList.remove('hidden'); sun.classList.add('hidden'); } }
        updateIcon();
        btn.addEventListener('click', () => { if (html.classList.contains('dark')) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); } else { html.classList.add('dark'); localStorage.setItem('theme', 'dark'); } updateIcon(); });
    </script>
</body>
</html>
