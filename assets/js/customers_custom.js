document.addEventListener('DOMContentLoaded', function () {
    // Initialize search suggestions for customer search
    const customerSearch = new SearchSuggestions('#customer-search', {
        apiUrl: 'api/search_customers.php',
        minChars: 1,
        delay: 300,
        onSelect: function (suggestion) {
            // When a suggestion is selected, redirect to the customer details page
            window.location.href = `customers.php?action=view&id=${suggestion.id}`;
        }
    });
});
