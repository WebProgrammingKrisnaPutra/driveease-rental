<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status != 'maintenance'");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$car) {
    header("Location: index.php");
    exit;
}

// Prefill form values from Hero Search
$location = $_GET['location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';

// Coordinate mapping for local cities
$map_coords = [-7.721, 110.368]; // Sleman Default
$map_title = "Garasi Pusat DriveEase Sleman";

if ($location === 'Bandara YIA') {
    $map_coords = [-7.904, 110.057];
    $map_title = "Meeting Point: Terminal Kedatangan Bandara YIA";
} elseif ($location === 'Bandara Adisutjipto') {
    $map_coords = [-7.788, 110.431];
    $map_title = "Meeting Point: VIP Lounge Bandara Adisutjipto";
} elseif ($location === 'Stasiun Tugu') {
    $map_coords = [-7.789, 110.363];
    $map_title = "Meeting Point: Drop Zone Pintu Timur Stasiun Tugu";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumbs -->
    <nav class="text-xs text-zinc-500 mb-8 font-medium">
        <a href="index.php" class="hover:text-goldAccent">Home</a> &gt; 
        <a href="index.php#catalog" class="hover:text-goldAccent">Katalog</a> &gt; 
        <span class="text-zinc-300 dark:text-zinc-350 light:text-zinc-800"><?php echo htmlspecialchars($car['name']); ?></span>
    </nav>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Left Side: Visuals & Specifications (Lg: col-span-7) -->
        <div class="lg:col-span-7 space-y-8">
            <!-- Hero Car Image -->
            <div class="relative bg-zinc-950 rounded-2xl overflow-hidden border border-zinc-800 shadow-2xl h-[400px]">
                <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($car['name']); ?>" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-6 left-6 right-6">
                    <span class="inline-block px-2.5 py-1 bg-goldAccent text-black font-extrabold text-[10px] uppercase tracking-wider rounded-md mb-2">
                        <?php echo htmlspecialchars($car['fuel_type']); ?>
                    </span>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">
                        <?php echo htmlspecialchars($car['name']); ?>
                    </h1>
                </div>
            </div>

            <!-- Detailed Specifications Grid -->
            <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-6">
                <h3 class="text-lg font-bold text-white dark:text-white light:text-zinc-950">Spesifikasi Kendaraan</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center text-zinc-400 text-xs">
                    <div class="bg-zinc-900/50 p-4 rounded-xl border border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                        <i class="fa-solid fa-users text-goldAccent text-lg mb-2"></i>
                        <div class="font-bold text-white dark:text-white light:text-zinc-900 mb-0.5"><?php echo $car['capacity']; ?> Kursi</div>
                        <span class="text-[10px] text-zinc-500">Kapasitas Penumpang</span>
                    </div>
                    <div class="bg-zinc-900/50 p-4 rounded-xl border border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                        <i class="fa-solid fa-gears text-goldAccent text-lg mb-2"></i>
                        <div class="font-bold text-white dark:text-white light:text-zinc-900 capitalize mb-0.5"><?php echo $car['transmission']; ?></div>
                        <span class="text-[10px] text-zinc-500">Tipe Transmisi</span>
                    </div>
                    <div class="bg-zinc-900/50 p-4 rounded-xl border border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                        <i class="fa-solid fa-suitcase text-goldAccent text-lg mb-2"></i>
                        <div class="font-bold text-white dark:text-white light:text-zinc-900 mb-0.5"><?php echo $car['luggage']; ?> L</div>
                        <span class="text-[10px] text-zinc-500">Kapasitas Bagasi</span>
                    </div>
                    <div class="bg-zinc-900/50 p-4 rounded-xl border border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-50 light:border-zinc-200">
                        <i class="fa-solid fa-gas-pump text-goldAccent text-lg mb-2"></i>
                        <div class="font-bold text-white dark:text-white light:text-zinc-900 capitalize mb-0.5"><?php echo $car['fuel_type']; ?></div>
                        <span class="text-[10px] text-zinc-500">Bahan Bakar</span>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">Deskripsi</h4>
                    <p class="text-zinc-400 text-xs leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($car['description'] ?? '')); ?>
                    </p>
                </div>
            </div>

            <!-- Interactive Map Section (Layar 6) -->
            <div class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white dark:text-white light:text-zinc-950">Lokasi Penjemputan</h3>
                        <p class="text-zinc-400 text-[11px]">Ambil mobil langsung ke garasi kami atau pilih di titik jemput bandara/stasiun</p>
                    </div>
                    <i class="fa-solid fa-map-location-dot text-goldAccent text-xl"></i>
                </div>
                
                <!-- Leaflet Map Box -->
                <div id="map" class="h-64 rounded-xl overflow-hidden border border-zinc-800/80 shadow-inner z-10"></div>
                <div class="flex items-center space-x-2.5 text-xs text-zinc-400">
                    <i class="fa-solid fa-circle-info text-goldAccent"></i>
                    <span>Titik aktif saat ini: <strong class="text-white dark:text-white light:text-zinc-800" id="active-map-loc"><?php echo $map_title; ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Right Side: Booking Sheet Form & Billing (Lg: col-span-5) -->
        <div class="lg:col-span-5">
            <div class="premium-glass p-8 rounded-2xl border border-zinc-800 shadow-2xl sticky top-28 space-y-6">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-zinc-400">Tarif Dasar</span>
                    <p class="text-3xl font-black text-white dark:text-white light:text-zinc-950">
                        Rp <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?> <span class="text-xs font-normal text-zinc-400">/ hari</span>
                    </p>
                </div>

                <?php if (is_logged_in()): ?>
                    <form action="booking_process.php" method="POST" class="space-y-5">
                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                        <input type="hidden" name="duration_days" id="duration_days" value="0">
                        
                        <!-- Inputs -->
                        <div class="space-y-4">
                            <!-- Lokasi -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Lokasi Pengambilan</label>
                                <select name="pickup_location" id="pickup_location_select" required 
                                        class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent appearance-none">
                                    <option value="Garasi DriveEase Sleman" <?php echo ($location === 'Garasi DriveEase Sleman' || empty($location)) ? 'selected' : ''; ?>>Garasi Pusat DriveEase Sleman</option>
                                    <option value="Bandara YIA" <?php echo ($location === 'Bandara YIA') ? 'selected' : ''; ?>>Bandara Internasional Yogyakarta (YIA)</option>
                                    <option value="Bandara Adisutjipto" <?php echo ($location === 'Bandara Adisutjipto') ? 'selected' : ''; ?>>Bandara Adisutjipto (JOG)</option>
                                    <option value="Stasiun Tugu" <?php echo ($location === 'Stasiun Tugu') ? 'selected' : ''; ?>>Stasiun Tugu Yogyakarta</option>
                                </select>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Tanggal Sewa</label>
                                    <input type="date" name="pickup_date" id="pickup_date" required min="<?php echo date('Y-m-d'); ?>"
                                           value="<?php echo htmlspecialchars($pickup_date); ?>"
                                           class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Kembali</label>
                                    <input type="date" name="return_date" id="return_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           value="<?php echo htmlspecialchars($return_date); ?>"
                                           class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                                </div>
                            </div>

                            <!-- Additional Options -->
                            <div class="space-y-3 pt-2">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400">Layanan Tambahan</label>
                                
                                <label class="flex items-center justify-between p-3 rounded-xl border border-zinc-800 bg-zinc-900/40 select-none cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" name="driver_option" id="driver_option" value="1" 
                                               class="h-4 w-4 rounded text-goldAccent focus:ring-goldAccent border-zinc-800 bg-zinc-900">
                                        <div>
                                            <span class="text-xs font-bold text-white">Dengan Driver Professional</span>
                                            <p class="text-[10px] text-zinc-500">Supir ramah dan berlisensi</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-goldAccent">+Rp 150k/hari</span>
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-xl border border-zinc-800 bg-zinc-900/40 select-none cursor-pointer">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" name="insurance_option" id="insurance_option" value="1" 
                                               class="h-4 w-4 rounded text-goldAccent focus:ring-goldAccent border-zinc-800 bg-zinc-900">
                                        <div>
                                            <span class="text-xs font-bold text-white">Proteksi Asuransi Premium</span>
                                            <p class="text-[10px] text-zinc-500">Penanggulangan lecet dan kecelakaan</p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-goldAccent">+Rp 50k/hari</span>
                                </label>
                            </div>
                        </div>

                        <!-- Live Cost Breakdown (Billing Screen preview) -->
                        <div class="border-t border-zinc-850 pt-5 space-y-2.5 text-xs text-zinc-400">
                            <h4 class="font-bold text-white text-xs uppercase tracking-wider mb-2">Simulasi Tagihan</h4>
                            <div class="flex justify-between">
                                <span>Durasi Sewa:</span>
                                <span id="calc-duration" class="font-bold text-white">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Kendaraan Dasar:</span>
                                <span id="calc-base" class="font-semibold text-white">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Supir Tambahan:</span>
                                <span id="calc-driver" class="font-semibold text-white">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Layanan Asuransi:</span>
                                <span id="calc-insurance" class="font-semibold text-white">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pajak (PPN 10%):</span>
                                <span id="calc-tax" class="font-semibold text-white">Rp 0</span>
                            </div>
                            <hr class="border-zinc-850 my-2">
                            <div class="flex justify-between items-baseline text-white">
                                <span class="font-bold">Total Pembayaran:</span>
                                <span id="calc-total" class="text-lg font-black text-goldAccent">Rp 0</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full py-3.5 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-sm transition-all duration-200 shadow-md">
                            Lanjutkan Pembayaran
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Redirect to Login with state carrying -->
                    <div class="space-y-4 py-4 text-center">
                        <p class="text-xs text-zinc-400">Anda harus masuk terlebih dahulu untuk melakukan reservasi mobil premium.</p>
                        <a href="login.php?redirect=<?php echo urlencode('car_details.php?id=' . $car['id'] . '&location=' . $location . '&pickup_date=' . $pickup_date . '&return_date=' . $return_date); ?>" 
                           class="inline-block w-full py-3.5 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200">
                            Masuk / Daftar Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Map coordinates selection updater -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Init Map
        const centerCoords = <?php echo json_encode($map_coords); ?>;
        const placeName = <?php echo json_encode($map_title); ?>;
        
        initRentalMap(centerCoords, placeName);
        
        // Location picker listener
        const locSelect = document.getElementById('pickup_location_select');
        const locLabel = document.getElementById('active-map-loc');
        
        if (locSelect) {
            locSelect.addEventListener('change', (e) => {
                let coords = [-7.721, 110.368]; // Sleman
                let text = "Garasi Pusat DriveEase Sleman";
                
                if (e.target.value === 'Bandara YIA') {
                    coords = [-7.904, 110.057];
                    text = "Meeting Point: Terminal Kedatangan Bandara YIA";
                } else if (e.target.value === 'Bandara Adisutjipto') {
                    coords = [-7.788, 110.431];
                    text = "Meeting Point: VIP Lounge Bandara Adisutjipto";
                } else if (e.target.value === 'Stasiun Tugu') {
                    coords = [-7.789, 110.363];
                    text = "Meeting Point: Drop Zone Pintu Timur Stasiun Tugu";
                }
                
                if (locLabel) locLabel.innerText = text;
                
                // Pan map
                if (window.rentalMap) {
                    window.rentalMap.setView(coords, 14);
                    // Clear old layers & reload with new center pin
                    window.rentalMap.eachLayer((layer) => {
                        if (layer instanceof L.Marker) {
                            window.rentalMap.removeLayer(layer);
                        }
                    });
                    
                    const goldIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class='w-8 h-8 rounded-full bg-[#D4AF37] border-2 border-white flex items-center justify-center shadow-lg animate-pulse'>
                                 <div class='w-2 h-2 rounded-full bg-black'></div>
                               </div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    
                    L.marker(coords, { icon: goldIcon }).addTo(window.rentalMap)
                        .bindPopup(`<strong class="text-xs font-bold font-sans">${text}</strong>`, { closeButton: false })
                        .openPopup();
                }
            });
        }

        // Init Price Calculations
        initBookingCalc(<?php echo (float)$car['price_per_day']; ?>);
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
