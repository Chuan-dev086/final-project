<?php
// connect to database
require './includes/header.php';

// get the form data with post method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // make sure the input of password and confirmed passowrd same
    // if not will return back to registration page
    if ($password !== $confirm_password) {
        echo "<script>alert('Password and Confirm Password does not match!'); history.back();</script>";
        exit;
    }
    // SQL query to insert the data to DB 
    $query = 'INSERT INTO users(username, email, password, role) VALUES(:username, :email, :password, :role)';

    //  password hashing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // insert data to database and redirected to login page
    $stmt = $db->prepare($query);
    $stmt->execute([
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $hashedPassword,
        'role' => $_POST['role']
    ]);

    echo "<script>alert('Successfully Registered!'); 
    window.location.href='login-form.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration-Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" /> 
    <link rel="stylesheet" href="./css/registration-form.css">
 
</head>

<body>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-6">

                <div class="signup-card">
                    <h1 class="mb-2 text-center signup-title">Create Account</h1>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        </div>
                        <input type="hidden" name="role" value="User">
                        <button type="submit" class="mt-4 btn-signup w-100 ">Sign Up</button>
                    </form>
                </div>
            </div>
            <div
                class="d-flex gap-5 pt-3 justify-content-center align-items-center mx-auto">
                <a href="login-form.php" class="text-decoration-none small">Already have an account? Login here
                    <i class="bi bi-arrow-right-circle"></i></a>
            </div>
        </div>
    </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
</body>

</html>