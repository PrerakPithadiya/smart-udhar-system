<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Customer Alliance'; ?> | Smart Udhar Pro</title>

    <!-- Antigravity Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-airy: #f8fafc;
            --accent-indigo: #6366f1;
            --glass-white: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.3);
            --sidebar-width-ag: 280px;
        }

        html,
        body {
            overflow-anchor: none;
            scroll-behavior: auto !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-airy);
            color: #1e293b;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
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

        /* Master Layout Engine */
        .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--sidebar-width-ag) !important;
            height: 100vh !important;
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(40px) !important;
            border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
            z-index: 50 !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .sidebar.closed {
            left: calc(-1 * var(--sidebar-width-ag)) !important;
        }

        .main-content {
            margin-left: var(--sidebar-width-ag) !important;
            min-height: 100vh !important;
            padding: 40px !important;
            position: relative !important;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .main-content.expanded {
            margin-left: 0 !important;
        }

        /* Sidebar Visibility Logic */
        #sidebarOpenBtn {
            display: none !important;
        }

        .sidebar.closed~#sidebarOpenBtn {
            display: flex !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width-ag)) !important;
                left: -100% !important;
                /* Force off-screen for mobile */
            }

            .sidebar.active {
                margin-left: 0 !important;
                left: 0 !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 20px !important;
            }

            .main-content.active::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(8px);
                z-index: 40;
            }

            /* Hide open btn on mobile to avoid clutter */
            .sidebar.closed~#sidebarOpenBtn {
                display: none !important;
            }
        }

        /* Sidebar Navigation Spacing */
        .sidebar-header {
            padding: 32px 24px !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .sidebar-header h4 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            color: #1e293b;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .sidebar .nav-link {
            margin: 4px 16px !important;
            padding: 12px 16px !important;
            border-radius: 16px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            transition: all 0.3s ease !important;
        }

        .sidebar .nav-link:hover {
            background: rgba(99, 102, 241, 0.05) !important;
            color: #6366f1 !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            background: #6366f1 !important;
            color: white !important;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.3) !important;
        }

        .sidebar .nav-link i {
            font-size: 1.25rem !important;
        }

        .shop-name {
            font-size: 9px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.15em !important;
            color: #94a3b8 !important;
            margin-top: 2px !important;
            opacity: 0.8 !important;
        }

        .sidebar-toggle-btn {
            width: 36px !important;
            height: 36px !important;
            background: rgba(255, 255, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            border-radius: 12px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #6366f1 !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        }

        .sidebar-toggle-btn:hover {
            background: #6366f1 !important;
            color: white !important;
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2) !important;
            transform: scale(1.05);
        }

        /* Glassmorphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 32px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }


        /* Status & Balance Badges */
        .status-pill {
            padding: 4px 12px;
            border-radius: 99px;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-active {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #10b98120;
        }

        .badge-inactive {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #ef444420;
        }

        .balance-pill {
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .balance-due {
            background: #fff1f2;
            color: #e11d48;
        }

        .balance-adv {
            background: #f0fdf4;
            color: #16a34a;
        }

        .balance-zero {
            background: #f8fafc;
            color: #64748b;
        }

        /* Animated Elements */
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

        /* Inputs */
        .floating-input {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .floating-input:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* Table */
        .table-ag {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .table-ag tr {
            transition: all 0.3s ease;
        }

        .table-ag tbody tr:hover {
            transform: scale(1.005) translateY(-2px);
        }

        .table-ag th {
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.1em;
            padding: 12px 24px;
            text-align: left;
        }

        .table-ag td {
            background: white;
            padding: 18px 24px;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-ag td:first-child {
            border-left: 1px solid #f1f5f9;
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }

        .table-ag td:last-child {
            border-right: 1px solid #f1f5f9;
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        /* Quantum Search Suggestions */
        .search-suggestions-container {
            position: absolute;
            top: 110%;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 32px;
            box-shadow:
                0 30px 100px -20px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.5) inset;
            z-index: 9999 !important;
            overflow: visible !important;
            padding: 12px;
            transform-origin: top center;
            animation: suggestAppear 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: auto !important;
        }

        @keyframes suggestAppear {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .search-suggestion-item {
            padding: 12px 20px;
            border-radius: 20px;
            margin-bottom: 6px;
            cursor: pointer !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            position: relative;
            z-index: 10000 !important;
            pointer-events: auto !important;
        }

        .search-suggestion-item:hover,
        .search-suggestion-item.active {
            background: white;
            border-color: rgba(99, 102, 241, 0.1);
            box-shadow:
                0 10px 25px -5px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(99, 102, 241, 0.05) inset;
            transform: translateX(4px);
        }

        .search-suggestion-item:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>

    <!-- Aesthetics Layers -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-60">
        <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <path class="noodle-path" d="M -100,200 C 200,100 400,400 600,200 C 800,0 1000,300 1200,100" />
            <path class="noodle-path" style="animation-delay: -5s;"
                d="M -100,500 C 300,400 500,700 800,500 C 1100,300 1400,600 1600,400" />
        </svg>
    </div>
    <div class="beam" style="left: 20%; animation-delay: 1s;"></div>
    <div class="beam" style="left: 60%; animation-delay: 3s;"></div>

    <?php include 'includes/sidebar.php'; ?>

    <!-- Sidebar Toggle (Visible when closed) -->
    <button id="sidebarOpenBtn"
        class="fixed left-0 top-1/2 -translate-y-1/2 w-10 h-28 bg-white backdrop-blur-xl border border-l-0 border-indigo-100 text-indigo-600 rounded-r-3xl flex flex-col items-center justify-center shadow-[10px_0_30px_rgba(99,102,241,0.15)] hover:w-14 hover:bg-white transition-all z-[9999] group">
        <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone"
            class="text-2xl group-hover:scale-125 transition-transform mb-1"></iconify-icon>
        <span
            class="[writing-mode:vertical-lr] text-[9px] font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">Expand</span>
    </button>

    <div id="mainContent" class="main-content min-h-screen relative z-10 px-6 py-8 md:px-12">

        <!-- Header Section -->
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <nav
                        class="flex text-[10px] items-center gap-1.5 font-bold uppercase tracking-widest text-slate-400 mb-2">
                        <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                        <span>Smart Udhar</span>
                        <iconify-icon icon="solar:alt-arrow-right-bold" class="text-[8px]"></iconify-icon>
                        <span class="text-indigo-500">Customers</span>
                    </nav>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tighter flex items-center gap-3">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"
                            class="text-indigo-500"></iconify-icon>
                        Client <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Alliance</span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($action == 'list'): ?>
                    <a href="customers.php?action=add"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3.5 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-1">
                        <iconify-icon icon="solar:user-plus-bold-duotone" width="22"></iconify-icon>
                        Onboard New Client
                    </a>
                <?php else: ?>
                    <a href="customers.php"
                        class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3.5 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all shadow-sm">
                        <iconify-icon icon="solar:alt-arrow-left-bold" width="22"></iconify-icon>
                        Return to Vault
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Messages -->
        <div class="mb-8">
            <?php displayMessage(); ?>
        </div>

        <?php if ($action == 'list'): ?>
            <!-- Statistics Deck -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
                <div class="glass-card p-6 border-l-4 border-indigo-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                        </div>
                        <span
                            class="text-[10px] font-black text-indigo-400 uppercase tracking-widest bg-indigo-50/50 px-2 py-1 rounded">Population</span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                        <?php echo number_format($total_customers); ?>
                    </h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tight mt-1">Total Registered</p>
                </div>

                <div class="glass-card p-6 border-l-4 border-emerald-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:user-check-bold-duotone"></iconify-icon>
                        </div>
                        <span
                            class="text-[10px] font-black text-emerald-400 uppercase tracking-widest bg-emerald-50/50 px-2 py-1 rounded">Active
                            Status</span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                        <?php echo number_format($active_count); ?>
                    </h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tight mt-1">Verified Nodes</p>
                </div>

                <div class="glass-card p-6 border-l-4 border-rose-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:hand-money-bold-duotone"></iconify-icon>
                        </div>
                        <span
                            class="text-[10px] font-black text-rose-400 uppercase tracking-widest bg-rose-50/50 px-2 py-1 rounded">Treasury
                            Out</span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                        ₹<?php echo number_format($due_total, 2); ?></h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tight mt-1">Total Receivables</p>
                </div>

                <div class="glass-card p-6 border-l-4 border-amber-500">
                    <div class="flex justify-between items-start mb-4">
                        <div
                            class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-2xl">
                            <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
                        </div>
                        <span
                            class="text-[10px] font-black text-amber-400 uppercase tracking-widest bg-amber-50/50 px-2 py-1 rounded">Advance
                            Reserve</span>
                    </div>
                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                        ₹<?php echo number_format(abs($adv_total), 2); ?></h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-tight mt-1">Credit Deposits</p>
                </div>
            </div>

            <!-- Intelligent Filters -->
            <div class="glass-card mb-8">
                <form id="customer-filter-form" method="GET" class="flex flex-col md:flex-row gap-4 items-end p-5">
                    <input type="hidden" name="action" value="list">

                    <div class="w-full md:w-80">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 block ms-1">Status
                            Protocol</label>
                        <div class="relative">
                            <select name="status" onchange="this.form.submit()"
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-4 text-sm font-black text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none cursor-pointer pr-16">
                                <option value="">Universal Overview</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active
                                    Pulse</option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Silent
                                    Nodes</option>
                            </select>
                            <iconify-icon icon="solar:round-alt-arrow-down-bold-duotone"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-indigo-400 pointer-events-none text-xl"></iconify-icon>
                        </div>
                    </div>

                    <div class="flex-grow w-full relative">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 block ms-1">Quantum
                            Query Search</label>
                        <div class="relative group">
                            <iconify-icon icon="solar:magnifer-bold-duotone"
                                class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors text-2xl"></iconify-icon>
                            <input type="text" name="search" id="customer-search"
                                class="w-full bg-white border border-slate-200 rounded-2xl pl-14 pr-14 py-4 text-sm font-bold text-slate-800 outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/5 transition-all shadow-sm"
                                placeholder="Locate entity by name, contact or digital ID..."
                                value="<?php echo htmlspecialchars($search); ?>" data-api-url="api/search_customers.php"
                                autocomplete="off">
                            <?php if (!empty($search)): ?>
                                <a href="customers.php?action=list"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors">
                                    <iconify-icon icon="solar:close-circle-bold" width="18"></iconify-icon>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit"
                        class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-3.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-lg hover:shadow-xl active:scale-95">
                        Execute Scan
                    </button>
                </form>
            </div>

            <!-- Client Grid -->
            <div class="pb-4">
                <table class="table-ag">
                    <thead>
                        <tr>
                            <th>
                                <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&order_by=name&order_dir=<?php echo $order_by == 'name' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>"
                                    class="flex items-center gap-2 group cursor-pointer">
                                    Client Profile
                                    <iconify-icon icon="solar:sort-vertical-linear"
                                        class="text-slate-300 group-hover:text-indigo-500 <?php echo $order_by == 'name' ? 'text-indigo-500' : ''; ?>"></iconify-icon>
                                </a>
                            </th>
                            <th>Communication</th>
                            <th>
                                <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&order_by=balance&order_dir=<?php echo $order_by == 'balance' && $order_dir == 'ASC' ? 'DESC' : 'ASC'; ?>"
                                    class="flex items-center gap-2 group cursor-pointer">
                                    Treasury Balance
                                    <iconify-icon icon="solar:sort-vertical-linear"
                                        class="text-slate-300 group-hover:text-indigo-500 <?php echo $order_by == 'balance' ? 'text-indigo-500' : ''; ?>"></iconify-icon>
                                </a>
                            </th>
                            <th>Status Node</th>
                            <th>Last Activity</th>
                            <th class="text-center">Protocol Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6"
                                    class="text-center py-24 bg-white/50 rounded-3xl border border-dashed border-slate-200">
                                    <iconify-icon icon="solar:user-block-bold-duotone"
                                        class="text-6xl text-slate-200 mb-4"></iconify-icon>
                                    <h4 class="text-xl font-bold text-slate-400 tracking-tight">Zero Matches Found in Data Core
                                    </h4>
                                    <a href="customers.php?action=add"
                                        class="text-indigo-500 font-bold hover:underline mt-2 inline-block">Initialize First
                                        Node</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer_item): ?>
                                <tr class="group">
                                    <td>
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-100 group-hover:scale-110 transition-transform">
                                                <?php echo strtoupper(substr($customer_item['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="text-sm font-black text-slate-800 tracking-tight leading-none mb-1">
                                                    <?php echo htmlspecialchars($customer_item['name']); ?>
                                                </h6>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                    <?php echo htmlspecialchars($customer_item['email'] ?: 'No Email Linked'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($customer_item['mobile'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($customer_item['mobile']); ?>"
                                                class="flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-indigo-500 transition-colors">
                                                <iconify-icon icon="solar:phone-bold-duotone" class="text-slate-300"></iconify-icon>
                                                <?php echo htmlspecialchars($customer_item['mobile']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-300 font-medium">No Contact</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $b = $customer_item['balance'];
                                        $b_c = $b > 0 ? 'balance-due' : ($b < 0 ? 'balance-adv' : 'balance-zero');
                                        $b_label = $b > 0 ? 'Due' : ($b < 0 ? 'Advance' : 'Clear');
                                        ?>
                                        <div class="inline-flex flex-col">
                                            <span class="balance-pill <?php echo $b_c; ?> text-sm tracking-tighter">
                                                ₹<?php echo number_format(abs($b), 2); ?>
                                            </span>
                                            <span
                                                class="text-[8px] font-black uppercase tracking-[0.2em] text-center mt-1 <?php echo $b > 0 ? 'text-rose-400' : ($b < 0 ? 'text-emerald-400' : 'text-slate-400'); ?>">
                                                <?php echo $b_label; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="status-pill <?php echo $customer_item['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                            <?php echo $customer_item['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-700">
                                                <?php echo $customer_item['last_transaction_date'] ? date('d M Y', strtotime($customer_item['last_transaction_date'])) : 'Inert Node'; ?>
                                            </span>
                                            <?php if ($customer_item['last_transaction_date']): ?>
                                                <span
                                                    class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"><?php echo date('H:i A', strtotime($customer_item['last_transaction_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="customers.php?action=view&id=<?php echo $customer_item['id']; ?>"
                                                class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all"
                                                title="Scan Profile">
                                                <iconify-icon icon="solar:eye-bold" width="20"></iconify-icon>
                                            </a>
                                            <a href="customers.php?action=edit&id=<?php echo $customer_item['id']; ?>"
                                                class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 hover:text-amber-600 hover:border-amber-200 hover:bg-amber-50 transition-all"
                                                title="Modify Node">
                                                <iconify-icon icon="solar:pen-bold" width="20"></iconify-icon>
                                            </a>

                                            <div class="relative group/actions">
                                                <button
                                                    class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover/actions:bg-slate-100 transition-all">
                                                    <iconify-icon icon="solar:menu-dots-bold" width="20"></iconify-icon>
                                                </button>
                                                <div
                                                    class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-100 rounded-2xl shadow-2xl opacity-0 invisible group-hover/actions:opacity-100 group-hover/actions:visible transition-all z-20 overflow-hidden">
                                                    <a href="udhar.php?action=add&customer_id=<?php echo $customer_item['id']; ?>"
                                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                                        <iconify-icon icon="solar:wad-of-money-bold" class="text-lg"></iconify-icon>
                                                        Add Udhar Node
                                                    </a>
                                                    <a href="payments.php?action=add&customer_id=<?php echo $customer_item['id']; ?>"
                                                        class="flex items-center gap-3 px-4 py-3 text-xs font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors border-t border-slate-50">
                                                        <iconify-icon icon="solar:cash-out-bold" class="text-lg"></iconify-icon>
                                                        Receive Credit
                                                    </a>
                                                    <button
                                                        onclick="if(confirm('Purge this node from digital memory?')) { document.getElementById('deleteForm<?php echo $customer_item['id']; ?>').submit(); }"
                                                        class="w-full flex items-center gap-3 px-4 py-3 text-xs font-bold text-rose-500 hover:bg-rose-50 transition-colors border-t border-slate-50">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold"
                                                            class="text-lg"></iconify-icon>
                                                        Delete Node
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="deleteForm<?php echo $customer_item['id']; ?>" method="POST"
                                            style="display:none;">
                                            <input type="hidden" name="customer_id" value="<?php echo $customer_item['id']; ?>">
                                            <input type="hidden" name="delete_customer" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bridge -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center items-center gap-2">
                    <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo max(1, $page - 1); ?>"
                        class="<?php echo $page == 1 ? 'pointer-events-none opacity-30' : ''; ?> w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:border-indigo-500 hover:text-indigo-500 transition-all">
                        <iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>
                    </a>
                    <div class="flex gap-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo $i; ?>"
                                class="w-10 h-10 rounded-xl font-black text-xs flex items-center justify-center transition-all <?php echo $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-400 hover:border-indigo-300'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <a href="?action=list&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>&page=<?php echo min($total_pages, $page + 1); ?>"
                        class="<?php echo $page == $total_pages ? 'pointer-events-none opacity-30' : ''; ?> w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:border-indigo-500 hover:text-indigo-500 transition-all">
                        <iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>
                    </a>
                </div>
            <?php endif; ?>

        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Onboarding / Calibration Forms -->
            <div class="max-w-4xl mx-auto">
                <div class="glass-card overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl">
                            <iconify-icon icon="solar:user-speak-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">
                                <?php echo $action == 'add' ? 'Initialize New Node' : 'Calibrate Entity Data'; ?>
                            </h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Metadata Configuration
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="" class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <!-- Field: Name -->
                            <div class="md:col-span-2">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Entity
                                    Primary Name *</label>
                                <div class="relative group">
                                    <iconify-icon icon="solar:user-id-bold-duotone"
                                        class="absolute left-5 top-1/2 -translate-y-1/2 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                    <input type="text" name="name" required class="w-full floating-input pl-14"
                                        placeholder="e.g. Alexander Pierce"
                                        value="<?php echo $action == 'edit' ? htmlspecialchars($customer['name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Field: Contact -->
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Bio-Link
                                    Contact</label>
                                <div class="relative group">
                                    <iconify-icon icon="solar:phone-calling-bold-duotone"
                                        class="absolute left-5 top-1/2 -translate-y-1/2 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                    <input type="tel" name="mobile" pattern="[0-9]{10}" class="w-full floating-input pl-14"
                                        placeholder="10 Digit Frequency"
                                        value="<?php echo $action == 'edit' ? htmlspecialchars($customer['mobile']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Field: Email -->
                            <div>
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Digital
                                    Signal (Email)</label>
                                <div class="relative group">
                                    <iconify-icon icon="solar:letter-bold-duotone"
                                        class="absolute left-5 top-1/2 -translate-y-1/2 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                    <input type="email" name="email" class="w-full floating-input pl-14"
                                        placeholder="node@matrix.com"
                                        value="<?php echo $action == 'edit' ? htmlspecialchars($customer['email']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Field: Address -->
                            <div class="md:col-span-2">
                                <label
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Geographic
                                    Coordinates (Address)</label>
                                <div class="relative group">
                                    <iconify-icon icon="solar:map-point-bold-duotone"
                                        class="absolute left-5 top-5 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                    <textarea name="address" rows="3" class="w-full floating-input pl-14 pt-4 resize-none"
                                        placeholder="Primary residency or base operations..."><?php echo $action == 'edit' ? htmlspecialchars($customer['address']) : ''; ?></textarea>
                                </div>
                            </div>

                            <?php if ($action == 'edit'): ?>
                                <!-- Node Status -->
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Node
                                        Connectivity</label>
                                    <div class="relative">
                                        <select name="status"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3.5 text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                            <option value="active" <?php echo $customer['status'] == 'active' ? 'selected' : ''; ?>>Active Stream</option>
                                            <option value="inactive" <?php echo $customer['status'] == 'inactive' ? 'selected' : ''; ?>>Cold Storage</option>
                                        </select>
                                        <iconify-icon icon="solar:alt-arrow-down-bold"
                                            class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></iconify-icon>
                                    </div>
                                </div>

                                <!-- Real-time Balance Display -->
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ms-1">Current
                                        Fiscal Gravity</label>
                                    <div
                                        class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                                        <span
                                            class="text-2xl font-black <?php echo $customer['balance'] > 0 ? 'text-rose-600' : ($customer['balance'] < 0 ? 'text-emerald-600' : 'text-slate-400'); ?>">
                                            ₹<?php echo number_format(abs($customer['balance']), 2); ?>
                                        </span>
                                        <span
                                            class="text-[9px] font-black uppercase tracking-widest px-2 py-1 bg-white border border-slate-200 rounded-lg">
                                            <?php echo $customer['balance'] > 0 ? 'Negative Delta (Due)' : ($customer['balance'] < 0 ? 'Positive Surplus (Adv)' : 'Neutral'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-col md:flex-row gap-4 pt-6 border-t border-slate-100">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                <button type="submit" name="update_customer"
                                    class="flex-grow bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-indigo-100 flex items-center justify-center gap-2">
                                    <iconify-icon icon="solar:check-read-bold" class="text-xl"></iconify-icon>
                                    Confirm Calibration
                                </button>
                                <button type="button" onclick="document.getElementById('editDeleteDialog').showModal();"
                                    class="px-8 bg-rose-50 text-rose-500 border border-rose-100 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-100 transition-all">
                                    Purge Data
                                </button>
                            <?php else: ?>
                                <button type="submit" name="add_customer"
                                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-indigo-100 flex items-center justify-center gap-2">
                                    <iconify-icon icon="solar:rocket-bold" class="text-xl"></iconify-icon>
                                    Launch Entity Node
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Native Modal for Delete Confirmation -->
            <dialog id="editDeleteDialog"
                class="p-0 rounded-3xl border-none shadow-2xl glass-card backdrop:backdrop-blur-sm max-w-md w-full">
                <div class="p-8">
                    <div
                        class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-6">
                        <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 text-center tracking-tight mb-2">Destructive Sequence
                        Initiated</h3>
                    <p class="text-sm text-slate-500 text-center leading-relaxed mb-8">
                        Are you certain you wish to purge <span
                            class="font-black text-slate-800"><?php echo htmlspecialchars($customer['name']); ?></span>?
                        This action will dissolve all transactional history and linked data.
                    </p>
                    <div class="flex gap-3">
                        <form method="POST" action="" class="flex-grow">
                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                            <button type="submit" name="delete_customer"
                                class="w-full bg-rose-600 hover:bg-rose-500 text-white py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Execute
                                Purge</button>
                        </form>
                        <button onclick="document.getElementById('editDeleteDialog').close();"
                            class="flex-grow bg-slate-50 hover:bg-slate-100 text-slate-500 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Abort
                            Action</button>
                    </div>
                </div>
            </dialog>

        <?php elseif ($action == 'view' && $customer): ?>
            <!-- Entity Intelligence Overview (Profile View) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Profile Matrix (Left) -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="glass-card p-8 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full -mr-12 -mt-12"></div>

                        <div class="relative mx-auto w-24 h-24 mb-6">
                            <div
                                class="w-full h-full rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-black text-4xl shadow-2xl shadow-indigo-200">
                                <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                            </div>
                            <div
                                class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full border-4 border-white <?php echo $customer['status'] == 'active' ? 'bg-emerald-500' : 'bg-slate-300'; ?>">
                            </div>
                        </div>

                        <h2 class="text-2xl font-black text-slate-800 tracking-tight leading-none mb-2">
                            <?php echo htmlspecialchars($customer['name']); ?>
                        </h2>
                        <span
                            class="status-pill <?php echo $customer['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?> text-[9px]">
                            Connection: <?php echo $customer['status']; ?>
                        </span>

                        <div class="mt-8 space-y-4 pt-8 border-t border-slate-100">
                            <?php if ($customer['mobile']): ?>
                                <div
                                    class="flex items-center gap-4 px-4 py-3 bg-slate-50/50 rounded-2xl border border-slate-100">
                                    <iconify-icon icon="solar:phone-bold-duotone"
                                        class="text-indigo-400 text-xl"></iconify-icon>
                                    <div class="text-left">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Mobile
                                            Frequency</p>
                                        <a href="tel:<?php echo $customer['mobile']; ?>"
                                            class="text-sm font-bold text-slate-700 hover:text-indigo-500 transition-colors"><?php echo $customer['mobile']; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($customer['email']): ?>
                                <div
                                    class="flex items-center gap-4 px-4 py-3 bg-slate-50/50 rounded-2xl border border-slate-100">
                                    <iconify-icon icon="solar:letter-bold-duotone"
                                        class="text-violet-400 text-xl"></iconify-icon>
                                    <div class="text-left">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Digital
                                            Signature</p>
                                        <a href="mailto:<?php echo $customer['email']; ?>"
                                            class="text-sm font-bold text-slate-700 hover:text-indigo-500 transition-colors truncate block w-full"><?php echo $customer['email']; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-8">
                            <a href="customers.php?action=edit&id=<?php echo $customer['id']; ?>"
                                class="flex items-center justify-center gap-2 bg-white border border-slate-200 py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:border-indigo-500 hover:text-indigo-500 transition-all active:scale-95 shadow-sm">
                                <iconify-icon icon="solar:pen-new-square-bold"></iconify-icon> Modify
                            </a>
                            <a href="udhar.php?action=add&customer_id=<?php echo $customer['id']; ?>"
                                class="flex items-center justify-center gap-2 bg-indigo-600 text-white py-3 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-indigo-500 transition-all active:scale-95 shadow-lg shadow-indigo-100">
                                <iconify-icon icon="solar:add-circle-bold"></iconify-icon> Udhar
                            </a>
                        </div>
                    </div>

                    <!-- Technical Specs -->
                    <div class="glass-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                            <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Technical
                                Specifications</h6>
                        </div>
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-slate-400 uppercase tracking-tighter">Entity ID</span>
                                    <span
                                        class="font-black text-slate-800">#<?php echo str_pad($customer['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                </li>
                                <li class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-slate-400 uppercase tracking-tighter">Member
                                        Activation</span>
                                    <span
                                        class="font-black text-slate-800"><?php echo date('d M Y', strtotime($customer['created_at'])); ?></span>
                                </li>
                                <li class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-slate-400 uppercase tracking-tighter">Last
                                        Synchronization</span>
                                    <span
                                        class="font-black text-slate-800"><?php echo date('d M Y', strtotime($customer['updated_at'])); ?></span>
                                </li>
                                <li class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-slate-400 uppercase tracking-tighter">Recent
                                        Pulsation</span>
                                    <span
                                        class="font-black <?php echo $customer['last_transaction_date'] ? 'text-indigo-500' : 'text-slate-300'; ?>">
                                        <?php echo $customer['last_transaction_date'] ? date('d M Y', strtotime($customer['last_transaction_date'])) : 'Never Pulse'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($customer['address']): ?>
                        <div class="glass-card p-6">
                            <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Base Operations
                                (Address)</h6>
                            <p class="text-sm font-bold text-slate-600 leading-relaxed italic">
                                "<?php echo nl2br(htmlspecialchars($customer['address'])); ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Financial Intelligence (Right) -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Balance Orbit Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="glass-card p-6 relative overflow-hidden group">
                            <div
                                class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-125 transition-transform duration-700">
                                <iconify-icon icon="solar:history-bold-duotone" width="120"></iconify-icon>
                            </div>
                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Lifetime
                                Exposure</p>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                                ₹<?php echo number_format($customer['total_udhar'] ?? 0, 2); ?></h3>
                            <div class="mt-4 w-12 h-1 bg-indigo-500 rounded-full"></div>
                        </div>

                        <div class="glass-card p-6 relative overflow-hidden group">
                            <div
                                class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-125 transition-transform duration-700 text-emerald-500">
                                <iconify-icon icon="solar:check-circle-bold-duotone" width="120"></iconify-icon>
                            </div>
                            <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2">Recuperated
                                Total</p>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                                ₹<?php echo number_format($customer['total_paid'] ?? 0, 2); ?></h3>
                            <div class="mt-4 w-12 h-1 bg-emerald-500 rounded-full"></div>
                        </div>

                        <div
                            class="glass-card p-6 relative overflow-hidden border-2 <?php echo $customer['balance'] > 0 ? 'border-rose-100' : ($customer['balance'] < 0 ? 'border-amber-100' : 'border-slate-100'); ?>">
                            <div class="absolute -right-4 -bottom-4 opacity-10">
                                <iconify-icon icon="solar:bill-list-bold-duotone" width="120"
                                    class="<?php echo $customer['balance'] > 0 ? 'text-rose-500' : ($customer['balance'] < 0 ? 'text-amber-500' : 'text-slate-500'); ?>"></iconify-icon>
                            </div>
                            <p
                                class="text-[10px] font-black uppercase tracking-widest mb-2 <?php echo $customer['balance'] > 0 ? 'text-rose-400' : ($customer['balance'] < 0 ? 'text-amber-400' : 'text-slate-400'); ?>">
                                Net Gravity Balance
                            </p>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tighter">
                                ₹<?php echo number_format(abs($customer['balance']), 2); ?></h3>
                            <p
                                class="text-[8px] font-black uppercase tracking-[0.2em] mt-1 <?php echo $customer['balance'] > 0 ? 'text-rose-500' : ($customer['balance'] < 0 ? 'text-amber-500' : 'text-slate-400'); ?>">
                                <?php echo $customer['balance'] > 0 ? 'Current Liabilities (Due)' : ($customer['balance'] < 0 ? 'Asset Reserve (Adv)' : 'Equilibrium'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Recent Chronology (Udhar Units) -->
                    <div class="glass-card">
                        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <iconify-icon icon="solar:history-bold-duotone"
                                    class="text-xl text-slate-400"></iconify-icon>
                                <h4 class="text-lg font-black text-slate-800 tracking-tight">Recent Synchronizations</h4>
                            </div>
                            <a href="reports.php?customer_id=<?php echo $customer['id']; ?>"
                                class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline">View
                                Galactic Log</a>
                        </div>
                        <div class="p-4 overflow-x-auto">
                            <?php if (empty($transactions)): ?>
                                <div class="text-center py-12 flex flex-col items-center">
                                    <iconify-icon icon="solar:plain-bold-duotone"
                                        class="text-5xl text-slate-200 mb-3"></iconify-icon>
                                    <p class="text-sm font-bold text-slate-400">No Transaction Data Emitted Yet</p>
                                </div>
                            <?php else: ?>
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                            <th class="px-4 py-3">Timestamp</th>
                                            <th class="px-4 py-3">Mission Narrative</th>
                                            <th class="px-4 py-3">Flow Vol</th>
                                            <th class="px-4 py-3">Condition</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-4 py-6 font-bold text-slate-700">
                                                    <?php echo date('d M, Y', strtotime($trans['transaction_date'])); ?>
                                                </td>
                                                <td class="px-4 py-6 text-slate-500 font-medium">
                                                    <?php echo htmlspecialchars($trans['description']); ?>
                                                </td>
                                                <td class="px-4 py-6 font-black text-slate-800 tracking-tight">
                                                    ₹<?php echo number_format($trans['amount'], 2); ?></td>
                                                <td class="px-4 py-6">
                                                    <?php
                                                    $ts = $trans['status'];
                                                    $ts_c = ($ts == 'paid') ? 'badge-active' : (($ts == 'partially_paid') ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'badge-inactive');
                                                    ?>
                                                    <span class="status-pill <?php echo $ts_c; ?> text-[8px]">
                                                        <?php echo str_replace('_', ' ', $ts); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Treasury Input Log (Payments) -->
                    <div class="glass-card">
                        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/20">
                            <div class="flex items-center gap-3">
                                <iconify-icon icon="solar:cash-out-bold-duotone"
                                    class="text-xl text-emerald-500"></iconify-icon>
                                <h4 class="text-lg font-black text-slate-800 tracking-tight">Recent Credit Injections</h4>
                            </div>
                            <a href="payments.php?customer_id=<?php echo $customer['id']; ?>"
                                class="text-[10px] font-black text-indigo-500 uppercase tracking-widest hover:underline">Full
                                Treasury Report</a>
                        </div>
                        <div class="p-4 overflow-x-auto">
                            <?php if (empty($payments)): ?>
                                <div class="text-center py-12 flex flex-col items-center">
                                    <iconify-icon icon="solar:cloud-snow-bold-duotone"
                                        class="text-5xl text-slate-200 mb-3"></iconify-icon>
                                    <p class="text-sm font-bold text-slate-400">Zero Credit Intake Detected</p>
                                </div>
                            <?php else: ?>
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr
                                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">
                                            <th class="px-4 py-3">Timestamp</th>
                                            <th class="px-4 py-3">Val Intake</th>
                                            <th class="px-4 py-3">Mode</th>
                                            <th class="px-4 py-3">Ref ID</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php foreach ($payments as $payment): ?>
                                            <tr class="hover:bg-emerald-50/20 transition-colors">
                                                <td class="px-4 py-6 font-bold text-slate-700">
                                                    <?php echo date('d M, Y', strtotime($payment['payment_date'])); ?>
                                                </td>
                                                <td class="px-4 py-6 font-black text-emerald-600 tracking-tight">
                                                    ₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td class="px-4 py-6">
                                                    <span
                                                        class="px-3 py-1 bg-white border border-slate-100 rounded-lg text-[10px] font-black text-slate-500 shadow-sm uppercase tracking-tighter">
                                                        <?php echo $payment['payment_mode']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-6 font-mono text-[10px] text-slate-400">
                                                    <?php echo htmlspecialchars($payment['reference_no'] ?: 'AUTO-GENERA'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Core Neural Links -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/search_suggestions.js"></script>
    <script src="assets/js/customers_custom.js"></script>

    <script>
        // Search suggestions are initialized in assets/js/customers_custom.js
        // to handle specific redirection logic.
    </script>
</body>

</html>