// File: smart-udhar-system/assets/js/payments.js

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
  const toggleBtn = document.getElementById("sidebarToggle");
  const floatingBtn = document.getElementById("floatingToggle");
  const mainContent = document.querySelector(".main-content");

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

// Update customer balance when customer is selected
function updateCustomerBalance(customerId) {
  let balance = 0;
  
  if (customerId && window.allCustomers) {
    const customer = window.allCustomers.find(c => c.id == customerId);
    if (customer) {
      balance = customer.balance;
    }
  }

  if (customerId) {
    document.getElementById("customerBalanceInfo").style.display = "block";
    document.getElementById("customerBalance").textContent = parseFloat(balance).toFixed(2);
  } else {
    document.getElementById("customerBalanceInfo").style.display = "none";
  }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('customer_search');
    const resultsDiv = document.getElementById('customer_results');
    const hiddenInput = document.getElementById('customer_id');

    if (searchInput && resultsDiv && window.allCustomers) {
        // Search input handler
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            const matches = window.allCustomers.filter(c => 
                c.name.toLowerCase().includes(query) || 
                c.mobile.includes(query)
            );

            if (matches.length > 0) {
                let html = '';
                matches.forEach(c => {
                    html += `
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action" 
                           onclick="selectCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${c.name}</strong><br>
                                    <small class="text-muted"><i class="bi bi-phone"></i> ${c.mobile}</small>
                                </div>
                                <span class="badge ${c.balance > 0 ? 'bg-danger' : 'bg-success'}">
                                    ₹${parseFloat(c.balance).toFixed(2)}
                                </span>
                            </div>
                        </a>
                    `;
                });
                resultsDiv.innerHTML = html;
                resultsDiv.style.display = 'block';
            } else {
                resultsDiv.innerHTML = '<div class="list-group-item text-muted">No customers found</div>';
                resultsDiv.style.display = 'block';
            }
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
        
        // Clear selection if input is cleared
        searchInput.addEventListener('change', function() {
             if (this.value === '') {
                 hiddenInput.value = '';
                 updateCustomerBalance('');
             }
        });
    }
});

// Global function to select customer (called from onclick in generated HTML)
window.selectCustomer = function(id, name) {
    document.getElementById('customer_search').value = name;
    document.getElementById('customer_id').value = id;
    document.getElementById('customer_results').style.display = 'none';
    updateCustomerBalance(id);
};

// Form validation for add payment
document
  .getElementById("paymentForm")
  ?.addEventListener("submit", function (e) {
    const customerId = document.getElementById("customer_id").value;
    const amount = parseFloat(document.getElementById("amount").value);

    if (!customerId) {
      e.preventDefault();
      alert("Please select a customer");
      document.getElementById("customer_id").focus();
      return false;
    }

    if (isNaN(amount) || amount <= 0) {
      e.preventDefault();
      alert("Please enter a valid amount greater than 0");
      document.getElementById("amount").focus();
      return false;
    }

    return true;
  });

// Delete confirmation
function confirmDelete(id, customerName) {
  if (
    confirm(
      'Are you sure you want to delete payment for "' +
        customerName +
        '"? This action cannot be undone.'
    )
  ) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "";

    const input1 = document.createElement("input");
    input1.type = "hidden";
    input1.name = "payment_id";
    input1.value = id;

    const input2 = document.createElement("input");
    input2.type = "hidden";
    input2.name = "delete_payment";
    input2.value = "1";

    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
  }
}

// Update total allocation amount
function updateTotalAllocation() {
  let total = 0;
  const remainingAmount = window.paymentRemainingAmount;

  document.querySelectorAll(".allocate-amount").forEach((input) => {
    const value = parseFloat(input.value) || 0;
    const max = parseFloat(input.max) || 0;

    if (value > max) {
      input.value = max.toFixed(2);
      total += max;
    } else {
      total += value;
    }
  });

  document.getElementById("totalAllocated").textContent = total.toFixed(2);

  const warningDiv = document.getElementById("allocationWarning");
  const warningMsg = document.getElementById("warningMessage");

  if (total > remainingAmount) {
    warningDiv.style.display = "block";
    warningMsg.textContent = `Total allocation (₹${total.toFixed(
      2
    )}) exceeds remaining payment amount (₹${remainingAmount.toFixed(2)})`;
    warningDiv.className = "alert alert-danger";
  } else if (total > 0) {
    warningDiv.style.display = "block";
    warningMsg.textContent = `Total allocation: ₹${total.toFixed(
      2
    )} | Remaining: ₹${(remainingAmount - total).toFixed(2)}`;
    warningDiv.className = "alert alert-success";
  } else {
    warningDiv.style.display = "none";
  }
}

// Auto allocate payment to pending udhar entries
function autoAllocate() {
  const remainingAmount = window.paymentRemainingAmount;
  let amountToAllocate = remainingAmount;

  document.querySelectorAll(".allocate-amount").forEach((input) => {
    const max = parseFloat(input.max) || 0;
    const allocate = Math.min(max, amountToAllocate);

    input.value = allocate.toFixed(2);
    amountToAllocate -= allocate;

    if (amountToAllocate <= 0) {
      amountToAllocate = 0;
    }
  });

  updateTotalAllocation();
}

// Validate allocation form
document
  .getElementById("allocateForm")
  ?.addEventListener("submit", function (e) {
    let total = 0;
    document.querySelectorAll(".allocate-amount").forEach((input) => {
      total += parseFloat(input.value) || 0;
    });

    const remainingAmount = window.paymentRemainingAmount;

    if (total <= 0) {
      e.preventDefault();
      alert("Please allocate at least some amount");
      return false;
    }

    if (total > remainingAmount) {
      e.preventDefault();
      alert(
        `Total allocation (₹${total.toFixed(
          2
        )}) cannot exceed remaining payment amount (₹${remainingAmount.toFixed(
          2
        )})`
      );
      return false;
    }

    return true;
  });

// Initialize customer balance if customer is pre-selected
if (window.currentAction === "add" && window.currentCustomerId > 0) {
  document.addEventListener("DOMContentLoaded", function () {
    updateCustomerBalance(window.currentCustomerId);
  });
}

// Initialize allocation update
if (window.currentAction === "allocate") {
  document.addEventListener("DOMContentLoaded", function () {
    updateTotalAllocation();
  });
}

// Quick amount entry for payment form
document.getElementById("amount")?.addEventListener("focus", function () {
  const customerSelect = document.getElementById("customer_id");
  if (customerSelect.value) {
    const selectedOption = customerSelect.options[customerSelect.selectedIndex];
    const balance = parseFloat(selectedOption.dataset.balance) || 0;
    if (balance > 0) {
      this.value = Math.min(balance, 1000000).toFixed(2);
    }
  }
});
