<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Handle Search Query Parameters
$search_location = $_GET['location'] ?? '';
$search_pickup = $_GET['pickup_date'] ?? '';
$search_return = $_GET['return_date'] ?? '';

// Handle Category Tabs Filter
$active_tab = $_GET['fuel'] ?? 'all';
if (!in_array($active_tab, ['all', 'diesel', 'bensin', 'hybrid'])) {
    $active_tab = 'all';
}

try {
    // Construct SQL Query based on selected fuel tab
    if ($active_tab === 'all') {
        $query = "SELECT * FROM cars WHERE status != 'maintenance' ORDER BY price_per_day ASC";
        $stmt = $pdo->query($query);
    } else {
        $query = "SELECT * FROM cars WHERE status != 'maintenance' AND fuel_type = ? ORDER BY price_per_day ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$active_tab]);
    }
    $cars = $stmt->fetchAll();

    // Fetch latest 3 routes for the destination guides section
    $routes_stmt = $pdo->query("SELECT * FROM routes ORDER BY id DESC LIMIT 3");
    $home_routes = $routes_stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving cars: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section with cinematic background -->
<div class="relative bg-black overflow-hidden min-h-[90vh] flex items-center transition-colors duration-300 dark:bg-black light:bg-zinc-900">
    <!-- Overlay Background Image / Pattern -->
    <div class="absolute inset-0 z-0 opacity-40">
        <div class="absolute inset-0 bg-gradient-to-t from-darkBg via-transparent to-black"></div>
        <img src="https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1920&q=80" 
             alt="Luxury Car Silhouette" 
             class="w-full h-full object-cover">
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Text Content -->
            <div class="lg:col-span-7 space-y-6 text-left animate-fade-in-up">
                <span class="inline-block py-1.5 px-3 bg-goldAccent/10 text-goldAccent font-semibold rounded-full text-xs tracking-wider uppercase border border-goldAccent/20">
                    Eksklusif & Premium
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-white">
                    Pengalaman Berkendara Kelas Atas bersama <span class="text-goldAccent">DriveEase</span>
                </h1>
                <p class="text-zinc-400 text-sm sm:text-base max-w-xl leading-relaxed">
                    Sewa kendaraan premium impian Anda dengan mudah. Mulai dari SUV diesel yang tangguh, sedan bensin sport yang mewah, hingga kendaraan listrik otonom masa depan.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="catalog.php" class="py-3.5 px-8 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-sm transition-all duration-200 shadow-lg shadow-goldAccent/10">
                        Eksplorasi Katalog
                    </a>
                    <a href="routes.php" class="py-3.5 px-8 bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-white font-semibold rounded-xl text-sm transition-all duration-200">
                        Rute Rekomendasi
                    </a>
                </div>
            </div>

            <!-- Glassmorphism Booking Form Widget -->
            <div class="lg:col-span-5 animate-fade-in-up" style="animation-delay: 0.1s;">
                <form action="catalog.php" method="GET" class="premium-glass p-8 rounded-2xl border border-white/10 shadow-2xl space-y-5">
                    <h3 class="text-white text-lg font-bold tracking-tight">Cari Mobil Premium</h3>
                    <p class="text-zinc-400 text-xs">Pilih jadwal sewa Anda untuk menyaring mobil yang tersedia</p>
                    
                    <div class="space-y-4">
                        <!-- Lokasi Penjemputan -->
                        <div>
                            <label class="block text-[10px] font-bold tracking-wider uppercase text-zinc-400 mb-1.5">Lokasi Penjemputan</label>
                            <div class="relative">
                                <i class="fa-solid fa-location-dot absolute left-4 top-3.5 text-goldAccent text-sm"></i>
                                <select name="location" class="w-full pl-10 pr-4 py-3 bg-zinc-950/70 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent appearance-none">
                                    <option value="" disabled selected>Pilih garasi / bandara</option>
                                    <option value="Bandara YIA" <?php echo ($search_location === 'Bandara YIA') ? 'selected' : ''; ?>>Yogyakarta International Airport (YIA)</option>
                                    <option value="Bandara Adisutjipto" <?php echo ($search_location === 'Bandara Adisutjipto') ? 'selected' : ''; ?>>Bandara Adisutjipto (JOG)</option>
                                    <option value="Stasiun Tugu" <?php echo ($search_location === 'Stasiun Tugu') ? 'selected' : ''; ?>>Stasiun Tugu Yogyakarta</option>
                                    <option value="Garasi DriveEase Sleman" <?php echo ($search_location === 'Garasi DriveEase Sleman') ? 'selected' : ''; ?>>Garasi Pusat DriveEase Sleman</option>
                                </select>
                            </div>
                        </div>

                        <!-- Grid Tanggal -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold tracking-wider uppercase text-zinc-400 mb-1.5">Tanggal Mulai</label>
                                <input type="date" name="pickup_date" value="<?php echo htmlspecialchars($search_pickup); ?>" 
                                       class="w-full px-4 py-3 bg-zinc-950/70 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold tracking-wider uppercase text-zinc-400 mb-1.5">Tanggal Selesai</label>
                                <input type="date" name="return_date" value="<?php echo htmlspecialchars($search_return); ?>" 
                                       class="w-full px-4 py-3 bg-zinc-950/70 border border-zinc-800 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md">
                        Cari Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Catalog Section -->
<section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 transition-colors duration-300">
    <div class="text-center space-y-3 mb-12">
        <span class="text-xs font-bold tracking-widest text-goldAccent uppercase">Garasi Kami</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white dark:text-white light:text-zinc-900">Armada Mobil Premium</h2>
        <div class="w-12 h-1 bg-goldAccent mx-auto rounded-full"></div>
    </div>

    <!-- Category Switcher Tabs -->
    <div class="flex justify-center mb-12">
        <div class="inline-flex p-1 bg-zinc-900 border border-zinc-800 rounded-2xl dark:bg-zinc-900 dark:border-zinc-800 light:bg-zinc-200 light:border-zinc-300">
            <a href="index.php?fuel=all#catalog" 
               class="px-5 py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-all duration-200 <?php echo ($active_tab === 'all') ? 'bg-goldAccent text-black shadow-md' : 'text-zinc-400 hover:text-white'; ?>">
                Semua Tipe
            </a>
            <a href="index.php?fuel=diesel#catalog" 
               class="px-5 py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-all duration-200 <?php echo ($active_tab === 'diesel') ? 'bg-goldAccent text-black shadow-md' : 'text-zinc-400 hover:text-white'; ?>">
                Diesel (SUV/MPV)
            </a>
            <a href="index.php?fuel=bensin#catalog" 
               class="px-5 py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-all duration-200 <?php echo ($active_tab === 'bensin') ? 'bg-goldAccent text-black shadow-md' : 'text-zinc-400 hover:text-white'; ?>">
                Bensin
            </a>
            <a href="index.php?fuel=hybrid#catalog" 
               class="px-5 py-2.5 rounded-xl text-xs font-bold tracking-wider uppercase transition-all duration-200 <?php echo ($active_tab === 'hybrid') ? 'bg-goldAccent text-black shadow-md' : 'text-zinc-400 hover:text-white'; ?>">
                Hybrid & EV
            </a>
        </div>
    </div>

    <!-- Cars Grid -->
    <?php if (empty($cars)): ?>
        <div class="text-center py-12 premium-glass border border-zinc-800 rounded-2xl">
            <i class="fa-solid fa-car-side text-4xl text-zinc-600 mb-4"></i>
            <p class="text-sm text-zinc-400">Tidak ada armada mobil yang tersedia di kategori ini saat ini.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($cars as $car): ?>
                <div class="bg-darkCard border border-zinc-800 rounded-2xl overflow-hidden shadow-xl hover:border-zinc-700 transition-all duration-300 flex flex-col justify-between group dark:bg-darkCard dark:border-zinc-800 light:bg-white light:border-zinc-200 light:shadow-md">
                    <!-- Image Wrapper -->
                    <div class="relative h-56 bg-zinc-950 overflow-hidden">
                        <!-- Availability Badge -->
                        <div class="absolute top-4 left-4 z-10">
                            <?php if ($car['status'] === 'available'): ?>
                                <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full">Tersedia</span>
                            <?php else: ?>
                                <span class="bg-red-500/10 border border-red-500/20 text-red-400 text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full">Sedang Disewa</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Fuel Type Badge -->
                        <div class="absolute top-4 right-4 z-10">
                            <span class="bg-black/60 backdrop-blur-sm border border-white/10 text-goldAccent text-[10px] font-semibold uppercase px-2 py-0.5 rounded-md">
                                <?php echo htmlspecialchars($car['fuel_type']); ?>
                            </span>
                        </div>

                        <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($car['name']); ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>

                    <!-- Details -->
                    <div class="p-6 space-y-4 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs text-zinc-400 font-semibold tracking-wider uppercase"><?php echo htmlspecialchars($car['brand']); ?></span>
                                <span class="text-xs font-bold text-goldAccent">Premium Class</span>
                            </div>
                            <h3 class="text-xl font-bold text-white dark:text-white light:text-zinc-900 group-hover:text-goldAccent transition-all duration-200">
                                <?php echo htmlspecialchars($car['name']); ?>
                            </h3>
                            <p class="text-zinc-400 text-xs mt-2 line-clamp-2 leading-relaxed">
                                <?php echo htmlspecialchars($car['description'] ?? 'Rasakan sensasi kenyamanan eksklusif berkendara dengan armada premium berkelas.'); ?>
                            </p>
                        </div>

                        <!-- Technical Spec Icons -->
                        <div class="grid grid-cols-3 gap-3 py-4 border-t border-b border-zinc-850 dark:border-zinc-850 light:border-zinc-100 text-zinc-400 text-[11px]">
                            <div class="flex flex-col items-center space-y-1">
                                <i class="fa-solid fa-users text-goldAccent"></i>
                                <span><?php echo $car['capacity']; ?> Kursi</span>
                            </div>
                            <div class="flex flex-col items-center space-y-1">
                                <i class="fa-solid fa-gears text-goldAccent"></i>
                                <span class="capitalize"><?php echo $car['transmission']; ?></span>
                            </div>
                            <div class="flex flex-col items-center space-y-1">
                                <i class="fa-solid fa-suitcase text-goldAccent"></i>
                                <span><?php echo $car['luggage']; ?> L Bagasi</span>
                            </div>
                        </div>

                        <!-- Price & CTA -->
                        <div class="flex items-center justify-between pt-4 mt-auto">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">Harga Per Hari</span>
                                <p class="text-lg font-black text-white dark:text-white light:text-zinc-900">
                                    <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?> <span class="text-xs font-normal text-zinc-400">/ hari</span>
                                </p>
                            </div>
                            
                            <?php
                            // Carry over booking dates to detail page
                            $details_params = http_build_query([
                                'id' => $car['id'],
                                'location' => $search_location,
                                'pickup_date' => $search_pickup,
                                'return_date' => $search_return
                            ]);
                            ?>
                            <a href="car_details.php?<?php echo $details_params; ?>" 
                               class="py-3 px-5 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 flex items-center space-x-1 shadow-md">
                                <span>Sewa</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Destination Guides & Routes Section -->
<section id="guides" class="bg-black/40 border-t border-b border-zinc-900 py-24 transition-colors duration-300 dark:bg-black/40 dark:border-zinc-900 light:bg-zinc-100 light:border-zinc-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-3 mb-16">
            <span class="text-xs font-bold tracking-widest text-goldAccent uppercase">Eksplorasi Destinasi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white dark:text-white light:text-zinc-900">Rekomendasi Rute & Armada</h2>
            <div class="w-12 h-1 bg-goldAccent mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php if (empty($home_routes)): ?>
                <div class="col-span-3 text-center py-12 premium-glass border border-zinc-800 rounded-2xl">
                    <p class="text-zinc-400">Belum ada rute rekomendasi.</p>
                </div>
            <?php else: ?>
                <?php foreach ($home_routes as $route): ?>
                    <!-- Dynamic Guide -->
                    <div class="premium-glass border border-zinc-800 rounded-2xl overflow-hidden flex flex-col justify-between group dark:border-zinc-800 light:bg-white light:border-zinc-200">
                        <div class="relative h-48 bg-zinc-950 overflow-hidden">
                            <img src="<?php echo (strpos($route['image_url'], 'http') === 0) ? htmlspecialchars($route['image_url']) : htmlspecialchars($route['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($route['title']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-4 left-4 bg-black/60 backdrop-blur-sm border border-goldAccent/30 text-goldAccent text-[9px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full">
                                <?php echo htmlspecialchars($route['type_label']); ?>
                            </span>
                        </div>
                        <div class="p-6 space-y-4 flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white dark:text-white light:text-zinc-900 line-clamp-1"><?php echo htmlspecialchars($route['title']); ?></h3>
                                <p class="text-zinc-400 text-xs mt-2 leading-relaxed line-clamp-3">
                                    <?php echo htmlspecialchars($route['description']); ?>
                                </p>
                            </div>
                            <div class="pt-4 border-t border-zinc-800 flex items-center justify-between mt-auto dark:border-zinc-850 light:border-zinc-100 text-xs">
                                <span class="text-zinc-400">Rekomendasi Mobil:</span>
                                <a href="catalog.php?fuel=<?php echo htmlspecialchars($route['fuel_type_link']); ?>" class="font-extrabold text-goldAccent hover:underline truncate max-w-[130px]" title="<?php echo htmlspecialchars($route['recommended_car']); ?>">
                                    <?php echo htmlspecialchars($route['recommended_car']); ?> &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to action Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
    <div class="premium-glass border border-zinc-800 p-12 rounded-3xl space-y-6 max-w-4xl mx-auto dark:border-zinc-800 light:bg-white light:border-zinc-200 light:shadow-lg">
        <h2 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-900">Nikmati Penawaran Member Eksklusif</h2>
        <p class="text-zinc-400 text-sm max-w-xl mx-auto leading-relaxed">
            Daftarkan diri Anda sebagai member DriveEase dan nikmati diskon khusus hingga 15% untuk sewa mobil pertama, opsi asuransi gratis, serta layanan penjemputan VIP.
        </p>
        <div class="pt-2">
            <a href="register.php" class="inline-block py-3.5 px-8 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-sm transition-all duration-200 shadow-md">
                Gabung Member Sekarang
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
