<?php
// home.php (index.php) - Dashboard with User Management CRUD
include 'config.php';
include 'helpers.php';
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user role
$user_role = $_SESSION['role'] ?? 'donor';

// Handle Insert/Add Donor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $full_name = mysqli_real_escape_string($link, $_POST['full_name']);
        $email = mysqli_real_escape_string($link, $_POST['email']);
        $phone = mysqli_real_escape_string($link, $_POST['phone']);
        $address = mysqli_real_escape_string($link, $_POST['address']);
        $blood_type = mysqli_real_escape_string($link, $_POST['blood_type'] ?? '');
        $blood_donation_date = mysqli_real_escape_string($link, $_POST['blood_donation_date'] ?? date('Y-m-d H:i:s'));

        // Validate required fields
        if (empty($full_name) || empty($email)) {
            $_SESSION['message'] = 'Full name and email are required!';
            $_SESSION['message_type'] = 'error';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }



        // Check for duplicate email
        $check_email_query = "SELECT id FROM user_profiles WHERE email = '$email'";
        $email_result = mysqli_query($link, $check_email_query);
        if (mysqli_num_rows($email_result) > 0) {
            $_SESSION['message'] = 'A donor with this email already exists!';
            $_SESSION['message_type'] = 'error';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }

        // Check for duplicate phone (if provided)
        if (!empty($phone)) {
            $check_phone_query = "SELECT id FROM user_profiles WHERE phone = '$phone'";
            $phone_result = mysqli_query($link, $check_phone_query);
            if (mysqli_num_rows($phone_result) > 0) {
                $_SESSION['message'] = 'A donor with this phone number already exists!';
                $_SESSION['message_type'] = 'error';
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
        }

        $insert_query = "INSERT INTO user_profiles (full_name, email, phone, address, blood_type, role, status, blood_donation_date)
                        VALUES ('$full_name', '$email', '$phone', '$address', '$blood_type', 'donor', 'active', '$blood_donation_date')";

        if (mysqli_query($link, $insert_query)) {
            $_SESSION['message'] = 'Donor added successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error adding donor: ' . mysqli_error($link);
            $_SESSION['message_type'] = 'error';
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get all donors
$users_query = "SELECT * FROM user_profiles ORDER BY blood_donation_date DESC";
$users_result = mysqli_query($link, $users_query);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <span class="logo-icon">🏥</span>
                    <span>DMS</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="#" class="nav-item">
                            <span class="nav-icon">📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-item" id="users-nav">
                            <span class="nav-icon">❤️</span>
                            <span>Donor Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-item">
                            <span class="nav-icon">⚙️</span>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <span class="logout-icon">🚪</span> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p>Donor Management System Dashboard</p>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="https://via.placeholder.com/40" alt="User" class="user-avatar">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                </div>
            </header>

            <!-- Messages -->
            <?php
            if (isset($_SESSION['message'])) {
                $type = $_SESSION['message_type'];
                $icon = $type == 'success' ? '✓' : '⚠';
                echo '<div class="alert alert-' . $type . '">
                    <span class="alert-icon">' . $icon . '</span>
                    ' . htmlspecialchars($_SESSION['message']) . '
                </div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>

            <!-- Content -->
            <div class="content-wrapper">
                <!-- Dashboard Stats -->
                <section class="dashboard-section">
                    <h2>Dashboard Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">❤️</div>
                            <div class="stat-content">
                                <h3><?php echo count($users); ?></h3>
                                <p>Total Donors</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✓</div>
                            <div class="stat-content">
                                <h3><?php echo count(array_filter($users, fn($u) => $u['status'] == 'active')); ?></h3>
                                <p>Active Donors</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-content">
                                <h3><?php echo date('Y-m-d'); ?></h3>
                                <p>Today's Date</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Add New Donor Form -->
                <?php if ($user_role === 'admin'): ?>
                <section class="dashboard-section">
                    <h2>Add New Donor</h2>
                    <div class="form-card">
                        <form action="" method="POST" class="add-user-form">
                            <input type="hidden" name="action" value="add">

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="full_name">
                                        👤 Full Name
                                    </label>
                                    <input type="text" id="full_name" name="full_name" required placeholder="Enter full name">
                                </div>
                                <div class="form-field">
                                    <label for="email">
                                        ✉️ Email
                                    </label>
                                    <input type="email" id="email" name="email" required placeholder="Enter email">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="phone">
                                        ☎️ Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number">
                                </div>
                                <div class="form-field">
                                    <label for="blood_type">
                                        🩸 Blood Type
                                    </label>
                                    <select id="blood_type" name="blood_type">
                                        <option value="">Select Blood Type</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="blood_donation_date">
                                        📅 Blood Donation Date
                                    </label>
                                    <input type="datetime-local" id="blood_donation_date" name="blood_donation_date"
                                           value="<?php echo date('Y-m-d\TH:i'); ?>">
                                </div>
                            </div>

                            <div class="form-field full-width">
                                <label for="address">
                                    📍 Address
                                </label>
                                <textarea id="address" name="address" rows="3" placeholder="Enter address"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                ➕ Add Donor
                            </button>
                        </form>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Donors Table -->
                <section class="dashboard-section">
                    <h2>Donor List</h2>
                    <div class="table-card">
                        <div class="table-responsive">
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Blood Type</th>
                                        <th>Status</th>
                                        <th>Donation Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (count($users) > 0) {
                                        foreach ($users as $index => $user) {
                                            $status_class = $user['status'] == 'active' ? 'badge-success' : 'badge-danger';
                                            ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge badge-blood"><?php echo htmlspecialchars($user['blood_type'] ?? 'N/A'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user['blood_donation_date'])); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if ($user_role === 'admin'): ?>
                                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                                            ✏️
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Are you sure?')" title="Delete">
                                                            🗑️
                                                        </a>
                                                        <?php else: ?>
                                                        <span class="btn-icon btn-view" title="View Only">
                                                            👁️
                                                        </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">No donors found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
