// 
// All following functions are implemented in JavaScript to avoid page reloads which would need more time
// They are also implemented in PHP, which is used to save the changes to the data files
// JavaScript is already necessary for the editor, so it's not a problem
// 
const editor = {
    deleteImageCluster: async (index) => {
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
    moveImageClusterUp: async (index) => {
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&moveUp=${index}`, {
            method: 'GET'
        }).then(response => {
            if (response.ok) location.reload();
        }).catch(error => {
            console.error('Error:', error);
        });
    },
    moveImageClusterDown: async (index) => {
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&moveDown=${index}`, {
            method: 'GET'
        }).then(response => {
            if (response.ok) location.reload();
        }).catch(error => {
            console.error('Error:', error);
        });
    },
    chooseImage: (image) => {
        mediaManager.changingImage = image;
        mediaManager.open(true);
    },
    addCluster: () => {
        document.getElementById('addCluster').style.display = 'grid';
    },
    addClusterElement: async (type) => {
        document.getElementById('addCluster').style.display = 'none';
        fetch(`/admin/php/clusterActions.php?page=${PAGE}&add=${type}`, {
            method: 'GET'
        }).then(response => {
            if (response.ok) location.reload();
        }).catch(error => {
            console.error('Error:', error);
        });
    },
    editPage: () => {
        document.getElementById('editPage').style.display = 'grid';
    },
    closeEditPage: () => {
        document.getElementById('editPage').style.display = 'none';
    },
    deletePage: () => {
        const confirmation = confirm('Are you sure you want to delete this page?');
        if (!confirmation) return;
        fetch(`/admin/php/deletePage.php?page=${PAGE}`, {
            method: 'GET'
        }).then(response => {
            if (response.ok) location.href = '/admin/';
        }).catch(error => {
            console.error('Error:', error);
        });
    }
};

async function changePageData() {
    fetch('/admin/php/changePageData.php?page=' + PAGE, {
        method: 'POST',
        body: new FormData(document.querySelector('.portfolio-metadata'))
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

// 
// Cluster auto-clouse on click outside
// 
const cluster = document.getElementById('addCluster');
cluster.addEventListener("click", (e) => {
    if (e.target === cluster) {
        document.getElementById('addCluster').style.display = 'none';
    }
});

//
// Page data auto-close on click outside
//
const pageData = document.getElementById('editPage');
pageData.addEventListener("click", (e) => {
    if (e.target === pageData) {
        document.getElementById('editPage').style.display = 'none';
    }
});