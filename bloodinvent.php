<?php
include 'config.php';
include 'connection.php';

// Chỉ cho phép Admin (hoặc Admin và Manager)
requireRole('Admin');


$current_username = $_SESSION['full_name'] ?? $_SESSION['username'];
$current_avatar = "https://ui-avatars.com/api/?name=" . urlencode($current_username) . "&background=random&color=fff";
$user_role = $_SESSION['role'] ?? 'Admin User';
$user_sub_role = 'Super Admin';

// --- 1. XỬ LÝ ADD NEW UNIT (Bỏ input Status) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_unit_direct') {
    $vol = intval($_POST['volume']);
    $rh = $_POST['rh_type']; 
    $blood_type = $_POST['blood_type_hidden']; 
    
    $col_date = $_POST['collection_date'];
    $exp_date = $_POST['expiry_date'];
    $location = mysqli_real_escape_string($link, $_POST['storage_location']);
    
    // Insert dữ liệu (Status tự động tính dựa trên expiryDate khi hiển thị)
    $sql = "INSERT INTO bloodunit (volume, bloodType, rhType, collectionDate, expiryDate, storageLocation, createdAt) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "isssss", $vol, $blood_type, $rh, $col_date, $exp_date, $location);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: bloodinvent.php"); 
        exit();
    }
}

// --- 2. LẤY DỮ LIỆU TỔNG QUAN ---
// Logic: "Expiring"
$inventory = [];
$all_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($all_groups as $g) {
    $inventory[$g] = ['total' => 0, 'available' => 0, 'expiring' => 0, 'expired' => 0];
}

$sql_stats = "SELECT 
                bloodType, rhType,
                COUNT(*) as total,
                SUM(CASE WHEN expiryDate > DATE_ADD(NOW(), INTERVAL 20 DAY) THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN expiryDate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 20 DAY) THEN 1 ELSE 0 END) as expiring,
                SUM(CASE WHEN expiryDate < NOW() THEN 1 ELSE 0 END) as expired
              FROM bloodunit
              GROUP BY bloodType, rhType";

$res_stats = mysqli_query($link, $sql_stats);
while ($row = mysqli_fetch_assoc($res_stats)) {
    $rh_symbol = ($row['rhType'] == 'Positive') ? '+' : '-';
    $key = $row['bloodType'] . $rh_symbol;
    if (isset($inventory[$key])) {
        $inventory[$key] = [
            'total' => $row['total'],
            'available' => $row['available'],
            'expiring' => $row['expiring'],
            'expired' => $row['expired']
        ];
    }
}

// Lọc hiển thị Card chính
$filter_type = $_GET['type'] ?? '';
if (!empty($filter_type)) {
    $inventory = array_filter($inventory, function($k) use ($filter_type) {
        return strpos($k, $filter_type) === 0; 
    }, ARRAY_FILTER_USE_KEY);
}

// --- 3. LẤY DATA CHI TIẾT CHO JS ---
$raw_units = [];
$sql_all = "SELECT id, volume, bloodType, rhType, expiryDate, collectionDate, storageLocation FROM bloodunit ORDER BY expiryDate ASC";
$res_all = mysqli_query($link, $sql_all);

