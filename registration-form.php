<?php
// connect to database
require 'header.php';

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
    <style>
        /* body and background color  */
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(-45deg, #654ea3, #141e30, #243b55, #302b63);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
        }

        /* background animation */
        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* form  */
        .signup-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            color: #ffffff;
        }

        /* table title */
        .signup-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #725AC1, #ff5252);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 25px;
        }

        /* input in normal */
        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 14px;
            color: #ffffff;
        }

        /* input when click  */
        .form-control:focus {
            background: rgba(0, 0, 0, 0.3);
            border-color: #a78bfa;
            box-shadow: 0 0 10px rgba(167, 139, 250, 0.3);
            color: #ffffff;
        }

        /* submit button  */
        button {
            position: relative;
            display: inline-block;
            margin: 15px;
            padding: 15px 30px;
            text-align: center;
            font-size: 18px;
            letter-spacing: 1px;
            text-decoration: none;
            color: #725AC1;
            background: transparent;
            cursor: pointer;
            transition: ease-out 0.5s;
            border: 2px solid #725AC1;
            border-radius: 10px;
            box-shadow: inset 0 0 0 0 #725AC1;
        }

        /* button hover effect  */
        button:hover {
            color: white;
            box-shadow: inset 0 -100px 0 0 #725AC1;
        }

        /* click effect  */
        button:active {
            transform: scale(0.9);
        }
    </style>
</head>

<body>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-6">

                <div class="signup-card">
                    <h1 class="signup-title text-center mb-2">Create Account</h1>
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
                        <button type="submit" class="btn-signup w-100 mt-4 ">Sign Up</button>
                    </form>
                </div>
            </div>
            <div
                class="d-flex justify-content-center align-items-center gap-5 mx-auto pt-3">
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