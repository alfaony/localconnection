<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Permintaan Barang - Live Tracking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #4361ee;
            --warning: #f59f00;
            --info: #0dcaf0;
            --success: #20c997;
            --danger: #dc3545;
            --light: #f8f9fa;
        }
        
        body {
            font-size: 16px;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .card-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0;
        }
        
        .card-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 8px;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 150px;
            display: inline-block;
            text-align: center;
            text-transform: uppercase;
        }
        
        .request-card {
            border-radius: 12px;
            border-left: 5px solid var(--primary);
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            background: white;
        }
        
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .request-card .card-body {
            padding: 25px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.05rem;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
        }
        
        .info-value {
            color: #212529;
            font-size: 1.1rem;
        }
        
        .countdown-box {
            background: linear-gradient(120deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }
        
        .countdown-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #495057;
        }
        
        .countdown-timer {
            font-size: 2.2rem;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
        }
        
        .btn-refresh {
            background: linear-gradient(120deg, #4361ee, #3a0ca3);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }
        
        .btn-refresh:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background-color: #f8f9fa;
            border-radius: 15px;
            margin-top: 30px;
            border: 2px dashed #dee2e6;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #ced4da;
            margin-bottom: 25px;
            opacity: 0.7;
        }
        
        .empty-state h4 {
            color: #6c757d;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            color: #868e96;
        }
        
        .card-status-requested { border-left-color: var(--primary); }
        .card-status-waiting_payment { border-left-color: var(--warning); }
        .card-status-paid { border-left-color: var(--info); }
        .card-status-done { border-left-color: var(--success); }
        
        .badge-requested { background: linear-gradient(120deg, var(--primary), #3f37c9); }
        .badge-waiting_payment { background: linear-gradient(120deg, var(--warning), #e67700); }
        .badge-paid { background: linear-gradient(120deg, var(--info), #0aa2c0); }
        .badge-done { background: linear-gradient(120deg, var(--success), #1aa179); }
        
        .countdown-expired {
            color: var(--danger);
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        .header-tools {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .stats-card {
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 15px 20px;
            min-width: 180px;
            backdrop-filter: blur(5px);
        }
        
        .stats-value {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .stats-label {
            font-size: 1.05rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .last-updated {
            background: rgba(0,0,0,0.1);
            border-radius: 20px;
            padding: 8px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            margin-top: 20px;
        }
        
        .last-updated i {
            margin-right: 8px;
        }
        
        .request-id {
            position: absolute;
            top: 20px;
            right: 25px;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        @media (max-width: 992px) {
            .header-tools {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-card {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-label {
                margin-bottom: 5px;
            }
            
            .countdown-timer {
                font-size: 1.8rem;
            }
            
            .card-title {
                font-size: 1.5rem;
            }
        }
        
        .footer {
            text-align: center;
            padding: 25px;
            color: #6c757d;
            font-size: 0.95rem;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header-gradient">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="card-title"><i class="fas fa-boxes mr-2"></i> Daftar Permintaan Barang</h1>
                    <p class="card-subtitle">Pelacakan real-time permintaan barang di perusahaan</p>
                </div>
                <div class="header-tools">
                    <div class="stats-card">
                        <div class="stats-value" id="active-requests">0</div>
                        <div class="stats-label">Permintaan Aktif</div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-value" id="expired-requests">0</div>
                        <div class="stats-label">Batas Waktu Habis</div>
                    </div>
                </div>
            </div>
            <div class="last-updated">
                <i class="fas fa-sync-alt"></i>
                <span>Terakhir diperbarui: <span id="last-updated-time">Belum pernah</span></span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <div>
                <h3 class="text-dark mb-0">Permintaan Terkini</h3>
                <p class="text-muted">Daftar semua permintaan barang yang diajukan</p>
            </div>
            <button class="btn-refresh" id="refresh-btn">
                <i class="fas fa-sync-alt mr-2"></i> Segarkan Data
            </button>
        </div>
        
        <div id="item-request-list">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="sr-only">Memuat...</span>
                </div>
                <p class="mt-3 h5">Memuat data permintaan...</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Sistem Pelacakan Permintaan Barang &copy; 2023 | Real-time Tracking</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <script>
        // Fungsi untuk mengubah format tanggal
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Fungsi untuk menghitung sisa waktu
        function getTimeRemaining(createdAt) {   
            const CLOSED_TIME = "{{ $settingCompany['closed_time'] ?? '16:00' }}";


            const now = new Date();
            const created = new Date(createdAt);
            const deadline = new Date(created);
            
            const [closedHour, closedMinute] = (typeof CLOSED_TIME !== 'undefined' ? CLOSED_TIME : '16:00').split(':').map(Number);
            
            deadline.setHours(closedHour, closedMinute || 0, 0, 0);
            
            const isSameDay = now.toDateString() === created.toDateString();
            const remaining = deadline - now;
            
            if (!isSameDay || remaining <= 0) {
                return { expired: true, countdown: "00:00:00" };
            }
            
            const hours = String(Math.floor((remaining / (1000 * 60 * 60)) % 24)).padStart(2, '0');
            const minutes = String(Math.floor((remaining / (1000 * 60)) % 60)).padStart(2, '0');
            const seconds = String(Math.floor((remaining / 1000) % 60)).padStart(2, '0');
            
            return {
                expired: false,
                countdown: `${hours}:${minutes}:${seconds}`
            };
        }
        
        // Fungsi untuk merender data permintaan
        function renderItemRequests(data) {
            const container = document.getElementById('item-request-list');
            
            if (!data || data.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Tidak ada permintaan barang</h4>
                        <p>Belum ada permintaan barang yang dibuat atau semua permintaan telah selesai</p>
                    </div>
                `;
                return;
            }
            
            // Hitung statistik
            let activeCount = 0;
            let expiredCount = 0;
            
            container.innerHTML = '';
            
            data.forEach(row => {
                const { expired, countdown } = getTimeRemaining(row.created_at);
                const statusText = {
                    requested: "Permintaan",
                    waiting_payment: "Menunggu Pembayaran",
                    paid: "Dibayar",
                    done: "Selesai"
                }[row.status] || row.status;
                
                // Update counters
                if (row.status !== 'done') {
                    activeCount++;
                    if (expired) expiredCount++;
                }
                
                const card = document.createElement('div');
                card.className = `request-card card-status-${row.status}`;
                card.dataset.createdAt = row.created_at;
                card.dataset.id = row.id;
                card.dataset.status = row.status;
                
                card.innerHTML = `
                    <div class="card-body">                        
                        <div class="info-row">
                            <span class="info-label">Sprinter:</span>
                            <span class="info-value">${row.sprinter}</span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Barang:</span>
                            <span class="info-value">${row.item}</span>
                        </div>
                        
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="info-row">
                                <span class="info-label">Jumlah:</span>
                                <span class="info-value">${row.qty}</span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Tanggal:</span>
                                <span class="info-value">${formatDateTime(row.created_at)}</span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Status:</span>
                                ${row.status_badge}
                            </div>
                        </div>
                        
                        <div class="countdown-box">
                            <div class="countdown-text">Batas Waktu Penyelesaian:</div>
                            <div class="countdown-timer ${expired ? 'countdown-expired' : 'text-success'}">${countdown}</div>
                            ${expired ? '<div class="text-danger mt-3 h5"><i class="fas fa-exclamation-circle mr-2"></i>Batas waktu telah habis</div>' : ''}
                        </div>
                    </div>
                `;
                
                container.appendChild(card);
            });
            
            // Update statistics
            document.getElementById('active-requests').textContent = activeCount;
            document.getElementById('expired-requests').textContent = expiredCount;
        }
        
        // Fungsi untuk memuat data dari server
        function loadItemRequests() {
            // Tampilkan loading state
            const container = document.getElementById('item-request-list');
            container.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Memuat...</span>
                    </div>
                    <p class="mt-3 h5">Memuat data permintaan terbaru...</p>
                </div>
            `;
            
            const slug = '{{ $company->slug }}'; // Ganti sesuai kebutuhan
            // Simulasi request AJAX
            fetch(`/item-request/ajax/${slug}`)
                .then(res => res.json())
                .then(apiData => 
                {
                    console.log("apiData", apiData);
                    
                    // Untuk demo, kita akan menggunakan data sampel karena endpoint tidak menyediakan data yang sesuai
                    // Dalam implementasi nyata, gunakan: fetch(`/item-request/ajax/${slug}`)
                    
                    // Data sampel untuk demo
                    const sampleData = [
                        {
                            id: 1,
                            sprinter: "Budi Santoso",
                            item: "Kertas A4 80gr (Rim)",
                            qty: 5,
                            status: "requested",
                            created_at: new Date()
                        },
                        {
                            id: 2,
                            sprinter: "Anita Rahayu",
                            item: "Tinta Printer Warna (Cartridge)",
                            qty: 3,
                            status: "waiting_payment",
                            created_at: new Date(Date.now() - 2*60*60*1000) // 2 hours ago
                        },
                        {
                            id: 3,
                            sprinter: "Rudi Hermawan",
                            item: "Stapler Max HD-10",
                            qty: 2,
                            status: "paid",
                            created_at: new Date(Date.now() - 5*60*60*1000) // 5 hours ago
                        },
                        {
                            id: 4,
                            sprinter: "Dewi Kurnia",
                            item: "Buku Catatan A5 (Pack)",
                            qty: 4,
                            status: "done",
                            created_at: new Date(Date.now() - 24*60*60*1000) // 1 day ago
                        },
                        {
                            id: 5,
                            sprinter: "Fajar Setiawan",
                            item: "Penghapus Whiteboard",
                            qty: 10,
                            status: "requested",
                            created_at: new Date(Date.now() - 10*60*60*1000) // 10 hours ago
                        }
                    ];
                    
                    renderItemRequests(apiData);
                    
                    // Update waktu terakhir pembaruan
                    const now = new Date();
                    document.getElementById('last-updated-time').textContent = 
                        now.toLocaleTimeString('id-ID') + ' ' + 
                        now.toLocaleDateString('id-ID');
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    container.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <h4>Gagal Memuat Data</h4>
                            <p>Terjadi kesalahan saat memuat data permintaan. Silakan coba lagi.</p>
                            <button class="btn-refresh mt-3" onclick="loadItemRequests()">
                                <i class="fas fa-redo mr-2"></i> Coba Lagi
                            </button>
                        </div>
                    `;
                });
        }
        
        // Update countdown setiap detik
        function updateCountdowns() {
            document.querySelectorAll('.request-card').forEach(card => {
                const createdAt = card.dataset.createdAt;
                const countdownEl = card.querySelector('.countdown-timer');
                
                if (countdownEl) {
                    const result = getTimeRemaining(createdAt);
                    countdownEl.textContent = result.countdown;
                    
                    if (result.expired) {
                        countdownEl.classList.add('countdown-expired');
                        countdownEl.classList.remove('text-success');
                    } else {
                        countdownEl.classList.remove('countdown-expired');
                        countdownEl.classList.add('text-success');
                    }
                }
            });
        }
        
        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Muat data pertama kali
            loadItemRequests();
            
            // Setup refresh button
            document.getElementById('refresh-btn').addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                
                // Tampilkan status loading
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat...';
                btn.disabled = true;
                
                // Muat ulang data
                loadItemRequests();
                
                // Kembalikan tombol setelah 1.5 detik
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 1500);
            });
            
            // Update countdown setiap detik
            setInterval(updateCountdowns, 1000);
            
            // Refresh data otomatis setiap 2 menit
            setInterval(loadItemRequests, 3 * 60 * 1000);
        });
    </script>
</body>
</html>