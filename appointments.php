<?php
include 'config.php';
include 'connection.php';

// Chỉ cho phép Admin (hoặc Admin và Manager)
requireRole('Admin');

// Lấy thông tin User
$current_username = $_SESSION['full_name'] ?? $_SESSION['username'];
$current_avatar = "https://ui-avatars.com/api/?name=" . urlencode($current_username) . "&background=random&color=fff";
$user_role = $_SESSION['role'] ?? 'Admin User'; 
$user_sub_role = 'Super Admin';

// --- XỬ LÝ SEARCH & FILTER ---
$search = $_GET['search'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';

// --- SQL QUERY ---
$sql = "SELECT * FROM appointments WHERE 1=1";

// 1. Tìm kiếm (Tên hoặc SĐT)
if (!empty($search)) {
    $search_e = mysqli_real_escape_string($link, $search);
    $sql .= " AND (name LIKE '%$search_e%' OR phone LIKE '%$search_e%')";
}

// 2. Lọc Nhóm máu
if (!empty($filter_type)) {
    $type_e = mysqli_real_escape_string($link, $filter_type);
    $sql .= " AND bloodType = '$type_e'";
}

// 3. Lọc Trạng thái
if (!empty($filter_status)) {
    $status_e = mysqli_real_escape_string($link, $filter_status);
    $sql .= " AND status = '$status_e'";
}

$sql .= " ORDER BY appointmentDate DESC";

$result = mysqli_query($link, $sql);
$appointments = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment List - B-DONOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: '#B91C1C', // Đỏ Header
                        secondary: '#CD554D', // Đỏ Filter Bar
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
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
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
                
                <a href="appointments.php" class="flex items-center gap-3 px-4 py-3 bg-primary text-white rounded-lg shadow-md shadow-red-200 dark:shadow-none transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="font-semibold">Appointment</span>
                </a>

                <a href="bloodinvent.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>
                    <span class="font-medium">Blood inventory</span>
                </a>
                <a href="user-management.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="font-medium">User management</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            
            <header class="h-20 bg-[#B91C1C] dark:bg-gray-800 flex items-center justify-between px-8 shadow-sm shrink-0 z-30">
                <h1 class="text-2xl font-bold text-white tracking-tight">Appointment List</h1>

                <div class="flex items-center gap-6">
                    <div class="relative hidden md:block">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" placeholder="Search here..." class="w-80 pl-10 pr-4 py-2.5 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400 placeholder-gray-400 shadow-sm">
                    </div>

                    <button class="relative p-2 bg-[#A01818] rounded-full text-white hover:bg-red-800 transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="absolute top-2 right-2.5 w-2 h-2 bg-yellow-400 rounded-full border border-[#B91C1C]"></span>
                    </button>

                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-3 pl-4 border-l border-red-800/50 focus:outline-none group">
                            <div class="text-right hidden md:block">
                                <div class="text-sm font-bold text-white leading-tight"><?php echo htmlspecialchars($current_username); ?></div>
                                <div class="text-xs text-red-200 font-medium"><?php echo $user_sub_role; ?></div>
                            </div>
                            <img src="<?php echo $current_avatar; ?>" class="w-10 h-10 rounded-full border-2 border-white/20 bg-white object-cover">
                            <svg id="user-menu-arrow" class="text-red-200 group-hover:text-white transition-transform duration-200" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        
                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 border border-gray-100 dark:border-gray-700 origin-top-right transition-all">
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-gray-700 hover:text-red-600 transition-colors">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 bg-[#F3F4F6] dark:bg-dark">
                
                <div class="bg-[#CD554D] dark:bg-gray-700 rounded-xl p-4 mb-8 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                    <form method="GET" class="w-full md:w-auto flex-1 max-w-lg">
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name or phone..." 
                                   class="w-full rounded-lg bg-white border-none py-3 pl-12 pr-4 text-sm text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-red-300 outline-none shadow-sm">
                        </div>
                        <input type="submit" hidden />
                    </form>

                    <form method="GET" class="flex items-center gap-3 w-full md:w-auto">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        
                        <select name="type" onchange="this.form.submit()" class="rounded-lg bg-white border-none py-3 px-4 text-sm text-gray-700 focus:ring-2 focus:ring-red-300 outline-none shadow-sm cursor-pointer min-w-[120px]">
                            <option value="">All Types</option>
                            <?php foreach(['A', 'B', 'AB', 'O'] as $t) {
                                $selected = ($filter_type == $t) ? 'selected' : '';
                                echo "<option value='$t' $selected>$t</option>";
                            } ?>
                        </select>

                        <select name="status" onchange="this.form.submit()" class="rounded-lg bg-white border-none py-3 px-4 text-sm text-gray-700 focus:ring-2 focus:ring-red-300 outline-none shadow-sm cursor-pointer min-w-[140px]">
                            <option value="">All Status</option>
                            <?php foreach(['Confirmed', 'Pending', 'Cancelled', 'Completed', 'ReadyToDonate', 'ReadyToCheck', 'Rejected'] as $s) {
                                $selected = ($filter_status == $s) ? 'selected' : '';
                                echo "<option value='$s' $selected>$s</option>";
                            } ?>
                        </select>

                        <a href="appointments.php" class="w-11 h-11 flex items-center justify-center rounded-lg bg-white text-[#CD554D] hover:bg-gray-50 shadow-sm transition">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                        </a>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">No.</th>
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">Donor Name</th>
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">Phone</th>
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Blood Type</th>
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                    <th class="py-5 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                <?php if (count($appointments) > 0): ?>
                                    <?php foreach ($appointments as $index => $apt): 
                                        $statusClass = match($apt['status']) {
                                            'Confirmed' => 'bg-[#67C3C8] text-white',
                                            'Pending' => 'bg-[#F2C94C] text-white',
                                            'Cancelled' => 'bg-[#EB5757] text-white',
                                            'Completed' => 'bg-[#27AE60] text-white',
                                            'ReadyToDonate' => 'bg-[#6FCF97] text-white',
                                            'ReadyToCheck' => 'bg-[#9CA3AF] text-white',
                                            'Rejected' => 'bg-[#991B1B] text-white',
                                            default => 'bg-gray-200 text-gray-600',
                                        };
                                        $dateObj = new DateTime($apt['appointmentDate']);
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                                        <td class="py-5 px-6 text-sm text-gray-400 font-medium">#<?php echo $index + 1; ?></td>
                                        
                                        <td class="py-5 px-6">
                                            <div>
                                                <div class="text-sm font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($apt['name']); ?></div>
                                                <div class="text-xs text-gray-400 mt-0.5"><?php echo htmlspecialchars($apt['email']); ?></div>
                                            </div>
                                        </td>

                                        <td class="py-5 px-6 text-sm text-gray-600 dark:text-gray-300 font-medium">
                                            <?php echo htmlspecialchars($apt['phone']); ?>
                                        </td>

                                        <td class="py-5 px-6 text-center">
                                            <span class="text-sm font-bold text-[#B91C1C] dark:text-red-400">
                                                <?php echo htmlspecialchars($apt['bloodType'] ?? 'N/A'); ?>
                                            </span>
                                        </td>

                                        <td class="py-5 px-6">
                                            <div>
                                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo $dateObj->format('d/m/Y'); ?></div>
                                                <div class="text-xs text-gray-400 mt-0.5"><?php echo $dateObj->format('H:i'); ?></div>
                                            </div>
                                        </td>

                                        <td class="py-5 px-6 text-right">
                                            <span class="inline-block w-[120px] py-2 text-center text-xs font-semibold rounded-md shadow-sm <?php echo $statusClass; ?>">
                                                <?php echo $apt['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-500">No appointments found.</td>
                                    </tr>
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

    <script>
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

        const btn = document.getElementById('theme-toggle');
        const moon = document.getElementById('moon-icon');
        const sun = document.getElementById('sun-icon');
        const html = document.documentElement;

        function updateIcon() {
            if (html.classList.contains('dark')) {
                moon.classList.add('hidden');
                sun.classList.remove('hidden');
            } else {
                moon.classList.remove('hidden');
                sun.classList.add('hidden');
            }
        }
        updateIcon();

        btn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcon();
        });
    </script>
</body>
</html>
