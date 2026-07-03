<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Route Protection
require_admin();

$success_msg = '';
$error_msg = '';

// 1. DELETE ROUTE ACTION
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success_msg = "Rute wisata berhasil dihapus dari sistem!";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus rute: " . $e->getMessage();
        }
    }
}

// 2. CREATE / UPDATE ROUTE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_route'])) {
    $route_id = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT); // Present if updating
    $title = trim($_POST['title'] ?? '');
    $type_label = trim($_POST['type_label'] ?? '');
    $distance = trim($_POST['distance'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $recommended_car = trim($_POST['recommended_car'] ?? '');
    $fuel_type_link = $_POST['fuel_type_link'] ?? 'all';
    $lat = filter_input(INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT);
    $lng = filter_input(INPUT_POST, 'lng', FILTER_VALIDATE_FLOAT);
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? ''); // Direct image URL for simplicity or upload
    
    // For simplicity, we use image URL input, if user uploads, handle it
    $image_path = $image_url;
    
    // Handle Image Upload if provided
    if (isset($_FILES['route_image']) && $_FILES['route_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/uploads/routes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_tmp = $_FILES['route_image']['tmp_name'];
        $file_name = basename($_FILES['route_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            $new_file_name = 'route_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $image_path = 'assets/uploads/routes/' . $new_file_name;
            }
        } else {
            $error_msg = "Format gambar harus JPG, JPEG, PNG, atau WEBP!";
        }
    } else {
        // If updating and no new file, keep existing URL
        $image_path = $_POST['existing_image'] ?? $image_path;
    }

    if (empty($title) || empty($type_label) || empty($description)) {
        $error_msg = "Semua kolom wajib diisi!";
    } else {
        try {
            if (empty($error_msg)) {
                if ($route_id) {
                    // Update
                    $stmt = $pdo->prepare("
                        UPDATE routes 
                        SET title = ?, type_label = ?, description = ?, distance = ?, duration = ?, recommended_car = ?, fuel_type_link = ?, image_url = ?, lat = ?, lng = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $type_label, $description, $distance, $duration, $recommended_car, $fuel_type_link, $image_path, $lat, $lng, $route_id
                    ]);
                    $success_msg = "Rute berhasil diperbarui!";
                } else {
                    // Create
                    $stmt = $pdo->prepare("
                        INSERT INTO routes (title, type_label, description, distance, duration, recommended_car, fuel_type_link, image_url, lat, lng) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $title, $type_label, $description, $distance, $duration, $recommended_car, $fuel_type_link, $image_path, $lat, $lng
                    ]);
                    $success_msg = "Rute wisata baru berhasil ditambahkan!";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}

// 3. GET ROUTE BY ID FOR EDIT POPULATION
$edit_route = null;
if (isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM routes WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_route = $stmt->fetch();
        } catch (PDOException $e) {
            $error_msg = "Gagal memuat detail rute: " . $e->getMessage();
        }
    }
}

// 4. FETCH ALL ROUTES
try {
    $routes = $pdo->query("SELECT * FROM routes ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval failed: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar Navigation -->
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
                    <a href="routes.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl bg-goldAccent text-black font-extrabold shadow-md transition-all duration-200">
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

        <!-- Main Workspace -->
        <div class="lg:col-span-9 space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Manajemen Rute Wisata</h1>
                    <p class="text-zinc-400 text-xs mt-1">Tambah, edit rute rekomendasi dan koordinat destinasi</p>
                </div>
                <button onclick="toggleForm()" class="self-start py-3 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah Rute Baru
                </button>
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

            <!-- CRUD Form -->
            <div id="route-form-section" class="<?php echo ($edit_route) ? '' : 'hidden'; ?> premium-glass p-8 rounded-2xl border border-zinc-800 space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h2 class="text-lg font-bold text-white"><?php echo ($edit_route) ? 'Edit Rute: ' . htmlspecialchars($edit_route['title']) : 'Tambah Rute Baru'; ?></h2>
                    <button onclick="closeForm()" class="text-zinc-500 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form action="routes.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <input type="hidden" name="save_route" value="1">
                    <?php if ($edit_route): ?>
                        <input type="hidden" name="route_id" value="<?php echo $edit_route['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_route['image_url']); ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Judul Rute</label>
                        <input type="text" name="title" required placeholder="Contoh: Ekspedisi Merbabu" 
                               value="<?php echo $edit_route ? htmlspecialchars($edit_route['title']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Kategori / Tema Wisata</label>
                        <input type="text" name="type_label" required placeholder="Contoh: Gunung & Alam" 
                               value="<?php echo $edit_route ? htmlspecialchars($edit_route['type_label']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Jarak Tempuh</label>
                        <input type="text" name="distance" required placeholder="Contoh: ± 45 KM dari Pusat Kota" 
                               value="<?php echo $edit_route ? htmlspecialchars($edit_route['distance']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Estimasi Durasi</label>
                        <input type="text" name="duration" required placeholder="Contoh: 2 - 3 Jam" 
                               value="<?php echo $edit_route ? htmlspecialchars($edit_route['duration']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Rekomendasi Armada</label>
                        <input type="text" name="recommended_car" required placeholder="Contoh: SUV Diesel" 
                               value="<?php echo $edit_route ? htmlspecialchars($edit_route['recommended_car']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Filter Kendaraan Terkait</label>
                        <select name="fuel_type_link" required 
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                            <option value="diesel" <?php echo ($edit_route && $edit_route['fuel_type_link'] === 'diesel') ? 'selected' : ''; ?>>Mobil Diesel (SUV/MPV)</option>
                            <option value="bensin" <?php echo ($edit_route && $edit_route['fuel_type_link'] === 'bensin') ? 'selected' : ''; ?>>Mobil Bensin (City Car/Sedan)</option>
                            <option value="hybrid" <?php echo ($edit_route && $edit_route['fuel_type_link'] === 'hybrid') ? 'selected' : ''; ?>>Mobil Hybrid / EV</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Latitude (Peta)</label>
                            <input type="number" step="any" name="lat" required placeholder="-7.7956" 
                                   value="<?php echo $edit_route ? htmlspecialchars($edit_route['lat']) : ''; ?>"
                                   class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        </div>
                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Longitude (Peta)</label>
                            <input type="number" step="any" name="lng" required placeholder="110.3695" 
                                   value="<?php echo $edit_route ? htmlspecialchars($edit_route['lng']) : ''; ?>"
                                   class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-zinc-400 font-semibold mb-1.5">Gambar / Foto Destinasi</label>
                        <div class="flex space-x-2">
                            <input type="text" name="image_url" placeholder="URL Gambar (atau upload di sebelah)" 
                                   value="<?php echo $edit_route ? htmlspecialchars($edit_route['image_url']) : ''; ?>"
                                   class="w-1/2 px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                            <input type="file" name="route_image" 
                                   class="w-1/2 px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-xl text-zinc-400 focus:outline-none file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-goldAccent file:text-black">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-zinc-400 font-semibold mb-1.5">Deskripsi Lengkap Rute</label>
                        <textarea name="description" rows="4" required placeholder="Tuliskan pengalaman rute..."
                                  class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent"><?php echo $edit_route ? htmlspecialchars($edit_route['description']) : ''; ?></textarea>
                    </div>

                    <div class="sm:col-span-2 flex justify-end gap-3 pt-3 border-t border-zinc-800">
                        <button type="button" onclick="closeForm()" class="py-2.5 px-6 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-white rounded-lg font-semibold">Batal</button>
                        <button type="submit" class="py-2.5 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-lg shadow-md">Simpan Rute</button>
                    </div>
                </form>
            </div>

            <!-- Routes Table -->
            <div class="premium-glass rounded-2xl border border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-zinc-400">
                        <thead class="bg-zinc-900/50 text-[10px] font-bold uppercase text-zinc-400 border-b border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-100 light:border-zinc-200">
                            <tr>
                                <th class="py-4 px-6">Destinasi</th>
                                <th class="py-4 px-6">Tipe Rute</th>
                                <th class="py-4 px-6">Jarak & Waktu</th>
                                <th class="py-4 px-6">Armada Rekomendasi</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-850 dark:divide-zinc-850 light:divide-zinc-200">
                            <?php if (empty($routes)): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-500">Belum ada rute terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($routes as $route): ?>
                                    <tr class="hover:bg-zinc-900/10 transition-colors duration-150">
                                        <td class="py-4 px-6 flex items-center space-x-3">
                                            <img src="<?php echo (strpos($route['image_url'], 'http') === 0) ? htmlspecialchars($route['image_url']) : '../' . htmlspecialchars($route['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($route['title']); ?>" 
                                                 class="w-12 h-9 object-cover rounded border border-zinc-850">
                                            <span class="font-bold text-white dark:text-white light:text-zinc-900"><?php echo htmlspecialchars($route['title']); ?></span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-2 py-1 bg-zinc-800 border border-zinc-700 text-goldAccent rounded-lg text-[10px]">
                                                <?php echo htmlspecialchars($route['type_label']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div><?php echo htmlspecialchars($route['distance']); ?></div>
                                            <div class="text-[10px] text-zinc-500"><?php echo htmlspecialchars($route['duration']); ?></div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <?php echo htmlspecialchars($route['recommended_car']); ?>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="routes.php?edit_id=<?php echo $route['id']; ?>#route-form-section" 
                                                   class="p-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded border border-zinc-750" 
                                                   title="Edit Rute">
                                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                                </a>
                                                <a href="routes.php?delete_id=<?php echo $route['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus rute wisata ini?')" 
                                                   class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded border border-red-500/20" 
                                                   title="Hapus Rute">
                                                    <i class="fa-solid fa-trash-can text-[10px]"></i>
                                                </a>
                                            </div>
                                        </td>
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

<script>
    function toggleForm() {
        const formSection = document.getElementById('route-form-section');
        formSection.classList.toggle('hidden');
    }
    
    function closeForm() {
        const formSection = document.getElementById('route-form-section');
        formSection.classList.add('hidden');
        if (window.location.search.includes('edit_id')) {
            window.location.href = 'routes.php';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
