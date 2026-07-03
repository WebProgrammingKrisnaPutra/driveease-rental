<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce admin permissions
require_admin();

$success_msg = '';
$error_msg = '';

// 1. DELETE TRANSACTION ACTION
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success_msg = "Transaksi sewa berhasil dihapus!";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus transaksi: " . $e->getMessage();
        }
    }
}

// 2. UPDATE STATUS ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $new_status = $_POST['status'] ?? '';
    
    if ($booking_id && in_array($new_status, ['pending', 'approved', 'active', 'completed', 'cancelled'])) {
        try {
            // First, find the car ID associated with this booking
            $car_stmt = $pdo->prepare("SELECT car_id FROM bookings WHERE id = ?");
            $car_stmt->execute([$booking_id]);
            $car_id = $car_stmt->fetchColumn();
            
            if ($car_id) {
                // Update Booking Status
                $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $booking_id]);
                
                // Real-time synchronization of car availability status based on booking status
                if ($new_status === 'active') {
                    // Car is rented out
                    $car_up = $pdo->prepare("UPDATE cars SET status = 'rented' WHERE id = ?");
                    $car_up->execute([$car_id]);
                } elseif ($new_status === 'completed' || $new_status === 'cancelled') {
                    // Car is returned and available
                    $car_up = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
                    $car_up->execute([$car_id]);
                }
                
                $success_msg = "Status sewa #DE-{$booking_id} berhasil diubah menjadi: " . strtoupper($new_status);
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal mengubah status sewa: " . $e->getMessage();
        }
    }
}

