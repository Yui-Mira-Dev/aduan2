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
            <div class="mb-4 flex items-center border border-gray-300 rounded px-3 py-2">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.973 9.973 0 0112 16a9.973 9.973 0 016.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0zM12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                </svg>
                <input type="text" id="username" name="username" placeholder="Username" required class="w-full border-none focus:outline-none">
            </div>
            <div class="mb-6 flex items-center border border-gray-300 rounded px-3 py-2 relative">
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
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">
                Login
            </button>
        </form>
    </div>
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