// Master Layout Synchronization Engine
const TOGGLE_LOG_PREFIX = "[System Core: Sidebar]";

function setSidebarStateCookie(state) {
    try {
        document.cookie = `sidebarState=${encodeURIComponent(state)}; path=/; max-age=31536000; samesite=lax`;
    } catch (e) {
        // no-op
    }
}

function toggleSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const openBtn = document.getElementById("sidebarOpenBtn") || document.getElementById("floatingToggle");

    if (!sidebar || !mainContent) {
        console.error(`${TOGGLE_LOG_PREFIX} Engine failure: Essential DOM nodes missing.`);
        return;
    }

    const isMobile = window.innerWidth <= 768;
    console.log(`${TOGGLE_LOG_PREFIX} Initializing state change. Env: ${isMobile ? 'Mobile' : 'Desktop'}`);

    if (isMobile) {
        // Mobile Protocol: Toggle .active
        const isActive = sidebar.classList.toggle("active");
        mainContent.classList.toggle("active");
        console.log(`${TOGGLE_LOG_PREFIX} Mobile active state: ${isActive}`);
    } else {
        // Desktop Protocol: Toggle .closed
        const isClosed = sidebar.classList.toggle("closed");
        mainContent.classList.toggle("expanded");

        // Synchronize Open Trigger
        if (openBtn) {
            if (isClosed) {
                openBtn.classList.remove("hidden");
                // Force display if CSS didn't catch it
                openBtn.style.display = "flex";
            } else {
                openBtn.classList.add("hidden");
                openBtn.style.display = "none";
            }
        }

        // Persist Synchronized State
        localStorage.setItem('sidebarState', isClosed ? 'closed' : 'open');
        setSidebarStateCookie(isClosed ? 'closed' : 'open');
        console.log(`${TOGGLE_LOG_PREFIX} Desktop state synchronized: ${isClosed ? 'CLOSED' : 'OPEN'}`);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector(".sidebar");
    const mainContent = document.querySelector(".main-content");
    const openBtn = document.getElementById("sidebarOpenBtn") || document.getElementById("floatingToggle");
    const sidebarToggleBtn = document.getElementById("sidebarToggle");

    // Initialize state from local storage
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'closed' && window.innerWidth > 768) {
        if (sidebar) sidebar.classList.add("closed");
        if (mainContent) mainContent.classList.add("expanded");
        if (openBtn) {
            openBtn.classList.remove("hidden");
            openBtn.style.display = "flex";
        }
    } else {
        if (openBtn) {
            openBtn.classList.add("hidden");
            openBtn.style.display = "none";
        }
    }

    if (savedState === 'closed' || savedState === 'open') {
        setSidebarStateCookie(savedState);
    }

    // Attach Neural Triggers
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener("click", toggleSidebar);
    }

    if (openBtn) {
        openBtn.addEventListener("click", toggleSidebar);
    }

    // Advanced Event Delegation for Mobile Dismissal
    document.addEventListener("click", (event) => {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains("active")) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggleBtn && sidebarToggleBtn.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove("active");
                mainContent.classList.remove("active");
                console.log(`${TOGGLE_LOG_PREFIX} Mobile auto-dismiss triggered.`);
            }
        }
    });

    console.log(`${TOGGLE_LOG_PREFIX} Engine initialized.`);
});
