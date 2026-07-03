<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

// If already logged in, redirect
if (is_logged_in()) {
    if (get_user_role() === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter!';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Alamat email sudah terdaftar!';
            } else {
                // Insert new user as 'customer'
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'customer', ?)");
                $insert_stmt->execute([$name, $email, $hashed_password, $phone]);

                header("Location: login.php?registered=true");
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Gagal mendaftar: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-darkBg text-zinc-100 transition-colors duration-300 dark:bg-darkBg dark:text-zinc-100 light:bg-[#FAF9F6] light:text-zinc-900">
    <div class="max-w-md w-full space-y-8 premium-glass p-8 rounded-2xl border border-zinc-800 shadow-2xl animate-fade-in-up">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-white dark:text-white light:text-zinc-900">
                Gabung DriveEase
            </h2>
            <p class="mt-2 text-sm text-zinc-400">
                Dapatkan akses ke kendaraan eksklusif dan mulailah perjalanan mewah Anda hari ini.
            </p>
        </div>

        <!-- Alert Error -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form class="mt-8 space-y-5" action="register.php" method="POST">
            <div class="rounded-md space-y-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="Masukkan nama lengkap Anda"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div>
                    <label for="email-address" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Alamat Email</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="nama@email.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Nomor Handphone</label>
                    <input id="phone" name="phone" type="tel" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="Contoh: 0812345678"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="Minimal 6 karakter">
                </div>
                <div>
                    <label for="confirm_password" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Konfirmasi Kata Sandi</label>
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="Ulangi kata sandi baru Anda">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-black bg-goldAccent hover:bg-[#c5a028] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-goldAccent transition-all duration-200 shadow-lg shadow-goldAccent/10">
                    Daftar Akun Baru
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <p class="text-xs text-zinc-400">
                Sudah memiliki akun? 
                <a href="login.php" class="font-bold text-goldAccent hover:underline ml-1">
                    Masuk di sini
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
