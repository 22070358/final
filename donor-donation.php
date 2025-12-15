<?php
include 'config.php';
include 'connection.php';

// Chỉ cho phép Donor vào
requireRole('Donor');



$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Lấy thông tin mặc định từ Profile để điền sẵn (Pre-fill)
$sql_info = "SELECT * FROM donor_profiles WHERE userId = $user_id";
$res_info = mysqli_query($link, $sql_info);
$donor_info = mysqli_fetch_assoc($res_info);

// Lấy email từ bảng users
$res_u = mysqli_query($link, "SELECT email, name FROM users WHERE id = $user_id");
$row_u = mysqli_fetch_assoc($res_u);

// Gán biến hiển thị
$default_name = $row_u['name'] ?? $username;
$default_email = $row_u['email'] ?? '';
$default_phone = $donor_info['phone'] ?? '';
$default_dob = $donor_info['dateOfBirth'] ?? '';
$default_blood = ($donor_info['bloodType'] ?? '') . ($donor_info['rhType'] == 'Positive' ? '+' : ($donor_info['rhType'] == 'Negative' ? '-' : ''));

// --- XỬ LÝ FORM SUBMIT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    // 1. Lấy dữ liệu từ Form nhập (Không lấy từ DB nữa)
    $post_name = mysqli_real_escape_string($link, $_POST['fullname']);
    $post_email = mysqli_real_escape_string($link, $_POST['email']);
    $post_phone = mysqli_real_escape_string($link, $_POST['phone']);
    $post_blood = mysqli_real_escape_string($link, $_POST['blood_group']);
    
    $appt_date = $_POST['appointment_date'];
    $location = mysqli_real_escape_string($link, $_POST['location']);
    $notes = mysqli_real_escape_string($link, $_POST['notes']);
    
    // 2. Insert vào bảng appointments
    $sql_insert = "INSERT INTO appointments (userId, name, email, phone, appointmentDate, bloodType, location, notes, status, createdAt) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    
    if ($stmt = mysqli_prepare($link, $sql_insert)) {
        // Giờ mặc định là 8h sáng
        $appt_datetime = $appt_date . ' 08:00:00'; 

        mysqli_stmt_bind_param($stmt, "isssssss", $user_id, $post_name, $post_email, $post_phone, $appt_datetime, $post_blood, $location, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Appointment registered successfully!'); window.location.href='donor-donation.php';</script>";
        } else {
            echo "<script>alert('Error registering: " . mysqli_error($link) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Donation - B-DONOR</title>
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
            <div class="flex justify-between items-center h-16 gap-8">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <a href="donor-home.php" class="text-2xl font-bold text-brand tracking-wide uppercase">B-DONOR</a>
                </div>
                <div class="hidden md:flex flex-1 max-w-lg">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-full leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-brand focus:border-brand sm:text-sm" placeholder="Search for...">
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button class="text-gray-400 hover:text-gray-600 relative">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>
                    
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <div class="h-9 w-9 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </div>
                            <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($default_name); ?></span>
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
                
                <a href="donor-donation.php" class="py-4 bg-nav-active border-b-4 border-brand-dark flex flex-col items-center gap-1">
                    <div class="bg-white text-brand p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
                    <span class="text-sm font-bold text-gray-900">Register Appointment</span>
                </a>

                <a href="donor-notification.php" class="py-4 hover:bg-brand-dark transition group flex flex-col items-center gap-1">
                    <div class="bg-white/20 p-2 rounded-full group-hover:bg-white/30"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                    <span class="text-sm font-medium">Notification</span>
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

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <div class="flex items-center gap-3 mb-8">
            <svg class="w-8 h-8 text-brand" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12 2 5 11 5 16C5 19.866 8.13401 23 12 23C15.866 23 19 19.866 19 16C19 11 12 2 12 2Z"/></svg>
            <h1 class="text-3xl font-bold text-brand">Register blood donation appointment</h1>
        </div>

        <form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <input type="hidden" name="action" value="register">

            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Donor Information</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($default_name); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white" required>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($default_email); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($default_phone); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Date of Birth</label>
                            <input type="date" name="dob" value="<?php echo htmlspecialchars($default_dob); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Blood Type</label>
                            <select name="blood_group" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white">
                                <option value="" <?php echo $default_blood == '' ? 'selected' : ''; ?>>Unknown</option>
                                <option value="A+" <?php echo $default_blood == 'A+' ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo $default_blood == 'A-' ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo $default_blood == 'B+' ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo $default_blood == 'B-' ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo $default_blood == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo $default_blood == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo $default_blood == 'O+' ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo $default_blood == 'O-' ? 'selected' : ''; ?>>O-</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Schedule Details</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Appointment Date</label>
                        <div class="relative">
                            <input type="date" name="appointment_date" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none cursor-pointer" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Location</label>
                        <select name="location" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none bg-white appearance-none" required>
                            <option value="">Select donation site...</option>
                            <option value="Main Center">Main Donation Center (Hanoi)</option>
                            <option value="Mobile Unit 1">Mobile Unit 1</option>
                            <option value="City Hospital">City Hospital</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Notes</label>
                        <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-800 focus:ring-2 focus:ring-brand focus:border-transparent outline-none resize-none" placeholder="Additional notes (optional)..."></textarea>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-center gap-4 mt-4">
                <button type="submit" class="bg-brand hover:bg-red-700 text-white px-10 py-3.5 rounded-lg font-semibold shadow-md transition w-full sm:w-auto">Register</button>
                <button type="reset" class="bg-gray-800 hover:bg-gray-900 text-white px-10 py-3.5 rounded-lg font-semibold shadow-md transition w-full sm:w-auto">Cancel</button>
            </div>
        </form>
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
