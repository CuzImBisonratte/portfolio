const mediaManager = {
    element: document.getElementById('mediamanager'),
    changingImage: null,
    open: (forSelection) => {
        if (forSelection) mediaManager.element.classList.add('forSelection');
        mediaManager.element.style.display = 'grid';
    },
    submitUpload: async (f) => {
        document.getElementById('upload-spinner').style.display = 'grid';
        // Check if files are too large
        const files = f.files;
        for (let i = 0; i < files.length; i++) if (files[i].size > MAX_UPLOAD_SIZE) {
            alert('File ' + files[i].name + ' is too large, maximum is ' + MAX_UPLOAD_SIZE / 1000000 + 'MB');
            document.getElementById('upload-spinner').style.display = 'none';
            return;
        }
        let uploadsFinished = 0;
        // Upload every file 
        for (let i = 0; i < files.length; i++) {
            const formData = new FormData();
            formData.append('file', files[i]);
            fetch(`/admin/php/mediaManagerUpload.php?page=${PAGE}`, {
                method: 'POST',
                body: formData
            }).then(response => {
                if (++uploadsFinished === files.length) { // Last file uploaded
                    document.getElementById('upload-spinner').style.display = 'none';
                    location.href = location.href;
                }
            }).catch(error => {
                console.error('Error:', error);
            });
            console.log('Uploading: ', i);
        }
    },
    close: () => {
        mediaManager.element.style.display = 'none';
        mediaManager.element.classList.remove('forSelection');
    },
    click: async (img) => {
        if (!mediaManager.changingImage) return;
        mediaManager.close();
        if (mediaManager.changingImage == "cover") { // If cover change is requested
            mediaManager.changingImage = null;
            // Send change to backend
            fetch(`/admin/php/changeCover.php?page=${PAGE}&img=${img}`, {
                method: 'GET'
            }).then(response => {
                if (response.ok) {
                    console.log('Cover changed successfully');
                    document.getElementById('coverImagePreview').src = '/admin/images/' + PAGE + '/' + img + ".webp";
                } else {
                    console.error('Failed to change cover');
                }
            }).catch(error => {
                console.error('Error:', error);
            });
            return;
        }
        console.log('Changing image to: ', img);
        const cluster = mediaManager.changingImage.toString().split('-')[0];
        const image = mediaManager.changingImage.toString().split('-')[1];
        document.getElementById("cluster" + cluster).getElementsByClassName('i' + image)[0].src = '/admin/images/' + PAGE + '/' + img + ".webp";
        const imgChange = cluster + '-' + image + '-' + img;
        mediaManager.changingImage = null;
        // Send change to backend
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&imgchange=${imgChange}`, {
            method: 'GET'
        }).then(response => {
            if (response.ok) {
                console.log('Image changed successfully');
            } else {
                console.error('Failed to change image');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    },
    deleteImage: async (img) => {
        if (!confirm('Are you sure you want to delete this image?')) return;
        document.getElementById(img).remove();
        let url = '/admin/pages/' + PAGE + '/images/' + img + '.webp';
        // Delete all img elements with the same src
        let imgs = document.getElementsByTagName('img');
        for (let i = 0; i < imgs.length; i++) if (imgs[i].getAttribute('src') === url) imgs[i].src = '';
        fetch(`/admin/php/mediaManagerDelete.php?page=${PAGE}&img=${img}`, {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                console.log('Image deleted successfully');
            } else {
                console.error('Failed to delete image');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }
};

mediaManager.element.addEventListener("click", (e) => {
    if (e.target === mediaManager.element) {
        mediaManager.close();
    }
});