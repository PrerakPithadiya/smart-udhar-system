<?php
// File: smart-udhar-system/dashboard.php

require_once 'config/database.php';
requireLogin();

// Get current user
$user = getCurrentUser();
$conn = getDBConnection();

// Get dashboard statistics
$stats = [];

// Total customers
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_customers'] = $result->fetch_assoc()['total'];
$stmt->close();

// Total udhar
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM udhar_transactions WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_udhar'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total received
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_received'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Pending amount
$stats['pending_amount'] = $stats['total_udhar'] - $stats['total_received'];

// Today's udhar
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM udhar_transactions WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND transaction_date = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$stats['today_udhar'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Today's collection
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM payments WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?) AND payment_date = ?");
$stmt->bind_param("is", $_SESSION['user_id'], $today);
$stmt->execute();
$result = $stmt->get_result();
$stats['today_collection'] = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Recent udhar transactions
$stmt = $conn->prepare("
    SELECT ut.*, c.name as customer_name 
    FROM udhar_transactions ut 
    JOIN customers c ON ut.customer_id = c.id 
    WHERE c.user_id = ? 
    ORDER BY ut.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recent payments
$stmt = $conn->prepare("
    SELECT p.*, c.name as customer_name 
    FROM payments p 
    JOIN customers c ON p.customer_id = c.id 
    WHERE c.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Top customers with highest balance
$stmt = $conn->prepare("
    SELECT name, mobile, balance 
    FROM customers 
    WHERE user_id = ? AND balance > 0 
    ORDER BY balance DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$top_customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Terminal | Smart Udhar Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Outfit:wght@200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/common.js" defer></script>
    <style>
        :root {
            --bg-airy: #f8fafc;
            --accent-indigo: #6366f1;
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

        /* Animated Noodles */
        .noodle-path {
            stroke: rgba(99, 102, 241, 0.1);
            stroke-width: 1.5;
            fill: none;
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: drawNoodle 10s linear infinite;
        }

        @keyframes drawNoodle {
            to {
                stroke-dashoffset: 0;
            }
        }

        .dashboard-surface {
            background: rgba(255, 255, 255, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 28px;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="bg-[var(--bg-airy)]">

    <!-- Sidebar Toggle Commander (Visible when closed) -->
    <button id="sidebarOpenBtn" class="fixed left-0 top-1/2 -translate-y-1/2 w-12 h-16 bg-white border border-slate-200 text-indigo-600 rounded-r-2xl flex items-center justify-center shadow-xl shadow-indigo-100/50 hover:w-14 active:scale-95 transition-all z-[100] hidden">
        <iconify-icon icon="solar:sidebar-minimalistic-bold-duotone" width="24"></iconify-icon>
    </button>

    <!-- SVG Noodles Layer -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-60">
        <svg class="w-full h-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
            <path class="noodle-path"
                d="M -100,200 C 200,100 400,400 600,200 C 800,0 1000,300 1200,100 C 1400,-100 1600,200 1800,100" />
            <path class="noodle-path" style="animation-delay: -3s; stroke: rgba(168, 85, 247, 0.2);"
                d="M -100,500 C 300,400 500,700 800,500 C 1100,300 1300,600 1600,400" />
            <path class="noodle-path" style="animation-delay: -6s; stroke: rgba(45, 212, 191, 0.1);"
                d="M -100,800 C 100,700 400,900 700,750 C 1000,600 1300,850 1500,700" />
        </svg>
    </div>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content min-h-screen">
        <!-- Dashboard Header -->
        <div class="px-4 pt-8 pb-4 md:px-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <nav class="flex text-[10px] items-center gap-1.5 font-bold uppercase tracking-widest text-slate-400 mb-2">
                    <iconify-icon icon="solar:home-2-bold" class="text-xs"></iconify-icon>
                    <span>Smart Udhar</span>
                    <span>/</span>
                    <span class="text-indigo-500">Dashboard</span>
                </nav>
                <h1 class="text-4xl font-black text-slate-800 tracking-tighter">
                    Dashboard <span class="text-indigo-600">Overview</span>
                </h1>
            </div>

            <div class="dashboard-surface flex items-center gap-4 p-2">
                <div class="flex -space-x-2">
                    <div
                        class="w-10 h-10 rounded-full border-2 border-white bg-indigo-600 flex items-center justify-center text-xs font-bold text-white shadow-lg">
                        PP</div>
                </div>
                <div class="pr-4 border-r border-slate-200 hidden sm:block">
                    <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest leading-none mb-1">
                        Authenticated</p>
                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                </div>
                <button class="p-2.5 text-slate-400 hover:text-slate-700 transition-colors relative group">
                    <i class="bi bi-bell-fill text-xl"></i>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full ring-4 ring-white"></span>
                    <div
                        class="absolute top-full right-0 mt-2 p-2 bg-white rounded-xl text-[10px] hidden group-hover:block whitespace-nowrap shadow-2xl border border-slate-200 text-slate-700">
                        New Reminders Available</div>
                </button>
            </div>
        </div>

        <div class="p-4 md:p-8 space-y-8">
            <!-- Executive Statistics - Bento Style -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Highlight: Pending Amount -->
                <div
                    class="md:col-span-2 glass-card p-8 relative flex flex-col justify-between overflow-hidden border border-rose-100">
                    <div class="absolute top-0 right-0 p-6 opacity-10">
                        <i class="bi bi-activity text-[120px]"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div
                                class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl shadow-inner border border-rose-200">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-[0.2em] text-rose-500">Gross
                                Receivables</span>
                        </div>
                        <h2 class="text-5xl md:text-6xl font-black text-slate-800 tracking-tighter mb-2">
                            ₹<?php echo number_format($stats['pending_amount'], 2); ?>
                        </h2>
                        <p class="text-slate-500 text-sm font-medium">Currently distributing across <span
                                class="text-slate-800 font-bold"><?php echo $stats['total_customers']; ?></span> linked
                            accounts</p>
                    </div>
                    <div class="mt-8 flex items-center gap-4">
                        <a href="udhar.php"
                            class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-rose-200">Inspect
                            Ledger</a>
                        <div class="h-2 flex-grow bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-500 pulse-soft" style="width: 65%"></div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Stats -->
                <div class="glass-card p-8 flex flex-col justify-between border border-emerald-100 group">
                    <div class="flex justify-between items-start">
                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-200 group-hover:scale-110 transition-transform">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500/60 uppercase tracking-widest">Active</span>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Collection</p>
                        <h3 class="text-3xl font-black text-slate-800">
                            ₹<?php echo number_format($stats['today_collection'], 2); ?></h3>
                    </div>
                    <p class="text-[10px] text-emerald-500 font-bold bg-emerald-500/5 px-2 py-1 rounded w-fit">REAL-TIME
                        FEED</p>
                </div>

                <div class="glass-card p-8 flex flex-col justify-between border border-indigo-100 group">
                    <div class="flex justify-between items-start">
                        <div
                            class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-200 group-hover:scale-110 transition-transform">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <span class="text-[10px] font-black text-indigo-500/60 uppercase tracking-widest">Growth</span>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Lifetime</p>
                        <h3 class="text-3xl font-black text-slate-800">
                            ₹<?php echo number_format($stats['total_received'], 2); ?></h3>
                    </div>
                    <p class="text-[10px] text-indigo-500 font-bold bg-indigo-500/5 px-2 py-1 rounded w-fit">TOTAL
                        INFLOW</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Panel: Lists -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Transactions Segment -->
                    <div class="glass-card overflow-hidden">
                        <div class="px-8 py-6 flex justify-between items-center border-b border-slate-100 bg-slate-50/30">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-8 bg-indigo-500 rounded-full"></div>
                                <h3 class="text-xl font-extrabold text-slate-800">Recent Transactions</h3>
                            </div>
                            <button
                                class="text-xs font-black uppercase text-indigo-600 hover:text-indigo-500 transition-colors">View
                                Deep Logs</button>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php if (empty($recent_transactions)): ?>
                                    <div class="py-12 text-center text-slate-500 font-medium italic">Empty data stream...
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                        <div
                                            class="group flex items-center justify-between p-5 rounded-2xl bg-white border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all cursor-pointer">
                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                                                    <?php echo strtoupper(substr($transaction['customer_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-800">
                                                        <?php echo htmlspecialchars($transaction['customer_name']); ?>
                                                    </p>
                                                    <p class="text-[10px] text-slate-500 font-black uppercase tracking-widest">
                                                        <?php echo date('d M Y', strtotime($transaction['transaction_date'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right flex items-center gap-6">
                                                <div>
                                                    <p class="text-sm font-black text-slate-800 tracking-tight">
                                                        ₹<?php echo number_format($transaction['amount'], 2); ?></p>
                                                    <p class="text-[10px] text-slate-500 font-bold italic">Reference ID:
                                                        <?php echo $transaction['id']; ?>
                                                    </p>
                                                </div>
                                                <div class="h-10 w-[1px] bg-slate-100 mx-2 hidden sm:block"></div>
                                                <div class="hidden sm:block">
                                                    <?php
                                                    $s_c = '';
                                                    switch ($transaction['status']) {
                                                        case 'paid':
                                                            $s_c = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                                                            break;
                                                        case 'partially_paid':
                                                            $s_c = 'bg-blue-500/10 text-blue-500 border-blue-500/20';
                                                            break;
                                                        default:
                                                            $s_c = 'bg-rose-500/10 text-rose-500 border-rose-500/20';
                                                    }
                                                    ?>
                                                    <span
                                                        class="px-3 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg border <?php echo $s_c; ?>">
                                                        <?php echo str_replace('_', ' ', $transaction['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Segment -->
                    <div class="glass-card overflow-hidden">
                        <div
                            class="px-8 py-6 flex justify-between items-center border-b border-slate-100 bg-emerald-50/50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-8 bg-emerald-500 rounded-full"></div>
                                <h3 class="text-xl font-extrabold text-slate-800">Recent Payments</h3>
                            </div>
                        </div>
                        <div class="p-6 overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-100">
                                        <th class="px-4 py-3 text-left">Customer</th>
                                        <th class="px-4 py-3 text-right">Amount</th>
                                        <th class="px-4 py-3 text-center">Payment Mode</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($recent_payments as $payment): ?>
                                        <tr class="group hover:bg-emerald-50/40 transition-colors">
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-3">
                                                    <i class="bi bi-arrow-down-left-circle text-emerald-500 text-lg"></i>
                                                    <span class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($payment['customer_name']); ?></span>
                                                    <span
                                                        class="text-xs font-bold text-slate-700"><?php echo htmlspecialchars($payment['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="text-sm font-black text-emerald-600">+
                                                    ₹<?php echo number_format($payment['amount'], 2); ?></span>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span
                                                    class="text-[10px] font-black text-slate-500 uppercase px-2 py-1 bg-white rounded-md border border-slate-200"><?php echo $payment['payment_mode']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Insights -->
                <div class="space-y-8">
                    <!-- Top Nodes (Customers) -->
                    <div class="glass-card p-8 border border-indigo-100">
                        <h4 class="text-xl font-black text-slate-800 mb-8 flex items-center gap-3 italic">
                            <i class="bi bi-cpu text-indigo-500"></i> High-Priority Nodes
                        </h4>
                        <div class="space-y-6">
                            <?php foreach ($top_customers as $index => $customer): ?>
                                <div class="relative pl-6 border-l border-slate-100 group">
                                    <div
                                        class="absolute top-0 left-[-4px] w-2 h-2 rounded-full bg-slate-300 group-hover:bg-indigo-500 group-hover:scale-150 transition-all">
                                    </div>
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p
                                                class="text-xs font-black text-slate-800 group-hover:text-indigo-600 transition-colors uppercase tracking-wider">
                                                <?php echo htmlspecialchars($customer['name']); ?>
                                            </p>
                                            <p class="text-[10px] text-slate-500 font-bold">
                                                <?php echo htmlspecialchars($customer['mobile']); ?>
                                            </p>
                                        </div>
                                        <p class="text-sm font-black text-rose-400 tracking-tight">
                                            ₹<?php echo number_format($customer['balance'], 2); ?></p>
                                    </div>
                                    <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500/40 group-hover:bg-indigo-500 transition-all"
                                            style="width: <?php echo min(100, ($customer['balance'] / 10000) * 100); ?>%">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quick Command Panel -->
                    <div class="glass-card p-1 bg-gradient-to-br from-indigo-50 to-violet-50">
                        <div class="bg-white rounded-[22px] p-8 border border-slate-100">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-8 text-center">
                                System Procedures</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="customers.php?action=add"
                                    class="flex flex-col items-center justify-center p-4 rounded-3xl bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-600 transition-all group aspect-square">
                                    <i
                                        class="bi bi-person-plus text-2xl text-indigo-600 group-hover:text-white mb-2"></i>
                                    <span
                                        class="text-[10px] font-black uppercase text-center text-slate-500 group-hover:text-white">Add
                                        Cust</span>
                                </a>
                                <a href="udhar.php?action=add"
                                    class="flex flex-col items-center justify-center p-4 rounded-3xl bg-white border border-slate-200 hover:border-emerald-300 hover:bg-emerald-600 transition-all group aspect-square">
                                    <i
                                        class="bi bi-plus-circle text-2xl text-emerald-600 group-hover:text-white mb-2"></i>
                                    <span
                                        class="text-[10px] font-black uppercase text-center text-slate-500 group-hover:text-white">Entry</span>
                                </a>
                                <a href="payments.php?action=add"
                                    class="flex flex-col items-center justify-center p-4 rounded-3xl bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-600 transition-all group aspect-square">
                                    <i class="bi bi-cash-stack text-2xl text-amber-400 group-hover:text-white mb-2"></i>
                                    <span
                                        class="text-[10px] font-black uppercase text-center text-slate-500 group-hover:text-white">Collect</span>
                                </a>
                                <a href="reports.php"
                                    class="flex flex-col items-center justify-center p-4 rounded-3xl bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-600 transition-all group aspect-square">
                                    <i
                                        class="bi bi-file-earmark-bar-graph text-2xl text-rose-500 group-hover:text-white mb-2"></i>
                                    <span
                                        class="text-[10px] font-black uppercase text-center text-slate-500 group-hover:text-white">Analyze</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer
            class="px-8 py-12 flex flex-col md:flex-row justify-between items-center bg-white/60 border-t border-slate-200 gap-6">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center border border-slate-200">
                    <i class="bi bi-shield-lock-fill text-indigo-500"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800 uppercase italic">Audit Trail Active</p>
                    <p class="text-[10px] text-slate-500 font-medium">Last system ping: <?php echo date('H:i:s'); ?>
                        (GMT+5:30)</p>
                </div>
            </div>
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">&copy; <?php echo date('Y'); ?>
                PRERAK PITHADIYA. EXECUTIVE TERMINAL V3.0-DARK-MATTER.</p>
        </footer>
    </div>

    <!-- Core Scripts -->
    <script src="assets/js/dashboard.js"></script>
    <script>
        // Smooth Inbound Animation
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('loaded');
        });
        const cards = document.querySelectorAll('.glass-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = `all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) ${index * 0.1}s`;
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>

</html>