document.addEventListener('DOMContentLoaded', function () {
    // Initialize search suggestions for customer search in payments
    const searchElement = document.getElementById('customer-search-payments');
    if (searchElement) {
        const apiUrl = searchElement.getAttribute('data-api-url') || 'api/search_customers.php?has_payments=1';
        const customerSearch = new SearchSuggestions('#customer-search-payments', {
            apiUrl: apiUrl,
            minChars: 1,
            delay: 300,
            onSelect: function (suggestion) {
                // When a suggestion is selected, filter the payments by that customer
                window.location.href = `payments.php?action=list&customer=${suggestion.id}&search=${encodeURIComponent(suggestion.name)}`;
            }
        });
    }
});
