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
    close: () => {
        mediaManager.element.style.display = 'none';
    }
};