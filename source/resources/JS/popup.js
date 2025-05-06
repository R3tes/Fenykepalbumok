// Function to open the upload popup
function openPopup() {
    openExclusivePopup("uploadPopup");
}
function closePopup() {
    document.getElementById("uploadPopup").style.display = "none";
}

// Function to open the album popup
function openAlbumPopup() {
    openExclusivePopup("albumPopup");
}
function closeAlbumPopup() {
    document.getElementById("albumPopup").style.display = "none";
}

// Function to open the add photos popup
function openAddPhotosPopup() {
    openExclusivePopup("addPhotosPopup");
}
function closeAddPhotosPopup() {
    document.getElementById("addPhotosPopup").style.display = "none";
}
function updateSelectedPhotos() {
    const selected = document.querySelectorAll('.photo-option.selected');
    const selectedIds = Array.from(selected).map(div => div.getAttribute('data-kepid'));
    document.getElementById('selectedPhotos').value = selectedIds.join(',');
}

// Function to open the edit photo popup
function togglePhotoSelection(elem) {
    elem.classList.toggle("selected");
    const selected = [...document.querySelectorAll('.photo-option.selected')];
    const ids = selected.map(el => el.dataset.kepid);
    document.getElementById("selectedPhotos").value = ids.join(",");
}

// Function to toggle the selection of a photo for deletion
function openDeletePhotoPopup() {
    openExclusivePopup("deletePhotoPopup");
}
function closeDeletePhotoPopup() {
    document.getElementById('deletePhotoPopup').style.display = 'none';
}
function updateSelectedPhotosForDeletion() {
    const selected = document.querySelectorAll('.photo-del-option.selected');
    const ids = Array.from(selected).map(div => div.getAttribute('data-kepid'));
    document.getElementById('selectedPhotos').value = ids.join(',');

    return true;
}

// Function to open the delete album popup
function openDeleteAlbumPopup() {
    openExclusivePopup("deleteAlbumPopup");
}

function closeDeleteAlbumPopup() {
    document.getElementById('deleteAlbumPopup').style.display = 'none';
}
function toggleAlbumSelection(element) {
    element.classList.toggle("selected");
    const selected = [...document.querySelectorAll('.album-del-option.selected')];
    const ids = selected.map(el => el.dataset.albumid);
    document.getElementById("selectedAlbums").value = ids.join(",");
}

// Functions to open and close all popups
function closeAllPopups() {
    document.getElementById("uploadPopup").style.display = "none";
    document.getElementById("albumPopup").style.display = "none";
    document.getElementById("addPhotosPopup").style.display = "none";
    document.getElementById("deletePhotoPopup").style.display = "none";
    document.getElementById("deleteAlbumPopup").style.display = "none";
}
function openExclusivePopup(popupId) {
    document.querySelectorAll('.popup').forEach(p => p.style.display = 'none');
    document.getElementById(popupId).style.display = 'block';
}


function openAlbumEditPopup() {
    document.getElementById('editAlbumPopup').style.display = 'block';
}
function closeAlbumEditPopup() {
    document.getElementById('editAlbumPopup').style.display = 'none';
}
function toggleAlbumEditPhotoSelection(elem) {
    elem.classList.toggle('selected');
    const selected = [...document.querySelectorAll('.photo-album-del-option.selected')];
    const ids = selected.map(el => el.dataset.kepid);
    document.getElementById('deleteFromAlbum').value = ids.join(',');
}
function updateAlbumEditForm() {
    const selected = document.querySelectorAll('.photo-album-del-option.selected');
    const ids = Array.from(selected).map(el => el.getAttribute('data-kepid'));
    document.getElementById('deleteFromAlbum').value = ids.join(',');

    return true;
}

function openErtesitesPopup() {
    openExclusivePopup("ertesitesPopup");
}
function markAsRead(ertesitesId) {
    fetch('mark_ertesites_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(ertesitesId)
    })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'OK') {
                const elem = document.getElementById('ertesites-' + ertesitesId);
                if (elem) elem.remove();
            }
        });
}
function closeErtesitesPopup() {
    document.getElementById("ertesitesPopup").style.display = "none";
    location.reload(); // frissíti az oldalt a popup bezárásakor
}