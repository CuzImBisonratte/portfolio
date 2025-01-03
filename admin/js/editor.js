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
        location.assign(`/admin/php/clusterActions.php?page=${PAGE}&moveUp=${index}`);
    },
    moveImageClusterDown: (index) => {
        location.assign(`/admin/php/clusterActions.php?page=${PAGE}&moveDown=${index}`);
    },
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