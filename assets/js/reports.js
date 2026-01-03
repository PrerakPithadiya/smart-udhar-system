// File: smart-udhar-system/assets/js/reports.js

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
