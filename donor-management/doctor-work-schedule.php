<?php
// doctor-work-schedule.php - Lịch làm việc & Danh sách hẹn (Design theo image_fc3b64.png & image_fc3b62.png)
include 'config.php';
include 'connection.php';

session_start();

// Kiểm tra quyền Doctor
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$full_name = $_SESSION['full_name'] ?? 'Dr. Alice';

// --- XỬ LÝ THỜI GIAN ---
// Lấy tháng/năm/ngày từ URL hoặc mặc định hiện tại
$current_month = isset($_GET['m']) ? intval($_GET['m']) : date('n');
$current_year  = isset($_GET['y']) ? intval($_GET['y']) : date('Y');
$selected_day  = isset($_GET['d']) ? intval($_GET['d']) : date('j');

// Xử lý nút Next/Prev tháng
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

// Thông tin ngày đã chọn
$selected_date_str = sprintf('%04d-%02d-%02d', $current_year, $current_month, $selected_day);
$selected_date_display = date('l, d F Y', strtotime($selected_date_str));

// --- 1. LẤY DỮ LIỆU CHO LỊCH (Counts per day) ---
// Lấy tất cả lịch hẹn trong tháng này để hiển thị badge trên lịch
$month_start = sprintf('%04d-%02d-01', $current_year, $current_month);
$month_end   = date('Y-m-t', strtotime($month_start));

$calendar_data = [];
$sql_cal = "SELECT DAY(appointmentDate) as day_num, status, COUNT(*) as cnt 
            FROM appointments 
            WHERE DATE(appointmentDate) BETWEEN '$month_start' AND '$month_end' 
            GROUP BY day_num, status";
$res_cal = mysqli_query($link, $sql_cal);
while ($row = mysqli_fetch_assoc($res_cal)) {
    $d = $row['day_num'];
    if (!isset($calendar_data[$d])) {
        $calendar_data[$d] = ['pending' => 0, 'set' => 0];
    }
    
    if ($row['status'] == 'Pending') {
        $calendar_data[$d]['pending'] += $row['cnt'];
    } else {
        // Confirmed, Completed, ReadyToDonate... coi như là "Set"
        $calendar_data[$d]['set'] += $row['cnt'];
    }
}

// --- 2. LẤY CHI TIẾT LỊCH HẸN CHO NGÀY ĐANG CHỌN (List bên phải) ---
$daily_appts = [];
$sql_daily = "SELECT * FROM appointments 
              WHERE DATE(appointmentDate) = '$selected_date_str' 
              ORDER BY appointmentDate ASC";
$res_daily = mysqli_query($link, $sql_daily);
while ($row = mysqli_fetch_assoc($res_daily)) {
    $daily_appts[] = $row;
}

