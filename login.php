<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

       if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id']; // هذا السطر ضروري جداً
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: dashboard.php");
    exit;
} else {
            $error = "❌ Incorrect password or User not found" ;
        }
    } else {
        $error = "❌ Incorrect password or User not found";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #e0f7fa, #ffffff);
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      height: 100vh;

      /* تمركز كامل */
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      flex-direction: column;
    }

    .login-box {
      background: #fff;
      padding: 40px 30px;
      border-radius: 12px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      max-width: 400px;
      width: 90%;
      box-sizing: border-box;
      margin: 0 auto;
    }

    .login-box h2 {
      margin-bottom: 25px;
      color: #2c3e50;
      font-size: 1.8rem;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
    }

    .input-group input {
      width: 100%;
      padding: 12px 40px 12px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
      text-align: left; /* تخلي النصوص في الحقول تبدأ من اليسار */
    }

    .input-group input:focus {
      border-color: #2ecc71;
      outline: none;
    }

    .input-group .toggle-password {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      color: #999;
      cursor: pointer;
      user-select: none;
      font-size: 1.2rem;
    }

    button {
      width: 100%;
      padding: 14px;
      background: #2ecc71;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      font-size: 18px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #27ae60;
    }

    .error {
      background: #fdecea;
      color: #e74c3c;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 0.95rem;
      line-height: 1.3;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      body {
        height: 100vh;
      }

      .login-box {
        width: 90%;
        max-width: 360px;
        padding: 30px 20px;
        margin: 0 auto;
      }

      .login-box h2 {
        font-size: 1.5rem;
        margin-bottom: 20px;
      }

      .input-group input {
        font-size: 14px;
        padding: 10px 38px 10px 10px;
      }

      .input-group .toggle-password {
        font-size: 1rem;
        right: 10px;
      }

      button {
        font-size: 16px;
        padding: 12px;
      }

      .error {
        font-size: 0.9rem;
        padding: 8px;
      }
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>Login</h2>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="input-group">
        <input type="text" name="username" placeholder="Username" required autocomplete="username" />
      </div>

      <div class="input-group">
        <input type="password" name="password" placeholder="Password" id="password" required autocomplete="current-password" />
        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility" role="button" tabindex="0"></i>
      </div>

      <button type="submit">Login</button>
    </form>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("password");
      const icon = document.querySelector(".toggle-password");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }
  </script>

</body>
</html>
