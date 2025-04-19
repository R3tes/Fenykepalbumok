function openPopup() {
    document.getElementById("uploadPopup").style.display = "block";
}

function closePopup() {
    document.getElementById("uploadPopup").style.display = "none";
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