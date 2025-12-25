// File: smart-udhar-system/assets/js/reports.js

// Sidebar toggle
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (sidebar && mainContent) {
    sidebar.classList.toggle("closed");
    mainContent.classList.toggle("expanded");
  }
}

// Sidebar toggle button inside sidebar
const sidebarToggleBtn = document.getElementById("sidebarToggle");
if (sidebarToggleBtn) {
  sidebarToggleBtn.addEventListener("click", toggleSidebar);
}

// Floating toggle button (visible when sidebar is closed)
const floatingToggleBtn = document.getElementById("floatingToggle");
if (floatingToggleBtn) {
  floatingToggleBtn.addEventListener("click", toggleSidebar);
}

// Trend Chart
if (window.reportType === "trend") {
  const ctx = document.getElementById("trendChart");
  if (ctx) {
    new Chart(ctx, {
      type: "line",
      data: {
        labels: window.trendLabels,
        datasets: [
          {
            label: "Sales",
            data: window.salesData,
            borderColor: "rgb(255, 99, 132)",
            backgroundColor: "rgba(255, 99, 132, 0.1)",
            tension: 0.4,
          },
          {
            label: "Payments",
            data: window.paymentData,
            borderColor: "rgb(75, 192, 192)",
            backgroundColor: "rgba(75, 192, 192, 0.1)",
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: "top",
          },
          title: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "₹" + value.toLocaleString();
              },
            },
          },
        },
      },
    });
  }
}