// --- HÀM VẼ LỊCH ---
function build_calendar($month, $year, $calendar_data, $selected_day) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDayOfWeek = date('w', strtotime("$year-$month-01")); // 0 (Sun) - 6 (Sat)
    
    $html = '';
    $dayCount = 1;
    
    // Hàng tuần
    $html .= '<div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">';
    
    // Header Thứ
    $daysOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
    foreach ($daysOfWeek as $day) {
        $html .= '<div class="bg-gray-50 text-center py-3 text-xs font-bold text-gray-500 uppercase">' . $day . '</div>';
    }

    // Ô trống đầu tháng
    for ($i = 0; $i < $firstDayOfWeek; $i++) {
        $html .= '<div class="bg-white h-32"></div>';
    }

    // Các ngày trong tháng
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $isToday = ($day == date('j') && $month == date('n') && $year == date('Y'));
        $isSelected = ($day == $selected_day);
        
        // CSS cho ô ngày
        $cellClass = "bg-white h-32 p-2 relative flex flex-col justify-between hover:bg-gray-50 transition cursor-pointer border border-transparent";
        if ($isSelected) {
            $cellClass = "bg-red-50 h-32 p-2 relative flex flex-col justify-between cursor-pointer border border-red-400 z-10";
        }

        // Link chọn ngày
        $link = "?m=$month&y=$year&d=$day";

        $html .= "<a href='$link' class='$cellClass'>";
        
        // Số ngày
        $numClass = $isSelected ? "text-red-600 font-bold" : "text-gray-700 font-semibold";
        $html .= "<span class='$numClass'>$day</span>";

        // Badge Today
        if ($isToday) {
            $html .= "<span class='absolute top-2 right-2 text-[10px] font-bold text-blue-600 uppercase'>TODAY</span>";
        }

        // Badges Appointments
        $html .= "<div class='flex flex-col gap-1 mt-1'>";
        if (isset($calendar_data[$day])) {
            if ($calendar_data[$day]['set'] > 0) {
                $html .= "<span class='bg-green-100 text-green-700 text-[10px] px-2 py-0.5 rounded font-medium text-center'>{$calendar_data[$day]['set']} Set</span>";
            }
            if ($calendar_data[$day]['pending'] > 0) {
                $html .= "<span class='bg-yellow-100 text-yellow-700 text-[10px] px-2 py-0.5 rounded font-medium text-center'>{$calendar_data[$day]['pending']} Pending</span>";
            }
        }
        $html .= "</div>";

        $html .= "</a>";

        // Xuống dòng nếu hết tuần (tùy chọn, grid tự handle nhưng comment để rõ logic)
    }

    // Ô trống cuối tháng
    $remainingDays = 7 - (($daysInMonth + $firstDayOfWeek) % 7);
    if ($remainingDays < 7) {
        for ($i = 0; $i < $remainingDays; $i++) {
            $html .= '<div class="bg-white h-32"></div>';
        }
    }

    $html .= '</div>'; // End grid
    return $html;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Schedule - B-DONOR</title>
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
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-4">
                <div class="flex-shrink-0">
                    <a href="doctor-home.php" class="text-2xl font-bold text-brand uppercase tracking-wide">B-DONOR</a>
                </div>
                <div class="hidden md:flex flex-1 max-w-lg mx-auto">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></span>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-brand focus:border-brand sm:text-sm" placeholder="Search for...">
                    </div>
                </div>
                <div class="flex items-center gap-4 text-gray-400">
                    <button class="hover:text-gray-600 relative"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg><span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 border border-white"></span></button>
                    <span class="text-sm font-medium text-gray-500">EN</span>
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-sm"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                            <span class="text-sm font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($full_name); ?></span>
                            <svg id="user-menu-arrow" class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="user-menu-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-100 z-50">
                            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="bg-brand text-white shadow-md">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-5 text-center">
                <a href="doctor-home.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                    <span class="text-sm font-medium">Home</span>
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
                
                <a href="doctor-work-schedule.php" class="py-4 bg-brand-light border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Work Schedule</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="bg-[#C0392B] rounded-lg shadow-lg p-4 mb-8 flex items-center justify-between text-white">
            <h1 class="text-2xl font-bold uppercase tracking-wide pl-4">Doctor Schedule</h1>
            <div class="flex items-center gap-4 pr-4">
                <a href="?m=<?php echo $prev_month; ?>&y=<?php echo $prev_year; ?>" class="hover:bg-white/20 p-2 rounded-full transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                <span class="text-xl font-bold"><?php echo date('F Y', strtotime("$current_year-$current_month-01")); ?></span>
                <a href="?m=<?php echo $next_month; ?>&y=<?php echo $next_year; ?>" class="hover:bg-white/20 p-2 rounded-full transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-8">
                <?php echo build_calendar($current_month, $current_year, $calendar_data, $selected_day); ?>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-[#FFF5F5] rounded-2xl p-6 h-full border border-red-100 flex flex-col">
                    <div class="mb-6 pb-4 border-b border-red-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-1">Appointments List</h2>
                        <p class="text-red-600 font-medium"><?php echo $selected_date_display; ?></p>
                    </div>

                    <?php if (empty($daily_appts)): ?>
                        <div class="flex-1 flex flex-col items-center justify-center text-center py-10 opacity-70">
                            <div class="bg-gray-200 p-4 rounded-full mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-gray-500 italic mb-2">No appointments found for this day.</p>
                            <p class="text-xs text-gray-400">(Check console log for debug info)</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4 flex-1 overflow-y-auto pr-2">
                            <?php foreach ($daily_appts as $appt): 
                                $statusBadge = 'bg-gray-100 text-gray-600';
                                if ($appt['status'] == 'Completed') $statusBadge = 'bg-gray-200 text-gray-700'; // Như hình
                                if ($appt['status'] == 'Confirmed') $statusBadge = 'bg-green-100 text-green-700';
                                if ($appt['status'] == 'Pending') $statusBadge = 'bg-yellow-100 text-yellow-700';
                            ?>
                            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900"><?php echo htmlspecialchars($appt['name']); ?></h3>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5">ID: 0900000<?php echo $appt['id']; ?></p>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-1 rounded <?php echo $statusBadge; ?>">
                                        <?php echo $appt['status']; ?>
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-gray-700 font-bold bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php echo date('H:i', strtotime($appt['appointmentDate'])); ?>
                                    </div>
                                    
                                    <button class="bg-[#C0392B] hover:bg-[#A93226] text-white text-xs font-bold px-4 py-2 rounded-lg shadow transition">
                                        Change Time
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>

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
    </script>
</body>
</html>
