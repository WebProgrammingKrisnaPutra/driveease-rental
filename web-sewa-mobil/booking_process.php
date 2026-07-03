<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Route guard: user must be logged in
require_login();

// 1. Finalize Booking Confirmation (Saves to Database)
if (isset($_POST['confirm_booking'])) {
    $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
    $pickup_loc = trim($_POST['pickup_location'] ?? '');
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $driver_opt = isset($_POST['driver_option']) ? 1 : 0;
    $insurance_opt = isset($_POST['insurance_option']) ? 1 : 0;
    
    // Server-side recalculations
    try {
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status != 'maintenance'");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch();
        
        if (!$car) {
            die("Error: Mobil tidak tersedia.");
        }
        
        $start = new DateTime($pickup_date);
        $end = new DateTime($return_date);
        $duration = $end->diff($start)->days;
        
        if ($duration <= 0) {
            die("Error: Durasi penyewaan salah.");
        }
        
        $price_per_day = $car['price_per_day'];
        $driver_fee_per_day = $driver_opt ? 150000 : 0;
        $insurance_fee_per_day = $insurance_opt ? 50000 : 0;
        
        $base_price = $price_per_day * $duration;
        $driver_fee = $driver_fee_per_day * $duration;
        $insurance_fee = $insurance_fee_per_day * $duration;
        $tax_price = ($base_price + $driver_fee + $insurance_fee) * 0.10;
        $total_price = $base_price + $driver_fee + $insurance_fee + $tax_price;
        
        $user_id = $_SESSION['user_id'];
        
        // Insert booking via prepared statement
        $insert = $pdo->prepare("
            INSERT INTO bookings 
            (user_id, car_id, pickup_location, pickup_date, return_date, duration_days, driver_option, insurance_option, driver_fee, insurance_fee, base_price, tax_price, total_price, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $insert->execute([
            $user_id, $car_id, $pickup_loc, $pickup_date, $return_date, $duration, 
            $driver_opt, $insurance_opt, $driver_fee, $insurance_fee, $base_price, $tax_price, $total_price
        ]);
        
        // Redirect to customer dashboard
        header("Location: dashboard.php?booking_success=true");
        exit;
        
    } catch (PDOException $e) {
        die("Error saving booking: " . $e->getMessage());
    }
}

// 2. Initial Form Submission Processing (Displays Billing Screen)
$car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
$pickup_loc = trim($_POST['pickup_location'] ?? '');
$pickup_date = $_POST['pickup_date'] ?? '';
$return_date = $_POST['return_date'] ?? '';
$driver_opt = isset($_POST['driver_option']) ? 1 : 0;
$insurance_opt = isset($_POST['insurance_option']) ? 1 : 0;

if (!$car_id || empty($pickup_loc) || empty($pickup_date) || empty($return_date)) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status != 'maintenance'");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    
    if (!$car) {
        header("Location: index.php");
        exit;
    }
    
    // Verify dates
    $start = new DateTime($pickup_date);
    $end = new DateTime($return_date);
    
    if ($end <= $start) {
        die("Error: Tanggal pengembalian harus setelah tanggal penjemputan.");
    }
    
    $duration = $end->diff($start)->days;
    
    // Server-side calculation to prevent pricing tampering
    $price_per_day = $car['price_per_day'];
    $driver_fee_per_day = $driver_opt ? 150000 : 0;
    $insurance_fee_per_day = $insurance_opt ? 50000 : 0;
    
    $base_price = $price_per_day * $duration;
    $driver_fee = $driver_fee_per_day * $duration;
    $insurance_fee = $insurance_fee_per_day * $duration;
    $tax_price = ($base_price + $driver_fee + $insurance_fee) * 0.10; // 10% VAT
    $total_price = $base_price + $driver_fee + $insurance_fee + $tax_price;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Layar Anggaran & Rincian Pembayaran (Billing Screen) -->
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="premium-glass p-8 rounded-2xl border border-zinc-800 shadow-2xl space-y-8 animate-fade-in-up">
        
        <!-- Header Rincian -->
        <div class="border-b border-zinc-800 pb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Rincian Pembayaran</h1>
                <p class="text-zinc-400 text-xs mt-1">Periksa kembali detail anggaran penyewaan premium Anda</p>
            </div>
            <span class="text-goldAccent font-black text-xl tracking-wider">INVOICE PREVIEW</span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Info Mobil & Jadwal -->
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($car['name']); ?>" 
                         class="w-24 h-16 object-cover rounded-lg border border-zinc-800">
                    <div>
                        <span class="text-[10px] text-goldAccent font-bold uppercase tracking-wider"><?php echo htmlspecialchars($car['brand']); ?></span>
                        <h3 class="text-base font-bold text-white"><?php echo htmlspecialchars($car['name']); ?></h3>
                        <span class="text-[10px] text-zinc-400 capitalize"><?php echo $car['transmission']; ?> | <?php echo $car['fuel_type']; ?></span>
                    </div>
                </div>

                <div class="bg-zinc-900/50 p-5 rounded-xl border border-zinc-850 space-y-3.5 text-xs text-zinc-400 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                    <div class="flex justify-between">
                        <span>Titik Pengambilan:</span>
                        <span class="font-bold text-white dark:text-white light:text-zinc-900"><?php echo htmlspecialchars($pickup_loc); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tanggal Penjemputan:</span>
                        <span class="font-bold text-white dark:text-white light:text-zinc-900"><?php echo date('d M Y', strtotime($pickup_date)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tanggal Pengembalian:</span>
                        <span class="font-bold text-white dark:text-white light:text-zinc-900"><?php echo date('d M Y', strtotime($return_date)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Durasi Sewa:</span>
                        <span class="font-bold text-goldAccent"><?php echo $duration; ?> Hari</span>
                    </div>
                </div>
            </div>

            <!-- Kalkulasi Biaya Detail (Billing Screen Breakdowns) -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-widest text-zinc-400">Rincian Anggaran</h4>
                <div class="space-y-3 text-xs text-zinc-400">
                    <div class="flex justify-between">
                        <span>Tarif Mobil (<?php echo $duration; ?> Hari x Rp <?php echo number_format($price_per_day, 0, ',', '.'); ?>)</span>
                        <span class="font-semibold text-white dark:text-white light:text-zinc-900">Rp <?php echo number_format($base_price, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span>Layanan Supir (<?php echo $duration; ?> Hari x Rp <?php echo $driver_opt ? '150.000' : '0'; ?>)</span>
                        <span class="font-semibold text-white dark:text-white light:text-zinc-900">Rp <?php echo number_format($driver_fee, 0, ',', '.'); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Proteksi Asuransi (<?php echo $duration; ?> Hari x Rp <?php echo $insurance_opt ? '50.000' : '0'; ?>)</span>
                        <span class="font-semibold text-white dark:text-white light:text-zinc-900">Rp <?php echo number_format($insurance_fee, 0, ',', '.'); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span>Pajak Penambahan Nilai (PPN 10%)</span>
                        <span class="font-semibold text-white dark:text-white light:text-zinc-900">Rp <?php echo number_format($tax_price, 0, ',', '.'); ?></span>
                    </div>

                    <hr class="border-zinc-850 dark:border-zinc-850 light:border-zinc-200 my-2">
                    
                    <div class="flex justify-between items-baseline text-white">
                        <span class="font-bold text-sm">Total Pengeluaran:</span>
                        <span class="text-2xl font-black text-goldAccent">Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pemilihan Metode Pembayaran & Form Final -->
        <form action="booking_process.php" method="POST" class="border-t border-zinc-800 pt-6 space-y-6">
            <!-- Hidden parameters -->
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <input type="hidden" name="pickup_location" value="<?php echo htmlspecialchars($pickup_loc); ?>">
            <input type="hidden" name="pickup_date" value="<?php echo htmlspecialchars($pickup_date); ?>">
            <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>">
            <?php if ($driver_opt): ?>
                <input type="hidden" name="driver_option" value="1">
            <?php endif; ?>
            <?php if ($insurance_opt): ?>
                <input type="hidden" name="insurance_option" value="1">
            <?php endif; ?>
            <input type="hidden" name="confirm_booking" value="1">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">Pilih Metode Pembayaran</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <label class="flex items-center space-x-3 p-4 rounded-xl border border-zinc-800 bg-zinc-900/40 cursor-pointer select-none">
                        <input type="radio" name="payment_method" value="bank_transfer" checked 
                               class="h-4 w-4 text-goldAccent focus:ring-goldAccent border-zinc-800 bg-zinc-900">
                        <div>
                            <span class="font-bold text-white">Bank Transfer</span>
                            <p class="text-[9px] text-zinc-500">BCA, Mandiri, BNI</p>
                        </div>
                    </label>
                    <label class="flex items-center space-x-3 p-4 rounded-xl border border-zinc-800 bg-zinc-900/40 cursor-pointer select-none">
                        <input type="radio" name="payment_method" value="e_wallet" 
                               class="h-4 w-4 text-goldAccent focus:ring-goldAccent border-zinc-800 bg-zinc-900">
                        <div>
                            <span class="font-bold text-white">E-Wallet</span>
                            <p class="text-[9px] text-zinc-500">GoPay, OVO, Dana</p>
                        </div>
                    </label>
                    <label class="flex items-center space-x-3 p-4 rounded-xl border border-zinc-800 bg-zinc-900/40 cursor-pointer select-none">
                        <input type="radio" name="payment_method" value="credit_card" 
                               class="h-4 w-4 text-goldAccent focus:ring-goldAccent border-zinc-800 bg-zinc-900">
                        <div>
                            <span class="font-bold text-white">Kartu Kredit</span>
                            <p class="text-[9px] text-zinc-500">Visa / MasterCard</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
                <a href="car_details.php?id=<?php echo $car['id']; ?>" 
                   class="py-4 text-center bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-white font-semibold rounded-xl text-xs transition-all duration-200">
                    Batal, Ubah Pesanan
                </a>
                <button type="submit" 
                        class="py-4 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md">
                    Konfirmasi & Ajukan Penyewaan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
