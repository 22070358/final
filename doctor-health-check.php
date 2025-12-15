<?php
// doctor-health-check.php - Update: Redesign Conclusion Section (Compact & Equal)
include 'config.php';
include 'connection.php';

session_start();

// Kiểm tra quyền Doctor
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$full_name = $_SESSION['full_name'] ?? 'Dr. Alice';

// --- 1. XỬ LÝ LỌC DANH SÁCH (Filter) ---
$filter_mode = $_GET['filter'] ?? 'all'; 
$where_clause = "WHERE a.status = 'Confirmed'"; 

if ($filter_mode == 'today') {
    $where_clause .= " AND DATE(a.appointmentDate) = CURDATE()";
}

// --- 2. LẤY DANH SÁCH CHỜ ---
$queue = [];
$sql_queue = "SELECT a.*, dp.dateOfBirth 
              FROM appointments a 
              LEFT JOIN donor_profiles dp ON a.userId = dp.userId 
              $where_clause
              ORDER BY a.appointmentDate ASC";
$res_queue = mysqli_query($link, $sql_queue);
while ($row = mysqli_fetch_assoc($res_queue)) {
    $age = 'N/A';
    if (!empty($row['dateOfBirth'])) {
        $dob = new DateTime($row['dateOfBirth']);
        $now = new DateTime();
        $age = $now->diff($dob)->y . ' years old';
    }
    $row['age_display'] = $age;
    $queue[] = $row;
}

// --- 3. XỬ LÝ CHỌN DONOR ---
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

