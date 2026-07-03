<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Route Protection
require_admin();

$success_msg = '';
$error_msg = '';

// 1. DELETE CAR ACTION
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$delete_id]);
            $success_msg = "Mobil berhasil dihapus dari sistem!";
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus mobil: " . $e->getMessage();
        }
    }
}

// 2. CREATE / UPDATE CAR ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_car'])) {
    $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT); // Present if updating
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $fuel_type = $_POST['fuel_type'] ?? '';
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
    $transmission = $_POST['transmission'] ?? '';
    $luggage = filter_input(INPUT_POST, 'luggage', FILTER_VALIDATE_INT);
    $price_per_day = filter_input(INPUT_POST, 'price_per_day', FILTER_VALIDATE_FLOAT);
    $status = $_POST['status'] ?? 'available';
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name) || empty($brand) || empty($fuel_type) || !$capacity || empty($transmission) || !$price_per_day) {
        $error_msg = "Semua kolom spesifikasi wajib diisi!";
    } else {
        try {
            // Handle Image Upload
            $upload_dir = __DIR__ . '/../assets/uploads/cars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image_path = $_POST['existing_image'] ?? '';
            
            if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['car_image']['tmp_name'];
                $file_name = basename($_FILES['car_image']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($file_ext, $allowed)) {
                    $new_file_name = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                        $image_path = 'assets/uploads/cars/' . $new_file_name;
                    }
                } else {
                    $error_msg = "Format gambar harus JPG, JPEG, atau PNG!";
                }
            }
            
            if (empty($image_path) && empty($error_msg)) {
                $error_msg = "Gambar mobil wajib diunggah untuk mobil baru!";
            }
            
            // Database operation
            if (empty($error_msg)) {
                if ($car_id) {
                    // Update
                    $stmt = $pdo->prepare("
                        UPDATE cars 
                        SET name = ?, brand = ?, fuel_type = ?, capacity = ?, transmission = ?, luggage = ?, price_per_day = ?, image_url = ?, status = ?, description = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $brand, $fuel_type, $capacity, $transmission, $luggage, $price_per_day, $image_path, $status, $description, $car_id
                    ]);
                    $success_msg = "Spesifikasi mobil berhasil diperbarui!";
                } else {
                    // Create
                    $stmt = $pdo->prepare("
                        INSERT INTO cars (name, brand, fuel_type, capacity, transmission, luggage, price_per_day, image_url, status, description) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $name, $brand, $fuel_type, $capacity, $transmission, $luggage, $price_per_day, $image_path, $status, $description
                    ]);
                    $success_msg = "Mobil baru berhasil ditambahkan!";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan database: " . $e->getMessage();
        }
    }
}

// 3. GET CAR BY ID FOR EDIT POPULATION
$edit_car = null;
if (isset($_GET['edit_id'])) {
    $edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    if ($edit_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_car = $stmt->fetch();
        } catch (PDOException $e) {
            $error_msg = "Gagal memuat detail mobil: " . $e->getMessage();
        }
    }
}

