const editor = {
    deleteImageCluster: (index) => {
        const confirmation = confirm('Are you sure you want to delete this image cluster?');
        if (confirmation) location.assign(`/admin/php/clusterActions.php?page=${PAGE}&delete=${index}`);
    },
    moveImageClusterUp: (index) => {
        location.assign(`/admin/php/clusterActions.php?page=${PAGE}&moveUp=${index}`);
    },
    moveImageClusterDown: (index) => {
        location.assign(`/admin/php/clusterActions.php?page=${PAGE}&moveDown=${index}`);
    },
};

// 
// All following functions are implemented in JavaScript to avoid page reloads which would need more time
// JavaScript is already necessary for the editor, so it's not a problem
// 
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