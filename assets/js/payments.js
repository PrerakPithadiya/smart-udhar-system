// File: assets/js/payments.js

/**
 * Antigravity Processing Engine - Payments Module
 */

// Update customer balance when customer is selected
function updateCustomerBalance(customerId) {
  let balance = 0;

  if (customerId && window.allCustomers) {
    const customer = window.allCustomers.find(c => c.id == customerId);
    if (customer) {
      balance = customer.balance;
    }
  }

  const infoDiv = document.getElementById("customerBalanceInfo");
  const balanceSpan = document.getElementById("customerBalance");

  if (customerId && infoDiv && balanceSpan) {
    infoDiv.classList.remove('hidden');
    balanceSpan.textContent = parseFloat(balance).toLocaleString('en-IN', { minimumFractionDigits: 2 });
  } else if (infoDiv) {
    infoDiv.classList.add('hidden');
  }
}

// Search functionality for Receive Payment
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('customer_search');
  const resultsDiv = document.getElementById('customer_results');
  const hiddenInput = document.getElementById('customer_id');

  if (searchInput && resultsDiv && window.allCustomers) {
    // Search input handler
    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();

      if (query.length === 0) {
        resultsDiv.classList.add('hidden');
        return;
      }

      const matches = window.allCustomers.filter(c =>
        c.name.toLowerCase().includes(query) ||
        c.mobile.includes(query)
      );

      if (matches.length > 0) {
        let html = '<div class="divide-y divide-slate-50">';
        matches.forEach(c => {
          html += `
                        <div class="p-4 hover:bg-indigo-50 cursor-pointer transition-colors group" 
                           onclick="selectCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}')">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-black text-slate-800">${c.name}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center gap-1">
                                        <iconify-icon icon="solar:phone-bold"></iconify-icon> ${c.mobile}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-black ${c.balance > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                                        ₹${parseFloat(c.balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </p>
                                    <span class="text-[8px] font-black uppercase tracking-tighter text-slate-300">Outstanding</span>
                                </div>
                            </div>
                        </div>
                    `;
        });
        html += '</div>';
        resultsDiv.innerHTML = html;
        resultsDiv.classList.remove('hidden');
      } else {
        resultsDiv.innerHTML = '<div class="p-6 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest">No entities found in databank</div>';
        resultsDiv.classList.remove('hidden');
      }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function (e) {
      if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.classList.add('hidden');
      }
    });

    // Clear selection if input is cleared
    searchInput.addEventListener('input', function () {
      if (this.value === '') {
        hiddenInput.value = '';
        updateCustomerBalance('');
      }
    });
  }
});

// Global function to select customer (called from onclick in generated HTML)
window.selectCustomer = function (id, name) {
  const searchInput = document.getElementById('customer_search');
  const hiddenInput = document.getElementById('customer_id');
  const resultsDiv = document.getElementById('customer_results');

  if (searchInput) searchInput.value = name;
  if (hiddenInput) hiddenInput.value = id;
  if (resultsDiv) resultsDiv.classList.add('hidden');

  updateCustomerBalance(id);
};

// Form validation for add payment
document.getElementById("paymentForm")?.addEventListener("submit", function (e) {
  const customerId = document.getElementById("customer_id")?.value;
  const amountInput = document.querySelector('input[name="amount"]');
  const amount = parseFloat(amountInput?.value || 0);

  if (!customerId) {
    e.preventDefault();
    alert("[SECURITY BREACH] Entity identifier missing. Please select a customer.");
    return false;
  }

  if (isNaN(amount) || amount <= 0) {
    e.preventDefault();
    alert("[PROTOCOL ERROR] Capital value must be a positive non-zero aggregate.");
    amountInput?.focus();
    return false;
  }

  return true;
});

// Update total allocation amount in Settlement Mapping
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

  const totalAllocatedElem = document.getElementById("totalAllocated");
  if (totalAllocatedElem) {
    totalAllocatedElem.textContent = total.toLocaleString('en-IN', { minimumFractionDigits: 2 });
  }

  const warningDiv = document.getElementById("allocationWarning");
  const warningMsg = document.getElementById("warningMessage");
  const warningContainer = warningDiv?.querySelector('div');

  if (warningDiv && warningMsg) {
    if (total > remainingAmount) {
      warningDiv.classList.remove('hidden');
      warningMsg.textContent = `CRITICAL: Over-Allocation (₹${total.toFixed(2)} > ₹${remainingAmount.toFixed(2)})`;
      if (warningContainer) {
        warningContainer.classList.remove('bg-indigo-50', 'text-indigo-600', 'bg-emerald-50', 'text-emerald-600');
        warningContainer.classList.add('bg-rose-50', 'text-rose-600', 'border-rose-100');
      }
    } else if (total > 0) {
      warningDiv.classList.remove('hidden');
      const residual = (remainingAmount - total).toFixed(2);
      warningMsg.textContent = `Valid Chain: ₹${total.toFixed(2)} mapped | Residual: ₹${residual}`;
      if (warningContainer) {
        warningContainer.classList.remove('bg-rose-50', 'text-rose-600', 'bg-indigo-50', 'text-indigo-600');
        warningContainer.classList.add('bg-emerald-50', 'text-emerald-600', 'border-emerald-100');
      }
    } else {
      warningDiv.classList.add('hidden');
    }
  }
}

// Auto allocate payment to pending udhar entries
function autoAllocate() {
  const remainingAmount = window.paymentRemainingAmount;
  let amountToAllocate = remainingAmount;

  document.querySelectorAll(".allocate-amount").forEach((input) => {
    const max = parseFloat(input.max) || 0;
    const allocate = Math.min(max, amountToAllocate);

    input.value = allocate > 0 ? allocate.toFixed(2) : "";
    amountToAllocate -= allocate;

    if (amountToAllocate <= 0) {
      amountToAllocate = 0;
    }
  });

  updateTotalAllocation();
}

// Validate allocation form
document.getElementById("allocateForm")?.addEventListener("submit", function (e) {
  let total = 0;
  document.querySelectorAll(".allocate-amount").forEach((input) => {
    total += parseFloat(input.value) || 0;
  });

  const remainingAmount = window.paymentRemainingAmount;

  if (total <= 0) {
    e.preventDefault();
    alert("[ALLOCATION ERROR] Null mapping detected. Please distribute capital.");
    return false;
  }

  if (total > remainingAmount) {
    e.preventDefault();
    alert(`[LIMIT OVERFLOW] Total mapping (₹${total.toFixed(2)}) exceeds available liquidity (₹${remainingAmount.toFixed(2)}).`);
    return false;
  }

  return true;
});

// Initialize logic
document.addEventListener("DOMContentLoaded", function () {
  // Customer selection in Add Payment
  if (window.currentAction === "add" && window.currentCustomerId > 0) {
    updateCustomerBalance(window.currentCustomerId);
  }

  // Allocation tracking
  if (window.currentAction === "allocate") {
    updateTotalAllocation();
  }
});