// 4. FETCH ALL CARS
try {
    $cars = $pdo->query("SELECT * FROM cars ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    die("Database retrieval failed: " . $e->getMessage());
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
                    <a href="cars.php" class="flex items-center space-x-3 py-3 px-4 rounded-xl bg-goldAccent text-black font-extrabold shadow-md transition-all duration-200">
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
        <div class="lg:col-span-9 space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-white dark:text-white light:text-zinc-950">Manajemen Armada Mobil</h1>
                    <p class="text-zinc-400 text-xs mt-1">Tambah, edit, dan hapus ketersediaan mobil premium DriveEase</p>
                </div>
                <button onclick="toggleForm()" class="self-start py-3 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-xl text-xs transition-all duration-200 shadow-md">
                    <i class="fa-solid fa-plus mr-1.5"></i> Tambah Mobil Baru
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

            <!-- CRUD Car Input/Edit Form (Collapsible/Hidden by default, visible if editing) -->
            <div id="car-form-section" class="<?php echo ($edit_car) ? '' : 'hidden'; ?> premium-glass p-8 rounded-2xl border border-zinc-800 space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                    <h2 class="text-lg font-bold text-white"><?php echo ($edit_car) ? 'Edit Mobil: ' . htmlspecialchars($edit_car['name']) : 'Tambah Mobil Baru'; ?></h2>
                    <button onclick="closeForm()" class="text-zinc-500 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>

                <form action="cars.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                    <input type="hidden" name="save_car" value="1">
                    <?php if ($edit_car): ?>
                        <input type="hidden" name="car_id" value="<?php echo $edit_car['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_car['image_url']); ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Nama Model Mobil</label>
                        <input type="text" name="name" required placeholder="Contoh: Civic RS Turbo" 
                               value="<?php echo $edit_car ? htmlspecialchars($edit_car['name']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Brand / Merk</label>
                        <input type="text" name="brand" required placeholder="Contoh: Honda" 
                               value="<?php echo $edit_car ? htmlspecialchars($edit_car['brand']) : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Tipe Bahan Bakar</label>
                        <select name="fuel_type" required 
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                            <option value="diesel" <?php echo ($edit_car && $edit_car['fuel_type'] === 'diesel') ? 'selected' : ''; ?>>Diesel (SUV / MPV)</option>
                            <option value="bensin" <?php echo ($edit_car && $edit_car['fuel_type'] === 'bensin') ? 'selected' : ''; ?>>Bensin (Gasoline)</option>
                            <option value="hybrid" <?php echo ($edit_car && $edit_car['fuel_type'] === 'hybrid') ? 'selected' : ''; ?>>Hybrid / Electric (EV)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Tipe Transmisi</label>
                        <select name="transmission" required 
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                            <option value="automatic" <?php echo ($edit_car && $edit_car['transmission'] === 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                            <option value="manual" <?php echo ($edit_car && $edit_car['transmission'] === 'manual') ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Kapasitas Penumpang</label>
                            <input type="number" name="capacity" required placeholder="Kursi" 
                                   value="<?php echo $edit_car ? $edit_car['capacity'] : '5'; ?>"
                                   class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        </div>
                        <div>
                            <label class="block text-zinc-400 font-semibold mb-1.5">Kapasitas Bagasi (L)</label>
                            <input type="number" name="luggage" required placeholder="Tas/Liter" 
                                   value="<?php echo $edit_car ? $edit_car['luggage'] : '300'; ?>"
                                   class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Harga Sewa / Hari (IDR)</label>
                        <input type="number" step="any" name="price_per_day" required placeholder="Rp per Hari" 
                               value="<?php echo $edit_car ? $edit_car['price_per_day'] : ''; ?>"
                               class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Status Ketersediaan</label>
                        <select name="status" required 
                                class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent">
                            <option value="available" <?php echo ($edit_car && $edit_car['status'] === 'available') ? 'selected' : ''; ?>>Tersedia (Available)</option>
                            <option value="rented" <?php echo ($edit_car && $edit_car['status'] === 'rented') ? 'selected' : ''; ?>>Sedang Disewa (Rented)</option>
                            <option value="maintenance" <?php echo ($edit_car && $edit_car['status'] === 'maintenance') ? 'selected' : ''; ?>>Perawatan (Maintenance)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-zinc-400 font-semibold mb-1.5">Foto Mobil</label>
                        <input type="file" name="car_image" 
                               class="w-full px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-xl text-zinc-400 focus:outline-none file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-goldAccent file:text-black">
                        <?php if ($edit_car): ?>
                            <p class="text-[10px] text-zinc-500 mt-2">Biarkan kosong jika tidak ingin mengubah gambar. <a href="../<?php echo $edit_car['image_url']; ?>" target="_blank" class="underline text-goldAccent">Lihat gambar saat ini</a></p>
                        <?php endif; ?>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-zinc-400 font-semibold mb-1.5">Deskripsi Mobil</label>
                        <textarea name="description" rows="3" placeholder="Masukkan spesifikasi tambahan atau deskripsi kemewahan mobil..."
                                  class="w-full px-4 py-3 bg-zinc-900 border border-zinc-800 rounded-xl text-white focus:outline-none focus:ring-1 focus:ring-goldAccent"><?php echo $edit_car ? htmlspecialchars($edit_car['description']) : ''; ?></textarea>
                    </div>

                    <div class="sm:col-span-2 flex justify-end gap-3 pt-3 border-t border-zinc-800">
                        <button type="button" onclick="closeForm()" class="py-2.5 px-6 bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-white rounded-lg font-semibold">Batal</button>
                        <button type="submit" class="py-2.5 px-6 bg-goldAccent hover:bg-[#c5a028] text-black font-extrabold rounded-lg shadow-md">Simpan Mobil</button>
                    </div>
                </form>
            </div>

            <!-- Cars Table -->
            <div class="premium-glass rounded-2xl border border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs text-zinc-400">
                        <thead class="bg-zinc-900/50 text-[10px] font-bold uppercase text-zinc-400 border-b border-zinc-850 dark:bg-zinc-900/50 dark:border-zinc-850 light:bg-zinc-100 light:border-zinc-200">
                            <tr>
                                <th class="py-4 px-6">Model Kendaraan</th>
                                <th class="py-4 px-6">Brand</th>
                                <th class="py-4 px-6">Bahan Bakar</th>
                                <th class="py-4 px-6">Transmisi</th>
                                <th class="py-4 px-6 text-center">Spek (Kursi/Tas)</th>
                                <th class="py-4 px-6 text-right">Tarif per Hari</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-850 dark:divide-zinc-850 light:divide-zinc-200">
                            <?php if (empty($cars)): ?>
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-zinc-500">Belum ada mobil terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cars as $car): ?>
                                    <tr class="hover:bg-zinc-900/10 transition-colors duration-150">
                                        <!-- Model & Image -->
                                        <td class="py-4 px-6 flex items-center space-x-3">
                                            <img src="<?php echo (strpos($car['image_url'], 'http') === 0) ? htmlspecialchars($car['image_url']) : '../' . htmlspecialchars($car['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($car['name']); ?>" 
                                                 class="w-12 h-9 object-cover rounded border border-zinc-850">
                                            <span class="font-bold text-white dark:text-white light:text-zinc-900"><?php echo htmlspecialchars($car['name']); ?></span>
                                        </td>
                                        
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($car['brand']); ?></td>
                                        
                                        <td class="py-4 px-6 capitalize"><?php echo htmlspecialchars($car['fuel_type']); ?></td>
                                        
                                        <td class="py-4 px-6 capitalize"><?php echo htmlspecialchars($car['transmission']); ?></td>
                                        
                                        <td class="py-4 px-6 text-center"><?php echo $car['capacity']; ?> Kursi / <?php echo $car['luggage']; ?>L</td>
                                        
                                        <td class="py-4 px-6 text-right font-semibold text-white dark:text-white light:text-zinc-900">
                                            Rp <?php echo number_format($car['price_per_day'], 0, ',', '.'); ?>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="py-4 px-6 text-center">
                                            <?php if ($car['status'] === 'available'): ?>
                                                <span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[9px] font-bold uppercase rounded">Tersedia</span>
                                            <?php elseif ($car['status'] === 'rented'): ?>
                                                <span class="px-2 py-0.5 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[9px] font-bold uppercase rounded">Disewa</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 bg-red-500/10 border border-red-500/20 text-red-400 text-[9px] font-bold uppercase rounded">Servis</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="cars.php?edit_id=<?php echo $car['id']; ?>#car-form-section" 
                                                   class="p-1.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 rounded border border-zinc-750" 
                                                   title="Edit Mobil">
                                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                                </a>
                                                <a href="cars.php?delete_id=<?php echo $car['id']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus mobil ini dari garasi?')" 
                                                   class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded border border-red-500/20" 
                                                   title="Hapus Mobil">
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
        const formSection = document.getElementById('car-form-section');
        formSection.classList.toggle('hidden');
    }
    
    function closeForm() {
        const formSection = document.getElementById('car-form-section');
        formSection.classList.add('hidden');
        // Clear query parameters to reset edit state
        if (window.location.search.includes('edit_id')) {
            window.location.href = 'cars.php';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
