<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logopusri">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="logincss">
</head>

<body class="flex h-screen bg-gray-100">
    <div class="loading-container" id="loadingContainer">
        <div class="loading-balls">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white relative">
        <div class="absolute top-0 w-full h-4 bg-blue-500"></div>
        <h2 class="text-2xl font-bold mb-8 mt-4">Login</h2>
        <form id="loginForm" action="proses_login" method="POST" class="w-1/2">
            <!-- Username Field -->
            <div class="mb-4 flex items-center border border-gray-300 rounded px-3 py-2">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.973 9.973 0 0112 16a9.973 9.973 0 016.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0zM12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                </svg>
                <input type="text" id="username" name="username" placeholder="Username" required class="w-full border-none focus:outline-none">
            </div>
            <!-- Username Error Message -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'user_not_found'): ?>
                <div class="text-red-500 text-sm mb-4">Username tidak ditemukan.</div>
            <?php endif; ?>

            <!-- Password Field -->
            <div class="mb-4 flex items-center border border-gray-300 rounded px-3 py-2 relative">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 0c-2.21 0-4-1.79-4-4S9.79 3 12 3s4 1.79 4 4-1.79 4-4 4z"></path>
                </svg>
                <input type="password" id="password" name="password" placeholder="Password" required class="w-full border-none focus:outline-none">
                <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 focus:outline-none">
                    <svg id="eye-icon" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.942 5 12 5c4.059 0 8.269 2.943 9.542 7-1.273 4.057-5.483 7-9.542 7-4.058 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>
            <!-- Password Error Message -->
            <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_password'): ?>
                <div class="text-red-500 text-sm mb-4">Password yang Anda masukkan salah.</div>
            <?php endif; ?>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
                Login
            </button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.942 5 12 5c4.059 0 8.269 2.943 9.542 7-1.273 4.057-5.483 7-9.542 7-4.058 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
    <div class="hidden lg:flex w-1/2 flex-col justify-center items-center bg-blue-600 relative">
        <img src="src/assets/images/pusri_logo/Pusri Logo Horizontal Light - LARGE.png" alt="Logo" class="w-1/2 mb-8">
        <div class="random-boxes box-1"></div>
        <div class="random-boxes box-2"></div>
        <div class="random-boxes box-3"></div>
        <div class="random-boxes box-4"></div>
    </div>
    <script src="loginjs"></script>
    <script>
        document.getElementById("loginForm").addEventListener("submit", function(event) {
            // Show the loading animation
            document.getElementById("loadingContainer").style.display = "flex";

            // Disable form submission to prevent multiple requests
            document.getElementById("loginForm").onsubmit = function() {
                return false;
            };

            // Simulate delay for loading
            setTimeout(function() {
                document.getElementById("loginForm").submit();
            }, 1000); // Adjust delay as needed
        });
    </script>
</body>

</html>