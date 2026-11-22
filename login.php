<?php include 'db.php'; ?>

<?php
// Ensure session is started in db.php or here
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed);
        $stmt->fetch();

        if ($hashed && password_verify($password, $hashed)) {
            $_SESSION['user'] = $id;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Login | Welcome Back</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-md rounded-2xl p-10 transition-all duration-300">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Welcome Back</h1>
            <p class="text-gray-500 mt-2">Please enter your details to sign in</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" name="username" placeholder="Enter username" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" placeholder="••••••••" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-indigo-500/30">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-gray-600">Don't have an account? 
                <a href="signup.php" class="text-indigo-600 font-semibold hover:underline">Create Account</a>
            </p>
        </div>
    </div>

</body>
</html>