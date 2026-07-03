<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Retrieve search & reservation inputs to carry them through
$search_location = $_GET['location'] ?? '';
$search_pickup = $_GET['pickup_date'] ?? '';
$search_return = $_GET['return_date'] ?? '';

// Retrieve filtering parameters
$filter_name = trim($_GET['search'] ?? '');
$filter_brand = $_GET['brand'] ?? 'all';
$filter_fuel = $_GET['fuel'] ?? 'all';
$filter_trans = $_GET['transmission'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'price_asc';

// Build dynamic SQL
$sql = "SELECT * FROM cars WHERE status != 'maintenance'";
$params = [];

if (!empty($filter_name)) {
    $sql .= " AND name LIKE ?";
    $params[] = '%' . $filter_name . '%';
}
if ($filter_brand !== 'all' && !empty($filter_brand)) {
    $sql .= " AND brand = ?";
    $params[] = $filter_brand;
}
if ($filter_fuel !== 'all' && !empty($filter_fuel)) {
    $sql .= " AND fuel_type = ?";
    $params[] = $filter_fuel;
}
if ($filter_trans !== 'all' && !empty($filter_trans)) {
    $sql .= " AND transmission = ?";
    $params[] = $filter_trans;
}

// Order sorting
if ($sort_by === 'price_desc') {
    $sql .= " ORDER BY price_per_day DESC";
} else {
    $sql .= " ORDER BY price_per_day ASC";
}

try {
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll();
    
    // Fetch distinct brands for filter options
    $brands_stmt = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand ASC");
    $brands = $brands_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error filtering catalog: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header Page -->
    <div class="mb-10 text-center md:text-left">
        <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Katalog Kendaraan Premium</h1>
        <p class="text-zinc-400 text-xs mt-1">Pilih dari deretan koleksi mobil mewah dan modern untuk kenyamanan perjalanan Anda</p>
    </div>

    <!-- Booking details carry-over warning bar -->
    <div class="no-print premium-glass p-5 rounded-2xl border border-zinc-800 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs items-center">
        <div>
            <span class="block text-[10px] text-zinc-500 uppercase font-bold tracking-wider">Lokasi Penjemputan</span>
            <span class="font-semibold text-white dark:text-white light:text-zinc-900"><?php echo !empty($search_location) ? htmlspecialchars($search_location) : 'Belum ditentukan (Pilih di detail mobil)'; ?></span>
        </div>
        <div>
            <span class="block text-[10px] text-zinc-500 uppercase font-bold tracking-wider">Jadwal Sewa</span>
            <span class="font-semibold text-white dark:text-white light:text-zinc-900">
                <?php if (!empty($search_pickup) && !empty($search_return)): ?>
                    <?php echo date('d M Y', strtotime($search_pickup)); ?> s/d <?php echo date('d M Y', strtotime($search_return)); ?>
                <?php else: ?>
                    Belum ditentukan (Pilih di detail mobil)
                <?php endif; ?>
            </span>
        </div>
        <div class="text-right">
            <a href="index.php" class="text-goldAccent hover:underline font-bold flex items-center justify-end space-x-1">
                <span>Ubah Pencarian Utama</span>
                <i class="fa-solid fa-arrow-rotate-left text-[10px]"></i>
            </a>
        </div>
    </div>

    <!-- Main Workspace: Filters & Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Left Side: Filter Controls -->
        <div class="lg:col-span-1 space-y-6">
            <form action="catalog.php" method="GET" class="premium-glass p-6 rounded-2xl border border-zinc-800 space-y-5 text-xs text-zinc-400">
                <!-- Keep date parameters -->
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($search_location); ?>">
                <input type="hidden" name="pickup_date" value="<?php echo htmlspecialchars($search_pickup); ?>">
                <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($search_return); ?>">

                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h3 class="font-bold text-white uppercase tracking-wider text-xs">Filter Pencarian</h3>
                    <a href="catalog.php?location=<?php echo urlencode($search_location); ?>&pickup_date=<?php echo urlencode($search_pickup); ?>&return_date=<?php echo urlencode($search_return); ?>" 
                       class="text-goldAccent text-[10px] hover:underline">Reset</a>
                </div>

                <!-- 1. Search text -->
                <div>
                    <label class="block font-semibold mb-1.5">Kata Kunci Model</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-zinc-500"></i>
                        <input type="text" name="search" placeholder="Cari nama mobil..." value="<?php echo htmlspecialchars($filter_name); ?>" 
                               class="w-full pl-9 pr-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>
                </div>

                <!-- 2. Brand Selector -->
                <div>
                    <label class="block font-semibold mb-1.5">Merk / Brand</label>
                    <select name="brand" class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        <option value="all">Semua Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo ($filter_brand === $brand) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Fuel Type -->
                <div>
                    <label class="block font-semibold mb-1.5">Bahan Bakar</label>
                    <select name="fuel" class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        <option value="all" <?php echo ($filter_fuel === 'all') ? 'selected' : ''; ?>>Semua Bahan Bakar</option>
                        <option value="bensin" <?php echo ($filter_fuel === 'bensin') ? 'selected' : ''; ?>>Bensin</option>
                        <option value="diesel" <?php echo ($filter_fuel === 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                        <option value="hybrid" <?php echo ($filter_fuel === 'hybrid') ? 'selected' : ''; ?>>Hybrid & EV</option>
                    </select>
                </div>

                <!-- 4. Transmission -->
                <div>
                    <label class="block font-semibold mb-1.5">Transmisi</label>
                    <select name="transmission" class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        <option value="all" <?php echo ($filter_trans === 'all') ? 'selected' : ''; ?>>Semua Transmisi</option>
                        <option value="automatic" <?php echo ($filter_trans === 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                        <option value="manual" <?php echo ($filter_trans === 'manual') ? 'selected' : ''; ?>>Manual</option>
                    </select>
                </div>

                <!-- 5. Sort By Price -->
                <div>
                    <label class="block font-semibold mb-1.5">Urutkan Tarif</label>
                    <select name="sort" class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Tarif Terendah &rarr; Tertinggi</option>
                        <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Tarif Tertinggi &rarr; Terendah</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-3.5 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl transition-all duration-205 shadow-md">
                    Terapkan Filter
                </button>
            </form>
        </div>

        <!-- Right Side: Car Grid -->
        <div class="lg:col-span-3">
            <?php if (empty($cars)): ?>
                <div class="text-center py-20 premium-glass border border-zinc-800 rounded-2xl space-y-4">
                    <div class="w-16 h-16 bg-zinc-900 text-zinc-650 rounded-full flex items-center justify-center mx-auto border border-zinc-800">
                        <i class="fa-solid fa-car-side text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Armada Tidak Ditemukan</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto">Kami tidak dapat menemukan armada dengan kombinasi filter tersebut. Coba reset filter atau gunakan kata kunci lain.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($cars as $car): ?>
                        <div class="bg-darkCard border border-zinc-800 rounded-2xl overflow-hidden shadow-xl hover:border-zinc-700 transition-all duration-300 flex flex-col justify-between group dark:bg-darkCard dark:border-zinc-800 light:bg-white light:border-zinc-200 light:shadow-md">
                            <!-- Image Wrapper -->
                            <div class="relative h-48 bg-zinc-950 overflow-hidden">
                                <!-- Status Badge -->
                                <div class="absolute top-4 left-4 z-10">
                                    <?php if ($car['status'] === 'available'): ?>
                                        <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full">Tersedia</span>
                                    <?php else: ?>
                                        <span class="bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full">Disewa</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Fuel Type Badge -->
                                <div class="absolute top-4 right-4 z-10">
                                    <span class="bg-black/60 backdrop-blur-sm border border-white/10 text-goldAccent text-[10px] font-semibold uppercase px-2 py-0.5 rounded-md">
                                        <?php echo htmlspecialchars($car['fuel_type']); ?>
                                    </span>
                                </div>

                                <img src="<?php echo (strpos($car['image_url'], 'http') === 0) ? htmlspecialchars($car['image_url']) : htmlspecialchars($car['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($car['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>

                            <!-- Details -->
                            <div class="p-6 space-y-4 flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-xs text-zinc-400 font-semibold tracking-wider uppercase"><?php echo htmlspecialchars($car['brand']); ?></span>
                                        <span class="text-xs font-bold text-goldAccent">Premium</span>
                                    </div>
                                    <h3 class="text-base font-bold text-white dark:text-white light:text-zinc-900 group-hover:text-goldAccent transition-all duration-200">
                                        <?php echo htmlspecialchars($car['name']); ?>
                                    </h3>
                                    <p class="text-zinc-400 text-[11px] mt-2 line-clamp-2 leading-relaxed">
                                        <?php echo htmlspecialchars($car['description'] ?? 'Pilihan tepat untuk merasakan kenyamanan eksklusif berkendara.'); ?>
                                    </p>
                                </div>

                                <!-- Technical Spec Icons -->
                                <div class="grid grid-cols-3 gap-2 py-3 border-t border-b border-zinc-850 dark:border-zinc-850 light:border-zinc-100 text-zinc-400 text-[10px] text-center">
                                    <div class="flex flex-col items-center space-y-0.5">
                                        <i class="fa-solid fa-users text-goldAccent"></i>
                                        <span><?php echo $car['capacity']; ?> Kursi</span>
                                    </div>
                                    <div class="flex flex-col items-center space-y-0.5">
                                        <i class="fa-solid fa-gears text-goldAccent"></i>
                                        <span class="capitalize"><?php echo $car['transmission']; ?></span>
                                    </div>
                                    <div class="flex flex-col items-center space-y-0.5">
                                        <i class="fa-solid fa-suitcase text-goldAccent"></i>
                                        <span><?php echo $car['luggage']; ?>L</span>
                                    </div>
                                </div>

                                <!-- Price & CTA -->
                                <div class="flex items-center justify-between pt-3 mt-auto">
                                    <div>
                                        <span class="text-[9px] uppercase font-bold tracking-wider text-zinc-500">Tarif per Hari</span>
                                        <p class="text-base font-black text-white dark:text-white light:text-zinc-900">
                                            Rp <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?>
                                        </p>
                                    </div>
                                    
                                    <?php
                                    // Carry parameters to booking sheet
                                    $details_params = http_build_query([
                                        'id' => $car['id'],
                                        'location' => $search_location,
                                        'pickup_date' => $search_pickup,
                                        'return_date' => $search_return
                                    ]);
                                    ?>
                                    <a href="car_details.php?<?php echo $details_params; ?>" 
                                       class="py-2.5 px-4 bg-goldAccent hover:bg-[#c5a028] text-black font-bold rounded-xl text-xs transition-all duration-200 flex items-center space-x-1 shadow-md">
                                        <span>Sewa</span>
                                        <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
