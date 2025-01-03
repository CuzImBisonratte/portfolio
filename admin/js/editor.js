// 
// All following functions are implemented in JavaScript to avoid page reloads which would need more time
// They are also implemented in PHP, which is used to save the changes to the data files
// JavaScript is already necessary for the editor, so it's not a problem
// 
const editor = {
    deleteImageCluster: (index) => {
        const confirmation = confirm('Are you sure you want to delete this image cluster?');
        if (!confirmation) return;
        document.getElementById('cluster' + index).remove();
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&delete=${index}`, {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                console.log('Image cluster deleted successfully');
            } else {
                console.error('Failed to delete image cluster');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
        const numClusters = document.querySelectorAll('.image-cluster-container').length;
        for (let i = index + 1; i <= numClusters; i++) document.getElementById('cluster' + i).id = 'cluster' + (i - 1);
        // Fix onclick actions (when image is clicked, changingImage needs to be set right)
        for (const element of document.getElementsByClassName("image-cluster-container")) {
            const cluster = element.id.replace(/[a-z]+/gm, "");
            const images = element.getElementsByClassName("image-cluster")[0].getElementsByTagName("img");
            for (let i = 0; i < images.length; i++) {
                images[i].setAttribute("onclick", "editor.chooseImage('" + cluster + "-" + (i + 1) + "')");
            }
        }
        // Fix arrows
        document.getElementsByClassName('up-button')[0].classList.add('firstUpDisabled');
        document.getElementsByClassName('down-button')[numClusters - 1].classList.add('lastDownDisabled');
    },
    moveImageClusterUp: (index) => {
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&moveUp=${index}`, {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                console.log('Image cluster moved up successfully');
            } else {
                console.error('Failed to move image cluster up');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
        const cluster = document.getElementById('cluster' + index);
        const previousCluster = document.getElementById('cluster' + (index - 1));
        cluster.parentNode.insertBefore(cluster, previousCluster);
        // Fix numbering
        cluster.id = 'cluster' + (index - 1);
        previousCluster.id = 'cluster' + index;
        // Fix arrows
        if (index === 1) {
            cluster.getElementsByClassName('up-button')[0].classList.add('firstUpDisabled');
            cluster.getElementsByClassName('up-button')[0].href = 'javascript:void(0)';
            previousCluster.getElementsByClassName('up-button')[0].classList.remove('firstUpDisabled');
        }
        if (index === document.querySelectorAll('.image-cluster-container').length - 1) {
            previousCluster.getElementsByClassName('down-button')[0].classList.add('lastDownDisabled');
            previousCluster.getElementsByClassName('down-button')[0].href = 'javascript:void(0)';
            cluster.getElementsByClassName('down-button')[0].classList.remove('lastDownDisabled');
        }
        // Fix action (done in a href)
        cluster.getElementsByClassName('delete-button')[0].href = 'javascript:editor.deleteImageCluster(' + (index - 1) + ')';
        previousCluster.getElementsByClassName('delete-button')[0].href = 'javascript:editor.deleteImageCluster(' + index + ')';
        if (index !== 1) cluster.getElementsByClassName('up-button')[0].href = 'javascript:editor.moveImageClusterUp(' + (index - 1) + ')';
        previousCluster.getElementsByClassName('up-button')[0].href = 'javascript:editor.moveImageClusterUp(' + index + ')';
        cluster.getElementsByClassName('down-button')[0].href = 'javascript:editor.moveImageClusterDown(' + (index - 1) + ')';
        previousCluster.getElementsByClassName('down-button')[0].href = 'javascript:editor.moveImageClusterDown(' + index + ')';
    },
    moveImageClusterDown: (index) => {
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&moveDown=${index}`, {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                console.log('Image cluster moved down successfully');
            } else {
                console.error('Failed to move image cluster down');
            }
        }).catch(error => {
            console.error('Error:', error);
        });
        const cluster = document.getElementById('cluster' + index);
        const nextCluster = document.getElementById('cluster' + (index + 1));
        cluster.parentNode.insertBefore(nextCluster, cluster);
        // Fix numbering
        cluster.id = 'cluster' + (index + 1);
        nextCluster.id = 'cluster' + index;
        // Fix arrows
        if (index === document.querySelectorAll('.image-cluster-container').length - 1) {
            cluster.getElementsByClassName('down-button')[0].classList.add('lastDownDisabled');
            cluster.getElementsByClassName('down-button')[0].href = 'javascript:void(0)';
            nextCluster.getElementsByClassName('down-button')[0].classList.remove('lastDownDisabled');
        }
        if (index === 0) {
            nextCluster.getElementsByClassName('up-button')[0].classList.add('firstUpDisabled');
            nextCluster.getElementsByClassName('up-button')[0].href = 'javascript:void(0)';
            cluster.getElementsByClassName('up-button')[0].classList.remove('firstUpDisabled');
        }
        // Fix action (done in a href)
        cluster.getElementsByClassName('delete-button')[0].href = 'javascript:editor.deleteImageCluster(' + (index + 1) + ')';
        nextCluster.getElementsByClassName('delete-button')[0].href = 'javascript:editor.deleteImageCluster(' + index + ')';
        cluster.getElementsByClassName('up-button')[0].href = 'javascript:editor.moveImageClusterUp(' + (index + 1) + ')';
        nextCluster.getElementsByClassName('up-button')[0].href = 'javascript:editor.moveImageClusterUp(' + index + ')';
        if (index !== document.querySelectorAll('.image-cluster-container').length - 1) {
            cluster.getElementsByClassName('down-button')[0].href = 'javascript:editor.moveImageClusterDown(' + (index + 1) + ')';
        }
        nextCluster.getElementsByClassName('down-button')[0].href = 'javascript:editor.moveImageClusterDown(' + index + ')';
    },
    chooseImage: (image) => {
        mediaManager.changingImage = image;
        mediaManager.open(true);
    },
    addCluster: () => {
        document.getElementById('addCluster').style.display = 'grid';
    },
    addClusterElement: (type) => {
        document.getElementById('addCluster').style.display = 'none';
        // Enable last downdisable
        document.getElementsByClassName("lastDownDisabled")[0].classList.remove("lastDownDisabled");
        // This mess just creates the HTML for the new cluster
        const cluster = document.createElement('div');
        cluster.className = 'image-cluster-container';
        cluster.id = 'cluster' + document.querySelectorAll('.image-cluster-container').length;
        textHTML = `
            <div class="image-cluster-actions">
                <a href="javascript:editor.deleteImageCluster(0)" class="delete-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                    </svg>
                </a>
                <a href="javascript:" class="up-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"></path>
                    </svg>
                </a>
                <a href="javascript:editor.moveImageClusterDown(0)" class="lastDownDisabled down-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path>
                    </svg>
                </a>
            </div>
            <div class="image-cluster ${type}">`;
        for (let i = 0; i < type.replace(/[e]+/gm, "").length; i++) textHTML += '<img src="/res/img/placeholder.webp" alt="" class=i' + (i + 1) + ' onclick="editor.chooseImage(\'' + document.querySelectorAll('.image-cluster-container').length + '-' + (i + 1) + '\')">';
        textHTML += '</div>';
        cluster.innerHTML = textHTML;
        document.getElementsByClassName('wysiwyg-editor')[0].appendChild(cluster);
        // From here on, the new cluster is added to the backend
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&add=${type}`, {
            method: 'POST'
        }).then(response => {
            if (!response.ok) console.error('Failed to add image cluster');
        }).catch(error => {
            console.error('Error:', error);
        });
    }
};

function changePageData() {
    fetch('/admin/php/changePageData.php?page=' + PAGE, {
        method: 'POST',
        body: new FormData(document.querySelector('.portfolio-title'))
    }).then(response => {
        if (response.ok) {
            console.log('Page data updated successfully');
        } else {
            console.error('Failed to update page data');
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}