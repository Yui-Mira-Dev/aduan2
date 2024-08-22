document.addEventListener('DOMContentLoaded', () => {
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    const sidebarLinks = sidebar.querySelectorAll('a');
    const content = document.getElementById('content');
    const loading = document.getElementById('loading');
    const toggleUserInfo = document.getElementById('toggleUserInfo');
    const dropdownMenu = document.getElementById('dropdownMenu');

    function toggleSidebar() {
        sidebar.classList.toggle('closed');
        contentWrapper.classList.toggle('ml-0');
        contentWrapper.classList.toggle('lg:ml-180'); 
        toggleSidebarBtn.style.display = sidebar.classList.contains('closed') ? 'block' : 'none';
    }

    toggleSidebarBtn.addEventListener('click', toggleSidebar);

    closeSidebarBtn.addEventListener('click', () => {
        sidebar.classList.add('closed');
        contentWrapper.classList.remove('lg:ml-180'); 
        contentWrapper.classList.add('ml-0');
        toggleSidebarBtn.style.display = 'block';
    });

    toggleUserInfo.addEventListener('click', () => {
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function(event) {
        const isClickInside = dropdownMenu.contains(event.target) || toggleUserInfo.contains(event.target);
        if (!isClickInside) {
            dropdownMenu.classList.remove('show');
        }
    });

    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('closed');
                contentWrapper.classList.add('ml-0');
                contentWrapper.classList.remove('lg:ml-180');
                toggleSidebarBtn.style.display = 'block';
            }
        });
    });

    function checkWindowWidth() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('closed');
            contentWrapper.classList.remove('ml-0');
            contentWrapper.classList.add('lg:ml-180'); 
            toggleSidebarBtn.style.display = 'none'; 
        }else {
            sidebar.classList.add('closed');
            contentWrapper.classList.add('ml-0');
            contentWrapper.classList.remove('lg:ml-180');
            toggleSidebarBtn.style.display = 'block';
        }
    }

    window.addEventListener('resize', checkWindowWidth);
    checkWindowWidth(); 
    
    setTimeout(() => {
        loading.classList.add('hidden');
        content.classList.remove('hidden');
        content.classList.add('fade-in');
    }, 1000);

});

function logout() {
    alert('Logging out...');
}

function toggleSubMenu(subMenuId) {
    const subMenu = document.getElementById(subMenuId);
    subMenu.classList.toggle('hidden');
}

