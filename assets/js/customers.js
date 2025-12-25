// Initialize DataTable
$(document).ready(function () {
  $("table").DataTable({
    pageLength: 10,
    responsive: true,
    order: [],
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search...",
      lengthMenu: "_MENU_ records per page",
      info: "Showing _START_ to _END_ of _TOTAL_ records",
      infoEmpty: "Showing 0 to 0 of 0 records",
      infoFiltered: "(filtered from _MAX_ total records)",
    },
  });
});

// Mobile number validation
document.getElementById("mobile")?.addEventListener("input", function (e) {
  this.value = this.value.replace(/[^0-9]/g, "");
  if (this.value.length > 10) {
    this.value = this.value.slice(0, 10);
  }
});

// Address character counter
const addressField = document.getElementById("address");
if (addressField) {
  addressField.addEventListener("input", function () {
    const maxLength = 500;
    const currentLength = this.value.length;
    const counter =
      document.getElementById("addressCounter") ||
      function () {
        const counter = document.createElement("small");
        counter.id = "addressCounter";
        counter.className = "form-text text-muted";
        this.parentElement.appendChild(counter);
        return counter;
      }.call(this);

    counter.textContent = `${currentLength}/${maxLength} characters`;
    if (currentLength > maxLength) {
      this.value = this.value.substring(0, maxLength);
      counter.textContent = `${maxLength}/${maxLength} characters (max reached)`;
      counter.className = "form-text text-danger";
    } else if (currentLength > maxLength * 0.8) {
      counter.className = "form-text text-warning";
    } else {
      counter.className = "form-text text-muted";
    }
  });

  // Trigger input event to show initial count
  addressField.dispatchEvent(new Event("input"));
}

// Form validation
const form = document.querySelector("form");
if (form) {
  form.addEventListener("submit", function (e) {
    const nameField = document.getElementById("name");
    if (nameField && nameField.value.trim().length < 2) {
      e.preventDefault();
      alert("Customer name must be at least 2 characters long.");
      nameField.focus();
      return false;
    }

    const mobileField = document.getElementById("mobile");
    if (
      mobileField &&
      mobileField.value &&
      !/^[0-9]{10}$/.test(mobileField.value)
    ) {
      e.preventDefault();
      alert("Mobile number must be exactly 10 digits.");
      mobileField.focus();
      return false;
    }

    const emailField = document.getElementById("email");
    if (
      emailField &&
      emailField.value &&
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)
    ) {
      e.preventDefault();
      alert("Please enter a valid email address.");
      emailField.focus();
      return false;
    }

    return true;
  });
}

// Quick search with Enter key
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
  searchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      this.form.submit();
    }
  });
}

// Sidebar toggle function
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  sidebar.classList.toggle("closed");
  mainContent.classList.toggle("expanded");
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

// Auto-hide sidebar on mobile when clicking outside
document.addEventListener("click", function (event) {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");
  const toggleBtn = document.getElementById("sidebarToggle");
  const floatingBtn = document.getElementById("floatingToggle");

  if (
    window.innerWidth <= 768 &&
    !sidebar.contains(event.target) &&
    !toggleBtn.contains(event.target) &&
    !floatingBtn.contains(event.target) &&
    !sidebar.classList.contains("closed")
  ) {
    sidebar.classList.add("closed");
    mainContent.classList.add("expanded");
  }
});
