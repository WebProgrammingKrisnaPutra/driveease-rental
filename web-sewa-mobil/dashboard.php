<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Force login
require_login();

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Booking Cancellation (DELETE CRUD)
if (isset($_GET['cancel_id'])) {
    $cancel_id = filter_input(INPUT_GET, 'cancel_id', FILTER_VALIDATE_INT);
    if ($cancel_id) {
        try {
            // Verify ownership and pending status before deleting
            $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$cancel_id, $user_id]);
            $booking = $stmt->fetch();
            
            if ($booking && $booking['status'] === 'pending') {
                $del = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $del->execute([$cancel_id]);
                $success_msg = "Pesanan sewa berhasil dibatalkan!";
            } else {
                $error_msg = "Pesanan tidak dapat dibatalkan (hanya pesanan berstatus pending yang dapat dibatalkan).";
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal membatalkan pesanan: " . $e->getMessage();
        }
    }
}

// Handle Reschedule Booking (UPDATE CRUD)
if (isset($_POST['reschedule_booking'])) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $new_pickup = $_POST['new_pickup_date'] ?? '';
    $new_return = $_POST['new_return_date'] ?? '';
    
    if ($booking_id && !empty($new_pickup) && !empty($new_return)) {
        try {
            $stmt = $pdo->prepare("
                SELECT b.*, c.price_per_day 
                FROM bookings b 
                JOIN cars c ON b.car_id = c.id 
                WHERE b.id = ? AND b.user_id = ?
            ");
            $stmt->execute([$booking_id, $user_id]);
            $booking = $stmt->fetch();
            
            if ($booking && $booking['status'] === 'pending') {
                $start = new DateTime($new_pickup);
                $end = new DateTime($new_return);
                $duration = $end->diff($start)->days;
                
                if ($duration <= 0) {
                    $error_msg = "Tanggal pengembalian harus setelah tanggal penjemputan!";
                } else {
                    // Recalculate fees
                    $price_per_day = $booking['price_per_day'];
                    $driver_opt = $booking['driver_option'];
                    $insurance_opt = $booking['insurance_option'];
                    
                    $driver_fee = ($driver_opt ? 150000 : 0) * $duration;
                    $insurance_fee = ($insurance_opt ? 50000 : 0) * $duration;
                    $base_price = $price_per_day * $duration;
                    $tax_price = ($base_price + $driver_fee + $insurance_fee) * 0.10;
                    $total_price = $base_price + $driver_fee + $insurance_fee + $tax_price;
                    
                    $update = $pdo->prepare("
                        UPDATE bookings 
                        SET pickup_date = ?, return_date = ?, duration_days = ?, driver_fee = ?, insurance_fee = ?, base_price = ?, tax_price = ?, total_price = ?
                        WHERE id = ?
                    ");
                    $update->execute([
                        $new_pickup, $new_return, $duration, $driver_fee, $insurance_fee, $base_price, $tax_price, $total_price, $booking_id
                    ]);
                    $success_msg = "Jadwal sewa berhasil diperbarui!";
                }
            } else {
                $error_msg = "Hanya pesanan berstatus pending yang dapat diubah jadwalnya.";
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal memperbarui jadwal sewa: " . $e->getMessage();
        }
    }
}

// Handle Profile & Document Edit (UPDATE CRUD)
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $license_no = trim($_POST['license_no'] ?? '');
    $ktp_no = trim($_POST['ktp_no'] ?? '');
    
    if (empty($name) || empty($phone)) {
        $error_msg = "Nama dan Nomor Handphone wajib diisi!";
    } else {
        try {
            // File Upload Setup
            $upload_dir = __DIR__ . '/assets/uploads/users/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $doc_pic_name = null;
            if (isset($_FILES['document_pic']) && $_FILES['document_pic']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['document_pic']['tmp_name'];
                $file_name = basename($_FILES['document_pic']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($file_ext, $allowed)) {
                    $new_file_name = 'doc_' . $user_id . '_' . time() . '.' . $file_ext;
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                        $doc_pic_name = 'assets/uploads/users/' . $new_file_name;
                    }
                } else {
                    $error_msg = "Format foto SIM/KTP harus JPG, JPEG, atau PNG!";
                }
            }
            
            // Execute Update
            if (empty($error_msg)) {
                if ($doc_pic_name) {
                    $up_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, license_no = ?, ktp_no = ?, document_pic = ? WHERE id = ?");
                    $up_stmt->execute([$name, $phone, $license_no, $ktp_no, $doc_pic_name, $user_id]);
                } else {
                    $up_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, license_no = ?, ktp_no = ? WHERE id = ?");
                    $up_stmt->execute([$name, $phone, $license_no, $ktp_no, $user_id]);
                }
                
                // Update Session Name
                $_SESSION['user_name'] = $name;
                $success_msg = "Profil Anda berhasil diperbarui!";
            }
        } catch (PDOException $e) {
            $error_msg = "Gagal memperbarui profil: " . $e->getMessage();
        }
    }
}

