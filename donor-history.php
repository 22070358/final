<?php
include 'config.php';
include 'connection.php';

// Chỉ cho phép Donor vào
requireRole('Donor');

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Donor';
$current_avatar = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=random&color=fff";

// --- XỬ LÝ HỦY LỊCH ---\
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);
    // Chỉ cho hủy nếu trạng thái là Pending
    $check_sql = "SELECT id FROM appointments WHERE id = $cancel_id AND userId = $user_id AND status = 'Pending'";
    $check_res = mysqli_query($link, $check_sql);
    
    if (mysqli_num_rows($check_res) > 0) {
        $update_sql = "UPDATE appointments SET status = 'Cancelled' WHERE id = $cancel_id";
        if (mysqli_query($link, $update_sql)) {
            echo "<script>alert('Appointment cancelled successfully.'); window.location.href='donor-history.php';</script>";
        }
    } else {
        echo "<script>alert('Cannot cancel this appointment (It may be completed or already cancelled).'); window.location.href='donor-history.php';</script>";
    }
    exit();
}

// --- LẤY DỮ LIỆU LỊCH SỬ ---
$sql = "SELECT * FROM appointments WHERE userId = $user_id ORDER BY appointmentDate DESC, appointmentTime DESC";
$result = mysqli_query($link, $sql);
$history_list = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Format lại dữ liệu hiển thị
        $row['date_fmt'] = date('d/m/Y', strtotime($row['appointmentDate']));
        $row['time_fmt'] = date('H:i', strtotime($row['appointmentTime']));
        
        // Màu sắc trạng thái
        if ($row['status'] == 'Completed') {
            $row['status_class'] = 'bg-green-100 text-green-700 border-green-200';
        } elseif ($row['status'] == 'Cancelled') {
            $row['status_class'] = 'bg-gray-100 text-gray-500 border-gray-200';
        } else {
            $row['status_class'] = 'bg-yellow-100 text-yellow-700 border-yellow-200';
        }
        
        $history_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History - B-DONOR</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex flex-col">

    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-4">
                <div class="flex-shrink-0">
                    <a href="donor-home.php" class="text-2xl font-bold text-brand uppercase tracking-wide">B-DONOR</a>
                </div>
                <div class="flex items-center gap-4 text-gray-400">
                    <div class="relative">
                        <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                            <img src="<?php echo $current_avatar; ?>" class="w-8 h-8 rounded-full border border-gray-200">
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

    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="donor-home.php" class="py-4 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 transition">Dashboard</a>
                <a href="donor-history.php" class="py-4 px-1 border-b-2 border-brand text-sm font-bold text-brand">Donation History</a>
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Appointment History</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4">Date & Time</th>
                            <th class="px-6 py-4">Location</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (count($history_list) > 0): ?>
                            <?php foreach ($history_list as $item): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900"><?php echo $item['date_fmt']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $item['time_fmt']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    Center A (Hanoi)
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border <?php echo $item['status_class']; ?>">
                                        <?php echo $item['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <button onclick='openModal(<?php echo json_encode($item); ?>)' class="text-gray-400 hover:text-brand p-1.5 rounded-full transition" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        
                                        <?php if ($item['status'] == 'Pending'): ?>
                                            <a href="donor-history.php?cancel_id=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to cancel this appointment?')" class="text-red-400 hover:text-red-600 p-1.5 rounded-full transition" title="Cancel Appointment">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="detailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0" id="detailModalContent">
            <div class="bg-brand px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-lg font-bold">Appointment Details</h3>
                <button onclick="closeModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-1.5 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Appointment ID</span>
                    <span class="font-mono font-bold text-gray-800" id="m_id">#000</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Status</span>
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold" id="m_status">Pending</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Date</span>
                    <span class="font-medium text-gray-800" id="m_date">--/--/----</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Time</span>
                    <span class="font-medium text-gray-800" id="m_time">--:--</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Blood Type</span>
                    <span class="font-bold text-brand" id="m_blood">--</span>
                </div>
                 <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-gray-500 text-sm">Location</span>
                    <span class="font-medium text-gray-800">Center A (Hanoi)</span>
                </div>
                 <div class="flex justify-between">
                    <span class="text-gray-500 text-sm">Phone</span>
                    <span class="font-medium text-gray-800" id="m_phone">--</span>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm transition">Close</button>
            </div>
        </div>
    </div>

    <script>
        // User Menu Logic
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

        // --- MODAL LOGIC (MỚI) ---
        const modal = document.getElementById('detailModal');
        const modalContent = document.getElementById('detailModalContent');

        function openModal(data) {
            // Fill data into modal
            document.getElementById('m_id').innerText = '#' + data.id;
            document.getElementById('m_date').innerText = data.date_fmt;
            document.getElementById('m_time').innerText = data.time_fmt;
            document.getElementById('m_blood').innerText = data.bloodType || 'N/A';
            document.getElementById('m_phone').innerText = data.phone || 'N/A';
            
            const statusEl = document.getElementById('m_status');
            statusEl.innerText = data.status;
            
            // Reset classes
            statusEl.className = 'px-2.5 py-0.5 rounded text-xs font-bold border';
            if(data.status === 'Completed') statusEl.classList.add('bg-green-100', 'text-green-700', 'border-green-200');
            else if(data.status === 'Cancelled') statusEl.classList.add('bg-gray-100', 'text-gray-500', 'border-gray-200');
            else statusEl.classList.add('bg-yellow-100', 'text-yellow-700', 'border-yellow-200');

            // Show Modal
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Close when clicking outside
        modal.addEventListener('click', (e) => {
            if(e.target === modal) closeModal();
        });
    </script>

</body>
</html>
