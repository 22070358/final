<?php
// doctor-home.php - Update: Bỏ icon Lightning, Đồng bộ Header & Sign Out
include 'config.php';
include 'connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Dr. Alice';
$role = $_SESSION['role'] ?? 'Specialist Doctor';
$current_month = date('F, Y');
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
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-4">
                <div class="flex-shrink-0">
                    <a href="doctor-home.php" class="text-2xl font-bold text-brand uppercase tracking-wide">B-DONOR</a>
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
                <a href="doctor-home.php" class="py-4 bg-nav-active border-b-4 border-brand-dark flex flex-col items-center gap-1">
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

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Information & Schedule</h1>
            <p class="text-gray-500 mt-1">View detailed information and shift schedule to book an appointment.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="h-24 bg-gradient-to-r from-red-500 to-red-400"></div>
                    <div class="px-6 pb-6 relative">
                        <div class="flex justify-between items-end -mt-12 mb-6">
                            <div class="flex items-end gap-4">
                                <div class="w-24 h-24 rounded-full border-4 border-white bg-gray-200 flex items-center justify-center overflow-hidden shadow-md">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div class="mb-1">
                                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($full_name); ?></h3>
                                    <p class="text-sm text-red-600 font-medium">Specialist Doctor</p>
                                </div>
                            </div>
                            <div class="hidden sm:flex bg-yellow-50 border border-yellow-100 p-1 rounded-md mb-1">
                                <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            </div>
                        </div>

                        <div class="mb-6 pl-4 border-l-4 border-red-200">
                            <p class="text-sm text-gray-600 italic">"Dedicated to saving lives through blood donation and patient care."</p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div class="p-2 bg-red-50 text-red-600 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div><p class="text-xs text-gray-400 font-bold uppercase">Staff ID</p><p class="text-sm font-semibold text-gray-800">DOC-3</p></div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div class="p-2 bg-red-50 text-red-600 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div><p class="text-xs text-gray-400 font-bold uppercase">Contact</p><p class="text-sm font-semibold text-gray-800">Update phone number</p></div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div class="p-2 bg-red-50 text-red-600 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div><p class="text-xs text-gray-400 font-bold uppercase">Email</p><p class="text-sm font-semibold text-gray-800">doctor1</p></div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition">
                                <div class="p-2 bg-red-50 text-red-600 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div><p class="text-xs text-gray-400 font-bold uppercase">Office</p><p class="text-sm font-semibold text-gray-800">Bach Mai Hospital</p></div>
                            </div>
                        </div>

                        <button class="mt-8 w-full bg-brand hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-md transition transform active:scale-95">Edit Information</button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 xl:col-span-8 h-full">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex flex-col h-full">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Work Schedule
                        </h3>
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <span class="px-3 text-sm font-semibold text-gray-700"><?php echo $current_month; ?></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 mb-2 text-center">
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Sun</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Mon</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Tue</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Wed</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Thu</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Fri</div>
                        <div class="text-xs font-bold text-gray-400 uppercase py-2">Sat</div>
                    </div>
                    <div class="grid grid-cols-7 gap-2 mb-6">
                        <?php for($i=27; $i<=30; $i++): ?>
                            <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 text-gray-300"><span class="text-sm font-bold"><?php echo $i; ?></span></div>
                        <?php endfor; ?>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">1</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div></div></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">2</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div></div></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">3</span></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">4</span></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">5</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div></div></div>
                        <div class="h-20 bg-brand text-white rounded-xl shadow-md transform scale-105 flex flex-col items-center justify-start py-2 cursor-pointer z-10"><span class="text-sm font-bold">6</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-white"></div><div class="w-1.5 h-1.5 rounded-full bg-white"></div></div></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">7</span></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">8</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div></div></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">9</span><div class="flex gap-1 mt-1"><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div><div class="w-1.5 h-1.5 rounded-full bg-red-500"></div></div></div>
                        <div class="h-20 border border-gray-100 rounded-xl flex flex-col items-center justify-start py-2 hover:border-red-200 transition cursor-pointer"><span class="text-sm font-bold text-gray-700">10</span></div>
                    </div>
                    <div class="mt-auto border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-bold text-gray-700 mb-3">Upcoming Shifts (Today)</h4>
                        <div class="flex gap-3">
                            <div class="flex items-center gap-2 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm font-medium border border-red-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> 08:00 - 12:00
                            </div>
                            <div class="flex items-center gap-2 bg-red-50 text-red-700 px-4 py-2 rounded-lg text-sm font-medium border border-red-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> 13:30 - 17:00
                            </div>
                        </div>
                    </div>
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