// 3. FETCH ALL BOOKINGS WITH JOINS
try {
    $stmt = $pdo->query("
        SELECT b.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.document_pic, c.name as car_name, c.brand as car_brand
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN cars c ON b.car_id = c.id
        ORDER BY b.created_at DESC
    ");
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar Navigation (Lg: col-span-3) -->
        <div class="lg:col-span-3 space-y-6">
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
                    <a href="bookings.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl bg-goldAccent text-black font-extrabold shadow-md transition-all duration-200">
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
        <div class="lg:col-span-9 space-y-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Manajemen Transaksi Sewa</h1>
                <p class="text-zinc-400 text-xs mt-1">Kelola perizinan status sewa, validasi dokumen, dan pembatalan sewa</p>
            </div>

            <!-- Alerts -->
            <?php if (!empty($success_msg)): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-4 rounded-xl text-xs flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo htmlspecialchars($success_msg); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-xl text-xs flex items-center space-x-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Bookings List -->
            <div class="space-y-6">
                <?php if (empty($bookings)): ?>
                    <div class="premium-glass p-8 rounded-2xl border border-zinc-800 text-center text-zinc-500">
                        <i class="fa-solid fa-receipt text-3xl mb-3"></i>
                        <p class="text-xs">Belum ada transaksi sewa terdaftar.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-6 flex flex-col justify-between">
                            <!-- Header Info -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-850 pb-4 dark:border-zinc-850 light:border-zinc-200">
                                <div>
                                    <span class="text-[9px] font-mono text-zinc-500 uppercase">Sewa ID: #DE-<?php echo $b['id']; ?> | Dibuat: <?php echo date('d M Y H:i', strtotime($b['created_at'])); ?></span>
                                    <h3 class="text-base font-bold text-white dark:text-white light:text-zinc-950"><?php echo htmlspecialchars($b['customer_name']); ?></h3>
                                    <p class="text-[10px] text-zinc-400">Kontak: <?php echo htmlspecialchars($b['customer_phone']); ?> | <?php echo htmlspecialchars($b['customer_email']); ?></p>
                                </div>
                                
                                <!-- Status Badge -->
                                <div class="flex items-center space-x-3 self-start sm:self-center">
                                    <?php if ($b['status'] === 'pending'): ?>
                                        <span class="px-3 py-1 bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-[10px] font-extrabold uppercase tracking-wider rounded-full">Pending Approval</span>
                                    <?php elseif ($b['status'] === 'approved'): ?>
                                        <span class="px-3 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-extrabold uppercase tracking-wider rounded-full">Approved</span>
                                    <?php elseif ($b['status'] === 'active'): ?>
                                        <span class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider rounded-full">Active Trip</span>
                                    <?php elseif ($b['status'] === 'completed'): ?>
                                        <span class="px-3 py-1 bg-zinc-800 border border-zinc-700 text-zinc-400 text-[10px] font-extrabold uppercase tracking-wider rounded-full">Completed</span>
                                    <?php elseif ($b['status'] === 'cancelled'): ?>
                                        <span class="px-3 py-1 bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-extrabold uppercase tracking-wider rounded-full">Cancelled</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Details Row -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-zinc-400">
                                <!-- Col 1: Car & Dates -->
                                <div class="space-y-2">
                                    <h4 class="font-bold text-white dark:text-white light:text-zinc-900">Armada & Jadwal</h4>
                                    <div>Mobil: <strong class="text-white dark:text-white light:text-zinc-900"><?php echo htmlspecialchars($b['car_brand'] . ' ' . $b['car_name']); ?></strong></div>
                                    <div>Penjemputan: <strong><?php echo htmlspecialchars($b['pickup_location']); ?></strong></div>
                                    <div>Tanggal: <strong><?php echo date('d M Y', strtotime($b['pickup_date'])); ?> s/d <?php echo date('d M Y', strtotime($b['return_date'])); ?></strong></div>
                                    <div>Durasi: <strong><?php echo $b['duration_days']; ?> Hari</strong></div>
                                </div>

                                <!-- Col 2: Extras & Document -->
                                <div class="space-y-2">
                                    <h4 class="font-bold text-white dark:text-white light:text-zinc-900">Opsi & Verifikasi</h4>
                                    <div>Layanan Supir: <strong><?php echo $b['driver_option'] ? 'Ya' : 'Lepas Kunci'; ?></strong></div>
                                    <div>Asuransi Proteksi: <strong><?php echo $b['insurance_option'] ? 'Ya' : 'Tidak'; ?></strong></div>
                                    <div class="pt-1">
                                        Dokumen KTP/SIM: 
                                        <?php if (!empty($b['document_pic'])): ?>
                                            <a href="../<?php echo $b['document_pic']; ?>" target="_blank" class="text-goldAccent hover:underline font-bold"><i class="fa-solid fa-file-image mr-1"></i>Lihat Foto Identitas</a>
                                        <?php else: ?>
                                            <span class="text-red-400 font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Belum Diunggah</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Col 3: Cost breakdown -->
                                <div class="space-y-1.5 md:border-l md:border-zinc-850 md:pl-6 dark:border-zinc-850 light:border-zinc-200">
                                    <h4 class="font-bold text-white dark:text-white light:text-zinc-900">Rincian Anggaran</h4>
                                    <div class="flex justify-between text-[11px]">
                                        <span>Harga Dasar:</span>
                                        <span>Rp <?php echo number_format($b['base_price'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="flex justify-between text-[11px]">
                                        <span>Supir & Asuransi:</span>
                                        <span>Rp <?php echo number_format($b['driver_fee'] + $b['insurance_fee'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="flex justify-between text-[11px]">
                                        <span>Pajak (10%):</span>
                                        <span>Rp <?php echo number_format($b['tax_price'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="flex justify-between text-white border-t border-zinc-850 pt-1 mt-1 font-bold text-xs dark:border-zinc-850 light:border-zinc-200 dark:text-white light:text-zinc-900">
                                        <span>Total:</span>
                                        <span class="text-goldAccent">Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Row -->
                            <div class="border-t border-zinc-850 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 dark:border-zinc-850 light:border-zinc-200">
                                <!-- Update Status Form -->
                                <form action="bookings.php" method="POST" class="flex items-center space-x-2 text-xs">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <span class="text-zinc-400">Ubah Status:</span>
                                    <select name="status" class="px-3 py-1.5 bg-zinc-900 border border-zinc-800 rounded-lg text-white text-xs focus:outline-none focus:ring-1 focus:ring-goldAccent">
                                        <option value="pending" <?php echo ($b['status'] === 'pending') ? 'selected' : ''; ?>>Pending Approval</option>
                                        <option value="approved" <?php echo ($b['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="active" <?php echo ($b['status'] === 'active') ? 'selected' : ''; ?>>Active Trip</option>
                                        <option value="completed" <?php echo ($b['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($b['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-750 text-white rounded-lg font-bold text-xs">
                                        Update
                                    </button>
                                </form>

                                <!-- Delete Row -->
                                <a href="bookings.php?delete_id=<?php echo $b['id']; ?>" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus catatan transaksi sewa ini?')"
                                   class="self-end sm:self-center text-xs text-red-500 hover:text-red-400 font-semibold underline flex items-center space-x-1">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                    <span>Hapus Catatan</span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
