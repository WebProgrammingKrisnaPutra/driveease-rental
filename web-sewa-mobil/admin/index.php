<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce admin permission
require_admin();

try {
    // 1. Calculate General KPI Metrics
    // Revenue
    $rev_stmt = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('approved', 'active', 'completed')");
    $total_revenue = (float)$rev_stmt->fetchColumn();

    // Active Bookings
    $act_stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'active'");
    $active_rentals = (int)$act_stmt->fetchColumn();

    // Cars Count
    $car_stmt = $pdo->query("SELECT COUNT(*) FROM cars");
    $total_cars = (int)$car_stmt->fetchColumn();

    // Customers Count
    $cust_stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = (int)$cust_stmt->fetchColumn();

    // 2. Fetch Fuel Type performance for Chart 1 (Diesel vs Bensin vs Hybrid/EV)
    $fuel_stmt = $pdo->query("
        SELECT c.fuel_type, COUNT(b.id) as booking_count 
        FROM bookings b 
        JOIN cars c ON b.car_id = c.id
        WHERE b.status != 'cancelled'
        GROUP BY c.fuel_type
    ");
    $fuel_data = $fuel_stmt->fetchAll();
    
    $fuel_labels = [];
    $fuel_counts = [];
    foreach ($fuel_data as $row) {
        $fuel_labels[] = strtoupper($row['fuel_type']);
        $fuel_counts[] = (int)$row['booking_count'];
    }

    // 3. Fetch Monthly Income Trends for Chart 2 (Bar Chart)
    $income_stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%M %Y') as month_label, SUM(total_price) as monthly_sum
        FROM bookings
        WHERE status IN ('approved', 'active', 'completed')
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY created_at ASC
    ");
    $income_data = $income_stmt->fetchAll();

    $income_labels = [];
    $income_sums = [];
    foreach ($income_data as $row) {
        $income_labels[] = $row['month_label'];
        $income_sums[] = (float)$row['monthly_sum'];
    }

} catch (PDOException $e) {
    die("Data loading error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Admin Controls Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar Navigation (Lg: col-span-3) -->
        <div class="lg:col-span-3 space-y-6 animate-fade-in-up">
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
                    <a href="index.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl bg-goldAccent text-black font-extrabold shadow-md transition-all duration-200">
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
                    <a href="reports.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-900/40 transition-all duration-200">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Laporan Keuangan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Workspace (Lg: col-span-9) -->
        <div class="lg:col-span-9 space-y-8 animate-fade-in-up" style="animation-delay: 0.1s;">
            <!-- Page Title -->
            <div>
                <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Analitik & Performa Keuangan</h1>
                <p class="text-zinc-400 text-xs mt-1">Laporan grafik performa transaksi real-time DriveEase</p>
            </div>

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- KPI 1 -->
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800 shadow-md">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total Pendapatan</span>
                    <p class="text-lg font-black text-white dark:text-white light:text-zinc-950 mt-1">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                    <div class="text-[9px] text-emerald-400 mt-1.5 flex items-center">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i>
                        <span>+12.4% vs bulan lalu</span>
                    </div>
                </div>

                <!-- KPI 2 -->
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800 shadow-md">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Sewa Aktif</span>
                    <p class="text-lg font-black text-goldAccent mt-1"><?php echo $active_rentals; ?> Kendaraan</p>
                    <div class="text-[9px] text-zinc-500 mt-1.5">Sedang di jalan raya</div>
                </div>

                <!-- KPI 3 -->
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800 shadow-md">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total Armada</span>
                    <p class="text-lg font-black text-white dark:text-white light:text-zinc-950 mt-1"><?php echo $total_cars; ?> Unit</p>
                    <div class="text-[9px] text-zinc-500 mt-1.5">Terdaftar di garasi</div>
                </div>

                <!-- KPI 4 -->
                <div class="premium-glass p-5 rounded-2xl border border-zinc-800 shadow-md">
                    <span class="text-[10px] text-zinc-500 font-bold uppercase tracking-wider">Total Member</span>
                    <p class="text-lg font-black text-white dark:text-white light:text-zinc-950 mt-1"><?php echo $total_customers; ?> Pengguna</p>
                    <div class="text-[9px] text-emerald-400 mt-1.5 flex items-center">
                        <i class="fa-solid fa-user-plus mr-1"></i>
                        <span>Pertumbuhan stabil</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section (Layar 8) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                <!-- Chart 1: Performa Sewa Bahan Bakar (Diesel vs Bensin vs EV) -->
                <div class="premium-glass p-6 rounded-2xl border border-zinc-800 md:col-span-5 space-y-4">
                    <h3 class="text-sm font-bold text-white dark:text-white light:text-zinc-900 tracking-tight">Kategori Terlaris (Bahan Bakar)</h3>
                    <div class="h-60 relative flex items-center justify-center">
                        <canvas id="fuelChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Pendapatan Bulanan -->
                <div class="premium-glass p-6 rounded-2xl border border-zinc-800 md:col-span-7 space-y-4">
                    <h3 class="text-sm font-bold text-white dark:text-white light:text-zinc-900 tracking-tight">Perkembangan Pendapatan Bulanan</h3>
                    <div class="h-60 relative">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ChartJS Library Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Data injected from PHP
        const fuelLabels = <?php echo json_encode($fuel_labels); ?>;
        const fuelCounts = <?php echo json_encode($fuel_counts); ?>;
        
        const incomeLabels = <?php echo json_encode($income_labels); ?>;
        const incomeSums = <?php echo json_encode($income_sums); ?>;
        
        // --- 1. Fuel Type Doughnut Chart (Neon Soft Theme) ---
        const ctxFuel = document.getElementById('fuelChart').getContext('2d');
        new Chart(ctxFuel, {
            type: 'doughnut',
            data: {
                labels: fuelLabels,
                datasets: [{
                    data: fuelCounts,
                    backgroundColor: [
                        'rgba(212, 175, 55, 0.85)',  // Neon Gold
                        'rgba(6, 182, 212, 0.85)',   // Neon Cyan
                        'rgba(16, 185, 129, 0.85)'   // Neon Emerald
                    ],
                    borderColor: '#161616',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#a1a1aa',
                            font: { size: 10, family: 'Inter' }
                        }
                    }
                }
            }
        });

        // --- 2. Monthly Revenue Bar Chart (Neon Soft Theme) ---
        const ctxIncome = document.getElementById('incomeChart').getContext('2d');
        new Chart(ctxIncome, {
            type: 'bar',
            data: {
                labels: incomeLabels,
                datasets: [{
                    label: 'Pendapatan (IDR)',
                    data: incomeSums,
                    backgroundColor: 'rgba(212, 175, 55, 0.7)', // Neon Gold Bar
                    borderColor: '#D4AF37',
                    borderWidth: 1.5,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            color: '#a1a1aa',
                            font: { size: 10, family: 'Inter' },
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#a1a1aa',
                            font: { size: 10, family: 'Inter' }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