while ($row = mysqli_fetch_assoc($res_all)) {
    $rh_symbol = ($row['rhType'] == 'Positive') ? '+' : '-';
    $row['group_key'] = $row['bloodType'] . $rh_symbol;
    $row['raw_type'] = $row['bloodType'];
    
    $row['col_date_fmt'] = date('d/m/Y', strtotime($row['collectionDate']));
    $row['exp_date_fmt'] = date('d/m/Y', strtotime($row['expiryDate']));
    
    // --- LOGIC TÍNH TRẠNG THÁI (MỚI) ---
    // Expired: < 0 ngày
    // Expiring: 0 <= ngày <= 20
    // Available: > 20 ngày
    
    $days_left = (strtotime($row['expiryDate']) - time()) / 86400;
    
    if ($days_left < 0) {
        $row['status'] = 'Expired';
        $row['css'] = 'bg-red-100 text-red-700 border border-red-200';
    } elseif ($days_left <= 20) { // Sửa thành 20 ngày theo yêu cầu
        $row['status'] = 'Expiring'; // Expiring Soon
        $row['css'] = 'bg-yellow-100 text-yellow-700 border border-yellow-200';
    } else {
        $row['status'] = 'Available';
        $row['css'] = 'bg-green-100 text-green-700 border border-green-200';
    }
    $raw_units[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - B-DONOR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { primary: '#B91C1C', accent: '#CF2222', tableHead: '#D85555', dark: '#1a2231', 'gray-dark': '#24303f' }
                }
            }
        }
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
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
                <a href="bloodinvent.php" class="flex items-center gap-3 px-4 py-3 bg-primary text-white rounded-lg shadow-md shadow-red-200 dark:shadow-none transition-all">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>
                    <span class="font-semibold">Blood inventory</span>
                </a>
                <a href="user-management.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="font-medium">User management</span>
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-20 bg-[#B91C1C] dark:bg-gray-800 flex items-center justify-between px-8 shadow-sm shrink-0 z-30">
                <h1 class="text-2xl font-bold text-white tracking-tight">Admin Dashboard</h1>
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
                            <svg id="user-menu-arrow" class="text-red-200 group-hover:text-white transition-transform duration-200" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
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
                <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                    <h2 class="text-3xl font-extrabold text-[#CF2222] dark:text-red-500 tracking-tight">Blood inventory list</h2>
                    <div class="flex items-center gap-3">
                        <form method="GET" class="relative">
                            <select name="type" onchange="this.form.submit()" class="appearance-none h-11 pl-4 pr-10 rounded-xl border border-gray-200 bg-white text-gray-700 font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-red-200 shadow-sm cursor-pointer dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                                <option value="">All Type</option>
                                <?php foreach(['A','B','AB','O'] as $t) { $sel = ($filter_type==$t)?'selected':''; echo "<option value='$t' $sel>Type $t</option>"; } ?>
                            </select>
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span>
                        </form>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-dark rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="bg-[#D85555] dark:bg-red-900 px-6 py-5 flex items-center text-white font-bold text-lg">
                        <div class="w-12 text-center"><input type="checkbox" class="w-5 h-5 rounded border-white/50 bg-white/20 checked:bg-white checked:text-red-600 focus:ring-0 cursor-pointer"></div>
                        <div class="flex-1 grid grid-cols-6 gap-4 text-center items-center">
                            <div class="text-left pl-4">Type</div>
                            <div>Total Units</div>
                            <div>Available</div>
                            <div>Expiring</div>
                            <div>Expired</div>
                            <div>Action</div>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <?php foreach ($inventory as $group => $data): ?>
                        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                            <div class="w-12 text-center"><input type="checkbox" class="w-5 h-5 rounded border-gray-300 text-[#CF2222] focus:ring-red-200 cursor-pointer dark:border-gray-600 dark:bg-gray-700"></div>
                            <div class="flex-1 grid grid-cols-6 gap-4 text-center items-center">
                                <div class="text-left pl-4">
                                    <div class="font-extrabold text-xl text-[#1a2231] dark:text-white">TYPE <?php echo str_replace(['+','-'],'', $group); ?> <span class="text-sm font-normal text-gray-400 ml-1"><?php echo substr($group,-1); ?></span></div>
                                    <button onclick="openDetailsModal('<?php echo $group; ?>')" class="text-[10px] font-bold text-[#D85555] bg-red-50 px-2 py-0.5 rounded-full mt-1 hover:bg-red-100 transition">View Details</button>
                                </div>
                                <div class="text-xl font-bold text-[#1a2231] dark:text-gray-200"><?php echo $data['total']; ?></div>
                                <div class="text-xl font-bold text-green-500"><?php echo $data['available']; ?></div>
                                <div class="text-xl font-bold text-yellow-500"><?php echo $data['expiring']; ?></div>
                                <div class="text-xl font-bold text-red-500"><?php echo $data['expired']; ?></div>
                                <div class="flex items-center justify-center gap-4">
                                    <button class="text-blue-500 hover:bg-blue-50 p-2 rounded-full transition dark:hover:bg-gray-700"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                                    <button class="text-[#CF2222] hover:bg-red-50 p-2 rounded-full transition dark:hover:bg-gray-700"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg></button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>

        <div id="addUnitModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm transition-all duration-300">
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="addUnitContent">
                <div class="bg-[#B91C1C] px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-white/20 rounded-full"><svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg></div>
                        <div>
                            <h3 class="text-lg font-bold">Add New Unit</h3>
                            <p class="text-xs text-red-100 opacity-90" id="addUnitSubtitle">Managing list for TYPE A</p>
                        </div>
                    </div>
                    <button onclick="closeAddUnitModal()" class="text-white/80 hover:text-white bg-black/10 hover:bg-black/20 rounded-full p-1"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>

                <div class="p-8">
                    <button onclick="closeAddUnitModal()" class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back to List
                    </button>
                    
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Add New Unit</h2>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="add_unit_direct">
                        <input type="hidden" name="blood_type_hidden" id="formBloodType">
                        <input type="hidden" name="rh_type" id="formRhType">

                        <div class="grid grid-cols-2 gap-6">
                            <div>
    <label class="block text-sm font-semibold text-gray-600 mb-2">Volume (ml)</label>
    <select name="volume" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none bg-white appearance-none cursor-pointer">
        <option value="250">250 ml</option>
        <option value="350">350 ml</option>
        <option value="450">450 ml</option>
    </select>
</div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Rh Type</label>
                                <select id="displayRhType" disabled class="w-full border border-gray-300 bg-gray-50 text-gray-500 rounded-lg px-4 py-3 outline-none">
                                    <option value="Positive">Positive (+)</option>
                                    <option value="Negative">Negative (-)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Collection Date</label>
                                <input type="date" name="collection_date" id="colDate" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Expiry Date</label>
                                <input type="date" name="expiry_date" id="expDate" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none" required>
                                <p class="text-xs text-gray-400 mt-1">Status depends on this date.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-600 mb-2">Storage Location</label>
                                <input type="text" name="storage_location" placeholder="e.g. Fridge A" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 outline-none" required>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" onclick="closeAddUnitModal()" class="px-6 py-2.5 rounded-lg bg-[#1a2231] text-white font-medium hover:bg-gray-800 transition">Cancel</button>
                            <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#DC2626] text-white font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">Create Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="detailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 w-full max-w-5xl rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col max-h-[90vh]" id="detailsModalContent">
                <div class="bg-[#B91C1C] px-8 py-5 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-3 text-white">
                        <div class="p-1.5 bg-white/20 rounded-lg"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg></div>
                        <div><h3 class="text-xl font-bold leading-none">Inventory Details</h3><p class="text-xs text-red-100 mt-1 opacity-90" id="detailModalSubtitle">Managing list for TYPE A</p></div>
                    </div>
                    <button onclick="closeDetailsModal()" class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="p-6 grid grid-cols-4 gap-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shrink-0">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase font-semibold">Blood Type</p><h4 class="text-xl font-extrabold text-gray-800 dark:text-white mt-1" id="statType">TYPE A+</h4></div><div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div></div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase font-semibold">Total Units</p><h4 class="text-xl font-extrabold text-gray-800 dark:text-white mt-1" id="statTotal">0</h4></div><div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg></div></div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase font-semibold">Total Volume</p><h4 class="text-xl font-extrabold text-gray-800 dark:text-white mt-1"><span id="statVol">0</span> <span class="text-sm text-gray-400 font-medium">ml</span></h4></div><div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/><path d="M10 2v2"/><path d="M14 2v2"/></svg></div></div>
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center justify-between"><div><p class="text-xs text-gray-500 uppercase font-semibold">Expiring Soon</p><h4 class="text-xl font-extrabold text-gray-800 dark:text-white mt-1" id="statExpiring">0</h4></div><div class="w-10 h-10 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div></div>
                </div>
                <div class="px-6 py-4 flex justify-between items-center bg-white dark:bg-gray-800 shrink-0 border-b border-gray-100 dark:border-gray-700">
                    <div class="relative w-72"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg></span><input type="text" id="detailSearch" placeholder="Search by Unit ID..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 text-sm outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"></div>
                    
                    <div class="flex items-center gap-3">
                        <select id="filterVol" class="py-2 px-3 rounded-lg border border-gray-200 text-sm text-gray-600 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 cursor-pointer">
                            <option value="">All Volume</option>
                            <option value="250">250 ml</option>
                            <option value="350">350 ml</option>
                            <option value="450">450 ml</option>
                        </select>
                        <select id="filterStatus" class="py-2 px-3 rounded-lg border border-gray-200 text-sm text-gray-600 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 cursor-pointer">
                            <option value="">All Status</option>
                            <option value="Available">Available</option>
                            <option value="Expiring">Expiring Soon</option>
                            <option value="Expired">Expired</option>
                        </select>
                        <button onclick="openAddUnitModal()" class="bg-[#CF2222] hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Unit
                        </button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto px-6 pb-6">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase tracking-wider sticky top-0 z-10">
                            <tr><th class="py-3 px-4">Unit ID</th><th class="py-3 px-4">Volume</th><th class="py-3 px-4">Collection Date</th><th class="py-3 px-4">Expiry Date</th><th class="py-3 px-4">Location</th><th class="py-3 px-4">Status</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700" id="detailsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <button id="theme-toggle" class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-full bg-blue-600 text-white shadow-lg flex items-center justify-center hover:bg-blue-700 transition-transform hover:scale-110 focus:outline-none">
            <svg id="moon-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </button>
    </div>

    <script>
        const allUnits = <?php echo json_encode($raw_units); ?>;
        let currentGroupKey = ''; // Global var để biết đang ở nhóm máu nào

        // --- ADD UNIT MODAL LOGIC ---
        const addUnitModal = document.getElementById('addUnitModal');
        const addUnitContent = document.getElementById('addUnitContent');

        function openAddUnitModal() {
            // Auto fill data
            document.getElementById('addUnitSubtitle').innerText = 'Managing list for TYPE ' + currentGroupKey;
            
            // Tách Type và Rh từ groupKey (VD: A+)
            const type = currentGroupKey.slice(0, -1);
            const rhSign = currentGroupKey.slice(-1);
            const rh = (rhSign === '+') ? 'Positive' : 'Negative';

            document.getElementById('formBloodType').value = type;
            document.getElementById('formRhType').value = rh;
            document.getElementById('displayRhType').value = rh; // Chỉ để hiển thị

            // Set default dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('colDate').value = today;
            
            // Auto calculate expiry (+35 days)
            const exp = new Date();
            exp.setDate(exp.getDate() + 35);
            document.getElementById('expDate').value = exp.toISOString().split('T')[0];

            addUnitModal.classList.remove('hidden');
            setTimeout(() => {
                addUnitContent.classList.remove('opacity-0', 'scale-95');
                addUnitContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeAddUnitModal() {
            addUnitContent.classList.remove('opacity-100', 'scale-100');
            addUnitContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => { addUnitModal.classList.add('hidden'); }, 300);
        }

        // Auto update Expiry when Collection Date changes
        document.getElementById('colDate').addEventListener('change', function() {
            const colDate = new Date(this.value);
            if (!isNaN(colDate)) {
                colDate.setDate(colDate.getDate() + 35);
                document.getElementById('expDate').value = colDate.toISOString().split('T')[0];
            }
        });

        // --- DETAILS MODAL LOGIC ---
        const detailModal = document.getElementById('detailsModal');
        const detailContent = document.getElementById('detailsModalContent');
        const tableBody = document.getElementById('detailsTableBody');
        const searchInput = document.getElementById('detailSearch');
        const filterVol = document.getElementById('filterVol');
        const filterStatus = document.getElementById('filterStatus');

        function openDetailsModal(groupKey) {
            currentGroupKey = groupKey;
            detailModal.classList.remove('hidden');
            setTimeout(() => { detailContent.classList.remove('opacity-0', 'scale-95'); detailContent.classList.add('opacity-100', 'scale-100'); }, 10);
            updateDetailsView();
        }

        function closeDetailsModal() {
            detailContent.classList.remove('opacity-100', 'scale-100'); detailContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => { detailModal.classList.add('hidden'); }, 300);
        }
        detailModal.addEventListener('click', (e) => { if (e.target === detailModal) closeDetailsModal(); });

        function updateDetailsView() {
            const searchTerm = searchInput.value.toLowerCase();
            const volVal = filterVol.value;
            const statusVal = filterStatus.value;

            // Filter Logic
            const units = allUnits.filter(u => {
                const matchGroup = u.group_key === currentGroupKey;
                const matchSearch = u.id.toString().includes(searchTerm) || u.storageLocation.toLowerCase().includes(searchTerm);
                const matchVol = volVal === '' || u.volume == volVal;
                const matchStatus = statusVal === '' || u.status === statusVal;
                
                return matchGroup && matchSearch && matchVol && matchStatus;
            });

            // Update Stats Headers (dựa trên toàn bộ list của nhóm máu đó, không bị ảnh hưởng bởi filter tìm kiếm)
            const allGroupUnits = allUnits.filter(u => u.group_key === currentGroupKey);
            document.getElementById('detailModalSubtitle').innerText = 'Managing list for TYPE ' + currentGroupKey;
            document.getElementById('statType').innerText = 'TYPE ' + currentGroupKey;
            document.getElementById('statTotal').innerText = allGroupUnits.length;
            document.getElementById('statVol').innerText = allGroupUnits.reduce((s, u) => s + parseInt(u.volume), 0);
            document.getElementById('statExpiring').innerText = allGroupUnits.filter(u => u.status === 'Expiring').length;

            // Render Table
            tableBody.innerHTML = '';
            if (units.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-gray-500">No units found matching criteria.</td></tr>';
            } else {
                units.forEach(u => {
                    const row = `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-100 dark:border-gray-700 transition">
                            <td class="py-3 px-4 font-bold text-gray-800 dark:text-white">#${u.id}</td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300 font-medium">${u.volume} ml</td>
                            <td class="py-3 px-4 text-sm text-gray-500">${u.col_date_fmt}</td>
                            <td class="py-3 px-4 text-sm text-gray-500">${u.exp_date_fmt}</td>
                            <td class="py-3 px-4 text-sm text-gray-600 dark:text-gray-300 font-medium">${u.storageLocation}</td>
                            <td class="py-3 px-4"><span class="px-2.5 py-1 rounded-md text-xs font-bold ${u.css}">${u.status}</span></td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            }
        }
        
        // Event Listeners for Filters
        searchInput.addEventListener('input', updateDetailsView);
        filterVol.addEventListener('change', updateDetailsView);
        filterStatus.addEventListener('change', updateDetailsView);

        // Common Logic
        const userBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-menu-dropdown');
        const userArrow = document.getElementById('user-menu-arrow');
        userBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('hidden'); userArrow.classList.toggle('rotate-180'); });
        document.addEventListener('click', (e) => { if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) { userDropdown.classList.add('hidden'); userArrow.classList.remove('rotate-180'); } });

        const btn = document.getElementById('theme-toggle');
        const moon = document.getElementById('moon-icon');
        const sun = document.getElementById('sun-icon');
        const html = document.documentElement;
        function updateIcon() { if (html.classList.contains('dark')) { moon.classList.add('hidden'); sun.classList.remove('hidden'); } else { moon.classList.remove('hidden'); sun.classList.add('hidden'); } }
        updateIcon();
        btn.addEventListener('click', () => { if (html.classList.contains('dark')) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); } else { html.classList.add('dark'); localStorage.setItem('theme', 'dark'); } updateIcon(); });
    </script>
</body>
</html>
