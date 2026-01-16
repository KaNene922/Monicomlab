<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MONICOMLAB</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
    /* same as before, but update label and add spacing */

.input-group label {
  font-size: 14px;
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  font-weight: 600;
}

.input-group label i {
  margin-right: 8px;
  color: #555;
}
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', sans-serif;
}

body, html {
  height: 100%;
}

.background {
  background: url('img/bg.jpg') no-repeat center center/cover;
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}

.logo-container {
  position: absolute;
  top: 30px;
  left: 50px;
  display: flex;
  align-items: center;
}

.logo {
  height: 120px;
  margin-right: 10px;
}

.logo-text {
  color: white;
  font-size: 20px;
  font-weight: bold;
}

.login-box {
  background: white;
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 0 20px rgba(0,0,0,0.2);
}

.login-box h2 {
  margin-bottom: 30px;
}

.input-group {
  margin-bottom: 20px;
  text-align: left;
}

.input-group label {
  font-size: 14px;
  margin-bottom: 5px;
  display: block;
  font-weight: 600;
}

.input-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #2E4DBB;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

button:hover {
  background-color: #1c3a9d;
}

.signup-link {
  margin-top: 20px;
  font-size: 14px;
  color: #666;
}

.signup-link a {
  color: #2E4DBB;
  text-decoration: none;
  font-weight: 600;
}

.signup-link a:hover {
  text-decoration: underline;
}

</style>
<body>
  <div class="background">
    <div class="logo-container">
      <img src="img/logo3.png" alt="Logo" class="logo">
    </div>

    <div class="login-box">
      <h2>LOGIN</h2>
      <form method="post" action="loginprocess.php">
        <div class="input-group">
          <label><i class="fas fa-user"></i> Email</label>
          <input type="text" name="email" placeholder="Enter your email">
        </div>
        <div class="input-group">
          <label><i class="fas fa-lock"></i> Password</label>
          <input type="password" name="password" placeholder="Enter your password">
        </div>
        <button type="submit">LOGIN</button>
      </form>
      <div class="signup-link">
        Don't have an account? <a href="register.php">Create Account</a>
      </div>
    </div>
  </div>
</body>
</html>
