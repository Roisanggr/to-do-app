// Wait for DOM and Chart.js to be fully loaded
document.addEventListener('DOMContentLoaded', function () {

    initQuantitySpinner();

    // Initialize all charts
    initCharts();


    // product single page
    var thumb_slider = new Swiper(".product-thumbnail-slider", {
        slidesPerView: 3,
        spaceBetween: 20,
        autoplay: true,
        direction: "vertical",
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    var large_slider = new Swiper(".product-large-slider", {
        slidesPerView: 1,
        autoplay: true,
        spaceBetween: 0,
        effect: 'fade',
        thumbs: {
            swiper: thumb_slider,
        },
    });

    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        });
    }

    // Handle sidebar close button for mobile
    const sidebarClose = document.getElementById('sidebarclose');
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        });
    }

});

// Toggle sidebar
document.getElementById('sidebarToggle').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('hidden');
    document.getElementById('content').classList.toggle('expanded');
});

// Toggle sidebar
document.getElementById('sidebarclose').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('hidden');
    document.getElementById('content').classList.toggle('expanded');
});

// Make sidebar links active when clicked
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', function () {
        document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});



function initCharts() {
    createChart("budgetChart", {
        type: "line",
        data: {
            labels: ["Minggu Ke-1", "Minggu Ke-2", "Minggu Ke-3", "Minggu Ke-4",],
            datasets: [
                {
                    label: "Transport",
                    data: [15000, 10000, 10000, 25000],
                    borderColor: "#88B267",
                    backgroundColor: "#EEF3E9",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: "Makan",
                    data: [15000, 18000, 13000, 17000],
                    borderColor: "#F39C12",
                    backgroundColor: "#F9F5EE",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: "Kuota",
                    data: [27000, 28000, 24000, 29000],
                    borderColor: "#65A1CB",
                    backgroundColor: "#E1F0FA",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: "Lain-lain",
                    data: [27000, 28000, 24000, 29000],
                    borderColor: "#65A1CB",
                    backgroundColor: "#E1F0FA",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } },
            },
        },
    });

    createChart("ageChart", {
        type: "doughnut",
        data: {
            labels: ["18-25", "25-30", "30-40", "40-60", "11-18"],
            datasets: [
                {
                    data: [25, 30, 20, 15, 20],
                    backgroundColor: ["#80BE4D", "#4699D3", "#F2D226", "#BC86E7", "#F2A52B"],
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "15%",
            plugins: { legend: { position: "bottom" } },
        },
    });

    createChart("genderChart", {
        type: "doughnut",
        data: {
            labels: ["Female", "Male"],
            datasets: [
                {
                    data: [65, 35],
                    backgroundColor: ["#F28D6D", "#A4D4EF"],
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "15%",
            plugins: { legend: { position: "bottom" } },
        },
    });

    createChart("discountChart", {
        type: "line",
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
            datasets: [
                {
                    label: "Discount Sales",
                    data: [500, 100, 200, 400, 300, 800],
                    borderColor: "#88B267",
                    backgroundColor: "#EEF3E9",
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } },
            },
        },
    });
}

function createChart(id, config) {
    const ctx = document.getElementById(id);
    if (!ctx) {
        console.error(`Canvas element with id '${id}' not found`);
        return;
    }

    if (ctx.chart) {
        ctx.chart.destroy();
    }

    ctx.chart = new Chart(ctx, config);
}

function initQuantitySpinner() {
    const productQtyElements = document.querySelectorAll('.product-qty');

    productQtyElements.forEach(function (productEl) {
      const quantityInput = productEl.querySelector('.quantity');
      const plusButton = productEl.querySelector('.quantity-right-plus');
      const minusButton = productEl.querySelector('.quantity-left-minus');

      plusButton.addEventListener('click', function (e) {
        e.preventDefault();
        let quantity = parseInt(quantityInput.value) || 0;
        quantityInput.value = quantity + 1;
      });

      minusButton.addEventListener('click', function (e) {
        e.preventDefault();
        let quantity = parseInt(quantityInput.value) || 0;
        if (quantity > 0) {
          quantityInput.value = quantity - 1;
        }
      });
    });
  }
  
  // Fungsi untuk update waktu real-time
function updateRealtimeClock() {
    const now = new Date();
    
    // Format waktu: hh:mm
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const timeString = `${hours}:${minutes}`;
    
    // Format tanggal: day, dd/mm/yyyy
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const dayName = days[now.getDay()];
    
    const day = now.getDate().toString().padStart(2, '0');
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const year = now.getFullYear();
    const dateString = `${dayName}, ${day}/${month}/${year}`;
    
    // Gabungkan format yang diinginkan: hh:mm - day, dd/mm/yyyy
    const formattedDateTime = `${timeString} - ${dateString}`;
    
    // Update elemen dengan ID realtime-clock
    const clockElement = document.getElementById('realtime-clock');
    if (clockElement) {
        clockElement.textContent = formattedDateTime;
    }
}

// Inisialisasi dan update waktu setiap menit
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('realtime-clock')) {
        updateRealtimeClock();
        setInterval(updateRealtimeClock, 60000); // Update setiap menit
    }
});

