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

document.addEventListener("keydown", function (e) {
  if (e.defaultPrevented) return;
  if (e.ctrlKey || e.altKey || e.metaKey) return;

  const target = e.target;
  const tag = target && target.tagName ? target.tagName.toLowerCase() : "";
  const isTypingTarget =
    tag === "input" ||
    tag === "textarea" ||
    tag === "select" ||
    (target && target.isContentEditable);

  if (isTypingTarget) return;

  if (e.key === "s" || e.key === "S") {
    e.preventDefault();
    window.location.href = "udhar.php?action=add&focus=customer_search";
  }
});
