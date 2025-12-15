<?php
// home.php - Dashboard (Hiển thị biểu đồ theo 5 tuần của tháng trước)
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

// --- 1. DATA: REGISTRATION TRENDS (LAST MONTH - 5 WEEKS) ---
// Xác định tháng trước
$prevMonthTimestamp = strtotime("first day of last month"); 
$targetMonth = date('m', $prevMonthTimestamp);
$targetYear = date('Y', $prevMonthTimestamp);
$monthLabel = date('F Y', $prevMonthTimestamp); // Ví dụ: November 2025
$daysInMonth = date('t', $prevMonthTimestamp); // Số ngày trong tháng (28, 30, 31)

// Khởi tạo mảng 5 tuần
$weeksData = [
    1 => ['label' => 'Week 1', 'val' => 0],
    2 => ['label' => 'Week 2', 'val' => 0],
    3 => ['label' => 'Week 3', 'val' => 0],
    4 => ['label' => 'Week 4', 'val' => 0],
    5 => ['label' => 'Week 5', 'val' => 0],
];

// Lấy dữ liệu Donor đăng ký trong tháng đó, nhóm theo Ngày
$sql_reg = "SELECT DAY(createdAt) as day_num, COUNT(*) as cnt 
            FROM users 
            WHERE role = 'Donor' 
            AND MONTH(createdAt) = '$targetMonth' 
            AND YEAR(createdAt) = '$targetYear'
            GROUP BY day_num";
$res_reg = mysqli_query($link, $sql_reg);
$dailyData = [];
if ($res_reg) {
    while($row = mysqli_fetch_assoc($res_reg)) {
        $dailyData[$row['day_num']] = $row['cnt'];
    }
}

// Logic phân bổ Ngày vào Tuần (Mon-Sun)
$currentWeek = 1;
for ($day = 1; $day <= $daysInMonth; $day++) {
    // Tạo timestamp cho ngày hiện tại đang xét
    $dateTs = mktime(0, 0, 0, $targetMonth, $day, $targetYear);
    $isMonday = (date('N', $dateTs) == 1); // 1 = Monday

    // Nếu gặp Thứ 2 (và không phải ngày 1), nhảy sang tuần tiếp theo
    if ($isMonday && $day > 1) {
        $currentWeek++;
    }

    // Cộng dồn số liệu vào tuần tương ứng (Gộp hết tuần 6+ vào tuần 5)
    $weekIndex = ($currentWeek > 5) ? 5 : $currentWeek;
    if (isset($dailyData[$day])) {
        $weeksData[$weekIndex]['val'] += $dailyData[$day];
    }
}

// Tìm giá trị lớn nhất để vẽ biểu đồ (Tránh chia cho 0)
$max_reg = 0;
foreach ($weeksData as $w) {
    if ($w['val'] > $max_reg) $max_reg = $w['val'];
}
if ($max_reg == 0) $max_reg = 10;

// --- 2. DATA: INVENTORY STATUS ---
$blood_counts = [];
$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$max_inv = 0;

$inv_query = "SELECT blood_group, quantity FROM blood_inventory";
$inv_result = mysqli_query($link, $inv_query);
$db_data = [];
if ($inv_result) {
    while($row = mysqli_fetch_assoc($inv_result)) {
        $db_data[$row['blood_group']] = $row['quantity'];
    }
}

foreach ($groups as $g) {
    $qty = $db_data[$g] ?? 0; 
    $blood_counts[] = ['type' => $g, 'val' => $qty];
    if ($qty > $max_inv) $max_inv = $qty;
}
if ($max_inv == 0) $max_inv = 50;

