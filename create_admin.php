<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <div class="admin-card">
        <div class="card-header text-center py-4">
            <h3><i class="fas fa-user-shield"></i> Create Admin Account</h3>
        </div>
        <div class="card-body p-4">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_admin'])) {
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "airport_management_system";
                
                $conn = mysqli_connect($servername, $username, $password, $dbname);
                
                if (!$conn) {
                    echo '<div class="alert alert-danger">Connection failed: ' . mysqli_connect_error() . '</div>';
                } else {
                    // Get form data
                    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_id']);
                    $admin_password = $_POST['password'];
                    $confirm_password = $_POST['confirm_password'];
                    $admin_name = mysqli_real_escape_string($conn, $_POST['admin_name']);
                    $admin_email = mysqli_real_escape_string($conn, $_POST['email']);
                    
                    // Validate inputs
                    $errors = [];
                    
                    if (empty($admin_id)) {
                        $errors[] = "Admin ID is required";
                    } elseif (strlen($admin_id) < 3) {
                        $errors[] = "Admin ID must be at least 3 characters";
                    }
                    
                    if (empty($admin_password)) {
                        $errors[] = "Password is required";
                    } elseif (strlen($admin_password) < 6) {
                        $errors[] = "Password must be at least 6 characters";
                    }
                    
                    if ($admin_password !== $confirm_password) {
                        $errors[] = "Passwords do not match";
                    }
                    
                    if (empty($admin_name)) {
                        $errors[] = "Admin name is required";
                    }
                    
                    // Check if admin ID already exists
                    $check_sql = "SELECT admin_id FROM admin WHERE admin_id = '$admin_id'";
                    $check_result = mysqli_query($conn, $check_sql);
                    
                    if (mysqli_num_rows($check_result) > 0) {
                        $errors[] = "Admin ID already exists. Please choose a different one.";
                    }
                    
                    if (empty($errors)) {
                        // Hash password
                        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                        
                        // Ensure admin table exists
                        $create_table_sql = "CREATE TABLE IF NOT EXISTS admin (
                            admin_id VARCHAR(50) PRIMARY KEY,
                            password VARCHAR(255) NOT NULL,
                            admin_name VARCHAR(100) NOT NULL,
                            email VARCHAR(100),
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            last_login TIMESTAMP NULL,
                            is_active BOOLEAN DEFAULT TRUE
                        )";
                        mysqli_query($conn, $create_table_sql);
                        
                        // Insert new admin
                        $insert_sql = "INSERT INTO admin (admin_id, password, admin_name, email) 
                                       VALUES ('$admin_id', '$hashed_password', '$admin_name', '$admin_email')";
                        
                        if (mysqli_query($conn, $insert_sql)) {
                            echo '<div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> <strong>Success!</strong> Admin account created successfully!
                                    <br><small>Admin ID: <strong>' . htmlspecialchars($admin_id) . '</strong></small>
                                  </div>';
                            echo '<a href="Adminloginpage.html" class="btn btn-primary btn-block">
                                    <i class="fas fa-sign-in-alt"></i> Go to Login Page
                                  </a>';
                        } else {
                            echo '<div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i> Error creating admin: ' . mysqli_error($conn) . '
                                  </div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">';
                        foreach ($errors as $error) {
                            echo '<li>' . htmlspecialchars($error) . '</li>';
                        }
                        echo '</ul></div>';
                    }
                    
                    mysqli_close($conn);
                }
            }
            ?>
            
            <?php if (!isset($_POST['create_admin']) || !empty($errors)): ?>
            <form method="POST" action="" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="admin_id"><i class="fas fa-id-badge"></i> Admin ID</label>
                    <input type="text" class="form-control" id="admin_id" name="admin_id" 
                           placeholder="Enter unique admin ID" required 
                           value="<?php echo isset($_POST['admin_id']) ? htmlspecialchars($_POST['admin_id']) : ''; ?>">
                    <small class="form-text text-muted">Must be at least 3 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_name"><i class="fas fa-user"></i> Admin Name</label>
                    <input type="text" class="form-control" id="admin_name" name="admin_name" 
                           placeholder="Enter full name" required
                           value="<?php echo isset($_POST['admin_name']) ? htmlspecialchars($_POST['admin_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email (Optional)</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter password" required>
                    <small class="form-text text-muted">Must be at least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Re-enter password" required>
                </div>
                
                <button type="submit" name="create_admin" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-plus"></i> Create Admin Account
                </button>
            </form>
            <?php endif; ?>
            
            <div class="mt-3 text-center">
                <a href="Adminloginpage.html" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
                <a href="Frontpage.html" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            const adminId = document.getElementById('admin_id').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (adminId.length < 3) {
                alert('Admin ID must be at least 3 characters');
                return false;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters');
                return false;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>

























