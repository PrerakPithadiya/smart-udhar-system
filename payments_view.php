<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Payment Management'; ?> | Smart Udhar Pro</title>

    <!-- Core Engine -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="assets/js/common.js" defer></script>

    <style>
        :root {
            --bg-airy: #f8fafc;
            --accent-indigo: #6366f1;
            --glass-white: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
            --sidebar-width-ag: 280px;
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

        /* Sidebar Style Overrides */
        .sidebar {
            width: var(--sidebar-width-ag) !important;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(20px) !important;
            border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
            box-shadow: 10px 0 40px -20px rgba(0, 0, 0, 0.05) !important;
            z-index: 50 !important;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .sidebar.closed { margin-left: calc(-1 * var(--sidebar-width-ag)) !important; }

        .sidebar-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
            padding: 32px 24px !important;
            position: relative !important;
        }

        #sidebarToggle {
            position: absolute !important;
            right: 20px !important;
            top: 20px !important;
            z-index: 60 !important;
        }

        .sidebar-header h4 { color: #0f172a !important; font-weight: 800 !important; }
        .sidebar-header .shop-name { color: #64748b !important; font-weight: 700 !important; letter-spacing: 0.1em !important; }
        .sidebar-header-content { display: flex; flex-direction: column; gap: 4px; }

        .sidebar .nav-link {
            padding: 14px 24px !important;
            margin: 4px 16px !important;
            border-radius: 16px !important;
            color: #64748b !important;
            font-weight: 600 !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            transition: all 0.3s ease !important;
        }

        .sidebar .nav-link i { font-size: 1.25rem !important; margin: 0 !important; width: auto !important; opacity: 0.7; }
        .sidebar .nav-link:hover { background: rgba(99, 102, 241, 0.05) !important; color: #6366f1 !important; transform: translateX(4px) !important; }
        .sidebar .nav-link.active {
            background: #6366f1 !important;
            color: #fff !important;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.3) !important;
        }
        .sidebar .nav-link.active i { opacity: 1; }

        .sidebar-toggle-btn {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: none !important;
            border-radius: 12px !important;
        }

        .sidebar-footer {
            background: transparent !important;
            border-top: 1px solid rgba(0, 0, 0, 0.03) !important;
            padding: 24px !important;
        }

        /* Glass Panels */
        .glass-panel {
            background: var(--glass-white);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-panel:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.08);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .stat-card-clean {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.03);
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

        @keyframes drawNoodle { to { stroke-dashoffset: 0; } }

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
            0% { top: -80px; opacity: 0; }
            50% { opacity: 0.6; }
            100% { top: 110%; opacity: 0; }
        }

        /* Form Styling */
        .form-input-clean {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.2s ease;
        }

        .form-input-clean:focus {
            background: #fff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar Expansion Fix */
        .main-content { margin-left: var(--sidebar-width-ag); transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); width: calc(100% - var(--sidebar-width-ag)); }
        .main-content.expanded { margin-left: 0; width: 100%; }
        
        @media (max-width: 768px) { 
            .main-content { margin-left: 0; width: 100%; } 
            .main-content.active::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 40;
            }
        }
        
        /* PHP Data Bridge */
        .status-pill { padding: 4px 12px; border-radius: 99px; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>

    <?php
    if (!function_exists('getSortUrl')) {
        function getSortUrl($column, $current_order_by, $current_order_dir)
        {
            $params = $_GET;
            $params['order_by'] = $column;
            if ($column === $current_order_by) {
                $params['order_dir'] = ($current_order_dir === 'ASC') ? 'DESC' : 'ASC';
            } else {
                $params['order_dir'] = ($column === 'p.payment_date' || $column === 'p.created_at') ? 'DESC' : 'ASC';
            }
            return '?' . http_build_query($params);
        }
    }

    if (!function_exists('getSortIcon')) {
        function getSortIcon($column, $current_order_by, $current_order_dir)
        {
            if ($column !== $current_order_by)
                return '<iconify-icon icon="solar:sort-vertical-linear" class="text-slate-300"></iconify-icon>';
            return $current_order_dir === 'ASC' ? '<iconify-icon icon="solar:sort-from-top-to-bottom-bold"></iconify-icon>' : '<iconify-icon icon="solar:sort-from-bottom-to-top-bold"></iconify-icon>';
        }
    }

    if (!function_exists('getFilterUrl')) {
        function getFilterUrl($param, $value)
        {
            $params = $_GET;
            if ($value === '') {
                unset($params[$param]);
            } else {
                $params[$param] = $value;
            }
            $params['page'] = 1; 
            return '?' . http_build_query($params);
        }
    }
    ?>
    <script>
        window.paymentRemainingAmount = <?php echo json_encode($payment['remaining_amount'] ?? 0); ?>;
        window.currentAction = <?php echo json_encode($action); ?>;
        window.currentCustomerId = <?php echo json_encode($customer_id); ?>;
        window.allCustomers = <?php echo json_encode($customers); ?>;
    </script>
</head>

<body class="bg-[var(--bg-airy)]">
    <!-- Sidebar Toggle Commander (Visible when closed) -->
    <button id="sidebarOpenBtn" class="fixed left-0 top-1/2 -translate-y-1/2 w-12 h-16 bg-white border border-slate-200 text-indigo-600 rounded-r-2xl flex items-center justify-center shadow-xl shadow-indigo-100/50 hover:w-14 active:scale-95 transition-all z-[100] hidden">
        <iconify-icon icon="solar:sidebar-minimalistic-bold-duotone" width="24"></iconify-icon>
    </button>

    <!-- Aesthetics Layer -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-60">
        <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <path class="noodle-path" d="M -100,300 C 200,200 400,500 600,300 C 800,100 1000,400 1200,200 C 1400,0 1600,300 1800,200" />
            <path class="noodle-path" style="animation-delay: -5s;" d="M -100,600 C 300,500 500,800 800,600 C 1100,400 1300,700 1600,500" />
        </svg>
    </div>
    <div class="beam" style="left: 15%; animation-delay: 1s;"></div>
    <div class="beam" style="left: 45%; animation-delay: 4s;"></div>
    <div class="beam" style="left: 85%; animation-delay: 2s;"></div>

    <?php include 'includes/sidebar.php'; ?>

    <div id="mainContent" class="main-content min-h-screen relative z-10 px-4 py-8 md:px-10">
        
        <!-- Header Section -->
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="flex flex-col">
                    <nav class="flex text-[10px] items-center gap-1.5 font-bold uppercase tracking-widest text-slate-400 mb-2">
                        <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                        <span>Smart Udhar</span>
                        <iconify-icon icon="solar:alt-arrow-right-bold" class="text-[8px]"></iconify-icon>
                        <span class="text-indigo-500">Payments</span>
                    </nav>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tighter flex items-center gap-3">
                        <iconify-icon icon="solar:cash-out-bold-duotone" class="text-indigo-500"></iconify-icon>
                        Treasury Flow
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($action == 'list'): ?>
                        <a href="payments.php?action=add" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl font-bold flex items-center gap-2 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-1">
                            <iconify-icon icon="solar:add-circle-bold" class="text-xl"></iconify-icon>
                            Receive Payment
                        </a>
                <?php else: ?>
                        <a href="payments.php" class="bg-white hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-2xl font-bold border border-slate-200 flex items-center gap-2 transition-all">
                            <iconify-icon icon="solar:arrow-left-bold" class="text-xl"></iconify-icon>
                            Return to Vault
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
                    <!-- Stat: Total -->
                    <div class="glass-panel p-6 bg-white/90">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl">
                                <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aggregate</span>
                        </div>
                        <?php
                        $total_stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ?");
                        $total_stmt->bind_param("i", $_SESSION['user_id']);
                        $total_stmt->execute();
                        $total_result = $total_stmt->get_result()->fetch_assoc();
                        $total_stmt->close();
                        ?>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Inflow</p>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">₹<?php echo number_format($total_result['total'] ?? 0, 2); ?></h2>
                    </div>

                    <!-- Stat: Allocated -->
                    <div class="glass-panel p-6 bg-white/90">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Verified</span>
                        </div>
                        <?php
                        $allocated_stmt = $conn->prepare("SELECT SUM(allocated_amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.is_allocated = 1");
                        $allocated_stmt->bind_param("i", $_SESSION['user_id']);
                        $allocated_stmt->execute();
                        $allocated_result = $allocated_stmt->get_result()->fetch_assoc();
                        $allocated_stmt->close();
                        ?>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Allocated Capital</p>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight text-emerald-600">₹<?php echo number_format($allocated_result['total'] ?? 0, 2); ?></h2>
                    </div>

                    <!-- Stat: Unallocated -->
                    <div class="glass-panel p-6 bg-white/90">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl">
                                <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Floating</span>
                        </div>
                        <?php
                        $unallocated_stmt = $conn->prepare("SELECT SUM(remaining_amount) as total FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.remaining_amount > 0");
                        $unallocated_stmt->bind_param("i", $_SESSION['user_id']);
                        $unallocated_stmt->execute();
                        $unallocated_result = $unallocated_stmt->get_result()->fetch_assoc();
                        $unallocated_stmt->close();
                        ?>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Unallocated Funds</p>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight text-amber-600">₹<?php echo number_format($unallocated_result['total'] ?? 0, 2); ?></h2>
                    </div>

                    <!-- Stat: Today -->
                    <div class="glass-panel p-6 bg-white/90">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                                <iconify-icon icon="solar:hand-stars-bold-duotone"></iconify-icon>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Daily Heat</span>
                        </div>
                        <?php
                        $today_stmt = $conn->prepare("SELECT COUNT(*) as count FROM payments p JOIN customers c ON p.customer_id = c.id WHERE c.user_id = ? AND p.payment_date = CURDATE()");
                        $today_stmt->bind_param("i", $_SESSION['user_id']);
                        $today_stmt->execute();
                        $today_result = $today_stmt->get_result()->fetch_assoc();
                        $today_stmt->close();
                        ?>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Today's Transactions</p>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo number_format($today_result['count'] ?? 0); ?> Events</h2>
                    </div>
                </div>

                <!-- Intelligent Filter Grid -->
                <div class="glass-panel p-8 mb-10 bg-white/95">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <input type="hidden" name="action" value="list">
                        <input type="hidden" name="customer" value="<?php echo $customer_filter; ?>">

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Protocol Filter</label>
                            <select name="payment_mode" class="w-full form-input-clean" onchange="this.form.submit()">
                                <option value="">All Payment Modes</option>
                                <option value="cash" <?php echo $payment_mode_filter == 'cash' ? 'selected' : ''; ?>>Physical Cash</option>
                                <option value="bank_transfer" <?php echo $payment_mode_filter == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="upi" <?php echo $payment_mode_filter == 'upi' ? 'selected' : ''; ?>>Unified Interface (UPI)</option>
                                <option value="cheque" <?php echo $payment_mode_filter == 'cheque' ? 'selected' : ''; ?>>Bank Instrument (Cheque)</option>
                                <option value="other" <?php echo $payment_mode_filter == 'other' ? 'selected' : ''; ?>>Alternative Mode</option>
                            </select>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Temporal Range</label>
                            <div class="flex items-center gap-2">
                                <input type="date" class="flex-1 form-input-clean" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                                <span class="text-slate-300">to</span>
                                <input type="date" class="flex-1 form-input-clean" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Global Search</label>
                            <div class="relative">
                                <iconify-icon icon="solar:magnifer-linear" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></iconify-icon>
                                <input type="text" name="search" id="customer-search-payments" class="w-full form-input-clean pl-10" 
                                    placeholder="Hash, ID, Name..." 
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    data-api-url="api/search_customers.php?has_payments=1">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                                <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon>
                                Execute
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Ledger Master List -->
                <div class="glass-panel overflow-hidden bg-white/95">
                    <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/30">
                        <h3 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                            <iconify-icon icon="solar:database-bold-duotone" class="text-indigo-500"></iconify-icon>
                            Historical Settlements
                        </h3>
                        <span class="px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black text-slate-500 uppercase"><?php echo $total_payments; ?> Records Linked</span>
                    </div>

                    <div>
                        <?php if (empty($payments_list)): ?>
                                <div class="py-24 text-center">
                                    <iconify-icon icon="solar:ghost-line-duotone" class="text-8xl text-slate-200 mb-6"></iconify-icon>
                                    <h4 class="text-slate-400 font-bold italic tracking-wide">Data stream currently silent...</h4>
                                    <p class="text-slate-300 text-sm">Initiate a payment to populate the ledger.</p>
                                </div>
                        <?php else: ?>
                                <table class="w-full">
                                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 border-b border-slate-100">
                                        <tr>
                                            <th class="px-8 py-5 text-left">
                                                <a href="<?php echo getSortUrl('p.payment_date', $order_by, $order_dir); ?>" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
                                                    Timeline
                                                    <?php echo getSortIcon('p.payment_date', $order_by, $order_dir); ?>
                                                </a>
                                            </th>
                                            <th class="px-8 py-5 text-left">
                                                <a href="<?php echo getSortUrl('c.name', $order_by, $order_dir); ?>" class="hover:text-indigo-600 transition-colors flex items-center gap-2">
                                                    Entity
                                                    <?php echo getSortIcon('c.name', $order_by, $order_dir); ?>
                                                </a>
                                            </th>
                                            <th class="px-8 py-5 text-right">
                                                <a href="<?php echo getSortUrl('p.amount', $order_by, $order_dir); ?>" class="hover:text-indigo-600 transition-colors justify-end flex items-center gap-2">
                                                    Credit Amount
                                                    <?php echo getSortIcon('p.amount', $order_by, $order_dir); ?>
                                                </a>
                                            </th>
                                            <th class="px-8 py-5 text-center">Settlement Mode</th>
                                            <th class="px-8 py-5 text-center">Allocation Logic</th>
                                            <th class="px-8 py-5 text-center">Kernel</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php foreach ($payments_list as $payment_item): ?>
                                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                                    <td class="px-8 py-6">
                                                        <div class="flex items-center gap-3">
                                                            <iconify-icon icon="solar:calendar-date-bold" class="text-slate-300"></iconify-icon>
                                                            <div>
                                                                <p class="text-sm font-bold text-slate-700"><?php echo date('d M Y', strtotime($payment_item['payment_date'])); ?></p>
                                                                <?php if ($payment_item['payment_date'] == date('Y-m-d')): ?>
                                                                        <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-1.5 py-0.5 rounded">Live Now</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 py-6">
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                                                                <?php echo strtoupper(substr($payment_item['customer_name'], 0, 1)); ?>
                                                            </div>
                                                            <p class="text-sm font-black text-slate-800 tracking-tight"><?php echo htmlspecialchars($payment_item['customer_name']); ?></p>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 py-6 text-right">
                                                        <span class="text-lg font-black text-indigo-600 tracking-tighter">₹<?php echo number_format($payment_item['amount'], 2); ?></span>
                                                    </td>
                                                    <td class="px-8 py-6 text-center">
                                                        <?php
                                                        $m = $payment_item['payment_mode'];
                                                        $m_c = 'text-slate-400 bg-slate-50 border-slate-100';
                                                        switch ($m) {
                                                            case 'cash':
                                                                $m_c = 'text-emerald-500 bg-emerald-50 border-emerald-100';
                                                                break;
                                                            case 'bank_transfer':
                                                                $m_c = 'text-blue-500 bg-blue-50 border-blue-100';
                                                                break;
                                                            case 'upi':
                                                                $m_c = 'text-purple-500 bg-purple-50 border-purple-100';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="status-pill border <?php echo $m_c; ?>"><?php echo $m ?? 'Unknown'; ?></span>
                                                    </td>
                                                    <td class="px-8 py-6">
                                                        <div class="flex flex-col items-center gap-1">
                                                            <?php
                                                            $ac = 'text-slate-400 bg-slate-50 border-slate-100';
                                                            if ($payment_item['is_allocated'] && $payment_item['remaining_amount'] == 0)
                                                                $ac = 'text-emerald-500 bg-emerald-50 border-emerald-100';
                                                            elseif ($payment_item['is_allocated'])
                                                                $ac = 'text-amber-500 bg-amber-50 border-amber-100';
                                                            ?>
                                                            <span class="status-pill border <?php echo $ac; ?>">
                                                                <?php echo ($payment_item['is_allocated'] && $payment_item['remaining_amount'] == 0) ? 'Fully Linked' : ($payment_item['is_allocated'] ? 'Split' : 'Floating'); ?>
                                                            </span>
                                                            <?php if ($payment_item['is_allocated']): ?>
                                                                    <div class="w-16 h-1 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                                                                        <div class="h-full bg-emerald-500" style="width: <?php echo ($payment_item['allocated_amount'] / $payment_item['amount']) * 100; ?>%"></div>
                                                                    </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-8 py-6">
                                                        <div class="flex justify-center items-center gap-2">
                                                            <a href="payments.php?action=view&id=<?php echo $payment_item['id']; ?>" class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                                                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                                            </a>
                                                            <a href="payments.php?action=edit&id=<?php echo $payment_item['id']; ?>" class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-amber-600 hover:border-amber-200 transition-all shadow-sm">
                                                                <iconify-icon icon="solar:pen-bold"></iconify-icon>
                                                            </a>
                                                            <button onclick="confirmDelete(<?php echo $payment_item['id']; ?>, '<?php echo htmlspecialchars(addslashes($payment_item['customer_name'])); ?>')" class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 hover:text-rose-600 hover:border-rose-200 transition-all shadow-sm">
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
                                    <a href="<?php echo getFilterUrl('page', max(1, $page - 1)); ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-400 transition-all">
                                        <iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>
                                    </a>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="<?php echo getFilterUrl('page', $i); ?>" class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-xs transition-all <?php echo $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                    <?php endfor; ?>
                                    <a href="<?php echo getFilterUrl('page', min($total_pages, $page + 1)); ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:border-indigo-400 transition-all">
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
                        <div class="px-10 py-8 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-100/50 flex items-center gap-6">
                            <div class="w-16 h-16 rounded-[2rem] bg-indigo-600 text-white flex items-center justify-center text-3xl shadow-xl shadow-indigo-200 animate-float">
                                <iconify-icon icon="<?php echo $action == 'add' ? 'solar:cloud-plus-bold-duotone' : 'solar:pen-bold-duotone'; ?>"></iconify-icon>
                            </div>
                            <div>
                                <h2 class="text-3xl font-black text-indigo-900 tracking-tighter"><?php echo $action == 'add' ? 'Initiate Receipt' : 'Update Protocol'; ?></h2>
                                <p class="text-indigo-500/70 text-sm font-bold uppercase tracking-widest">Transaction Kernel Module v4.7</p>
                            </div>
                        </div>
                    
                        <div class="p-10">
                            <form method="POST" action="" id="paymentForm" class="space-y-8">
                                <?php if ($action == 'edit'): ?>
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                <?php endif; ?>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="md:col-span-2 relative">
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Linked Customer Entity *</label>
                                        <div class="relative group">
                                            <iconify-icon icon="solar:user-circle-bold" class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl text-slate-300 group-focus-within:text-indigo-500 transition-colors"></iconify-icon>
                                            <?php if ($action == 'add'): ?>
                                                    <input type="text" class="w-full form-input-clean pl-12 py-5 text-lg font-bold" id="customer_search"
                                                        placeholder="Begin identifying entity..." autocomplete="off"
                                                        value="<?php echo htmlspecialchars($selected_customer_name ?? ''); ?>" required>
                                                    <input type="hidden" id="customer_id" name="customer_id" value="<?php echo $customer_id ? $customer_id : ''; ?>" required>
                                                    <div id="customer_results" class="absolute left-0 right-0 top-full mt-2 glass-panel z-50 overflow-hidden hidden"></div>
                                            <?php else: ?>
                                                    <input type="text" class="w-full form-input-clean pl-12 py-5 text-lg font-bold bg-slate-50 opacity-60" value="<?php echo htmlspecialchars($payment['customer_name']); ?>" disabled>
                                                    <p class="text-[10px] text-rose-400 font-bold uppercase mt-2 italic px-2">Encryption Lock: Entity immutability active for established records.</p>
                                            <?php endif; ?>
                                        </div>
                                        <div id="customerBalanceInfo" class="mt-4 flex items-center gap-2 hidden">
                                            <iconify-icon icon="solar:info-circle-bold-duotone" class="text-indigo-500"></iconify-icon>
                                            <span class="text-xs font-black text-slate-500 uppercase">Live Exposure: <span id="customerBalance" class="text-rose-600 font-extrabold">0.00</span></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Temporal Tag *</label>
                                        <input type="date" class="w-full form-input-clean" name="payment_date" 
                                            value="<?php echo $action == 'add' ? date('Y-m-d') : $payment['payment_date']; ?>" required>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Capital Value (INR) *</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                                            <input type="number" class="w-full form-input-clean pl-10 text-xl font-black text-indigo-600 tracking-tighter" name="amount" step="0.01" min="0.01" 
                                                placeholder="0.00" value="<?php echo $action == 'edit' ? number_format($payment['amount'], 2, '.', '') : ''; ?>" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Gateway Protocol *</label>
                                        <select class="w-full form-input-clean" name="payment_mode" required>
                                            <option value="cash" <?php echo ($action == 'edit' && $payment['payment_mode'] == 'cash') ? 'selected' : ''; ?>>Physical Cash Handover</option>
                                            <option value="bank_transfer" <?php echo ($action == 'edit' && $payment['payment_mode'] == 'bank_transfer') ? 'selected' : ''; ?>>Direct Ledger Transfer</option>
                                            <option value="upi" <?php echo ($action == 'edit' && $payment['payment_mode'] == 'upi') ? 'selected' : ''; ?>>Digital Merchant (UPI)</option>
                                            <option value="cheque" <?php echo ($action == 'edit' && $payment['payment_mode'] == 'cheque') ? 'selected' : ''; ?>>Instrumented Check</option>
                                            <option value="other" <?php echo ($action == 'edit' && $payment['payment_mode'] == 'other') ? 'selected' : ''; ?>>Alternative Settlement</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">External Reference Hash</label>
                                        <input type="text" class="w-full form-input-clean" name="reference_no" 
                                            placeholder="UTI / TXN NO..." value="<?php echo $action == 'edit' ? htmlspecialchars($payment['reference_no']) : ''; ?>">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] mb-3">Internal Annotation</label>
                                        <textarea class="w-full form-input-clean h-32 resize-none" name="notes" placeholder="Metadata relating to this inflow..."><?php echo $action == 'edit' ? htmlspecialchars($payment['notes']) : ''; ?></textarea>
                                    </div>

                                    <?php if ($action == 'add'): ?>
                                        <div class="md:col-span-2 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                            <label class="flex items-center gap-3 cursor-pointer group">
                                                <input type="checkbox" name="auto_allocate" value="1" checked class="w-6 h-6 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                <div>
                                                    <p class="text-sm font-black text-slate-700 tracking-tight">Activate Autonomous Allocation</p>
                                                    <p class="text-[10px] text-slate-500 font-bold italic uppercase tracking-widest mt-0.5">FIFO Algorithm: Link capital to oldest outstanding debits automatically.</p>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-center gap-4 pt-6">
                                    <button type="submit" name="<?php echo $action == 'add' ? 'add_payment' : 'update_payment'; ?>" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-lg tracking-tight shadow-xl shadow-indigo-100 transition-all hover:scale-[1.02]">
                                        <iconify-icon icon="solar:check-read-linear" class="mr-2"></iconify-icon>
                                        Commit Transaction
                                    </button>
                                    <a href="payments.php" class="px-8 py-4 text-slate-400 font-bold hover:text-slate-600 transition-colors">Discard</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

        <?php elseif ($action == 'view' && $payment): ?>
                <!-- Inspection Interface (View) -->
                <div class="max-w-5xl mx-auto space-y-8">
                    <div class="glass-panel overflow-hidden bg-white/95 shadow-3xl">
                        <div class="px-12 py-10 bg-slate-900 flex justify-between items-center text-white relative">
                            <div class="absolute inset-0 overflow-hidden opacity-20">
                                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                    <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="url(#grad)" />
                                    <defs><linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#6366f1"/><stop offset="100%" style="stop-color:#a855f7"/></linearGradient></defs>
                                </svg>
                            </div>
                            <div class="relative z-10">
                                <p class="text-[10px] font-black uppercase tracking-[0.4em] text-indigo-300 mb-2">Authenticated Receipt</p>
                                <h2 class="text-5xl font-black tracking-tighter">Settlement #<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                            </div>
                            <div class="text-right relative z-10">
                                <p class="text-4xl font-black text-indigo-400 tracking-tighter italic">₹<?php echo number_format($payment['amount'], 2); ?></p>
                                <span class="status-pill bg-white/10 text-white border border-white/20 mt-2 inline-block"><?php echo $payment['payment_mode'] ?? 'CORE'; ?></span>
                            </div>
                        </div>
                    
                        <div class="p-12">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                                <div class="space-y-6">
                                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Entry Metadata</h4>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase">Entity</span>
                                            <span class="text-sm font-black text-slate-800"><?php echo htmlspecialchars($payment['customer_name']); ?></span>
                                        </div>
                                        <div class="flex justify-between items-center p-4 rounded-xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase">Reference Channel</span>
                                            <span class="text-sm font-bold text-slate-800 italic"><?php echo $payment['reference_no'] ?: 'Direct Inflow'; ?></span>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-100">
                                            <span class="text-xs font-bold text-slate-500 uppercase">System Stamp</span>
                                            <span class="text-sm font-bold text-slate-800"><?php echo date('D, d M Y | H:i', strtotime($payment['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest border-b pb-2">Allocation Status</h4>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                                            <span class="text-xs font-bold text-emerald-600 uppercase">Linked Assets</span>
                                            <span class="text-sm font-black text-emerald-700">₹<?php echo number_format($payment['allocated_amount'], 2); ?></span>
                                        </div>
                                        <div class="flex justify-between items-center bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                                            <span class="text-xs font-bold text-amber-600 uppercase">Residual Capital</span>
                                            <span class="text-sm font-black text-amber-700">₹<?php echo number_format($payment['remaining_amount'], 2); ?></span>
                                        </div>
                                        <?php if ($payment['remaining_amount'] > 0): ?>
                                                <a href="payments.php?action=allocate&id=<?php echo $payment['id']; ?>" class="w-full py-4 bg-indigo-600 text-white rounded-xl font-black text-center text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 mt-4 block transition-all hover:bg-indigo-700">Link Floating Funds</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($allocations)): ?>
                                    <div class="mt-16">
                                        <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-6 flex items-center gap-2">
                                            <iconify-icon icon="solar:link-bold-duotone" class="text-indigo-500 text-lg"></iconify-icon>
                                            Linked Udhar Nodes
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <?php foreach ($allocations as $alloc): ?>
                                                    <div class="p-5 border border-slate-100 rounded-2xl bg-slate-50/30 hover:border-indigo-200 hover:bg-white transition-all group cursor-pointer">
                                                        <div class="flex justify-between items-start mb-4">
                                                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-indigo-500 shadow-sm group-hover:scale-110 transition-transform">
                                                                <iconify-icon icon="solar:bill-check-bold"></iconify-icon>
                                                            </div>
                                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><?php echo $alloc['bill_no']; ?></span>
                                                        </div>
                                                        <p class="text-xs font-bold text-slate-500 line-clamp-1 mb-2 italic"><?php echo htmlspecialchars($alloc['description']); ?></p>
                                                        <p class="text-sm font-black text-emerald-600 tracking-tight">₹<?php echo number_format($alloc['allocated_amount'], 2); ?></p>
                                                    </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                            <?php endif; ?>

                            <div class="mt-16 pt-8 border-t border-slate-100 flex items-center gap-4">
                                <a href="payments.php?action=edit&id=<?php echo $payment['id']; ?>" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 py-4 rounded-2xl font-black text-center transition-all">Update Document</a>
                                <button onclick="confirmDelete(<?php echo $payment['id']; ?>, '<?php echo htmlspecialchars(addslashes($payment['customer_name'])); ?>')" class="flex-1 bg-rose-50 hover:bg-rose-100 text-rose-600 py-4 rounded-2xl font-black transition-all">Purge Record</button>
                            </div>
                        </div>
                    </div>
                </div>

        <?php elseif ($action == 'allocate' && $payment): ?>
                <!-- Allocation Engine (Allocate) -->
                <div class="max-w-6xl mx-auto">
                    <div class="glass-panel overflow-hidden bg-white/95 border-amber-200/50">
                        <div class="px-10 py-8 bg-amber-50/50 border-b border-amber-100 flex justify-between items-center">
                            <div class="flex items-center gap-6">
                                <div class="w-16 h-16 rounded-3xl bg-amber-500 text-white flex items-center justify-center text-4xl shadow-xl shadow-amber-100">
                                    <iconify-icon icon="solar:link-broken-bold-duotone" class="animate-pulse"></iconify-icon>
                                </div>
                                <div>
                                    <h2 class="text-3xl font-black text-amber-900 tracking-tighter">Settlement Mapping</h2>
                                    <p class="text-amber-600/70 text-xs font-black uppercase tracking-widest">Customer ID: <?php echo str_pad($payment['customer_id'], 6, '0', STR_PAD_LEFT); ?> | Balance: ₹<?php echo number_format($payment['amount'], 2); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-amber-500 font-black uppercase mb-1">Available Liquidity</p>
                                <h3 class="text-2xl font-black text-amber-700 tracking-tighter">₹<?php echo number_format($payment['remaining_amount'], 2); ?></h3>
                            </div>
                        </div>

                        <div class="p-10">
                            <?php if (empty($udhar_entries)): ?>
                                    <div class="py-24 text-center">
                                        <iconify-icon icon="solar:shield-check-bold-duotone" class="text-8xl text-emerald-200 mb-6"></iconify-icon>
                                        <h4 class="text-slate-700 font-black italic">Perfect Equilibrium Reached</h4>
                                        <p class="text-slate-400 text-sm">No pending udhar nodes discovered for this entity.</p>
                                        <a href="payments.php?action=view&id=<?php echo $payment['id']; ?>" class="mt-8 bg-slate-800 text-white px-8 py-3 rounded-xl font-bold inline-block">Return to Vault</a>
                                    </div>
                            <?php else: ?>
                                    <form method="POST" action="" id="allocateForm" class="space-y-8">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                
                                        <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100/50 flex items-start gap-4">
                                            <iconify-icon icon="solar:info-circle-bold-duotone" class="text-2xl text-indigo-500 mt-1"></iconify-icon>
                                            <div>
                                                <p class="text-sm font-black text-indigo-900 tracking-tight">Algorithmic Guidance</p>
                                                <p class="text-xs text-indigo-600/80 leading-relaxed font-medium">Distribute the available capital (₹<?php echo number_format($payment['remaining_amount'], 2); ?>) across the pending debts below. You can use the 'Simulate Auto-Link' function to prioritize the oldest entries.</p>
                                            </div>
                                        </div>

                                        <div class="overflow-hidden border border-slate-100 rounded-2xl bg-white shadow-sm">
                                            <table class="w-full">
                                                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100">
                                                    <tr>
                                                        <th class="px-6 py-4 text-left">Bill Reference</th>
                                                        <th class="px-6 py-4 text-left">Description</th>
                                                        <th class="px-6 py-4 text-right">Exposure</th>
                                                        <th class="px-6 py-4 text-center">Residual</th>
                                                        <th class="px-6 py-4 text-right">Mapping Input</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    <?php foreach ($udhar_entries as $index => $entry): ?>
                                                            <tr class="hover:bg-amber-50/20 transition-colors">
                                                                <td class="px-6 py-5">
                                                                    <div class="flex items-center gap-3">
                                                                        <div class="text-[10px] font-black text-slate-300">#<?php echo $index + 1; ?></div>
                                                                        <span class="text-xs font-black text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded"><?php echo htmlspecialchars($entry['bill_no']); ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-5 text-sm font-bold text-slate-500 italic line-clamp-1 max-w-xs"><?php echo htmlspecialchars($entry['description']); ?></td>
                                                                <td class="px-6 py-5 text-right text-sm font-bold text-slate-400">₹<?php echo number_format($entry['amount'], 2); ?></td>
                                                                <td class="px-6 py-5 text-center">
                                                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 text-[10px] font-black border border-rose-100 rounded-lg">₹<?php echo number_format($entry['remaining_amount'], 2); ?></span>
                                                                </td>
                                                                <td class="px-6 py-5 text-right">
                                                                    <div class="relative inline-block w-40">
                                                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">₹</span>
                                                                        <input type="number" 
                                                                            class="w-full form-input-clean pl-8 pr-2 py-2 text-sm font-black text-amber-600 text-right allocate-amount"
                                                                            name="allocations[<?php echo $entry['id']; ?>]" 
                                                                            step="0.01" min="0" 
                                                                            max="<?php echo $entry['remaining_amount']; ?>"
                                                                            placeholder="0.00" 
                                                                            onchange="updateTotalAllocation()">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="bg-slate-900 text-white">
                                                    <tr>
                                                        <td colspan="4" class="px-8 py-6 text-right">
                                                            <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-50 block mb-1">Total Mapped Capital</span>
                                                            <span class="text-2xl font-black tracking-tighter">₹<span id="totalAllocated">0.00</span></span>
                                                        </td>
                                                        <td class="px-8 py-6 text-right">
                                                            <button type="button" onclick="autoAllocate()" class="px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                                                Simulate Auto-Link
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                
                                        <div id="allocationWarning" class="hidden animate-bounce">
                                            <div class="bg-rose-50 text-rose-600 p-4 rounded-xl border border-rose-100 flex items-center gap-3">
                                                <iconify-icon icon="solar:danger-bold" class="text-xl"></iconify-icon>
                                                <span id="warningMessage" class="text-xs font-black uppercase"></span>
                                            </div>
                                        </div>

                                        <div class="pt-10 flex items-center gap-4">
                                            <button type="submit" name="allocate_payment" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-5 rounded-2xl font-black text-xl tracking-tighter shadow-xl shadow-amber-100 transition-all">
                                                Confirm Mapping
                                            </button>
                                            <a href="payments.php?action=view&id=<?php echo $payment['id']; ?>" class="px-8 py-5 text-slate-400 font-bold hover:text-slate-600">Discard Task</a>
                                        </div>
                                    </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        <?php endif; ?>

    <!-- Interface Controller -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/payments.js"></script>
    <script src="assets/js/search_suggestions.js"></script>
    <script src="assets/js/payments_custom.js"></script>
    
    <script>
        // Clean Modern confirmDelete
        function confirmDelete(id, name) {
            const confirmed = confirm(`[SECURITY ALERT] Are you sure you wish to purge the payment record for ${name}? This action is immutable and will dissolve all associated udhar links.`);
            if (confirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_id';
                input.value = id;
                const submit = document.createElement('input');
                submit.type = 'hidden';
                submit.name = 'delete_payment';
                submit.value = '1';
                form.appendChild(input);
                form.appendChild(submit);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <script>
        (function () {
            if (window.__paymentsSidebarToggleInitialized) return;
            window.__paymentsSidebarToggleInitialized = true;

            function getNodes() {
                return {
                    sidebar: document.querySelector('.sidebar'),
                    mainContent: document.getElementById('mainContent') || document.querySelector('.main-content'),
                    openBtn: document.getElementById('sidebarOpenBtn'),
                    toggleBtn: document.getElementById('sidebarToggle')
                };
            }

            function isMobile() {
                return window.innerWidth <= 768;
            }

            function syncOpenButtonVisibility() {
                const { sidebar, openBtn } = getNodes();
                if (!sidebar || !openBtn) return;

                if (isMobile()) {
                    openBtn.classList.add('hidden');
                    openBtn.style.display = 'none';
                    return;
                }

                const isClosed = sidebar.classList.contains('closed');
                if (isClosed) {
                    openBtn.classList.remove('hidden');
                    openBtn.style.display = 'flex';
                } else {
                    openBtn.classList.add('hidden');
                    openBtn.style.display = 'none';
                }
            }

            function fallbackToggle() {
                const { sidebar, mainContent } = getNodes();
                if (!sidebar || !mainContent) return;

                if (isMobile()) {
                    sidebar.classList.toggle('active');
                    mainContent.classList.toggle('active');
                } else {
                    const isClosed = sidebar.classList.toggle('closed');
                    mainContent.classList.toggle('expanded');
                    localStorage.setItem('sidebarState', isClosed ? 'closed' : 'open');
                }

                syncOpenButtonVisibility();
            }

            function toggleSidebarSafe() {
                if (typeof window.toggleSidebar === 'function') {
                    window.toggleSidebar();
                    syncOpenButtonVisibility();
                    return;
                }
                fallbackToggle();
            }

            document.addEventListener('DOMContentLoaded', function () {
                const { sidebar, mainContent, openBtn, toggleBtn } = getNodes();
                if (!sidebar || !mainContent) return;

                const savedState = localStorage.getItem('sidebarState');
                if (savedState === 'closed' && !isMobile()) {
                    sidebar.classList.add('closed');
                    mainContent.classList.add('expanded');
                }

                syncOpenButtonVisibility();

                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        toggleSidebarSafe();
                    }, true);
                }

                if (openBtn) {
                    openBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        toggleSidebarSafe();
                    }, true);
                }

                window.addEventListener('resize', syncOpenButtonVisibility);
            });
        })();
    </script>
</body>
</html>