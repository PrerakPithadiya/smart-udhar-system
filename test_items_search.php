<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Items Search Suggestions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <style>
        /* Search Suggestions */
        .search-suggestions-container {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 8px;
            max-height: 450px;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 20px;
            box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.15);
            z-index: 9999;
        }

        .search-suggestion-item {
            padding: 16px 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .search-suggestion-item:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05));
        }
    </style>
</head>

<body class="bg-slate-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Items Search Test</h1>

        <div class="bg-white p-8 rounded-2xl shadow-lg">
            <h2 class="text-xl font-bold mb-4">Search for Items</h2>
            <div class="relative">
                <input type="text" id="item-search-input"
                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="Type to search items...">
            </div>

            <div class="mt-8 p-4 bg-slate-50 rounded-xl">
                <h3 class="font-bold mb-2">Debug Info:</h3>
                <div id="debug-info" class="text-sm text-slate-600 space-y-1">
                    <p>Waiting for input...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/search_suggestions.js"></script>
    <script src="assets/js/items_custom.js"></script>

    <script>
        // Debug logging
        const debugInfo = document.getElementById('debug-info');

        function log(message) {
            const p = document.createElement('p');
            p.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            debugInfo.appendChild(p);
            debugInfo.scrollTop = debugInfo.scrollHeight;
        }

        // Check if scripts loaded
        log('Page loaded');
        log(`SearchSuggestions class available: ${typeof SearchSuggestions !== 'undefined'}`);
        log(`Input element found: ${document.getElementById('item-search-input') !== null}`);

        // Monitor input
        const input = document.getElementById('item-search-input');
        if (input) {
            input.addEventListener('input', (e) => {
                log(`Input value: "${e.target.value}"`);
            });
        }
    </script>
</body>

</html>