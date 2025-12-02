<?php
// edit.php - Edit user information
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
$user_role = $_SESSION['role'] ?? 'donor';
if ($user_role !== 'admin') {
    header('Location: home.php');
    exit();
}

// Check if id is provided
if (!isset($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Get user data
$user_query = "SELECT * FROM user_profiles WHERE id = $user_id";
$user_result = mysqli_query($link, $user_query);

if (mysqli_num_rows($user_result) == 0) {
    header('Location: home.php');
    exit();
}

$user = mysqli_fetch_assoc($user_result);

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $full_name = mysqli_real_escape_string($link, $_POST['full_name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $address = mysqli_real_escape_string($link, $_POST['address']);
    $blood_type = mysqli_real_escape_string($link, $_POST['blood_type'] ?? '');
    $status = mysqli_real_escape_string($link, $_POST['status']);
    $blood_donation_date = mysqli_real_escape_string($link, $_POST['blood_donation_date']);
    
    $update_query = "UPDATE user_profiles SET 
                    full_name = '$full_name',
                    email = '$email',
                    phone = '$phone',
                    address = '$address',
                    blood_type = '$blood_type',
                    status = '$status',
                    blood_donation_date = '$blood_donation_date'
                    WHERE id = $user_id";
    
    if (mysqli_query($link, $update_query)) {
        $_SESSION['message'] = 'Donor updated successfully!';
        $_SESSION['message_type'] = 'success';
        header('Location: home.php');
        exit();
    } else {
        $error_message = 'Error updating donor!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor - Donor Management System</title>
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
                    <li>
                        <a href="home.php" class="nav-item">
                            <span class="nav-icon">📊</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="home.php" class="nav-item">
                            <span class="nav-icon">❤️</span>
                            <span>Donor Management</span>
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
                    <h1>Edit Donor</h1>
                    <p>Update donor information</p>
                </div>
                <div class="header-right">
                    <a href="home.php" class="btn btn-secondary">
                        ⬅️ Back
                    </a>
                </div>
            </header>

            <!-- Error Message -->
            <?php
            if (isset($error_message)) {
                echo '<div class="alert alert-error">
                    ⚠️
                    ' . htmlspecialchars($error_message) . '
                </div>';
            }
            ?>

            <!-- Content -->
            <div class="content-wrapper">
                <section class="dashboard-section">
                    <div class="form-card">
                        <form action="" method="POST" class="edit-user-form">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="form-row">
                                <div class="form-field">
                                    <label for="full_name">
                                        👤 Full Name
                                    </label>
                                    <input type="text" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                </div>
                                <div class="form-field">
                                    <label for="email">
                                        ✉️ Email
                                    </label>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="phone">
                                        ☎️ Phone
                                    </label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-field">
                                    <label for="blood_type">
                                        🩸 Blood Type
                                    </label>
                                    <select id="blood_type" name="blood_type">
                                        <option value="">Select Blood Type</option>
                                        <option value="O+" <?php echo $user['blood_type'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo $user['blood_type'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                                        <option value="A+" <?php echo $user['blood_type'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo $user['blood_type'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo $user['blood_type'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo $user['blood_type'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo $user['blood_type'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo $user['blood_type'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-field full-width">
                                <label for="address">
                                    📍 Address
                                </label>
                                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="status">
                                        ✅ Status
                                    </label>
                                    <select id="status" name="status" required>
                                        <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label for="blood_donation_date">
                                        📅 Blood Donation Date
                                    </label>
                                    <input type="datetime-local" id="blood_donation_date" name="blood_donation_date" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($user['blood_donation_date'])); ?>" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    💾 Update Donor
                                </button>
                                <a href="home.php" class="btn btn-secondary">
                                    ❌ Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
