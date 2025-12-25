// Sidebar toggle function
// Sidebar logic moved to common.js

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
