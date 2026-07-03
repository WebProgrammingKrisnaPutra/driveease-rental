<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce admin permissions
require_admin();

// Handle Filters
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT b.*, u.name as customer_name, c.name as car_name, c.brand as car_brand
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE b.status IN ('approved', 'active', 'completed')
";
$params = [];

if (!empty($start_date)) {
    $sql .= " AND b.created_at >= ?";
    $params[] = $start_date . ' 00:00:00';
}
if (!empty($end_date)) {
    $sql .= " AND b.created_at <= ?";
    $params[] = $end_date . ' 23:59:59';
}
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR c.name LIKE ? OR c.brand LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY b.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();
    
    // Calculate Report Aggregates
    $sum_base = 0;
    $sum_driver = 0;
    $sum_insurance = 0;
    $sum_tax = 0;
    $sum_total = 0;
    
    foreach ($reports as $r) {
        $sum_base += (float)$r['base_price'];
        $sum_driver += (float)$r['driver_fee'];
        $sum_insurance += (float)$r['insurance_fee'];
        $sum_tax += (float)$r['tax_price'];
        $sum_total += (float)$r['total_price'];
    }
} catch (PDOException $e) {
    die("Database report loading error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Print-only styling override -->
<style>
    @media print {
        nav, footer, .no-print {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            color: #000000 !important;
        }
        .print-box {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        th, td {
            border: 1px solid #ddd !important;
            color: #000000 !important;
            padding: 8px !important;
        }
        .text-goldAccent {
            color: #000000 !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar Navigation (Lg: col-span-3) -->
        <div class="lg:col-span-3 space-y-6 no-print">
            <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-4">
                <span class="text-[10px] text-goldAccent font-black uppercase tracking-wider">Level Akses: Administrator</span>
                <div class="flex items-center space-x-3 pb-4 border-b border-zinc-800">
                    <div class="w-10 h-10 rounded-full bg-[#D4AF37]/10 flex items-center justify-center border border-[#D4AF37]/35 text-goldAccent">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                        <span class="text-[9px] text-zinc-500">System Manager</span>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <a href="index.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900/40 transition-all duration-200">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard Analitik</span>
                    </a>
                    <a href="cars.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900/40 transition-all duration-200">
                        <i class="fa-solid fa-car-side"></i>
                        <span>Kelola Armada (CRUD)</span>
                    </a>
                    <a href="routes.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900/40 transition-all duration-200">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <span>Kelola Rute Wisata</span>
                    </a>
                    <a href="bookings.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900/40 transition-all duration-200">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Kelola Sewa & Transaksi</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl bg-goldAccent text-black font-extrabold shadow-md transition-all duration-200">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Laporan Keuangan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Workspace (Lg: col-span-9) -->
        <div class="lg:col-span-9 space-y-8 print-box">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Laporan Pendapatan Keuangan</h1>
                    <p class="text-zinc-400 text-xs mt-1">Mencatat rincian transaksi masuk untuk laporan laba/rugi usaha rental</p>
                </div>
                <button onclick="window.print()" class="no-print self-start py-3 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md flex items-center space-x-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Laporan (PDF)</span>
                </button>
            </div>

            <!-- Filters (No print) -->
            <form action="reports.php" method="GET" class="no-print premium-glass p-6 rounded-2xl border border-zinc-800 grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block text-zinc-400 font-semibold mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" 
                           class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                </div>
                <div>
                    <label class="block text-zinc-400 font-semibold mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" 
                           class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                </div>
                <div>
                    <label class="block text-zinc-400 font-semibold mb-1.5">Pencarian Kata Kunci</label>
                    <input type="text" name="search" placeholder="Cari nama / mobil / brand" value="<?php echo htmlspecialchars($search); ?>" 
                           class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-goldAccent">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-3.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-750 text-white font-bold rounded-xl transition-all duration-200">
                        Saring Laporan
                    </button>
                </div>
            </form>

            <!-- Aggregates Widgets -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total Omzet Bersih</span>
                    <p class="text-xl font-black text-goldAccent mt-1">Rp <?php echo number_format($sum_total, 0, ',', '.'); ?></p>
                </div>
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total Biaya Supir & Asuransi</span>
                    <p class="text-xl font-black text-white dark:text-white light:text-zinc-900 mt-1">Rp <?php echo number_format($sum_driver + $sum_insurance, 0, ',', '.'); ?></p>
                </div>
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Estimasi PPN Dikoleksi</span>
                    <p class="text-xl font-black text-white dark:text-white light:text-zinc-900 mt-1">Rp <?php echo number_format($sum_tax, 0, ',', '.'); ?></p>
                </div>
            </div>

            <!-- Tabular Report View -->
            <div class="premium-glass rounded-2xl border border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-zinc-400">
                        <thead class="bg-zinc-900/50 text-[10px] font-bold uppercase text-zinc-400 border-b border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-100 light:border-zinc-200">
                            <tr>
                                <th class="py-4 px-6">ID Sewa</th>
                                <th class="py-4 px-6">Tanggal Transaksi</th>
                                <th class="py-4 px-6">Customer</th>
                                <th class="py-4 px-6">Armada</th>
                                <th class="py-4 px-6 text-right">Durasi</th>
                                <th class="py-4 px-6 text-right">Sewa Mobil</th>
                                <th class="py-4 px-6 text-right">Add-ons</th>
                                <th class="py-4 px-6 text-right">Pajak (10%)</th>
                                <th class="py-4 px-6 text-right text-white">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-850 dark:divide-zinc-850 light:divide-zinc-200">
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-zinc-500">
                                        Tidak ada transaksi dalam filter pencarian ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $rep): ?>
                                    <tr class="hover:bg-zinc-900/10 transition-colors duration-150">
                                        <td class="py-4 px-6 font-mono text-zinc-300">#DE-<?php echo $rep['id']; ?></td>
                                        <td class="py-4 px-6"><?php echo date('d/M/Y H:i', strtotime($rep['created_at'])); ?></td>
                                        <td class="py-4 px-6 font-semibold text-white dark:text-white light:text-zinc-900"><?php echo htmlspecialchars($rep['customer_name']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($rep['car_brand'] . ' ' . $rep['car_name']); ?></td>
                                        <td class="py-4 px-6 text-right"><?php echo $rep['duration_days']; ?> Hari</td>
                                        <td class="py-4 px-6 text-right">Rp <?php echo number_format($rep['base_price'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-right text-[10px]">
                                            Supir: +Rp <?php echo number_format($rep['driver_fee'], 0, ',', '.'); ?><br>
                                            Asuransi: +Rp <?php echo number_format($rep['insurance_fee'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="py-4 px-6 text-right">Rp <?php echo number_format($rep['tax_price'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-right font-bold text-goldAccent">Rp <?php echo number_format($rep['total_price'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