// Fetch Logged User Info
try {
    $user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch();
    
    // Fetch Active / Completed / Pending Bookings
    $bookings_stmt = $pdo->prepare("
        SELECT b.*, c.name as car_name, c.brand as car_brand, c.image_url as car_image, c.price_per_day
        FROM bookings b 
        JOIN cars c ON b.car_id = c.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $bookings_stmt->execute([$user_id]);
    $bookings = $bookings_stmt->fetchAll();
    
    // Fetch member offers (Available cars with lowest price)
    $rec_stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available' ORDER BY price_per_day ASC LIMIT 3");
    $recommendations = $rec_stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database fetch error: " . $e->getMessage());
}

// Calculate active sewa details
$active_sewa = null;
foreach ($bookings as $b) {
    if ($b['status'] === 'active') {
        $active_sewa = $b;
        break; // Show the first active one
    }
}

if (isset($_GET['booking_success'])) {
    $success_msg = "Reservasi mobil Anda berhasil dikirim! Silakan menunggu verifikasi admin.";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="mb-10 animate-fade-in-up">
        <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Dashboard Member</h1>
        <p class="text-zinc-400 text-xs mt-1">Kelola transaksi penyewaan mobil premium Anda di sini</p>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($success_msg)): ?>
    <div
        class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-4 rounded-xl text-xs mb-8 flex items-center space-x-2 animate-fade-in-up">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo htmlspecialchars($success_msg); ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
    <div
        class="bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-xl text-xs mb-8 flex items-center space-x-2 animate-fade-in-up">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?php echo htmlspecialchars($error_msg); ?></span>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Side: Active rental summary & tabs navigation (Lg: col-span-4) -->
        <div class="lg:col-span-4 space-y-8">
            <!-- Active Rental Summary Widget (Layar 4) -->
            <?php if ($active_sewa): 
                $now = new DateTime();
                $return_dt = new DateTime($active_sewa['return_date']);
                $pickup_dt = new DateTime($active_sewa['pickup_date']);
                $total_duration = $active_sewa['duration_days'] * 24; // in hours
                $hours_rem = 0;
                $pct_rem = 0;
                
                if ($return_dt > $now) {
                    $diff = $now->diff($return_dt);
                    $hours_rem = ($diff->days * 24) + $diff->h;
                    $pct_rem = max(0, min(100, ($hours_rem / $total_duration) * 100));
                }
            ?>
            <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-4">
                <span
                    class="inline-block px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[9px] font-bold uppercase rounded-md">
                    Sewa Sedang Aktif
                </span>
                <div>
                    <h4 class="text-[10px] text-zinc-500 font-bold uppercase">Mobil yang disewa:</h4>
                    <h3 class="text-lg font-bold text-white">
                        <?php echo htmlspecialchars($active_sewa['car_brand'] . ' ' . $active_sewa['car_name']); ?></h3>
                </div>
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-zinc-400">
                        <span>Sisa Waktu Sewa:</span>
                        <span class="font-bold text-white"><?php echo max(0, $diff->days); ?> Hari
                            <?php echo $diff->h; ?> Jam</span>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full bg-zinc-900 rounded-full h-2.5 overflow-hidden border border-zinc-800">
                        <div class="bg-goldAccent h-2.5 rounded-full" style="width: <?php echo $pct_rem; ?>%"></div>
                    </div>
                </div>
                <div class="text-[11px] text-zinc-500 space-y-1">
                    <div>Lokasi Jemput:
                        <strong><?php echo htmlspecialchars($active_sewa['pickup_location']); ?></strong></div>
                    <div>Batas Pengembalian:
                        <strong><?php echo date('d M Y - H:i', strtotime($active_sewa['return_date'])); ?></strong>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="premium-glass p-6 rounded-2xl border border-zinc-800 text-center py-8">
                <div
                    class="w-12 h-12 rounded-full bg-zinc-900 flex items-center justify-center mx-auto mb-4 border border-zinc-800">
                    <i class="fa-solid fa-car text-zinc-600"></i>
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Tidak Ada Sewa Aktif</h3>
                <p class="text-zinc-500 text-xs px-4">Anda sedang tidak menyewa kendaraan apa pun saat ini.</p>
            </div>
            <?php endif; ?>

            <!-- Sidebar Navigation Tabs -->
            <div class="premium-glass rounded-2xl border border-zinc-800 overflow-hidden text-xs">
                <button onclick="switchTab('trips')" id="btn-tab-trips"
                    class="w-full py-4 px-6 text-left border-l-2 border-goldAccent bg-zinc-900/60 text-white font-bold transition-all duration-200">
                    <i class="fa-solid fa-receipt mr-3 text-goldAccent"></i>
                    Daftar & Riwayat Sewa
                </button>
                <button onclick="switchTab('profile')" id="btn-tab-profile"
                    class="w-full py-4 px-6 text-left border-l-2 border-transparent text-zinc-400 hover:text-white transition-all duration-200">
                    <i class="fa-solid fa-user-gear mr-3"></i>
                    Ubah Profil & Dokumen
                </button>
            </div>
        </div>

        <!-- Right Side: Action Forms & Playlists (Lg: col-span-8) -->
        <div class="lg:col-span-8">
            <!-- TAB 1: TRIPS LIST -->
            <div id="tab-trips" class="space-y-6 animate-fade-in-up">
                <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-6">
                    <h2 class="text-xl font-bold text-white dark:text-white light:text-zinc-950">Riwayat Penyewaan Anda
                    </h2>

                    <?php if (empty($bookings)): ?>
                    <div class="text-center py-12 text-zinc-500">
                        <i class="fa-solid fa-history text-3xl mb-3"></i>
                        <p class="text-xs">Belum ada transaksi sewa yang tercatat.</p>
                        <a href="index.php#catalog"
                            class="inline-block mt-4 text-xs font-bold text-goldAccent hover:underline">Sewa Mobil
                            Sekarang &rarr;</a>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($bookings as $b): ?>
                        <div
                            class="bg-zinc-900/30 border border-zinc-850 p-5 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                            <div class="flex items-center space-x-4">
                                <img src="<?php echo htmlspecialchars($b['car_image']); ?>"
                                    alt="<?php echo htmlspecialchars($b['car_name']); ?>"
                                    class="w-20 h-14 object-cover rounded-lg border border-zinc-800">
                                <div>
                                    <span class="text-[9px] uppercase tracking-wider text-zinc-500">ID Sewa:
                                        #DE-<?php echo $b['id']; ?></span>
                                    <h4 class="text-sm font-bold text-white dark:text-white light:text-zinc-950">
                                        <?php echo htmlspecialchars($b['car_brand'] . ' ' . $b['car_name']); ?></h4>
                                    <p class="text-[10px] text-zinc-400 mt-1">
                                        <?php echo date('d M Y', strtotime($b['pickup_date'])); ?> s/d
                                        <?php echo date('d M Y', strtotime($b['return_date'])); ?>
                                        (<?php echo $b['duration_days']; ?> Hari)
                                    </p>
                                </div>
                            </div>

                            <!-- Billing Info & Status -->
                            <div class="flex items-center justify-between md:justify-end gap-6">
                                <div class="text-left md:text-right">
                                    <span class="text-[9px] text-zinc-500 uppercase font-bold">Total Pembayaran</span>
                                    <p class="text-sm font-bold text-goldAccent">Rp
                                        <?php echo number_format($b['total_price'], 0, ',', '.'); ?></p>
                                </div>

                                <div class="flex flex-col items-end space-y-2">
                                    <?php if ($b['status'] === 'pending'): ?>
                                    <span
                                        class="px-2 py-0.5 bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-[9px] font-bold uppercase tracking-wider rounded-md">Pending</span>
                                    <!-- Action Cancel & Reschedule -->
                                    <div class="flex space-x-2 text-[10px]">
                                        <button
                                            onclick="openRescheduleModal(<?php echo $b['id']; ?>, '<?php echo date('Y-m-d', strtotime($b['pickup_date'])); ?>', '<?php echo date('Y-m-d', strtotime($b['return_date'])); ?>')"
                                            class="text-zinc-400 hover:text-goldAccent font-semibold underline">
                                            Ubah Jadwal
                                        </button>
                                        <span class="text-zinc-700">|</span>
                                        <a href="dashboard.php?cancel_id=<?php echo $b['id']; ?>"
                                            onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan sewa ini?')"
                                            class="text-red-500 hover:text-red-400 font-semibold underline">
                                            Batalkan
                                        </a>
                                    </div>
                                    <?php elseif ($b['status'] === 'approved'): ?>
                                    <span
                                        class="px-2 py-0.5 bg-blue-500/10 border border-blue-500/20 text-blue-450 text-[9px] font-bold uppercase tracking-wider rounded-md">Disetujui</span>
                                    <?php elseif ($b['status'] === 'active'): ?>
                                    <span
                                        class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[9px] font-bold uppercase tracking-wider rounded-md">Sedang
                                        Sewa</span>
                                    <?php elseif ($b['status'] === 'completed'): ?>
                                    <span
                                        class="px-2 py-0.5 bg-zinc-800 border border-zinc-700 text-zinc-400 text-[9px] font-bold uppercase tracking-wider rounded-md">Selesai</span>
                                    <?php elseif ($b['status'] === 'cancelled'): ?>
                                    <span
                                        class="px-2 py-0.5 bg-red-500/10 border border-red-500/20 text-red-400 text-[9px] font-bold uppercase tracking-wider rounded-md">Dibatalkan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Member Exclusive Recommendations Section -->
                <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-6">
                    <h3 class="text-lg font-bold text-white dark:text-white light:text-zinc-950">Penawaran Eksklusif
                        Member</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach ($recommendations as $rec): ?>
                        <a href="car_details.php?id=<?php echo $rec['id']; ?>"
                            class="group block border border-zinc-850 p-4 rounded-xl hover:border-zinc-700 transition-all duration-300 dark:border-zinc-850 light:bg-white light:border-zinc-200">
                            <img src="<?php echo htmlspecialchars($rec['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($rec['name']); ?>"
                                class="w-full h-24 object-cover rounded-lg mb-3">
                            <h4
                                class="text-xs font-bold text-white group-hover:text-goldAccent transition-all duration-200 dark:text-white light:text-zinc-900">
                                <?php echo htmlspecialchars($rec['name']); ?></h4>
                            <p class="text-[11px] font-black text-goldAccent mt-1">Rp
                                <?php echo number_format($rec['price_per_day'], 0, ',', '.'); ?><span
                                    class="text-[9px] font-normal text-zinc-500">/hari</span></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PROFILE EDITOR -->
            <div id="tab-profile" class="hidden space-y-6 animate-fade-in-up">
                <form action="dashboard.php" method="POST" enctype="multipart/form-data"
                    class="premium-glass p-8 rounded-2xl border border-zinc-800 space-y-6">
                    <h2 class="text-xl font-bold text-white dark:text-white light:text-zinc-950">Ubah Profil & Data
                        Identitas</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Nama Lengkap</label>
                            <input type="text" name="name" required
                                value="<?php echo htmlspecialchars($user_data['name']); ?>"
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                        </div>

                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Alamat Email (Akun)</label>
                            <input type="email" disabled value="<?php echo htmlspecialchars($user_data['email']); ?>"
                                class="w-full px-4 py-3 bg-zinc-950 border border-zinc-800 text-zinc-500 rounded-xl cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Nomor Handphone</label>
                            <input type="tel" name="phone" required
                                value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                        </div>

                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Nomor KTP (NIK)</label>
                            <input type="text" name="ktp_no" placeholder="Masukkan 16 digit NIK"
                                value="<?php echo htmlspecialchars($user_data['ktp_no'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                        </div>

                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Nomor SIM A</label>
                            <input type="text" name="license_no" placeholder="Masukkan nomor SIM A Anda"
                                value="<?php echo htmlspecialchars($user_data['license_no'] ?? ''); ?>"
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                        </div>

                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Unggah Foto SIM / KTP</label>
                            <input type="file" name="document_pic"
                                class="w-full px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-xl text-zinc-400 focus:outline-none focus:ring-1 focus:ring-goldAccent file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-goldAccent file:text-black hover:file:bg-[#c5a028] cursor-pointer">
                            <?php if (!empty($user_data['document_pic'])): ?>
                            <p class="text-[10px] text-emerald-400 mt-2"><i class="fa-solid fa-circle-check"></i>
                                Dokumen identitas sudah diunggah. <a href="<?php echo $user_data['document_pic']; ?>"
                                    target="_blank" class="underline font-bold text-goldAccent ml-1">Lihat Foto</a></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" name="update_profile"
                        class="py-3 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md">
                        Simpan Perubahan Profil
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Reschedule Popup Modal (Layar 5) -->
<div id="reschedule-modal"
    class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-4">
    <div class="premium-glass max-w-md w-full p-8 rounded-2xl border border-zinc-800 space-y-6 animate-fade-in-up">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <h3 class="text-base font-bold text-white">Ubah Jadwal Penyewaan</h3>
            <button onclick="closeRescheduleModal()" class="text-zinc-500 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="dashboard.php" method="POST" class="space-y-4 text-xs text-zinc-400">
            <input type="hidden" name="booking_id" id="modal-booking-id">
            <input type="hidden" name="reschedule_booking" value="1">

            <div>
                <label class="block mb-1 font-semibold">Tanggal Mulai Sewa Baru</label>
                <input type="date" name="new_pickup_date" id="modal-pickup-date" required
                    min="<?php echo date('Y-m-d'); ?>"
                    class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
            </div>

            <div>
                <label class="block mb-1 font-semibold">Tanggal Pengembalian Baru</label>
                <input type="date" name="new_return_date" id="modal-return-date" required
                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                    class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-zinc-800">
                <button type="button" onclick="closeRescheduleModal()"
                    class="py-2.5 px-4 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-white rounded-lg font-semibold">
                    Kembali
                </button>
                <button type="submit"
                    class="py-2.5 px-4 bg-goldAccent hover:bg-[#c5a028] text-black rounded-lg font-bold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tab switching engine
function switchTab(tab) {
    const tabTrips = document.getElementById('tab-trips');
    const tabProfile = document.getElementById('tab-profile');
    const btnTrips = document.getElementById('btn-tab-trips');
    const btnProfile = document.getElementById('btn-tab-profile');

    if (tab === 'trips') {
        tabTrips.classList.remove('hidden');
        tabProfile.classList.add('hidden');
        btnTrips.className =
            "w-full py-4 px-6 text-left border-l-2 border-goldAccent bg-zinc-900/60 text-white font-bold transition-all duration-200";
        btnProfile.className =
            "w-full py-4 px-6 text-left border-l-2 border-transparent text-zinc-400 hover:text-white transition-all duration-200";
    } else {
        tabTrips.classList.add('hidden');
        tabProfile.classList.remove('hidden');
        btnTrips.className =
            "w-full py-4 px-6 text-left border-l-2 border-transparent text-zinc-400 hover:text-white transition-all duration-200";
        btnProfile.className =
            "w-full py-4 px-6 text-left border-l-2 border-goldAccent bg-zinc-900/60 text-white font-bold transition-all duration-200";
    }
}

// Modal Control
function openRescheduleModal(id, pickup, returnDate) {
    document.getElementById('modal-booking-id').value = id;
    document.getElementById('modal-pickup-date').value = pickup;
    document.getElementById('modal-return-date').value = returnDate;
    document.getElementById('reschedule-modal').classList.remove('hidden');
}

function closeRescheduleModal() {
    document.getElementById('reschedule-modal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>