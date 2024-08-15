function openEditModal(id_pic, kode_pic, nama_pic) {
    document.getElementById('edit_id_pic').value = id_pic;
    document.getElementById('edit_kode_pic').value = kode_pic;
    document.getElementById('edit_nama_pic').value = nama_pic;
    document.getElementById('editModal').style.display = "block";
}

function closeEditModal() {
    document.getElementById('editModal').style.display = "none";
}