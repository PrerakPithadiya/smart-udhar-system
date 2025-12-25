// File: smart-udhar-system/assets/js/common.js

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const sidebarToggleBtn = document.getElementById("sidebarToggle");
    const floatingToggleBtn = document.getElementById("floatingToggle");

    // Function to apply sidebar state
    function applySidebarState(state) {
        if (window.innerWidth > 768) {
            if (state === 'closed') {
                sidebar.classList.add("closed");
                mainContent.classList.add("expanded");
            } else {
                sidebar.classList.remove("closed");
                mainContent.classList.remove("expanded");
            }
        }
    }

    // Load state from local storage on page load
    const savedState = localStorage.getItem('sidebarState');
    if (savedState) {
        applySidebarState(savedState);
    }

    // Toggle function
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            // Mobile behavior
            sidebar.classList.toggle("active");
            mainContent.classList.toggle("active");
        } else {
            // Desktop behavior
            sidebar.classList.toggle("closed");
            mainContent.classList.toggle("expanded");
            
            // Save state
            const newState = sidebar.classList.contains("closed") ? 'closed' : 'open';
            localStorage.setItem('sidebarState', newState);
        }
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener("click", toggleSidebar);
    }

    if (floatingToggleBtn) {
        floatingToggleBtn.addEventListener("click", toggleSidebar);
    }

    // Auto-hide on mobile
    document.addEventListener("click", function (event) {
        if (
            window.innerWidth <= 768 &&
            sidebar &&
            !sidebar.contains(event.target) &&
            toggleBtn && !toggleBtn.contains(event.target) &&
            floatingBtn && !floatingBtn.contains(event.target) &&
            sidebar.classList.contains("active")
        ) {
            sidebar.classList.remove("active");
            mainContent.classList.remove("active");
        }
    });
});
