<?php
// doctor-record-donation.php - Update: Đã sửa link menu Work Schedule
include 'config.php';
include 'connection.php';

session_start();

// Kiểm tra quyền Doctor
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$full_name = $_SESSION['full_name'] ?? 'Dr. Alice';

// --- 1. LẤY DANH SÁCH CHỜ THU THẬP ---
$queue = [];
$sql_queue = "SELECT a.*, dp.dateOfBirth, dp.bloodType as profile_blood 
              FROM appointments a 
              LEFT JOIN donor_profiles dp ON a.userId = dp.userId 
              WHERE a.status = 'ReadyToDonate' 
              ORDER BY a.appointmentDate ASC";
$res_queue = mysqli_query($link, $sql_queue);
while ($row = mysqli_fetch_assoc($res_queue)) {
    // Xác định nhóm máu hiển thị
    $b_type = !empty($row['bloodType']) ? $row['bloodType'] : ($row['profile_blood'] ?? '?');
    $row['display_blood'] = $b_type;
    $queue[] = $row;
}

// --- 2. XỬ LÝ CHỌN DONOR ---
$current_appt = null;
if (isset($_GET['id'])) {
    $selected_id = intval($_GET['id']);
    foreach ($queue as $item) {
        if ($item['id'] == $selected_id) {
            $current_appt = $item;
            break;
        }
    }
}

// --- 3. XỬ LÝ UPDATE (Hoàn thành) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appt_id'])) {
    $appt_id = intval($_POST['appt_id']);
    $volume = intval($_POST['volume']); // Giá trị từ select (250, 350, 450)
    $blood_type_final = $_POST['blood_type_final'];
    
    // Tách Rh
    $rh = 'Positive';
    $type_only = $blood_type_final;
    if (strpos($blood_type_final, '-') !== false) {
        $rh = 'Negative';
        $type_only = rtrim($blood_type_final, '-');
    } elseif (strpos($blood_type_final, '+') !== false) {
        $rh = 'Positive';
        $type_only = rtrim($blood_type_final, '+');
    }

// Insert kho máu
    $col_date = date('Y-m-d');
    $exp_date = date('Y-m-d', strtotime('+35 days'));
    
    // Câu lệnh INSERT giữ nguyên cột status
    $sql_inventory = "INSERT INTO bloodunit (volume, bloodType, rhType, collectionDate, expiryDate, storageLocation, status, createdAt) 
                      VALUES (?, ?, ?, ?, ?, 'Central Fridge', 'Available', NOW())";
    
    if ($stmt = mysqli_prepare($link, $sql_inventory)) {
        // FIX: Sửa 'isssss' thành 'issss' (5 tham số tương ứng với 5 dấu ?)
        mysqli_stmt_bind_param($stmt, "issss", $volume, $type_only, $rh, $col_date, $exp_date);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Update trạng thái lịch hẹn
    $sql_update = "UPDATE appointments SET status = 'Completed', bloodType = '$blood_type_final' WHERE id = $appt_id";
    mysqli_query($link, $sql_update);

    echo "<script>alert('Donation recorded successfully!'); window.location.href='doctor-record-donation.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Donation - B-DONOR</title>
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
<body class="bg-white text-gray-800 font-sans min-h-screen flex flex-col">

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
                
                <a href="doctor-record-donation.php" class="py-4 bg-brand-light border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Record Donation</span>
                </a>

                <a href="doctor-work-schedule.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-medium">Work Schedule</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-8 h-8 text-brand" viewBox="0 0 24 24" fill="currentColor"><path d="M16.5 13c-1.2 0-3.07.34-4.5 1-1.43-.67-3.3-1-4.5-1C5.38 13 4 14.19 4 17v2c0 1.1.9 2 2 2h1.5c1.1 0 2 .9 2 2v2h2v-2c0-1.1.9-2 2-2H20c1.1 0 2-.9 2-2v-2c0-2.81-1.38-4-3.5-4z"/><path d="M12 2C8.69 2 6 4.69 6 8s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 9c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z"/></svg>
            <h1 class="text-2xl font-bold text-gray-900">Record Donation (Collection)</h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-220px)]">
            
            <div class="w-full lg:w-80 flex-shrink-0 bg-white rounded-xl border border-gray-200 flex flex-col overflow-hidden">
                <div class="p-4 bg-green-50 border-b border-green-100 flex justify-between items-center">
                    <h2 class="font-bold text-green-800">Ready for Collection</h2>
                    <span class="bg-green-200 text-green-800 text-xs font-bold px-2 py-1 rounded-full"><?php echo count($queue); ?></span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-white">
                    <?php if (empty($queue)): ?>
                        <div class="text-center py-10 text-gray-400 text-sm">No donors ready.</div>
                    <?php else: ?>
                        <?php foreach($queue as $q): 
                            $isActive = ($current_appt && $current_appt['id'] == $q['id']);
                            $cardClass = $isActive ? 'bg-green-50 border-green-400 shadow-sm ring-1 ring-green-400' : 'bg-white border-gray-200 hover:border-gray-300';
                            
                            // Avatar luôn đỏ
                            $blood = $q['display_blood'];
                            $avatarColor = 'bg-red-100 text-red-600'; 
                        ?>
                        <a href="doctor-record-donation.php?id=<?php echo $q['id']; ?>" class="block p-4 rounded-xl border transition-all cursor-pointer flex items-center gap-4 <?php echo $cardClass; ?>">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full <?php echo $avatarColor; ?> flex items-center justify-center font-bold text-sm">
                                <?php echo substr($blood, 0, 1) ?: '?'; ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($q['name']); ?></h3>
                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Waiting...
                                </p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-8 overflow-y-auto">
                <?php if ($current_appt): ?>
                    
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Collected Blood Unit Information</h2>
                    <p class="text-sm text-gray-500 mb-8 font-medium">Donor: <span class="font-bold text-gray-800"><?php echo htmlspecialchars($current_appt['name']); ?></span></p>

                    <form method="POST">
                        <input type="hidden" name="appt_id" value="<?php echo $current_appt['id']; ?>">
                        <input type="hidden" name="blood_type_final" value="<?php echo htmlspecialchars($current_appt['display_blood']); ?>">
                        
                        <div class="space-y-6 max-w-2xl">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($current_appt['name']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-50 text-gray-800 font-semibold focus:outline-none" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Date</label>
                                <input type="text" value="<?php echo date('d/m/Y'); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white text-gray-800 focus:outline-none" readonly>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Volume (mL)</label>
                                <select name="volume" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent cursor-pointer">
                                    <option value="250">250 mL</option>
                                    <option value="350">350 mL</option>
                                    <option value="450">450 mL</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Blood Type</label>
                                <input type="text" value="Type <?php echo htmlspecialchars($current_appt['display_blood']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-white text-gray-900 font-bold text-lg focus:outline-none" readonly>
                            </div>
                        </div>

                        <div class="flex justify-start gap-4 mt-10">
                            <button type="button" onclick="window.location.reload()" class="px-8 py-3 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button type="submit" class="px-8 py-3 rounded-lg bg-[#C0392B] hover:bg-[#A93226] text-white font-bold shadow-md transition">Update Record</button>
                        </div>
                    </form>

                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <p class="text-lg font-medium text-gray-400">Select a donor from the list to record donation.</p>
                    </div>
                <?php endif; ?>
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
