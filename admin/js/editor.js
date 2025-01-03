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