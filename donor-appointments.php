<?php
include 'config.php';
include 'connection.php';
include 'auth.php'; // <--- Thêm dòng này

// Chỉ cho phép Donor vào
requireRole('Donor');

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Donor';
$current_avatar = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=random&color=fff";

// --- XỬ LÝ HỦY LỊCH (Nếu bấm nút Cancel) ---
if (isset($_POST['cancel_appt_id'])) {
    $cancel_id = intval($_POST['cancel_appt_id']);
    $sql_cancel = "UPDATE appointments SET status = 'Cancelled' WHERE id = $cancel_id AND userId = $user_id AND status = 'Pending'";
    mysqli_query($link, $sql_cancel);
    header("Location: donor-appointments.php");
    exit();
}

// --- LẤY LỊCH HẸN ĐANG ACTIVE (Mới nhất) ---
// Chỉ lấy những đơn Pending hoặc Confirmed (Approved)
$sql = "SELECT * FROM appointments 
        WHERE userId = $user_id 
        AND status IN ('Pending', 'Confirmed') 
        ORDER BY appointmentDate DESC LIMIT 1";
$result = mysqli_query($link, $sql);
$appt = mysqli_fetch_assoc($result);

// Xác định trạng thái để hiển thị giao diện
$has_appointment = ($appt != null);
$is_approved = ($has_appointment && $appt['status'] == 'Confirmed');

// Format dữ liệu hiển thị
if ($has_appointment) {
    $appt_date = date('l, d F Y', strtotime($appt['appointmentDate']));
    $appt_time = $is_approved ? date('H:i', strtotime($appt['appointmentDate'])) : 'Pending arrangement';
    
    // Tách nhóm máu (VD: A+)
    $blood_display = $appt['bloodType']; 
    if (empty($blood_display)) {
        $sql_p = "SELECT bloodType, rhType FROM donor_profiles WHERE userId = $user_id";
        $res_p = mysqli_query($link, $sql_p);
        $prof = mysqli_fetch_assoc($res_p);
        $blood_display = ($prof['bloodType'] ?? '') . ($prof['rhType'] == 'Positive' ? '+' : '-');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Appointment - B-DONOR</title>
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
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-brand focus:border-brand sm:text-sm" placeholder="Search...">
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
                
                <a href="donor-notification.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                    <span class="text-sm font-medium">Notification</span>
                </a>
                
                <a href="donor-history.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <span class="text-sm font-medium">History</span>
                </a>
                
                <a href="donor-appointments.php" class="py-4 bg-nav-active border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Appointment</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <?php if (!$has_appointment): ?>
            <div class="flex flex-col items-center justify-center py-20">
                <div class="bg-white p-8 rounded-full shadow-sm mb-6">
                    <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">You don't have any appointments</h2>
                <p class="text-gray-500 mb-8">Register to donate blood today to help save lives.</p>
                <a href="donor-donation.php" class="bg-brand hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Register Now
                </a>
            </div>

        <?php else: ?>
            <div class="flex items-center justify-center gap-3 mb-10">
                <svg class="w-8 h-8 text-brand" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12 2 5 11 5 16C5 19.866 8.13401 23 12 23C15.866 23 19 19.866 19 16C19 11 12 2 12 2Z"/></svg>
                <h1 class="text-3xl font-bold text-brand">Track Appointment</h1>
            </div>

            <div class="w-full max-w-4xl mx-auto mb-10">
                <div class="relative flex items-center justify-between w-full">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-2 bg-gray-200 rounded-full -z-10"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-2 bg-brand rounded-full -z-10 transition-all duration-500" 
                         style="width: <?php echo $is_approved ? '100%' : '50%'; ?>"></div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-brand flex items-center justify-center text-white border-4 border-white shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-brand">Registered</span>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full <?php echo $is_approved ? 'bg-brand' : 'bg-brand'; ?> flex items-center justify-center text-white border-4 border-white shadow">
                            <?php if ($is_approved): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm font-semibold <?php echo $is_approved ? 'text-gray-400' : 'text-brand'; ?>">Waiting for approval</span>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full <?php echo $is_approved ? 'bg-brand' : 'bg-gray-300'; ?> flex items-center justify-center text-white border-4 border-white shadow transition-colors duration-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-bold <?php echo $is_approved ? 'text-brand' : 'text-gray-400'; ?>">Approved</span>
                    </div>
                </div>
            </div>

            <div class="w-full max-w-5xl mx-auto bg-gradient-to-br from-[#B41919] to-[#8E1212] rounded-3xl shadow-2xl p-8 md:p-12 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

                <div class="mb-8 border-b border-white/20 pb-4 relative z-10">
                    <h3 class="text-2xl font-bold text-white uppercase flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-white rounded-full"></span>
                        Information Details
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-16 relative z-10">
                    <div class="flex flex-col gap-8">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-red-200/80 uppercase tracking-wide">Full Name</span>
                            <span class="text-white text-2xl font-bold text-yellow-300"><?php echo htmlspecialchars($appt['name']); ?></span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-red-200/80 uppercase tracking-wide">Phone Number</span>
                            <span class="text-white text-lg font-semibold"><?php echo htmlspecialchars($appt['phone']); ?></span>
                        </div>
                        <div class="p-4 bg-black/20 rounded-xl border border-white/10 w-fit">
                            <span class="text-xs font-bold text-red-200 uppercase block mb-1">Register Blood Type</span>
                            <span class="text-3xl font-black text-white"><?php echo htmlspecialchars($blood_display); ?></span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-8">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-red-200/80 uppercase tracking-wide">Appointment Location</span>
                            <span class="text-white text-lg font-semibold"><?php echo htmlspecialchars($appt['location']); ?></span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-red-200/80 uppercase tracking-wide">Scheduled Date</span>
                            <span class="text-white text-lg font-semibold"><?php echo $appt_date; ?></span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-medium text-red-200/80 uppercase tracking-wide">Appointment Time</span>
                            <span class="text-white text-2xl font-bold text-yellow-300"><?php echo $appt_time; ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-white/20 flex flex-col md:flex-row items-center justify-center gap-6 relative z-10">
                    <?php if (!$is_approved): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel?');">
                            <input type="hidden" name="cancel_appt_id" value="<?php echo $appt['id']; ?>">
                            <button type="submit" class="w-full md:w-auto px-10 py-3.5 rounded-xl border-2 border-red-300 text-red-100 font-bold hover:bg-red-900/50 transition">
                                Cancel Appointment
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="donor-home.php" class="w-full md:w-auto px-10 py-3.5 rounded-xl bg-[#580b0b] text-white font-bold text-lg shadow-lg hover:bg-[#420808] transition-transform hover:scale-105 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Back To Home
                    </a>
                </div>
            </div>

            <div class="w-full max-w-5xl mx-auto mt-8 mb-20">
                <div class="bg-[#B41919] rounded-2xl p-6 shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="text-red-200 text-sm font-medium">Medical Support Hotline</p>
                            <p class="text-xl font-bold text-white">(+84) 1900 1234</p>
                        </div>
                    </div>
                    <div class="h-px w-full md:w-px md:h-12 bg-white/20"></div>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-white/20 rounded-full text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-red-200 text-sm font-medium">Need help?</p>
                            <p class="text-xl font-bold text-white cursor-pointer hover:underline">Support Center</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

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
