document.addEventListener('DOMContentLoaded', () => {
    const addUserModal = document.getElementById('addUserModal');
    const editUserModal = document.getElementById('editUserModal');
    const editPasswordModal = document.getElementById('editPasswordModal');
    const closeAddUserModal = document.getElementById('closeAddUserModal');
    const closeEditUserModal = document.getElementById('closeEditUserModal');
    const closeEditPasswordModal = document.getElementById('closeEditPasswordModal');

    document.getElementById('openAddUserModal').addEventListener('click', () => {
        addUserModal.classList.remove('hidden');
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', () => {
            const userId = button.dataset.userId;
            const username = button.dataset.username;
            const email = button.dataset.email;
            const role = button.dataset.role;

            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;

            editUserModal.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.edit-password-btn').forEach(button => {
        button.addEventListener('click', () => {
            const userId = button.dataset.userId;

            document.getElementById('editPasswordUserId').value = userId;

            editPasswordModal.classList.remove('hidden');
        });
    });

    closeAddUserModal.addEventListener('click', () => {
        addUserModal.classList.add('hidden');
    });

    closeEditUserModal.addEventListener('click', () => {
        editUserModal.classList.add('hidden');
    });

    closeEditPasswordModal.addEventListener('click', () => {
        editPasswordModal.classList.add('hidden');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        // Fade out the message after 3 seconds
        setTimeout(() => {
            flashMessage.classList.add('opacity-0');
            setTimeout(() => {
                flashMessage.remove();
            }, 300); // Match this duration with the CSS transition
        }, 3000);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Handle delete button click
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', () => {
            const userId = button.dataset.userId;
            if (confirm('Are you sure you want to delete this user?')) {
                // Create a form dynamically to submit the deletion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Current page

                // Add action input
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'deleteUser';
                form.appendChild(actionInput);

                // Add user id input
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'id_user';
                userIdInput.value = userId;
                form.appendChild(userIdInput);

                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

function toggleSubMenu(subMenuId) {
    const subMenu = document.getElementById(subMenuId);
    subMenu.classList.toggle('hidden');
}