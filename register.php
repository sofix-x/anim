<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/css/log-reg.css">
    <style>
        .password-error-popup {
            display: none; /* Hidden by default */
            position: absolute;
            background-color: #f8d7da; /* Light red background */
            color: #721c24; /* Dark red text */
            border: 1px solid #f5c6cb;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 100;
            font-size: 0.9em;
            margin-left: 10px; /* Adjust as needed to position next to the input */
            white-space: nowrap;
        }
        .form-group {
            position: relative; /* Needed for absolute positioning of the popup */
            margin-bottom: 20px; /* Ensure space for popup if it appears below */
        }
    </style>
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="login.php">Войти</a></li>
        </ul>
    </nav>
</header>

<main>
    <section class="form-section">
        <h2>Регистрация</h2>
        <form action="assets/vendor/signup.php" method="post" id="registrationForm">
        <div class="form-group">
            <label for="username">Логин:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Повторите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span id="password_error" class="password-error-popup">пароли не совпадают!</span>
        </div>
        <button type="submit" class="submit-button">Зарегистрироваться</button>
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2024 Интернет-каталог товаров</p>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const registrationForm = document.getElementById('registrationForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordErrorPopup = document.getElementById('password_error');

        function validatePasswords() {
            if (passwordInput.value !== confirmPasswordInput.value && confirmPasswordInput.value !== '') {
                passwordErrorPopup.style.display = 'inline-block'; // Show error
                // Position popup next to the confirm_password input
                const inputRect = confirmPasswordInput.getBoundingClientRect();
                const formRect = registrationForm.getBoundingClientRect(); // or a closer parent
                
                // Calculate position relative to the form or a common ancestor
                // This might need adjustment based on your exact layout and CSS
                passwordErrorPopup.style.top = (inputRect.top - formRect.top + (inputRect.height / 2) - (passwordErrorPopup.offsetHeight / 2)) + 'px';
                passwordErrorPopup.style.left = (inputRect.right - formRect.left + 10) + 'px'; // 10px offset from the right of input

            } else {
                passwordErrorPopup.style.display = 'none'; // Hide error
            }
        }

        // Validate on input in confirm_password field
        confirmPasswordInput.addEventListener('input', validatePasswords);
        // Also validate on input in password field if confirm_password has a value
        passwordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value !== '') {
                validatePasswords();
            }
        });

        registrationForm.addEventListener('submit', function(event) {
            validatePasswords(); // Run validation one last time
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault(); // Prevent form submission
                // Ensure popup is visible and positioned
                passwordErrorPopup.style.display = 'inline-block';
                const inputRect = confirmPasswordInput.getBoundingClientRect();
                const formRect = registrationForm.getBoundingClientRect();
                passwordErrorPopup.style.top = (inputRect.top - formRect.top + (inputRect.height / 2) - (passwordErrorPopup.offsetHeight / 2)) + 'px';
                passwordErrorPopup.style.left = (inputRect.right - formRect.left + 10) + 'px';
                confirmPasswordInput.focus(); // Focus on the field with error
            }
        });

        // Optional: Hide popup if user clicks away or corrects the field
        document.addEventListener('click', function(event) {
            if (!passwordErrorPopup.contains(event.target) && event.target !== confirmPasswordInput && event.target !== passwordInput) {
                if (passwordInput.value === confirmPasswordInput.value || confirmPasswordInput.value === '') {
                     passwordErrorPopup.style.display = 'none';
                }
            }
        });
         // Re-position on window resize
        window.addEventListener('resize', () => {
            if (passwordErrorPopup.style.display === 'inline-block') {
                validatePasswords(); // This will re-calculate and re-apply position
            }
        });
    });
</script>

</body>
</html>
