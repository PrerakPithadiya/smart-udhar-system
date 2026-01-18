<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Items Management'; ?> | Smart Udhar Pro</title>

    <!-- Core Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/common.js" defer></script>

    <style>
        :root {
            --bg-airy: #f8fafc;
            --accent-indigo: #6366f1;
            --glass-white: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-airy);
            color: #1e293b;
            overflow-x: hidden;
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.05) 0px, transparent 50%);
        }

        h1,
        h2,
        h3,
        h4,
        .font-space {
            font-family: 'Space Grotesk', sans-serif;
        }

        /* Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 32px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: visible;
        }

        .glass-panel:hover {
            box-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.08);
        }

        /* Prevent transform on panels with search to avoid dropdown issues */
        .glass-panel:has(#item-search-input) {
            transform: none !important;
        }

        /* Noodle Animation */
        .noodle-path {
            stroke: rgba(99, 102, 241, 0.1);
            stroke-width: 1.5;
            fill: none;
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: drawNoodle 15s linear infinite;
        }

        @keyframes drawNoodle {
            to {
                stroke-dashoffset: 0;
            }
        }

        .beam {
            position: absolute;
            width: 1px;
            height: 80px;
            background: linear-gradient(to bottom, transparent, #6366f1, transparent);
            filter: blur(1px);
            opacity: 0;
            animation: beamTravel 5s infinite ease-in-out;
        }

        @keyframes beamTravel {
            0% {
                top: -80px;
                opacity: 0;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                top: 110%;
                opacity: 0;
            }
        }

        /* Form Inputs */
        .form-input-clean {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 18px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-input-clean:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* Status Pills */
        .status-pill {
            padding: 4px 12px;
            border-radius: 99px;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

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
            z-index: 99999 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            isolation: isolate;
        }

        .search-suggestion-item {
            padding: 16px 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
            position: relative;
            z-index: 1;
        }

        .search-suggestion-item:last-child {
            border-bottom: none;
        }

        .search-suggestion-item:hover,
        .search-suggestion-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05));
            transform: translateX(4px);
            z-index: 2;
        }

        .search-suggestions-container::-webkit-scrollbar {
            width: 6px;
        }

        .search-suggestions-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .search-suggestions-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .search-suggestions-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Ensure search input parent has proper stacking */
        .relative {
            position: relative;
            z-index: 100;
        }
    </style>
</head>

