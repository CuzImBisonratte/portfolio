const mediaManager = {
    element: document.getElementById('mediamanager'),
    open: () => {
        mediaManager.element.style.display = 'grid';
        mediaManager.element.addEventListener("click", (e) => {
            if (e.target === mediaManager.element) {
                mediaManager.close();
            }
        });
    },
    submitUpload: (f) => {
        document.getElementById('upload-spinner').style.display = 'grid';
        // Check if files are too large
        const files = f.files;
        let sum = 0;
        for (let i = 0; i < files.length; i++) sum += files[i].size;
        if (sum > MAX_UPLOAD_SIZE) {
            alert('Files are too large, please upload files with a total size of ' + MAX_UPLOAD_SIZE / 1000000 + 'MB or less');
            document.getElementById('upload-spinner').style.display = 'none';
            return;
        }
        // Check for maximum number of files
        if (files.length > MAX_UPLOAD_FILES) {
            alert('Maximum number of files exceeded, maximum is ' + MAX_UPLOAD_FILES);
            document.getElementById('upload-spinner').style.display = 'none';
            return;
        }
        // Upload files
        f.form.submit();
    },
    close: () => {
        mediaManager.element.style.display = 'none';
    }
};