// --- 3. DATA: RECENT APPOINTMENTS ---
$appt_query = "SELECT * FROM appointments ORDER BY appointmentDate DESC LIMIT 5";
$appt_result = mysqli_query($link, $appt_query);
$appointments = [];
if ($appt_result) {
    while ($row = mysqli_fetch_assoc($appt_result)) {
        $appointments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - B-DONOR</title>
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
                        'primary-hover': '#991B1B',
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
                <a href="home.php" class="flex items-center gap-3 px-4 py-3 bg-primary text-white rounded-lg shadow-md shadow-red-200 dark:shadow-none transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span class="font-semibold">Dashboard</span>
                </a>
                <a href="appointments.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span class="font-medium">Appointment</span>
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
            <header class="h-20 bg-[#B91C1C] dark:bg-gray-800 flex items-center justify-between px-8 shadow-sm shrink-0 z-30 relative">
                <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard</h1>
                <div class="flex items-center gap-6">
                    <div class="relative hidden md:block">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                        <input type="text" placeholder="Search here..." class="w-80 pl-10 pr-4 py-2.5 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400 placeholder-gray-400 shadow-sm">
                    </div>
                    <button class="relative p-2 bg-[#A01818] rounded-full text-white hover:bg-red-800 transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="absolute top-2 right-2.5 w-2 h-2 bg-yellow-400 rounded-full border border-[#B91C1C]"></span>
                    </button>
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-3 pl-4 border-l border-red-800/50 focus:outline-none group">
                            <div class="text-right hidden md:block"><div class="text-sm font-bold text-white leading-tight"><?php echo htmlspecialchars($current_username); ?></div><div class="text-xs text-red-200 font-medium"><?php echo $user_sub_role; ?></div></div>
                            <img src="<?php echo $current_avatar; ?>" class="w-10 h-10 rounded-full border-2 border-white/20 bg-white object-cover">
                            <svg id="user-menu-arrow" class="text-red-200 group-hover:text-white transition-transform duration-200" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-3 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 border border-gray-100 dark:border-gray-700 origin-top-right transform transition-all duration-200">
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-gray-700 hover:text-red-600 transition-colors"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Sign Out</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 bg-[#F3F4F6] dark:bg-dark">
                <div class="grid grid-cols-12 gap-6 mb-8">
                    <div class="col-span-12 xl:col-span-8 bg-white dark:bg-gray-dark rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-8">
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                                Registration Trends <span class="text-sm font-normal text-gray-500 ml-1">(<?php echo $monthLabel; ?>)</span>
                            </h2>
                            <span class="text-xs font-semibold bg-red-50 text-red-600 px-2 py-1 rounded border border-red-100">Monthly View</span>
                        </div>

                        <div class="h-64 flex items-end justify-between gap-4 px-4 relative">
                            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8 w-full">
                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 h-0 w-full"></div>
                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 h-0 w-full"></div>
                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 h-0 w-full"></div>
                                <div class="border-t border-dashed border-gray-300 dark:border-gray-600 h-0 w-full"></div>
                                <div class="border-b border-gray-300 dark:border-gray-600 h-0 w-full"></div>
                            </div>
                            
                            <?php foreach($weeksData as $week): 
                                $percent = ($week['val'] / $max_reg) * 100;
                                $height = $percent > 0 ? $percent . '%' : '4px'; // 4px để hiện vạch nhỏ nếu 0
                                $color = $week['val'] > 0 ? 'bg-[#CD554D] hover:bg-[#B91C1C]' : 'bg-gray-200 dark:bg-gray-700';
                            ?>
                            <div class="flex flex-col items-center flex-1 z-10 group relative h-full justify-end">
                                <div class="absolute -top-10 mb-1 hidden group-hover:block bg-black/80 text-white text-xs font-bold px-2 py-1 rounded shadow-lg z-50 transition-all">
                                    <?php echo $week['val']; ?> Donors
                                </div>
                                
                                <div class="w-12 md:w-16 <?php echo $color; ?> rounded-t-sm transition-all duration-500 ease-out relative" style="height: <?php echo $height; ?>">
                                    <?php if($week['val'] > 0): ?>
                                        <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-bold text-gray-700 dark:text-gray-300"><?php echo $week['val']; ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-4 text-xs font-semibold text-gray-500 dark:text-gray-400"><?php echo $week['label']; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-span-12 xl:col-span-4 bg-white dark:bg-gray-dark rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Inventory Status</h2>
                        <div class="flex-1 flex items-end justify-between px-2 pb-2 h-64 gap-2">
                            <?php foreach($blood_counts as $bc): 
                                $percent = ($bc['val'] / $max_inv) * 100;
                                $height = $percent > 0 ? $percent . '%' : '1px';
                            ?>
                            <div class="flex flex-col items-center w-full gap-2 group relative h-full justify-end">
                                <div class="absolute -top-8 mb-1 text-xs font-bold text-gray-700 dark:text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white dark:bg-gray-700 px-2 py-1 rounded shadow z-20"><?php echo $bc['val']; ?></div>
                                <div class="w-full bg-red-100 dark:bg-red-900/30 rounded-t-sm relative overflow-hidden group-hover:bg-red-200 transition-colors" style="height: 100%">
                                    <div class="absolute bottom-0 left-0 w-full bg-[#B91C1C] transition-all duration-500" style="height: <?php echo $height; ?>"></div>
                                </div>
                                <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400"><?php echo $bc['type']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center text-xs text-gray-400 mt-4 italic">Real-time inventory levels</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-dark rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">Recent Appointments</h2>
                        <a href="appointments.php" class="text-sm font-semibold text-red-600 hover:text-red-700">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">No.</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">Donor Name</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">Phone</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">Blood Type</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-8 py-4 text-xs font-semibold text-gray-500 uppercase text-right">Details</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php if(count($appointments) > 0): ?>
                                    <?php foreach($appointments as $index => $apt): 
                                        $statusClass = match($apt['status']) {
                                            'Confirmed' => 'bg-green-50 text-green-700 border border-green-200',
                                            'Pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                                            'Cancelled' => 'bg-red-50 text-red-700 border border-red-200',
                                            default => 'bg-gray-50 text-gray-600'
                                        };
                                    ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="px-8 py-5 text-sm text-gray-500"><?php echo str_pad($index + 1, 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="px-8 py-5 text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($apt['name']); ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-500"><?php echo htmlspecialchars($apt['phone']); ?></td>
                                        <td class="px-8 py-5 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($apt['appointmentDate'])); ?></td>
                                        <td class="px-8 py-5 text-sm font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($apt['bloodType'] ?? 'N/A'); ?></td>
                                        <td class="px-8 py-5"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                        <td class="px-8 py-5 text-right"><button class="p-1.5 border border-gray-200 rounded text-gray-400 hover:text-gray-600 dark:border-gray-600 dark:hover:text-white"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg></button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="px-8 py-10 text-center text-gray-500 italic">No appointments found.</td></tr>
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
        const userBtn = document.getElementById('user-menu-btn'); const userDropdown = document.getElementById('user-menu-dropdown'); const userArrow = document.getElementById('user-menu-arrow');
        userBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('hidden'); userArrow.classList.toggle('rotate-180'); });
        document.addEventListener('click', (e) => { if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) { userDropdown.classList.add('hidden'); userArrow.classList.remove('rotate-180'); } });
        const btn = document.getElementById('theme-toggle'); const moon = document.getElementById('moon-icon'); const sun = document.getElementById('sun-icon'); const html = document.documentElement;
        function updateIcon() { if (html.classList.contains('dark')) { moon.classList.add('hidden'); sun.classList.remove('hidden'); } else { moon.classList.remove('hidden'); sun.classList.add('hidden'); } }
        updateIcon();
        btn.addEventListener('click', () => { if (html.classList.contains('dark')) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); } else { html.classList.add('dark'); localStorage.setItem('theme', 'dark'); } updateIcon(); });
    </script>
</body>
</html>
