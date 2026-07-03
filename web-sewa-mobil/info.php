<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/header.php';

$active_tab = $_GET['tab'] ?? 'terms';
$valid_tabs = ['terms', 'privacy', 'faq', 'location', 'contact'];
if (!in_array($active_tab, $valid_tabs)) {
    $active_tab = 'terms';
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col md:flex-row gap-8">
    
    <!-- Sidebar Navigation -->
    <div class="w-full md:w-64 flex-shrink-0">
        <div class="sticky top-28 bg-darkCard border border-zinc-800 rounded-2xl p-4 dark:bg-darkCard dark:border-zinc-800 light:bg-white light:border-zinc-200 shadow-sm">
            <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-4 px-3">Pusat Informasi</h3>
            <ul class="space-y-1">
                <li>
                    <a href="?tab=terms" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?php echo $active_tab === 'terms' ? 'bg-goldAccent/10 text-goldAccent font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50'; ?>">
                        <i class="fa-solid fa-file-contract w-6"></i> Syarat & Ketentuan
                    </a>
                </li>
                <li>
                    <a href="?tab=privacy" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?php echo $active_tab === 'privacy' ? 'bg-goldAccent/10 text-goldAccent font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50'; ?>">
                        <i class="fa-solid fa-shield-halved w-6"></i> Kebijakan Privasi
                    </a>
                </li>
                <li>
                    <a href="?tab=faq" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?php echo $active_tab === 'faq' ? 'bg-goldAccent/10 text-goldAccent font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50'; ?>">
                        <i class="fa-solid fa-circle-question w-6"></i> FAQ & Bantuan
                    </a>
                </li>
                <li>
                    <a href="?tab=location" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?php echo $active_tab === 'location' ? 'bg-goldAccent/10 text-goldAccent font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50'; ?>">
                        <i class="fa-solid fa-map-location-dot w-6"></i> Peta Garasi
                    </a>
                </li>
                <li>
                    <a href="?tab=contact" class="flex items-center px-3 py-2.5 rounded-xl text-sm transition-all duration-200 <?php echo $active_tab === 'contact' ? 'bg-goldAccent/10 text-goldAccent font-bold' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/50'; ?>">
                        <i class="fa-solid fa-headset w-6"></i> Hubungi CS
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-grow">
        <div class="premium-glass p-8 rounded-3xl border border-zinc-800 dark:border-zinc-800 light:bg-white light:border-zinc-200 min-h-[500px]">
            
            <?php if ($active_tab === 'terms'): ?>
                <div class="animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 border-b border-zinc-800 pb-4">Syarat & Ketentuan Sewa</h2>
                    <div class="space-y-6 text-sm text-zinc-400 leading-relaxed">
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">1. Persyaratan Umum</h3>
                            <p>Penyewa diwajibkan memiliki Kartu Tanda Penduduk (KTP) yang sah dan Surat Izin Mengemudi (SIM A) yang masih berlaku untuk layanan sewa lepas kunci. Semua dokumen harus diunggah di dashboard saat melakukan verifikasi pesanan.</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">2. Durasi Sewa dan Overtime</h3>
                            <p>Sistem sewa dihitung per hari (24 Jam). Keterlambatan pengembalian (overtime) akan dikenakan biaya sebesar 10% dari tarif sewa harian per jam keterlambatan. Maksimal overtime adalah 6 jam, lewat dari itu akan dihitung sebagai tambahan 1 hari penuh.</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">3. Asuransi dan Kerusakan</h3>
                            <p>Kami menyediakan opsi asuransi all-risk. Jika penyewa tidak memilih opsi asuransi, maka segala bentuk kerusakan, lecet, atau kehilangan kendaraan selama masa sewa menjadi tanggung jawab penuh penyewa sesuai nilai kerusakan dari bengkel resmi.</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">4. Pembatalan dan Reschedule</h3>
                            <p>Pembatalan yang dilakukan selambat-lambatnya 24 jam sebelum waktu penjemputan akan mendapatkan pengembalian dana penuh (dipotong biaya admin bank). Pembatalan kurang dari 24 jam akan dikenakan penalti sebesar 50% dari total tagihan.</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($active_tab === 'privacy'): ?>
                <div class="animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 border-b border-zinc-800 pb-4">Kebijakan Privasi</h2>
                    <div class="space-y-6 text-sm text-zinc-400 leading-relaxed">
                        <p>DriveEase berkomitmen untuk melindungi privasi dan keamanan data pribadi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">Data yang Kami Kumpulkan</h3>
                            <p>Kami hanya mengumpulkan informasi yang diperlukan untuk proses penyewaan: Nama lengkap, alamat email terverifikasi, nomor telepon, password yang dienkripsi (bcrypt), dan scan dokumen identitas (KTP/SIM) yang diperlukan oleh pihak asuransi dan regulasi hukum.</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">Penggunaan Data</h3>
                            <p>Data identitas Anda tidak akan pernah dijual kepada pihak ketiga. Penggunaan data difokuskan hanya pada: verifikasi identitas penyewa, menghubungi Anda terkait pesanan/booking, memproses pembayaran, dan mencegah tindak penipuan/penggelapan kendaraan.</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-base mb-2">Keamanan</h3>
                            <p>Sistem kami dilengkapi pengamanan enkripsi sandi BCRYPT tingkat tinggi. Dokumen fisik yang Anda unggah disimpan dengan aman di server dan hanya dapat diakses oleh admin berwenang untuk tujuan verifikasi pesanan.</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($active_tab === 'faq'): ?>
                <div class="animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 border-b border-zinc-800 pb-4">FAQ & Bantuan</h2>
                    <div class="space-y-4">
                        <!-- FAQ Item -->
                        <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-xl">
                            <h4 class="text-white font-bold text-sm mb-2"><i class="fa-solid fa-q text-goldAccent mr-1"></i> Apakah saya bisa menyewa lepas kunci (tanpa supir)?</h4>
                            <p class="text-zinc-400 text-sm">Tentu saja. Anda bisa menyewa lepas kunci asalkan melampirkan KTP dan SIM A asli di menu Dashboard setelah melakukan booking, serta lolos verifikasi dari tim kami.</p>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-xl">
                            <h4 class="text-white font-bold text-sm mb-2"><i class="fa-solid fa-q text-goldAccent mr-1"></i> Apakah asuransi wajib digunakan?</h4>
                            <p class="text-zinc-400 text-sm">Tidak wajib, namun sangat disarankan demi ketenangan Anda. Tanpa asuransi, semua risiko kerusakan sepenuhnya menjadi tanggungan Anda pribadi.</p>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-xl">
                            <h4 class="text-white font-bold text-sm mb-2"><i class="fa-solid fa-q text-goldAccent mr-1"></i> Metode pembayaran apa saja yang diterima?</h4>
                            <p class="text-zinc-400 text-sm">Kami menerima Transfer Bank (BCA, Mandiri, BNI, BRI), Kartu Kredit, serta dompet digital (GoPay, OVO, Dana). Pembayaran dilakukan di tempat garasi atau bisa ditransfer via e-invoice.</p>
                        </div>
                        <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-xl">
                            <h4 class="text-white font-bold text-sm mb-2"><i class="fa-solid fa-q text-goldAccent mr-1"></i> Bagaimana jika mobil yang saya mau sedang disewa?</h4>
                            <p class="text-zinc-400 text-sm">Anda bisa menghubungi CS kami. Tim DriveEase memiliki rekanan garasi eksklusif lain untuk mencarikan unit pengganti yang setara atau upgrade dengan harga spesial untuk member.</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($active_tab === 'location'): ?>
                <div class="animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 border-b border-zinc-800 pb-4">Peta Garasi & Kantor Pusat</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-white font-semibold text-sm mb-2">DriveEase HQ Sleman</h3>
                            <p class="text-zinc-400 text-xs mb-3">Garasi pusat dengan koleksi lengkap bensin, diesel, dan hybrid. Melayani penjemputan 24 jam.</p>
                            <p class="text-goldAccent text-xs font-bold"><i class="fa-solid fa-location-dot"></i> Jl. Mutiara Raya No. 42, Sleman, Yogyakarta</p>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-sm mb-2">Drop Point Bandara YIA</h3>
                            <p class="text-zinc-400 text-xs mb-3">Layanan pengantaran dan penjemputan unit mobil khusus di area Yogyakarta International Airport.</p>
                            <p class="text-goldAccent text-xs font-bold"><i class="fa-solid fa-plane-arrival"></i> Kulon Progo, D.I. Yogyakarta</p>
                        </div>
                    </div>
                    
                    <div id="map" class="h-80 w-full rounded-2xl z-0 border border-zinc-800"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            if (typeof L !== 'undefined') {
                                window.rentalMap = L.map('map').setView([-7.7596, 110.3695], 11);
                                const isLight = document.documentElement.classList.contains('light');
                                const tileUrl = isLight 
                                    ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png' 
                                    : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
                                
                                window.tileLayer = L.tileLayer(tileUrl, { maxZoom: 20 }).addTo(window.rentalMap);

                                const icon = L.divIcon({
                                    className: 'custom-div-icon',
                                    html: `<div class='w-6 h-6 rounded-full bg-[#D4AF37] border-2 border-white flex items-center justify-center shadow-lg'><div class='w-1.5 h-1.5 rounded-full bg-black'></div></div>`
                                });

                                L.marker([-7.7596, 110.3695], {icon: icon}).addTo(window.rentalMap).bindPopup('<b>DriveEase HQ Sleman</b>').openPopup();
                                L.marker([-7.8943, 110.0537], {icon: icon}).addTo(window.rentalMap).bindPopup('<b>Bandara YIA Drop Point</b>');
                            }
                        });
                    </script>
                </div>

            <?php elseif ($active_tab === 'contact'): ?>
                <div class="animate-fade-in-up">
                    <h2 class="text-2xl font-bold text-white dark:text-white light:text-zinc-900 mb-6 border-b border-zinc-800 pb-4">Hubungi Customer Service</h2>
                    <p class="text-zinc-400 text-sm mb-8">Tim support kami siaga 24/7 untuk melayani semua pertanyaan terkait pemesanan, informasi kendaraan, penjemputan darurat, dan klaim asuransi.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="https://wa.me/6285778297231" target="_blank" class="flex items-center space-x-4 bg-zinc-900 border border-zinc-800 hover:border-goldAccent p-5 rounded-2xl transition-all duration-300 group">
                            <div class="w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-brands fa-whatsapp text-2xl text-green-500"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">WhatsApp</h4>
                                <p class="text-zinc-400 text-xs">+62 857-7829-7231</p>
                            </div>
                        </a>
                        
                        <div class="flex items-center space-x-4 bg-zinc-900 border border-zinc-800 hover:border-goldAccent p-5 rounded-2xl transition-all duration-300 group">
                            <div class="w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-phone text-xl text-blue-500"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Telepon Hotline</h4>
                                <p class="text-zinc-400 text-xs">(0274) 123-4567</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 bg-zinc-900 border border-zinc-800 hover:border-goldAccent p-5 rounded-2xl transition-all duration-300 group">
                            <div class="w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-envelope text-xl text-red-500"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">Email Support</h4>
                                <p class="text-zinc-400 text-xs">support@driveease.com</p>
                            </div>
                        </div>
                        
                        <a href="https://www.instagram.com/krisnarmdhnptr/" class="flex items-center space-x-4 bg-zinc-900 border border-zinc-800 hover:border-goldAccent p-5 rounded-2xl transition-all duration-300 group">
                                <div class="w-12 h-12 bg-purple-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fa-brands fa-instagram text-2xl text-purple-500"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-sm">Instagram</h4>
                                    <p class="text-zinc-400 text-xs">@krisnarmdhnptr</p>
                                </div>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
