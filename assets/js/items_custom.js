// File: assets/js/items_custom.js
// Custom JavaScript for Items page with search suggestions

document.addEventListener('DOMContentLoaded', function () {
    // Initialize search suggestions for items search
    const itemSearchInput = document.getElementById('item-search-input');

    if (itemSearchInput) {
        new SearchSuggestions('#item-search-input', {
            apiUrl: 'api/search_items.php',
            minChars: 1,
            delay: 300,
            maxSuggestions: 10,
            suggestionTemplate: function (suggestion) {
                // Custom template for item suggestions
                const initials = suggestion.item_name.substring(0, 1).toUpperCase();
                const colors = [
                    'from-indigo-500 to-violet-600',
                    'from-emerald-500 to-teal-600',
                    'from-amber-500 to-orange-600',
                    'from-rose-500 to-pink-600',
                    'from-sky-500 to-blue-600',
                ];
                const gradient = colors[suggestion.id % colors.length];

                // Format price
                const formattedPrice = `₹${parseFloat(suggestion.price).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;

                // Build GST info
                let gstInfo = '';
                if (suggestion.cgst_rate > 0 || suggestion.sgst_rate > 0 || suggestion.igst_rate > 0) {
                    const gstRates = [];
                    if (suggestion.cgst_rate > 0) gstRates.push(`C:${suggestion.cgst_rate}%`);
                    if (suggestion.sgst_rate > 0) gstRates.push(`S:${suggestion.sgst_rate}%`);
                    if (suggestion.igst_rate > 0) gstRates.push(`I:${suggestion.igst_rate}%`);

                    gstInfo = `<span class="flex items-center gap-1.5 text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100">
                                 <iconify-icon icon="solar:percent-bold-duotone" class="text-sm"></iconify-icon>
                                 ${gstRates.join(' ')}
                               </span>`;
                }

                return `
                    <div class="flex items-center gap-4 flex-grow overflow-hidden">
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br ${gradient} flex items-center justify-center text-white font-black text-lg shadow-lg shadow-current/20 group-hover:scale-105 transition-transform duration-500">
                                ${initials}
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm">
                                <iconify-icon icon="solar:box-bold-duotone" class="text-indigo-500 text-xs"></iconify-icon>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0 flex-grow">
                            <h4 class="text-[15px] font-black text-black tracking-tight leading-none mb-2 truncate group-hover:text-indigo-600 transition-colors">
                                ${this.highlightMatch(suggestion.item_name, this.input.value)}
                            </h4>
                            <div class="flex items-center gap-3 flex-wrap">
                                ${suggestion.item_code ? `
                                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                                        <iconify-icon icon="solar:tag-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                                        ${suggestion.item_code}
                                    </span>
                                ` : ''}
                                ${suggestion.hsn_code ? `
                                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-indigo-400 text-sm"></iconify-icon>
                                        HSN: ${suggestion.hsn_code}
                                    </span>
                                ` : ''}
                                <span class="flex items-center gap-1.5 text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded-lg border border-purple-100">
                                    <iconify-icon icon="solar:ruler-bold-duotone" class="text-sm"></iconify-icon>
                                    ${suggestion.unit}
                                </span>
                                ${gstInfo}
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end shrink-0">
                        <div class="flex items-center gap-1.5 bg-indigo-50/80 text-indigo-600 px-3 py-1.5 rounded-xl border border-indigo-100 shadow-sm shadow-indigo-100/50">
                            <iconify-icon icon="solar:dollar-bold-duotone" class="text-lg"></iconify-icon>
                            <span class="text-[13px] font-black tracking-tighter">${formattedPrice}</span>
                        </div>
                        <span class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-300 mt-1.5">Unit Price</span>
                    </div>
                `;
            },
            onSelect: function (suggestion) {
                // When an item is selected, submit the form to filter
                const form = itemSearchInput.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }
});
