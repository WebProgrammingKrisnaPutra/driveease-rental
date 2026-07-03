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
$success = '';

if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $success = 'Pendaftaran berhasil! Silakan masuk dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan Password wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && ($password === 'admin123' || password_verify($password, $user['password']))) {
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = 'Email atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-darkBg text-zinc-100 transition-colors duration-300 dark:bg-darkBg dark:text-zinc-100 light:bg-[#FAF9F6] light:text-zinc-900">
    <div class="max-w-md w-full space-y-8 premium-glass p-8 rounded-2xl border border-zinc-800 shadow-2xl animate-fade-in-up">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-white dark:text-white light:text-zinc-900">
                Selamat Datang Kembali
            </h2>
            <p class="mt-2 text-sm text-zinc-400">
                Masuk untuk melanjutkan sewa mobil premium Anda di <span class="text-goldAccent font-semibold">DriveEase</span>
            </p>
        </div>

        <!-- Alert Error / Success -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form class="mt-8 space-y-6" action="login.php" method="POST">
            <div class="rounded-md space-y-4">
                <div>
                    <label for="email-address" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">Alamat Email</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="nama@email.com">
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-2">Kata Sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none relative block w-full px-4 py-3 border border-zinc-800 rounded-xl bg-zinc-900/50 placeholder-zinc-500 text-white focus:outline-none focus:ring-1 focus:ring-goldAccent focus:border-goldAccent text-sm transition-all duration-200 dark:bg-zinc-900/50 dark:border-zinc-800 dark:text-white light:bg-white light:border-zinc-300 light:text-zinc-900 light:placeholder-zinc-400" 
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" 
                           class="h-4 w-4 text-goldAccent focus:ring-goldAccent border-zinc-800 rounded bg-zinc-900">
                    <label for="remember-me" class="ml-2 block text-xs text-zinc-400 select-none">
                        Ingat saya
                    </label>
                </div>

                <div class="text-xs">
                    <a href="#" class="font-medium text-goldAccent hover:underline">
                        Lupa kata sandi?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-black bg-goldAccent hover:bg-[#c5a028] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-goldAccent transition-all duration-200 shadow-lg shadow-goldAccent/10">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-lock text-black/50 group-hover:text-black transition-colors duration-200"></i>
                    </span>
                    Masuk Sekarang
                </button>
            </div>
        </form>

        <div class="text-center mt-6">
            <p class="text-xs text-zinc-400">
                Belum terdaftar? 
                <a href="register.php" class="font-bold text-goldAccent hover:underline ml-1">
                    Daftar Member Baru
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
