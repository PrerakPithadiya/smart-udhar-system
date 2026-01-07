// File: smart-udhar-system/assets/js/items.js

// Sidebar logic moved to common.js

document.addEventListener('DOMContentLoaded', function () {
  const itemSearchEl = document.getElementById('item-search');
  if (!itemSearchEl || typeof SearchSuggestions === 'undefined') return;

  new SearchSuggestions('#item-search', {
    apiUrl: 'api/search_items.php',
    minChars: 2,
    delay: 250,
    maxSuggestions: 10,
    suggestionTemplate: function (item) {
      const name = item.name || item.item_name || '';
      const initials = name ? name.substring(0, 1).toUpperCase() : '?';
      const colors = [
        'from-indigo-500 to-violet-600',
        'from-emerald-500 to-teal-600',
        'from-amber-500 to-orange-600',
        'from-rose-500 to-pink-600',
        'from-sky-500 to-blue-600'
      ];
      const gradient = colors[(parseInt(item.id, 10) || 0) % colors.length];
      const price = parseFloat(item.price || 0);

      return `
        <div class="flex items-center gap-4 flex-grow overflow-hidden">
          <div class="relative shrink-0">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br ${gradient} flex items-center justify-center text-white font-black text-lg shadow-lg shadow-current/20 group-hover:scale-105 transition-transform duration-500">
              ${initials}
            </div>
          </div>
          <div class="flex flex-col min-w-0">
            <h4 class="text-[15px] font-black text-black tracking-tight leading-none mb-2 truncate group-hover:text-indigo-600 transition-colors">
              ${this.highlightMatch(name, this.input.value)}
            </h4>
            <div class="flex items-center gap-3 flex-wrap">
              <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                <iconify-icon icon="solar:box-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                Code: ${item.item_code || 'N/A'}
              </span>
              <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                <iconify-icon icon="solar:document-text-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                HSN: ${item.hsn_code || 'N/A'}
              </span>
              ${item.unit ? `
                <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                  <iconify-icon icon="solar:ruler-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                  ${item.unit}
                </span>
              ` : ''}
            </div>
          </div>
        </div>
        <div class="flex flex-col items-end shrink-0">
          <div class="flex items-center gap-1.5 bg-slate-50/80 text-slate-600 px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
            <iconify-icon icon="solar:tag-price-bold-duotone" class="text-lg text-indigo-400"></iconify-icon>
            <span class="text-[13px] font-black tracking-tighter">₹${Number.isFinite(price) ? price.toFixed(2) : '0.00'}</span>
          </div>
        </div>
      `;
    }
  });
});

function confirmDelete(itemId, itemName) {
  if (
    confirm(
      'Are you sure you want to delete "' +
      itemName +
      '"? This action cannot be undone.'
    )
  ) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "";

    const input1 = document.createElement("input");
    input1.type = "hidden";
    input1.name = "item_id";
    input1.value = itemId;

    const input2 = document.createElement("input");
    input2.type = "hidden";
    input2.name = "delete_item";
    input2.value = "1";

    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
  }
}

// Form validation
document.getElementById("itemForm")?.addEventListener("submit", function (e) {
  const price = parseFloat(document.getElementById("price").value);
  if (isNaN(price) || price <= 0) {
    e.preventDefault();
    alert("Price must be greater than 0");
    document.getElementById("price").focus();
    return false;
  }

  return true;
});