<body class="bg-[var(--bg-airy)]">
    <!-- Sidebar Toggle Commander (Visible when closed) -->
    <button id="sidebarOpenBtn"
        class="fixed left-0 top-1/2 -translate-y-1/2 w-12 h-16 bg-white border border-slate-200 text-indigo-600 rounded-r-2xl flex items-center justify-center shadow-xl shadow-indigo-100/50 hover:w-14 active:scale-95 transition-all z-[100] hidden">
        <iconify-icon icon="solar:sidebar-minimalistic-bold-duotone" width="24"></iconify-icon>
    </button>

    <!-- Aesthetics Layer -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-60">
        <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <path class="noodle-path" d="M -100,300 C 200,200 400,500 600,300 C 800,100 1000,400 1200,200" />
            <path class="noodle-path" style="animation-delay: -5s;"
                d="M -100,600 C 300,500 500,800 800,600 C 1100,400 1400,700 1600,500" />
        </svg>
    </div>
    <div class="beam" style="left: 20%; animation-delay: 1s;"></div>
    <div class="beam" style="left: 60%; animation-delay: 3s;"></div>

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content min-h-screen relative z-10 px-4 py-8 md:px-10">

        <!-- Header Section -->
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <nav
                        class="flex text-[10px] items-center gap-1.5 font-bold uppercase tracking-widest text-slate-400 mb-2">
                        <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                        <span>Smart Udhar</span>
                        <iconify-icon icon="solar:alt-arrow-right-bold" class="text-[8px]"></iconify-icon>
                        <span class="text-indigo-500">Items</span>
                    </nav>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tighter flex items-center gap-3">
                        <iconify-icon icon="solar:box-bold-duotone" class="text-indigo-500"></iconify-icon>
                        Items
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($action == 'list'): ?>
                    <a href="import_items.php"
                        class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                        <iconify-icon icon="solar:cloud-upload-bold" class="text-xl"></iconify-icon>
                        Import Items
                    </a>
                    <a href="items.php?action=add"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-1">
                        <iconify-icon icon="solar:add-circle-bold" class="text-xl"></iconify-icon>
                        Add New Item
                    </a>
                <?php else: ?>
                    <a href="items.php"
                        class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all">
                        <iconify-icon icon="solar:arrow-left-bold" class="text-xl"></iconify-icon>
                        Back to Catalog
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Message Display -->
        <div class="mb-8">
            <?php displayMessage(); ?>
        </div>

        <?php if ($action == 'list'): ?>
            <!-- Statistics Deck -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="glass-panel p-6 bg-white/90">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Catalog</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Items</p>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo number_format($total_items); ?>
                    </h2>
                </div>

                <div class="glass-panel p-6 bg-white/90">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:dollar-bold-duotone"></iconify-icon>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Average</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Avg Price</p>
                    <h2 class="text-3xl font-black text-emerald-600 tracking-tight">
                        ₹<?php echo number_format($avg_price ?? 0, 2); ?></h2>
                </div>

                <div class="glass-panel p-6 bg-white/90">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:percent-bold-duotone"></iconify-icon>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Taxed</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Items with GST</p>
                    <h2 class="text-3xl font-black text-amber-600 tracking-tight"><?php echo number_format($gst_count); ?>
                    </h2>
                </div>

                <div class="glass-panel p-6 bg-white/90">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:tag-bold-duotone"></iconify-icon>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Basic</span>
                    </div>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Without GST</p>
                    <h2 class="text-3xl font-black text-rose-600 tracking-tight">
                        <?php echo number_format($total_items - $gst_count); ?>
                    </h2>
                </div>
            </div>

            <!-- Intelligent Filter Grid -->
            <div class="glass-panel p-8 mb-10 bg-white/95" style="position: relative; z-index: 100;">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <input type="hidden" name="action" value="list">

                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Category
                            Filter</label>
                        <select name="category" class="w-full form-input-clean" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <option value="Fertilizers" <?php echo $category_filter == 'Fertilizers' ? 'selected' : ''; ?>>
                                Fertilizers</option>
                            <option value="Seeds" <?php echo $category_filter == 'Seeds' ? 'selected' : ''; ?>>Seeds
                            </option>
                            <option value="Insecticides" <?php echo $category_filter == 'Insecticides' ? 'selected' : ''; ?>>
                                Insecticides</option>
                            <option value="Others" <?php echo $category_filter == 'Others' ? 'selected' : ''; ?>>Others
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-7">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Global
                            Search</label>
                        <div class="relative" style="z-index: 1000;">
                            <iconify-icon icon="solar:magnifer-linear"
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></iconify-icon>
                            <input type="text" id="item-search-input" name="search" class="w-full form-input-clean pl-10"
                                placeholder="Search by name, code, HSN..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit"
                            class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon>
                            Execute
                        </button>
                    </div>
                </form>
            </div>

            <!-- Items Master List -->
            <div class="glass-panel overflow-hidden bg-white/95" style="position: relative; z-index: 1;">
                <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                    <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                        <iconify-icon icon="solar:database-bold-duotone" class="text-indigo-500"></iconify-icon>
                        Product Registry
                    </h3>
                    <span
                        class="px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-500 uppercase"><?php echo $total_items; ?>
                        Items</span>
                </div>

                <div>
                    <?php if (empty($items)): ?>
                        <div class="py-24 text-center">
                            <iconify-icon icon="solar:ghost-line-duotone" class="text-8xl text-slate-200 mb-6"></iconify-icon>
                            <h4 class="text-slate-400 font-bold italic tracking-wide">Inventory awaiting population...
                            </h4>
                            <p class="text-slate-300 text-sm">Add your first item to begin cataloging.</p>
                            <a href="items.php?action=add"
                                class="mt-8 bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold inline-block">Add
                                First Item</a>
                        </div>
                    <?php else: ?>
                        <table class="w-full">
                            <thead
                                class="bg-slate-50 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="px-8 py-5 text-left">
                                        <a href="?action=list&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&order_by=item_name&order_dir=<?php echo $order_by == 'item_name' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>"
                                            class="hover:text-indigo-600 transition-colors flex items-center gap-2">
                                            Product Name
                                            <?php if ($order_by == 'item_name'): ?>
                                                <iconify-icon
                                                    icon="solar:sort-from-<?php echo $order_dir == 'ASC' ? 'top-to-bottom' : 'bottom-to-top'; ?>-bold"></iconify-icon>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th class="px-8 py-5 text-left">Code</th>
                                    <th class="px-8 py-5 text-left">HSN</th>
                                    <th class="px-8 py-5 text-right">Price</th>
                                    <th class="px-8 py-5 text-center">Unit</th>
                                    <th class="px-8 py-5 text-center">Category</th>
                                    <th class="px-8 py-5 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach ($items as $itm): ?>
                                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                                                    <?php echo strtoupper(substr($itm['item_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-black text-slate-800 tracking-tight">
                                                        <?php echo htmlspecialchars($itm['item_name']); ?>
                                                    </p>
                                                    <?php if (!empty($itm['description'])): ?>
                                                        <p class="text-[10px] text-slate-400 font-medium italic">
                                                            <?php echo htmlspecialchars(substr($itm['description'], 0, 40)); ?>...
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-sm font-bold text-slate-500">
                                            <?php echo htmlspecialchars($itm['item_code'] ?: 'N/A'); ?>
                                        </td>
                                        <td class="px-8 py-6 text-sm font-bold text-slate-500">
                                            <?php echo htmlspecialchars($itm['hsn_code'] ?: 'N/A'); ?>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <span
                                                class="text-lg font-black text-indigo-600 tracking-tighter">₹<?php echo number_format($itm['price'], 2); ?></span>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span
                                                class="status-pill text-purple-500 bg-purple-50 border border-purple-100"><?php echo htmlspecialchars($itm['unit']); ?></span>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span
                                                class="status-pill text-slate-500 bg-slate-50 border border-slate-100"><?php echo htmlspecialchars($itm['category'] ?: 'N/A'); ?></span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex justify-center items-center gap-2">
                                                <a href="items.php?action=view&id=<?php echo $itm['id']; ?>"
                                                    class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                                                    <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                                </a>
                                                <a href="items.php?action=edit&id=<?php echo $itm['id']; ?>"
                                                    class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-amber-600 hover:border-amber-200 transition-all shadow-sm">
                                                    <iconify-icon icon="solar:pen-bold"></iconify-icon>
                                                </a>
                                                <button
                                                    onclick="confirmDelete(<?php echo $itm['id']; ?>, '<?php echo htmlspecialchars(addslashes($itm['item_name'])); ?>')"
                                                    class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Pagination Deck -->
                <?php if ($total_pages > 1): ?>
                    <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                        <nav class="flex items-center gap-2">
                            <a href="?action=list&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&page=<?php echo max(1, $page - 1); ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-400 transition-all">
                                <iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>
                            </a>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?action=list&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&page=<?php echo $i; ?>"
                                    class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs transition-all <?php echo $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <a href="?action=list&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&page=<?php echo min($total_pages, $page + 1); ?>"
                                class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-400 transition-all">
                                <iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Processing Interface (Add/Edit) -->
            <div class="max-w-4xl mx-auto">
                <div class="glass-panel overflow-hidden bg-white/95 shadow-2xl">
                    <div
                        class="px-10 py-8 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100/50 flex items-center gap-6">
                        <div
                            class="w-16 h-16 rounded-[2rem] bg-indigo-600 text-white flex items-center justify-center text-3xl shadow-xl shadow-indigo-200">
                            <iconify-icon
                                icon="<?php echo $action == 'add' ? 'solar:add-square-bold-duotone' : 'solar:pen-bold-duotone'; ?>"></iconify-icon>
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-indigo-900 tracking-tighter">
                                <?php echo $action == 'add' ? 'Register New Product' : 'Update Product Data'; ?>
                            </h2>
                            <p class="text-indigo-500/70 text-sm font-bold uppercase tracking-widest">Inventory
                                Management Module v3.2</p>
                        </div>
                    </div>

                    <div class="p-10">
                        <form method="POST" action="" id="itemForm" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Product
                                        Name *</label>
                                    <div class="relative group">
                                        <iconify-icon icon="solar:tag-bold-duotone"
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                        <input type="text" class="w-full form-input-clean pl-12 py-5 text-lg font-bold"
                                            name="item_name" placeholder="Enter product name..." required
                                            value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['item_name']) : ''; ?>">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Item
                                        Code</label>
                                    <input type="text" class="w-full form-input-clean" name="item_code"
                                        placeholder="SKU / Code"
                                        value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['item_code']) : ''; ?>">
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">HSN
                                        Code</label>
                                    <input type="text" class="w-full form-input-clean" name="hsn_code"
                                        placeholder="HSN Classification"
                                        value="<?php echo $action == 'edit' && $item ? htmlspecialchars($item['hsn_code']) : ''; ?>">
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Price
                                        (INR) *</label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                                        <input type="number"
                                            class="w-full form-input-clean pl-10 text-xl font-black text-indigo-600 tracking-tighter"
                                            name="price" step="0.01" min="0.01" placeholder="0.00" required
                                            value="<?php echo $action == 'edit' && $item ? number_format($item['price'], 2, '.', '') : '0.00'; ?>">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Unit
                                        of Measure *</label>
                                    <select class="w-full form-input-clean" name="unit" required>
                                        <option value="PCS" <?php echo ($action == 'edit' && $item && $item['unit'] == 'PCS') ? 'selected' : ''; ?>>Pieces (PCS)</option>
                                        <option value="KG" <?php echo ($action == 'edit' && $item && $item['unit'] == 'KG') ? 'selected' : ''; ?>>Kilogram (KG)</option>
                                        <option value="L" <?php echo ($action == 'edit' && $item && $item['unit'] == 'L') ? 'selected' : ''; ?>>Liter (L)</option>
                                        <option value="M" <?php echo ($action == 'edit' && $item && $item['unit'] == 'M') ? 'selected' : ''; ?>>Meter (M)</option>
                                        <option value="PACK" <?php echo ($action == 'edit' && $item && $item['unit'] == 'PACK') ? 'selected' : ''; ?>>Pack</option>
                                        <option value="BOTTLE" <?php echo ($action == 'edit' && $item && $item['unit'] == 'BOTTLE') ? 'selected' : ''; ?>>Bottle</option>
                                        <option value="BOX" <?php echo ($action == 'edit' && $item && $item['unit'] == 'BOX') ? 'selected' : ''; ?>>Box</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Category
                                        *</label>
                                    <select class="w-full form-input-clean" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Fertilizers" <?php echo ($action == 'edit' && $item && $item['category'] == 'Fertilizers') ? 'selected' : ''; ?>>Fertilizers</option>
                                        <option value="Seeds" <?php echo ($action == 'edit' && $item && $item['category'] == 'Seeds') ? 'selected' : ''; ?>>Seeds</option>
                                        <option value="Insecticides" <?php echo ($action == 'edit' && $item && $item['category'] == 'Insecticides') ? 'selected' : ''; ?>>Insecticides</option>
                                        <option value="Others" <?php echo ($action == 'edit' && $item && $item['category'] == 'Others') ? 'selected' : ''; ?>>Others</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Product
                                        Description</label>
                                    <textarea class="w-full form-input-clean h-32 resize-none" name="description"
                                        placeholder="Additional product details..."><?php echo $action == 'edit' && $item ? htmlspecialchars($item['description']) : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-6">
                                <?php if ($action == 'edit' && $item): ?>
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="update_item"
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-lg tracking-tight shadow-xl shadow-indigo-100 transition-all hover:scale-[1.02]">
                                        <iconify-icon icon="solar:check-read-linear" class="mr-2"></iconify-icon>
                                        Update Product
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="add_item"
                                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-lg tracking-tight shadow-xl shadow-indigo-100 transition-all hover:scale-[1.02]">
                                        <iconify-icon icon="solar:check-read-linear" class="mr-2"></iconify-icon>
                                        Register Product
                                    </button>
                                <?php endif; ?>
                                <a href="items.php"
                                    class="px-8 py-4 text-slate-400 font-bold hover:text-slate-600 transition-colors">Discard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($action == 'view' && $item): ?>
            <!-- Inspection Interface (View) -->
            <div class="max-w-5xl mx-auto space-y-8">
                <div class="glass-panel overflow-hidden bg-white/95 shadow-3xl">
                    <div class="px-12 py-10 bg-slate-900 flex justify-between items-center text-white relative">
                        <div class="absolute inset-0 overflow-hidden opacity-20">
                            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="url(#grad)" />
                                <defs>
                                    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#6366f1" />
                                        <stop offset="100%" style="stop-color:#a855f7" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-300 mb-2">Product
                                Specification</p>
                            <h2 class="text-5xl font-black tracking-tighter">
                                <?php echo htmlspecialchars($item['item_name']); ?>
                            </h2>
                        </div>
                        <div class="text-right relative z-10">
                            <p class="text-4xl font-black text-indigo-400 tracking-tighter italic">
                                ₹<?php echo number_format($item['price'], 2); ?></p>
                            <span
                                class="status-pill bg-white/10 text-white border border-white/20 mt-2 inline-block"><?php echo $item['unit']; ?></span>
                        </div>
                    </div>

                    <div class="p-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div class="space-y-6">
                                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">
                                    Product Metadata</h4>
                                <div class="space-y-4">
                                    <div
                                        class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <span class="text-xs font-bold text-slate-500 uppercase">Item Code</span>
                                        <span
                                            class="text-sm font-black text-slate-800"><?php echo htmlspecialchars($item['item_code'] ?: 'N/A'); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center p-4 rounded-xl border border-slate-100">
                                        <span class="text-xs font-bold text-slate-500 uppercase">HSN Code</span>
                                        <span
                                            class="text-sm font-bold text-slate-800 italic"><?php echo htmlspecialchars($item['hsn_code'] ?: 'N/A'); ?></span>
                                    </div>
                                    <div
                                        class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                        <span class="text-xs font-bold text-slate-500 uppercase">Category</span>
                                        <span
                                            class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($item['category'] ?: 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">
                                    System Information</h4>
                                <div class="space-y-4">
                                    <div
                                        class="flex justify-between items-center bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                                        <span class="text-xs font-bold text-emerald-600 uppercase">Created</span>
                                        <span
                                            class="text-sm font-black text-emerald-700"><?php echo date('d M Y', strtotime($item['created_at'])); ?></span>
                                    </div>
                                    <?php if ($item['created_at'] != $item['updated_at']): ?>
                                        <div
                                            class="flex justify-between items-center bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                                            <span class="text-xs font-bold text-amber-600 uppercase">Last Updated</span>
                                            <span
                                                class="text-sm font-black text-amber-700"><?php echo date('d M Y', strtotime($item['updated_at'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($item['description'])): ?>
                            <div class="mt-12 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Product
                                    Description</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-16 pt-8 border-t border-slate-100 flex items-center gap-4">
                            <a href="items.php?action=edit&id=<?php echo $item['id']; ?>"
                                class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 py-4 rounded-2xl font-black text-center transition-all">Update
                                Product</a>
                            <a href="udhar.php?action=add&item_id=<?php echo $item['id']; ?>"
                                class="flex-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 py-4 rounded-2xl font-black transition-all text-center">Use
                                in Bill</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Interface Controller -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/search_suggestions.js"></script>
    <script src="assets/js/items.js"></script>
    <script src="assets/js/items_custom.js"></script>

    <script>
        function confirmDelete(id, name) {
            const confirmed = confirm(`[SECURITY ALERT] Are you sure you wish to remove ${name} from the inventory? This action is permanent.`);
            if (confirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'item_id';
                input.value = id;
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'delete_item';
                submit.value = '1';
                form.appendChild(input);
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>