<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account - MONICOMLAB</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
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
  overflow-y: auto;
  padding: 20px 0;
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

.register-box {
  background: white;
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 450px;
  text-align: center;
  box-shadow: 0 0 20px rgba(0,0,0,0.2);
  margin: 150px auto 50px;
}

.register-box h2 {
  margin-bottom: 30px;
  color: #333;
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

.input-group input,
.input-group select {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 10px;
  font-size: 14px;
}

.input-group input:focus,
.input-group select:focus {
  outline: none;
  border-color: #2E4DBB;
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
  margin-top: 10px;
}

button:hover {
  background-color: #1c3a9d;
}

.login-link {
  margin-top: 20px;
  font-size: 14px;
  color: #666;
}

.login-link a {
  color: #2E4DBB;
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
  text-decoration: underline;
}

.error-message {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 5px;
  display: none;
}

.input-group.error input,
.input-group.error select {
  border-color: #e74c3c;
}

.input-group.error .error-message {
  display: block;
}

.help-text {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
  font-style: italic;
}
</style>
<body>
  <div class="background">
    <div class="logo-container">
      <img src="img/logo3.png" alt="Logo" class="logo">
    </div>

    <div class="register-box">
      <h2>CREATE ACCOUNT</h2>
      <form method="post" action="registerprocess.php" id="registerForm">
        <div class="input-group">
          <label><i class="fas fa-user"></i> Full Name</label>
          <input type="text" name="name" id="name" placeholder="Enter your full name" required>
          <span class="error-message">Please enter your full name</span>
        </div>

        <div class="input-group">
          <label><i class="fas fa-envelope"></i> Email</label>
          <input type="email" name="email" id="email" placeholder="Enter your email" required>
          <span class="error-message">Please enter a valid email</span>
        </div>

        <div class="input-group">
          <label><i class="fas fa-map-marker-alt"></i> Department/Location</label>
          <select name="department" id="department" required>
            <option value="">Select your department/location</option>
            <option value="IT Department">IT Department</option>
            <option value="Computer Lab 1">Computer Lab 1</option>
            <option value="Computer Lab 2">Computer Lab 2</option>
            <option value="Computer Lab 3">Computer Lab 3</option>
            <option value="Faculty Office">Faculty Office</option>
            <option value="Administration">Administration</option>
            <option value="Library">Library</option>
            <option value="Other">Other</option>
          </select>
          <p class="help-text">This helps us track where reports are coming from</p>
          <span class="error-message">Please select a department</span>
        </div>

        <div class="input-group">
          <label><i class="fas fa-building"></i> Building/Room (Optional)</label>
          <input type="text" name="location" id="location" placeholder="e.g., Building A, Room 201">
        </div>

        <div class="input-group">
          <label><i class="fas fa-lock"></i> Password</label>
          <input type="password" name="password" id="password" placeholder="Enter your password" required minlength="6">
          <p class="help-text">Must be at least 6 characters</p>
          <span class="error-message">Password must be at least 6 characters</span>
        </div>

        <div class="input-group">
          <label><i class="fas fa-lock"></i> Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
          <span class="error-message">Passwords do not match</span>
        </div>

        <button type="submit">CREATE ACCOUNT</button>
      </form>
      <div class="login-link">
        Already have an account? <a href="index.php">Login here</a>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      let isValid = true;
      
      // Reset errors
      document.querySelectorAll('.input-group').forEach(group => {
        group.classList.remove('error');
      });

      // Validate name
      const name = document.getElementById('name').value.trim();
      if (name.length < 2) {
        document.getElementById('name').closest('.input-group').classList.add('error');
        isValid = false;
      }

      // Validate email
      const email = document.getElementById('email').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        document.getElementById('email').closest('.input-group').classList.add('error');
        isValid = false;
      }

      // Validate department
      const department = document.getElementById('department').value;
      if (!department) {
        document.getElementById('department').closest('.input-group').classList.add('error');
        isValid = false;
      }

      // Validate password
      const password = document.getElementById('password').value;
      if (password.length < 6) {
        document.getElementById('password').closest('.input-group').classList.add('error');
        isValid = false;
      }

      // Validate confirm password
      const confirmPassword = document.getElementById('confirm_password').value;
      if (password !== confirmPassword) {
        document.getElementById('confirm_password').closest('.input-group').classList.add('error');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });

    // Real-time password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && password !== confirmPassword) {
        this.closest('.input-group').classList.add('error');
      } else {
        this.closest('.input-group').classList.remove('error');
      }
    });
  </script>
</body>
</html>
