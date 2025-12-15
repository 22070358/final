<?php
include 'config.php';
include 'connection.php';

// Chỉ cho phép Donor vào
requireRole('Donor');



$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Donor';
$username = $_SESSION['username'];

// --- LOGIC LẤY THÔNG BÁO TỪ LỊCH HẸN ---
// Lấy các lịch hẹn có trạng thái đã được xử lý (Khác Pending) hoặc mới nhất
$sql_notif = "SELECT * FROM appointments 
              WHERE userId = $user_id 
              AND status != 'Pending' 
              ORDER BY appointmentDate DESC LIMIT 10";
$res_notif = mysqli_query($link, $sql_notif);
$notifications = [];

if ($res_notif && mysqli_num_rows($res_notif) > 0) {
    while ($row = mysqli_fetch_assoc($res_notif)) {
        // Tạo nội dung thông báo dựa trên trạng thái
        $status = $row['status'];
        $date = date('d/m/Y', strtotime($row['appointmentDate']));
        $title = "";
        $message = "";
        $iconColor = ""; // blue, red, green
        $iconSvg = "";

        switch ($status) {
            case 'Confirmed':
                $title = "Appointment Confirmed";
                $message = "Your donation appointment on <b>$date</b> at <b>{$row['location']}</b> has been confirmed.";
                $iconColor = "bg-blue-100 text-blue-600";
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />';
                break;
            case 'Rejected':
                $title = "Appointment Rejected";
                $message = "Your appointment on <b>$date</b> was rejected. Please check details or contact support.";
                $iconColor = "bg-red-100 text-red-600";
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                break;
            case 'Completed':
                $title = "Donation Completed";
                $message = "Thank you for your donation on <b>$date</b>! You are a hero.";
                $iconColor = "bg-green-100 text-green-600";
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                break;
            case 'Cancelled':
                $title = "Appointment Cancelled";
                $message = "You cancelled the appointment scheduled for <b>$date</b>.";
                $iconColor = "bg-gray-100 text-gray-500";
                $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                break;
        }

        if ($title) {
            $notifications[] = [
                'title' => $title,
                'message' => $message,
                'iconColor' => $iconColor,
                'iconSvg' => $iconSvg,
                'time' => $date 
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - B-DONOR</title>
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
                    <a href="donor-home.php" class="text-2xl font-bold text-brand uppercase tracking-wide">B-DONOR</a>
                </div>
                <div class="hidden md:flex flex-1 max-w-lg mx-auto">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-brand focus:border-brand sm:text-sm" placeholder="Search for...">
                    </div>
                </div>
                <div class="flex items-center gap-4 text-gray-400">
                    <button class="hover:text-gray-600 relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500 border border-white"></span>
                    </button>
                    
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <div class="h-9 w-9 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-sm">
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
                <a href="donor-home.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                    <span class="text-sm font-medium">Home</span>
                </a>
                
                <a href="donor-donation.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
                    <span class="text-sm font-medium">Register Appointment</span>
                </a>
                
                <a href="donor-notification.php" class="py-4 bg-nav-active border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Notification</span>
                </a>

                <a href="donor-history.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-medium">History</span>
                </a>
                
                <a href="donor-appointments.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-medium">Appointment</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-8 h-8 text-brand" viewBox="0 0 24 24" fill="currentColor"><path d="M17.927,5.828h-4.41l-1.929-1.961c-0.078-0.079-0.186-0.125-0.297-0.125H4.159c-0.229,0-0.417,0.188-0.417,0.417v1.669c0,0.229,0.188,0.417,0.417,0.417h12.963c0.229,0,0.417,0.188,0.417,0.417c0,0.229-0.188,0.417-0.417,0.417H3.742c-0.229,0-0.417,0.188-0.417,0.417v1.967c0,0.229,0.188,0.417,0.417,0.417h12.963c0.229,0,0.417,0.188,0.417,0.417c0-0.229-0.188-0.417-0.417-0.417H3.742c-0.229,0-0.417,0.188-0.417,0.417v6.042c0,0.229,0.188,0.417,0.417,0.417h12.963c0.229,0,0.417-0.188,0.417-0.417c0-0.229-0.188-0.417-0.417-0.417H4.159c-0.229,0-0.417-0.188-0.417-0.417v-2.292c0-0.229,0.188-0.417,0.417-0.417h13.213c0.229,0,0.417-0.188,0.417-0.417c0-0.229-0.188-0.417-0.417-0.417H4.159c-0.229,0-0.417-0.188-0.417-0.417v-1.967c0-0.229,0.188-0.417,0.417-0.417h12.797c0.229,0,0.417-0.188,0.417-0.417V6.245C18.344,6.015,18.156,5.828,17.927,5.828z M3.325,18.75h13.213c0.229,0,0.417-0.188,0.417-0.417c0-0.229-0.188-0.417-0.417-0.417H3.742c-0.229,0-0.417,0.188-0.417,0.417v1.042C3.325,18.75,3.325,18.75,3.325,18.75z"/></path><path d="M12,2C6.477,2,2,6.477,2,12s4.477,10,10,10s10-4.477,10-10S17.523,2,12,2z M12,18c-3.314,0-6-2.686-6-6s2.686-6,6-6s6,2.686,6,6S15.314,18,12,18z" fill-rule="evenodd" clip-rule="evenodd" style="display:none;"/><path d="M12,22c5.523,0,10-4.477,10-10S17.523,2,12,2S2,6.477,2,12S6.477,22,12,22z" fill="#DC2626"/></svg>
            <h1 class="text-3xl font-bold text-brand">Notification</h1>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 min-h-[400px] flex flex-col">
            
            <?php if (empty($notifications)): ?>
                <div class="flex-1 flex flex-col items-center justify-center p-10">
                    <div class="bg-gray-50 p-6 rounded-full mb-4">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <p class="text-gray-400 text-lg font-medium">You don't have notifications.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="p-6 hover:bg-gray-50 transition flex items-start gap-4">
                            <div class="flex-shrink-0 p-3 rounded-full <?php echo $notif['iconColor']; ?>">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php echo $notif['iconSvg']; ?>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="text-base font-bold text-gray-800"><?php echo $notif['title']; ?></h3>
                                    <span class="text-xs text-gray-400"><?php echo $notif['time']; ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1"><?php echo $notif['message']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

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
