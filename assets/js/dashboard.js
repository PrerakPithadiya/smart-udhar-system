// Sidebar toggle
document.getElementById("sidebarToggle").addEventListener("click", function () {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (window.innerWidth <= 768) {
    // Mobile: toggle active classes
    sidebar.classList.toggle("active");
    mainContent.classList.toggle("active");
  } else {
    // Desktop: toggle closed/expanded classes
    sidebar.classList.toggle("closed");
    mainContent.classList.toggle("expanded");
  }
});

// Auto-hide sidebar on mobile when clicking outside
document.addEventListener("click", function (event) {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");
  const toggleBtn = document.getElementById("sidebarToggle");

  if (
    window.innerWidth <= 768 &&
    !sidebar.contains(event.target) &&
    !toggleBtn.contains(event.target) &&
    sidebar.classList.contains("active")
  ) {
    sidebar.classList.remove("active");
    mainContent.classList.remove("active");
  }
});

// Update current time
function updateTime() {
  const now = new Date();
  const options = {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  };
  document.getElementById("currentTime").textContent = now.toLocaleDateString(
    "en-US",
    options
  );
}

setInterval(updateTime, 1000);
updateTime();
