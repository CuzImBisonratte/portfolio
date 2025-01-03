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
        // Fix arrows
        document.getElementById("cluster0").getElementsByClassName('up-button')[0].classList.add('firstUpDisabled');
        document.getElementById("cluster" + numClusters).getElementsByClassName('down-button')[0].classList.add('lastDownDisabled');
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