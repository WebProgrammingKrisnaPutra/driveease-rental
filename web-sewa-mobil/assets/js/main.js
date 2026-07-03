// DriveEase - Modern Premium Car Rental Script

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initThemeToggle();
    initMobileMenu();
});

// 1. Theme Management (Dark & Light Mode)
function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        document.documentElement.classList.add('light');
        document.documentElement.classList.remove('dark');
    } else {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
        localStorage.setItem('theme', 'dark'); // Default theme
    }
}

function initThemeToggle() {
    const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const isLight = document.documentElement.classList.toggle('light');
            document.documentElement.classList.toggle('dark', !isLight);
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
            
            // Re-render Leaflet Map if initialized to adjust theme
            if (window.rentalMap) {
                updateMapTiles(isLight);
            }
        });
    });
}

// 2. Mobile Menu Navigation
function initMobileMenu() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// 3. Live Price Calculation for Booking
function initBookingCalc(pricePerDay) {
    const startDateInput = document.getElementById('pickup_date');
    const endDateInput = document.getElementById('return_date');
    const driverOpt = document.getElementById('driver_option');
    const insuranceOpt = document.getElementById('insurance_option');

    const durationText = document.getElementById('calc-duration');
    const basePriceText = document.getElementById('calc-base');
    const driverText = document.getElementById('calc-driver');
    const insuranceText = document.getElementById('calc-insurance');
    const taxText = document.getElementById('calc-tax');
    const totalText = document.getElementById('calc-total');
    
    const hiddenDuration = document.getElementById('duration_days');

    function calculate() {
        if (!startDateInput.value || !endDateInput.value) return;

        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);

        if (end <= start) {
            alert('Tanggal pengembalian harus setelah tanggal penjemputan!');
            endDateInput.value = '';
            return;
        }

        // Calculate days
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays <= 0) return;

        // Pricing logic
        const driverFeePerDay = driverOpt && driverOpt.checked ? 150000 : 0;
        const insuranceFeePerDay = insuranceOpt && insuranceOpt.checked ? 50000 : 0;

        const basePrice = pricePerDay * diffDays;
        const driverFee = driverFeePerDay * diffDays;
        const insuranceFee = insuranceFeePerDay * diffDays;
        const tax = (basePrice + driverFee + insuranceFee) * 0.10; // 10% tax
        const total = basePrice + driverFee + insuranceFee + tax;

        // Render UI
        if (durationText) durationText.innerText = `${diffDays} Hari`;
        if (basePriceText) basePriceText.innerText = formatIDR(basePrice);
        if (driverText) driverText.innerText = formatIDR(driverFee);
        if (insuranceText) insuranceText.innerText = formatIDR(insuranceFee);
        if (taxText) taxText.innerText = formatIDR(tax);
        if (totalText) totalText.innerText = formatIDR(total);

        // Update hidden field for submission
        if (hiddenDuration) hiddenDuration.value = diffDays;
    }

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', calculate);
        endDateInput.addEventListener('change', calculate);
        if (driverOpt) driverOpt.addEventListener('change', calculate);
        if (insuranceOpt) insuranceOpt.addEventListener('change', calculate);
    }
}

function formatIDR(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// 4. Dark/Light Theme Leaflet Map Integration
let tileLayer;
function initRentalMap(coords, popupText) {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    // Initialize Map
    window.rentalMap = L.map('map').setView(coords, 14);

    const isLight = document.documentElement.classList.contains('light');
    const tileUrl = isLight 
        ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png' 
        : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';

    tileLayer = L.tileLayer(tileUrl, {
        attribution: '&copy; CartoDB & Contributors',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(window.rentalMap);

    // Custom Gold Pin Icon
    const goldIcon = L.divIcon({
        className: 'custom-div-icon',
        html: `<div class='w-8 h-8 rounded-full bg-[#D4AF37] border-2 border-white flex items-center justify-center shadow-lg shadow-[#D4AF37]/40 animate-pulse'>
                 <div class='w-2 h-2 rounded-full bg-black'></div>
               </div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    L.marker(coords, { icon: goldIcon }).addTo(window.rentalMap)
        .bindPopup(`<strong class="text-xs font-bold font-sans">${popupText}</strong>`, { closeButton: false })
        .openPopup();
}

function updateMapTiles(isLight) {
    if (!tileLayer) return;
    
    const tileUrl = isLight 
        ? 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png' 
        : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
    
    tileLayer.setUrl(tileUrl);
}
