<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// Fetch routes from database
try {
    $routes = $pdo->query("SELECT * FROM routes ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header Page -->
    <div class="mb-12 text-center">
        <span class="text-xs font-bold tracking-widest text-goldAccent uppercase">Eksplorasi Tanpa Batas</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white dark:text-white light:text-zinc-900 mt-2">Panduan Rute Wisata Premium</h1>
        <p class="text-zinc-400 text-sm mt-3 max-w-2xl mx-auto">Kami telah menyusun beberapa rekomendasi perjalanan terbaik untuk Anda nikmati dengan kenyamanan maksimal menggunakan armada DriveEase.</p>
        <div class="w-12 h-1 bg-goldAccent mx-auto rounded-full mt-6"></div>
    </div>

    <!-- Routes Container -->
    <div class="space-y-16">
        <?php if (empty($routes)): ?>
            <div class="text-center py-12 premium-glass border border-zinc-800 rounded-2xl">
                <p class="text-zinc-400">Belum ada rute wisata yang ditambahkan.</p>
            </div>
        <?php else: ?>
            <?php foreach ($routes as $index => $route): 
                $is_even = ($index % 2) !== 0;
            ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div class="<?php echo $is_even ? 'order-2 lg:order-2' : 'order-2 lg:order-1'; ?> space-y-5">
                        <span class="inline-block py-1.5 px-3 bg-zinc-800 text-goldAccent font-semibold rounded-full text-[10px] tracking-wider uppercase border border-zinc-700">
                            <?php echo htmlspecialchars($route['type_label']); ?>
                        </span>
                        <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900">
                            <?php echo htmlspecialchars($route['title']); ?>
                        </h2>
                        <p class="text-zinc-400 text-sm leading-relaxed">
                            <?php echo htmlspecialchars($route['description']); ?>
                        </p>
                        <div class="bg-darkCard p-5 rounded-xl border border-zinc-800 dark:bg-darkCard dark:border-zinc-800 light:bg-white light:border-zinc-200 light:shadow-sm">
                            <h4 class="text-white dark:text-white light:text-zinc-900 font-bold text-sm mb-3">Detail Rute</h4>
                            <ul class="space-y-2 text-xs text-zinc-400">
                                <li class="flex items-start space-x-2"><i class="fa-solid fa-location-arrow text-goldAccent mt-0.5"></i> <span><strong>Jarak Tempuh:</strong> <?php echo htmlspecialchars($route['distance']); ?></span></li>
                                <li class="flex items-start space-x-2"><i class="fa-solid fa-clock text-goldAccent mt-0.5"></i> <span><strong>Durasi:</strong> <?php echo htmlspecialchars($route['duration']); ?></span></li>
                                <li class="flex items-start space-x-2"><i class="fa-solid fa-car text-goldAccent mt-0.5"></i> <span><strong>Rekomendasi Armada:</strong> <?php echo htmlspecialchars($route['recommended_car']); ?></span></li>
                            </ul>
                        </div>
                        <div class="pt-2">
                            <a href="catalog.php?fuel=<?php echo htmlspecialchars($route['fuel_type_link']); ?>" class="py-2.5 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-bold rounded-xl text-xs transition-all duration-200 inline-flex items-center shadow-md">
                                Cari Armada <i class="fa-solid fa-arrow-right ml-2 text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    <div class="<?php echo $is_even ? 'order-1 lg:order-1' : 'order-1 lg:order-2'; ?>">
                        <div class="relative h-72 sm:h-96 rounded-3xl overflow-hidden shadow-2xl">
                            <img src="<?php echo (strpos($route['image_url'], 'http') === 0) ? htmlspecialchars($route['image_url']) : htmlspecialchars($route['image_url']); ?>" alt="<?php echo htmlspecialchars($route['title']); ?>" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Map Placeholder Section -->
    <div class="mt-20">
        <div class="premium-glass p-8 rounded-3xl border border-zinc-800 dark:border-zinc-800 light:bg-white light:border-zinc-200">
            <h3 class="text-xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 text-center">Titik Penjemputan Populer & Destinasi</h3>
            <div id="map" class="h-96 w-full rounded-2xl z-0"></div>
            <p class="text-center text-xs text-zinc-500 mt-4">* Peta interaktif menunjukkan titik garasi dan rekomendasi destinasi wisata Anda</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Pass routes from PHP to JavaScript securely for Leaflet Markers
    const routesData = <?php echo json_encode($routes); ?>;

    if (typeof L !== 'undefined') {
        const coords = [-7.7956, 110.3695]; // Center Yogyakarta
        const isLight = document.documentElement.classList.contains('light');
        const tileUrl = isLight 
            ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png' 
            : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        
        window.rentalMap = L.map('map').setView(coords, 10);
        
        window.tileLayer = L.tileLayer(tileUrl, {
            attribution: '&copy; CartoDB & Contributors',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(window.rentalMap);

        const goldIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class='w-6 h-6 rounded-full bg-[#D4AF37] border-2 border-white flex items-center justify-center shadow-lg shadow-[#D4AF37]/40'><div class='w-1.5 h-1.5 rounded-full bg-black'></div></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const redIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class='w-6 h-6 rounded-full bg-red-500 border-2 border-white flex items-center justify-center shadow-lg'><div class='w-1.5 h-1.5 rounded-full bg-black'></div></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        // Add Garasi DriveEase
        L.marker([-7.7596, 110.3695], {icon: redIcon}).addTo(window.rentalMap).bindPopup('<b>DriveEase HQ Sleman</b>');
        L.marker([-7.8943, 110.0537], {icon: redIcon}).addTo(window.rentalMap).bindPopup('<b>Bandara YIA Drop Point</b>');

        // Dynamically add route markers
        routesData.forEach(route => {
            if (route.lat && route.lng) {
                L.marker([route.lat, route.lng], {icon: goldIcon})
                 .addTo(window.rentalMap)
                 .bindPopup('<b>' + route.title + '</b><br><span style="font-size:10px;">' + route.type_label + '</span>');
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
