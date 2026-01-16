<?php
// doctor-home.php - Dashboard Bác sĩ (Full tính năng: Giờ VN + Nút Edit góc trái)
include 'config.php';
include 'connection.php';

// 1. Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Chặn truy cập nếu không phải Doctor
requireRole('Doctor');

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Dr. User';
$role = $_SESSION['role'] ?? 'Specialist Doctor';
$current_month = date('F, Y');

// --- XỬ LÝ CẬP NHẬT PROFILE (PHP LOGIC) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $new_name = mysqli_real_escape_string($link, $_POST['full_name']);
    $new_password = $_POST['password'];
    
    // Câu lệnh SQL cơ bản: Cập nhật tên
    $sql_update = "UPDATE users SET name = '$new_name'";
    
    // Nếu người dùng nhập mật khẩu mới thì cập nhật thêm password
    if (!empty($new_password)) {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update .= ", password_hash = '$new_hash'";
    }
    
    $sql_update .= " WHERE id = $user_id";
    
    if (mysqli_query($link, $sql_update)) {
        // Cập nhật lại Session và biến hiển thị ngay lập tức
        $_SESSION['full_name'] = $new_name;
        $full_name = $new_name; 
        echo "<script>alert('Profile updated successfully!'); window.location.href='doctor-home.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . mysqli_error($link) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard - B-DONOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: '#DC2626',
                        'brand-dark': '#B91C1C',
                        'brand-light': '#FEE2E2',
                        'nav-active': '#FEE2E2',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col relative">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-4">
                <div class="flex-shrink-0">
                    <a href="doctor-home.php" class="text-2xl font-bold text-brand uppercase tracking-wide">B-DONOR</a>
                </div>

                <div class="hidden md:flex flex-1 max-w-lg mx-auto">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></span>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-brand focus:border-brand sm:text-sm" placeholder="Search for patients...">
                    </div>
                </div>

                <div class="flex items-center gap-4 text-gray-400">
                    <span class="text-sm font-medium text-gray-500">EN</span>
                    
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-sm">
                                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($full_name); ?></span>
                            <svg id="user-menu-arrow" class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        
                        <div id="user-menu-dropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-white rounded-md shadow-lg py-1 border border-gray-100 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($full_name); ?></p>
                                <p class="text-xs text-gray-500">Doctor</p>
                            </div>
                            <button onclick="openProfileModal()" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit Profile
                            </button>
                            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="bg-brand text-white shadow-md sticky top-16 z-30">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-5 text-center">
                <a href="doctor-home.php" class="py-4 bg-brand-light border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Home</span>
                </a>
                <a href="doctor-regis-confirm.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-medium">Confirmation</span>
                </a>
                <a href="doctor-health-check.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                    <span class="text-sm font-medium">Health Check</span>
                </a>
                <a href="doctor-record-donation.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-medium">Record Donation</span>
                </a>
                <a href="doctor-work-schedule.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-medium">Work Schedule</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Good Morning, <?php echo htmlspecialchars($full_name); ?>! 👋</h2>
                <p class="text-gray-500 text-sm mt-1">Ready to save lives today?</p>
            </div>
            <div class="text-right">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Today (Vietnam)</div>
                <div class="flex items-center gap-2 text-gray-700 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="font-bold"><?php echo date('l, d F Y'); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            My Work Schedule
                        </h3>
                        <span class="text-sm font-medium text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo $current_month; ?></span>
                    </div>

                    <div class="grid grid-cols-7 gap-2 mb-6">
                        <?php 
                        // Logic lịch tự động
                        $today = new DateTime(); // Đã theo múi giờ VN do set ở đầu file
                        $currentDate = $today->format('Y-m-d'); 
                        
                        $startOfWeek = clone $today;
                        // Nếu hôm nay không phải Thứ 2 (1), lùi về Thứ 2 gần nhất
                        if ($today->format('N') != 1) $startOfWeek->modify('last monday');

                        for ($i = 0; $i < 7; $i++) {
                            $date = clone $startOfWeek;
                            $date->modify("+$i days");
                            $thisDate = $date->format('Y-m-d');
                            $dayName = $date->format('D'); 
                            $dayNumber = $date->format('d'); 
                            $isToday = ($thisDate === $currentDate);
                            
                            if ($isToday) {
                                $boxClass = 'bg-red-50 border border-red-200 shadow-sm';
                                $textClass = 'text-brand';
                                $numClass = 'text-brand';
                            } else {
                                $boxClass = 'hover:bg-gray-50 cursor-pointer border border-transparent';
                                $textClass = 'text-gray-400';
                                $numClass = 'text-gray-700';
                            }
                        ?>
                            <div class="text-center p-2 rounded-lg transition-all <?php echo $boxClass; ?>">
                                <div class="text-xs font-medium uppercase mb-1 <?php echo $textClass; ?>">
                                    <?php echo $dayName; ?>
                                </div>
                                <div class="text-lg font-bold <?php echo $numClass; ?>">
                                    <?php echo $dayNumber; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 flex items-start gap-4">
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm">Upcoming Shift: Health Screening</h4>
                            <p class="text-xs text-gray-500 mt-1">Room 302, Main Building • 14:00 - 18:00</p>
                            <div class="mt-3 flex gap-2">
                                <span class="text-[10px] font-bold bg-white px-2 py-1 rounded border border-blue-100 text-blue-600">CONFIRMED</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center justify-between">
                        <span>Notifications</span>
                        <span class="bg-red-100 text-brand text-xs px-2 py-0.5 rounded-full">3 New</span>
                    </h3>
                    <div class="space-y-4">
                        <div class="flex gap-3 items-start pb-4 border-b border-gray-100">
                            <div class="w-2 h-2 mt-2 bg-brand rounded-full flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">Emergency Request: O- Blood needed</p>
                                <p class="text-xs text-gray-400 mt-1">10 mins ago • Central Hospital</p>
                            </div>
                        </div>
                        <div class="flex gap-3 items-start pb-4 border-b border-gray-100">
                            <div class="w-2 h-2 mt-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">New Donor Registration: Nguyen Van A</p>
                                <p class="text-xs text-gray-400 mt-1">1 hour ago • Waiting for approval</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <button onclick="openProfileModal()" class="fixed bottom-6 left-6 z-40 flex items-center gap-2 bg-gray-900 text-white px-5 py-3 rounded-full shadow-2xl hover:bg-gray-800 transition transform hover:scale-105">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
        <span class="font-bold text-sm">Edit Info</span>
    </button>

    <div id="profileModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="profileModalContent">
            <div class="flex justify-between items-center px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">Edit Profile</h3>
                <button onclick="closeProfileModal()" class="text-gray-400 hover:text-gray-600 bg-gray-50 p-1.5 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form method="POST" class="p-6 space-y-5">
                <input type="hidden" name="action" value="update_profile">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required 
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-gray-900 focus:ring-2 focus:ring-brand focus:border-brand outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password <span class="text-gray-400 font-normal text-xs">(Leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="••••••••" 
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-gray-900 focus:ring-2 focus:ring-brand focus:border-brand outline-none transition">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeProfileModal()" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 font-semibold hover:bg-gray-50 transition text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand hover:bg-brand-dark text-white font-semibold shadow-lg shadow-red-200 transition text-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // -- User Dropdown Logic --
        const userBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-menu-dropdown');
        const userArrow = document.getElementById('user-menu-arrow');

        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
            userArrow.classList.toggle('rotate-180');
        });

        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
                userArrow.classList.remove('rotate-180');
            }
        });

        // -- Profile Modal Logic --
        const profileModal = document.getElementById('profileModal');
        const profileContent = document.getElementById('profileModalContent');

        function openProfileModal() {
            // Đóng dropdown menu nếu đang mở
            userDropdown.classList.add('hidden');
            userArrow.classList.remove('rotate-180');
            
            profileModal.classList.remove('hidden');
            // Animation fade-in
            setTimeout(() => {
                profileContent.classList.remove('opacity-0', 'scale-95');
                profileContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeProfileModal() {
            // Animation fade-out
            profileContent.classList.remove('opacity-100', 'scale-100');
            profileContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                profileModal.classList.add('hidden');
            }, 200);
        }
    </script>
</body>
</html>
