function openPopup() {
    closeAlbumPopup();
    document.getElementById("uploadPopup").style.display = "block";
}

function closePopup() {
    document.getElementById("uploadPopup").style.display = "none";
}

function openAlbumPopup() {
    closePopup();
    document.getElementById("albumPopup").style.display = "block";
}

function closeAlbumPopup() {
    document.getElementById("albumPopup").style.display = "none";
}

function uploadFile() {
    let fileInput = document.getElementById("fileInput");
    if (fileInput.files.length > 0) {
        alert("File uploaded: " + fileInput.files[0].name);
        closePopup();
    } else {
        alert("Please select a file to upload.");
    }
}

function openAddPhotosPopup() {

    document.getElementById("addPhotosPopup").style.display = "block";
}

function closeAddPhotosPopup() {
    document.getElementById("addPhotosPopup").style.display = "none";
}

function updateSelectedPhotos() {
    const selected = document.querySelectorAll('.photo-option.selected');
    const selectedIds = Array.from(selected).map(div => div.getAttribute('data-kepid'));
    document.getElementById('selectedPhotos').value = selectedIds.join(',');
}


function togglePhotoSelection(elem) {
    elem.classList.toggle("selected");
    const selected = [...document.querySelectorAll('.photo-option.selected')];
    const ids = selected.map(el => el.dataset.kepid);
    document.getElementById("selectedPhotos").value = ids.join(",");
}

function openDeletePhotoPopup() {
    document.getElementById('deletePhotoPopup').style.display = 'block';
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

function openDeleteAlbumPopup() {
    document.getElementById('deleteAlbumPopup').style.display = 'block';
}