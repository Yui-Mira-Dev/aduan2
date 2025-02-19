<div class="navbar bg-white flex items-center">
    <div class="dashboard-role font-bold text-lg" style="margin-left: 200px;"> <!-- Custom margin-left -->
        Dashboard <?php echo htmlspecialchars($_SESSION['role']); ?>
    </div>
    <div class="user-info relative ml-auto text-gray-900"> <!-- Added text-gray-900 for dark text -->
        <div class="username font-bold text-lg" id="toggleUserInfo">
            <?php echo htmlspecialchars($_SESSION['username']); ?> <i class="bi bi-person p-2 text-lg"></i>
        </div>
        <div class="dropdown-menu absolute right-0 mt-2 py-2 w-48 bg-white rounded-lg shadow-xl" id="dropdownMenu">
            <a href="logout" class="block px-4 py-2 text-gray-800 hover:bg-indigo-500 hover:text-gray">Logout</a>
        </div>
    </div>
</div>

<!-- sidebbar v1 -->
<div class="sidebar bg-blue-900 text-white h-screen fixed top-0 left-0 overflow-y-auto z-10">
    <div class="p-4 relative">
        <img src="https://sikp.pusri.co.id/static/media/logo-white.b054006e16dac76de809.png" alt="Logo" class="w-15 h-10 mx-auto">
        <button id="closeSidebar" class="absolute mt-2 top-4 right-0 focus:outline-none">
            <i class="bi bi-chevron-double-left text-lg"></i>
        </button>
    </div>
    <hr class="border-t-2 border-dotted border-white">
    <nav class="p-4">
        <ul>
            <li class="mb-4">
                <a href="dashboard?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                    <i class="bi bi-speedometer2 text-lg mr-2"></i> Dashboard
                </a>
            </li>
            <?php if ($_SESSION['role'] !== 'teknisi') : ?>
                <li class="mb-4">
                    <a href="koordinator?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                        <i class="bi bi-people-fill text-lg mr-2"></i> Koordinator
                    </a>
                </li>
                <li class="mb-4">
                    <a href="pic?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                        <i class="bi bi-person-fill text-lg mr-2"></i> PIC
                    </a>
                </li>
            <?php endif; ?>
            <li class="mb-4">
                <a href="completestatus?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                    <i class="bi bi-check-circle-fill text-lg mr-2"></i> Complete Status
                </a>
            </li>
            <?php if ($_SESSION['role'] !== 'teknisi' && $_SESSION['role'] !== 'deskjob') : ?>
                <li class="mb-4">
                    <a href="logs?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                        <i class="bi bi-journal-text text-lg mr-2"></i> Logs
                    </a>
                </li>
                <li class="mb-4">
                    <a href="register?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                        <i class="bi bi-person-add  text-lg mr-2"></i> Register
                    </a>
                </li>
            <?php endif; ?>
            <hr>
            <li class="mb-4 mt-4">
                <a href="about?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                    <i class="bi bi-exclamation-circle text-lg mr-2"></i> About
                </a>
            </li>
        </ul>
    </nav>
</div>
<!-- sidebbar v1 -->


<!-- sidebar v2 -->
<!-- <div class="sidebar bg-blue-900 text-white h-screen fixed top-0 left-0 overflow-y-auto z-10 w-44">
    <div class="p-4 relative">
        <img src="https://sikp.pusri.co.id/static/media/logo-white.b054006e16dac76de809.png" alt="Logo" class="w-15 h-10 mx-auto">
        <button id="closeSidebar" class="absolute mt-2 top-4 right-0 focus:outline-none">
            <i class="bi bi-chevron-double-left text-sm"></i>
        </button>
    </div>
    <hr class="border-t-2 border-dotted border-white">
    <nav class="p-2">
        <ul>
            <li class="mb-2">
                <button class="flex items-center justify-between w-full text-left" onclick="toggleSubMenu('dashboardSubMenu')">
                    <span class="flex items-center">
                        <i class="bi bi-speedometer2 text-sm mr-2"></i>
                        <span class="text-sm">Dashboard</span>
                    </span>
                    <i class="bi bi-chevron-down text-xs"></i>
                </button>
                <ul id="dashboardSubMenu" class="hidden pl-4 mt-1">
                    <li class="mb-1">
                        <a href="dashboard?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                            <i class="bi bi-house-door-fill text-sm mr-2"></i>
                            <span class="text-sm">Home</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] !== 'teknisi') : ?>
                        <li class="mb-1">
                            <a href="koordinator?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                                <i class="bi bi-people-fill text-sm mr-2"></i>
                                <span class="text-sm">Koordinator</span>
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="pic?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                                <i class="bi bi-person-fill text-sm mr-2"></i>
                                <span class="text-sm">PIC</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="mb-1">
                        <a href="completestatus?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                            <i class="bi bi-check-circle-fill text-sm mr-2"></i>
                            <span class="text-sm">Complete Status</span>
                        </a>
                    </li>
                </ul>
            </li>

            <?php if ($_SESSION['role'] !== 'teknisi') : ?>
                <li class="mb-2">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleSubMenu('managementSubMenu')">
                        <span class="flex items-center">
                            <i class="bi bi-person-circle text-sm mr-2"></i>
                            <span class="text-sm">Mgmt Pengguna</span>
                        </span>
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <ul id="managementSubMenu" class="hidden pl-4 mt-1">
                        <li class="mb-1">
                            <a href="logs?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                                <i class="bi bi-journal-text text-sm mr-2"></i>
                                <span class="text-sm">Logs</span>
                            </a>
                        </li>
                        <li class="mb-1">
                            <a href="register?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                                <i class="bi bi-person-add text-sm mr-2"></i>
                                <span class="text-sm">Register</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <hr class="border-t-2 border-dotted border-white">

            <li class="mb-2 mt-2">
                <a href="about?key=<?php echo urlencode($_SESSION['token']); ?>" class="flex items-center">
                    <i class="bi bi-exclamation-circle text-sm mr-2"></i>
                    <span class="text-sm">About</span>
                </a>
            </li>
        </ul>
    </nav>
</div> -->