// --- 4. XỬ LÝ SUBMIT FORM ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appt_id'])) {
    $appt_id = intval($_POST['appt_id']);
    $conclusion = $_POST['conclusion']; 
    $notes = mysqli_real_escape_string($link, $_POST['notes']);
    
    if ($conclusion == 'approved') {
        $sql_update = "UPDATE appointments SET status = 'ReadyToDonate', notes = CONCAT(notes, ' | Health Check: OK. $notes') WHERE id = $appt_id";
        mysqli_query($link, $sql_update);
        header("Location: doctor-record-donation.php?id=$appt_id");
        exit();
    } else {
        $sql_update = "UPDATE appointments SET status = 'Rejected', notes = CONCAT(notes, ' | Rejected Reason: $notes') WHERE id = $appt_id";
        mysqli_query($link, $sql_update);
        echo "<script>alert('Donor has been rejected.'); window.location.href='doctor-health-check.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Check - B-DONOR</title>
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
    <style>
        .radio-card:checked + div {
            border-color: #DC2626;
            background-color: #FEF2F2;
            color: #DC2626;
        }
        .radio-card:checked + div .check-icon {
            display: block;
        }
    </style>
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
                    <button class="hover:text-gray-600 relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 border border-white"></span>
                    </button>
                    <span class="text-sm font-medium text-gray-500">EN</span>
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-sm">
                                <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700 hidden sm:block"><?php echo htmlspecialchars($full_name); ?></span>
                            <svg id="user-menu-arrow" class="h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="user-menu-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-100 z-50">
                            <a href="logout.php" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign out
                            </a>
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
                <a href="doctor-health-check.php" class="py-4 bg-brand-light border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Health Check</span>
                </a>
                <a href="#" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-medium">Record Donation</span>
                </a>
                <a href="#" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-medium">Work Schedule</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-8 h-8 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h1 class="text-2xl font-bold text-gray-900">Health Check & Screening</h1>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-220px)]">
            
            <div class="w-full lg:w-80 flex-shrink-0 bg-gray-50 rounded-xl border border-gray-200 flex flex-col overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-white flex justify-between items-center">
                    <h2 class="font-bold text-gray-700">Waiting Queue</h2>
                    <span class="bg-red-100 text-brand text-xs font-bold px-2 py-1 rounded-full"><?php echo count($queue); ?></span>
                </div>
                
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                    <select onchange="window.location.href='doctor-health-check.php?filter='+this.value" class="w-full text-sm border-gray-300 rounded-lg p-2 focus:ring-brand focus:border-brand">
                        <option value="all" <?php echo $filter_mode=='all'?'selected':''; ?>>All Requests</option>
                        <option value="today" <?php echo $filter_mode=='today'?'selected':''; ?>>Today Only</option>
                    </select>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-3">
                    <?php if (empty($queue)): ?>
                        <div class="text-center py-10 text-gray-400 text-sm">No patients in queue</div>
                    <?php else: ?>
                        <?php foreach($queue as $q): 
                            $isActive = ($current_appt && $current_appt['id'] == $q['id']);
                            $cardClass = $isActive ? 'bg-red-50 border-red-200 shadow-sm ring-1 ring-red-200' : 'bg-white border-gray-200 hover:border-red-200 hover:shadow-sm';
                        ?>
                        <a href="doctor-health-check.php?id=<?php echo $q['id']; ?>&filter=<?php echo $filter_mode; ?>" class="block p-4 rounded-xl border transition-all cursor-pointer <?php echo $cardClass; ?>">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($q['name']); ?></h3>
                                <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">
                                    <?php echo date('H:i d/m', strtotime($q['appointmentDate'])); ?>
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 mb-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <?php echo $q['age_display']; ?>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <?php echo htmlspecialchars($q['phone']); ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-6 overflow-y-auto">
                <?php if ($current_appt): ?>
                    
                    <h2 class="text-xl font-bold text-brand mb-6 uppercase tracking-wide">Patient Information</h2>
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Full Name</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 font-bold text-gray-800">
                                <?php echo htmlspecialchars($current_appt['name']); ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Age</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 font-bold text-gray-800">
                                <?php echo intval($current_appt['age_display']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 my-8"></div>

                    <h2 class="text-xl font-bold text-gray-800 mb-6 uppercase tracking-wide">Vital Signs & Rapid Test</h2>
                    
                    <form method="POST" id="healthCheckForm">
                        <input type="hidden" name="appt_id" value="<?php echo $current_appt['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div><label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg) *</label><input type="number" name="weight" placeholder="Ex: 65" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none" required></div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type (Rapid Test) *</label>
                                <select name="blood_type" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white" required>
                                    <option value="">Select blood type</option><option value="A+">A+</option><option value="A-">A-</option><option value="B+">B+</option><option value="B-">B-</option><option value="AB+">AB+</option><option value="AB-">AB-</option><option value="O+">O+</option><option value="O-">O-</option>
                                </select>
                            </div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-2">Temperature (°C)</label><input type="number" step="0.1" name="temp" placeholder="36.5" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div><label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure (mmHg)</label><input type="text" name="bp" placeholder="120/80" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none"></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-2">Heart Rate (bpm)</label><input type="number" name="pulse" placeholder="75" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none"></div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Doctor's Notes / Rejection Reason</label>
                            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand focus:border-transparent outline-none resize-none" placeholder="Enter additional notes..."></textarea>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 mb-8">
                            <h3 class="text-sm font-bold text-gray-500 mb-4 uppercase tracking-wider text-center">Conclusion</h3>
                            <div class="grid grid-cols-2 gap-6 max-w-md mx-auto">
                                <label class="cursor-pointer group relative w-full">
                                    <input type="radio" name="conclusion" value="approved" class="peer sr-only radio-card" required>
                                    <div class="w-full p-4 rounded-xl border-2 border-gray-200 bg-white peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 hover:border-green-300 transition-all flex flex-col items-center justify-center gap-2 h-24">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500 relative flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white hidden peer-checked:block check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <span class="font-bold text-sm uppercase">Approved</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer group relative w-full">
                                    <input type="radio" name="conclusion" value="rejected" class="peer sr-only radio-card">
                                    <div class="w-full p-4 rounded-xl border-2 border-gray-200 bg-white peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 hover:border-red-300 transition-all flex flex-col items-center justify-center gap-2 h-24">
                                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-red-500 peer-checked:bg-red-500 relative flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white hidden peer-checked:block check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </div>
                                        <span class="font-bold text-sm uppercase">Not Approved</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <button type="button" onclick="window.location.reload()" class="px-6 py-3 rounded-lg border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition text-sm">Cancel</button>
                            <button type="submit" class="px-8 py-3 rounded-lg bg-[#3B82F6] hover:bg-blue-700 text-white font-bold shadow-lg transition text-sm">CONFIRM</button>
                        </div>
                    </form>

                <?php else: ?>
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                        <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <p class="text-xl font-medium text-gray-400">Select a patient from the waiting list to start examination.</p>
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