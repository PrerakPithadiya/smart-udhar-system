// File: smart-udhar-system/assets/js/common.js

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const openBtn = document.getElementById("sidebarOpenBtn");

    if (!sidebar || !mainContent) return;

    if (window.innerWidth <= 768) {
        // Mobile behavior
        sidebar.classList.toggle("active");
        if (mainContent) mainContent.classList.toggle("active");
    } else {
        // Desktop behavior
        sidebar.classList.toggle("closed");
        if (mainContent) mainContent.classList.toggle("expanded");

        // UI Feedback: Handle Top-Left Open Button
        if (openBtn) {
            if (sidebar.classList.contains("closed")) {
                openBtn.classList.remove("hidden");
            } else {
                openBtn.classList.add("hidden");
            }
        }

        // Save state
        const newState = sidebar.classList.contains("closed") ? 'closed' : 'open';
        localStorage.setItem('sidebarState', newState);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const sidebarToggleBtn = document.getElementById("sidebarToggle");
    const floatingToggleBtn = document.getElementById("floatingToggle");
    const openBtn = document.getElementById("sidebarOpenBtn");

    // Function to apply sidebar state
    function applySidebarState(state) {
        if (window.innerWidth > 768) {
            if (state === 'closed') {
                if (sidebar) sidebar.classList.add("closed");
                if (mainContent) mainContent.classList.add("expanded");
                if (openBtn) openBtn.classList.remove("hidden");
            } else {
                if (sidebar) sidebar.classList.remove("closed");
                if (mainContent) mainContent.classList.remove("expanded");
                if (openBtn) openBtn.classList.add("hidden");
            }
        }
    }

    // Load state from local storage on page load
    const savedState = localStorage.getItem('sidebarState');
    if (savedState) {
        applySidebarState(savedState);
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
            ((sidebarToggleBtn && !sidebarToggleBtn.contains(event.target)) || !sidebarToggleBtn) &&
            ((floatingToggleBtn && !floatingToggleBtn.contains(event.target)) || !floatingToggleBtn) &&
            ((openBtn && !openBtn.contains(event.target)) || !openBtn) &&
            sidebar.classList.contains("active")
        ) {
            sidebar.classList.remove("active");
            if (mainContent) mainContent.classList.remove("active");
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        const state = localStorage.getItem('sidebarState');
        if (state) applySidebarState(state);
    });
});
