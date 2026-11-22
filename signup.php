<?php include 'db.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prevent duplicate username
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        header("Location: login.php?registered=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Join Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
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

    <div class="glass w-full max-w-md rounded-2xl p-10">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Create Account</h1>
            <p class="text-gray-500 mt-2">Join our community today</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm animate-pulse">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Choose Username</label>
                <input type="text" name="username" placeholder="Enter username" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Choose Password</label>
                <input type="password" name="password" placeholder="••••••••" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none transition-all">
            </div>

            <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-purple-500/30">
                Get Started
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <p class="text-gray-600">Already have an account? 
                <a href="login.php" class="text-purple-600 font-semibold hover:underline">Login here</a>
            </p>
        </div>
    </div>

</body>
</html>