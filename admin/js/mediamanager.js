const mediaManager = {
    element: document.getElementById('mediamanager'),
    changingImage: null,
    open: (forSelection) => {
        if (forSelection) mediaManager.element.classList.add('forSelection');
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
                if (++uploadsFinished === files.length)
                    // Run the after upload php script
                    fetch(`/admin/php/mediaManagerAfterUpload.php?page=${PAGE}&mediamanager`, {
                        method: 'POST'
                    }).then(response => {
                        document.getElementById('upload-spinner').style.display = 'none';
                        location.href = location.href;
                    }).catch(error => {
                        console.error('Error:', error);
                    });
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
    click: (img) => {
        if (!mediaManager.changingImage) return;
        console.log('Changing image to: ', img);
        const cluster = mediaManager.changingImage.toString().split('-')[0];
        const image = mediaManager.changingImage.toString().split('-')[1];
        document.getElementById("cluster" + cluster).getElementsByClassName('i' + image)[0].src = '/admin/pages/' + PAGE + '/images/' + img + ".webp";
        mediaManager.close();
        mediaManager.changingImage = null;
        const imgChange = cluster + '-' + image + '-' + img;
        // Send change to backend
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&imgchange=${imgChange}`, {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                console.log('Image changed successfully');
            } else {
                console.error('Failed to change image');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }
};