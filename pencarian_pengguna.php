<?php
session_start();

// Memanggil file koneksi database
include 'koneksi.php';

// Menangkap kata kunci pencarian jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Pengguna - Admin Masagena ITH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-blue: #0A4D68;
            --secondary-blue: #05bfdb;
            --light-blue: #EBF4F6;
            --orange-accent: #FF6B35;
            --orange-hover: #E85A28;
        }
        
        body { 
            background-color: #f4f7f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333;
        }
        
        .card { 
            border: 1px solid rgba(10, 77, 104, 0.08); 
            box-shadow: 0 10px 30px rgba(10, 77, 104, 0.04); 
            border-radius: 14px; 
            background-color: #ffffff;
        }
        
        .nav-title { font-weight: 700; color: var(--primary-blue); }
        
        .custom-card-header {
            background: linear-gradient(135deg, var(--primary-blue), #083D54);
            color: #ffffff;
            padding: 15px 20px;
            border-radius: 14px 14px 0 0 !important;
        }
        .custom-card-header h5 { color: #ffffff !important; font-weight: 600; }
        
        .table th { 
            font-weight: 600; 
            color: var(--primary-blue); 
            background-color: var(--light-blue);
            border-bottom: 2px solid rgba(10, 77, 104, 0.1);
        }
        
        .btn-blue {
            background-color: var(--primary-blue);
            color: #ffffff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-blue:hover {
            background-color: #063549;
            color: #ffffff;
        }

        .btn-orange {
            background-color: var(--orange-accent);
            color: #ffffff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-orange:hover {
            background-color: var(--orange-hover);
            color: #ffffff;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background-color: var(--light-blue);
            color: var(--primary-blue);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid rgba(10, 77, 104, 0.2);
        }
    </style>
</head>
<body>

    <div class="container py-5">
        
        <div class="mb-4 text-center text-md-start">
            <h2 class="nav-title m-0"><span style="color: var(--orange-accent)">Manajemen</span> Pengguna</h2>
            <p class="text-muted m-0 small">Cari, filter, dan lihat data seluruh mahasiswa yang terdaftar dalam sistem Masagena ITH.</p>
        </div>

        <div class="card p-3 mb-4">
            <form action="pencarian_pengguna.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Masukkan NIM, Nama Mahasiswa, atau Program Studi..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-blue w-100 fw-semibold"><i class="bi bi-funnel-fill"></i> Cari Data</button>
                        <?php if($search != ''): ?>
                            <a href="pencarian_pengguna.php" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="custom-card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0"><i class="bi bi-people-fill text-warning me-2"></i>Daftar Pengguna (Mahasiswa)</h5>
                <?php if($search != ''): ?>
                    <span class="badge bg-white text-dark fw-normal small">Hasil untuk: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 70px;">Profil</th>
                            <th style="width: 150px;">NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Program Studi</th>
                            <th>Email</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Membuat query dasar pencarian ke tabel mahasiswa
                        // (Sesuaikan nama kolom 'nim', 'nama', 'prodi', 'email' jika berbeda di database Anda)
                        $query = "SELECT * FROM mahasiswa WHERE 1=1";
                        
                        if ($search != '') {
                            $query .= " AND (nim LIKE '%$search%' OR nama LIKE '%$search%' OR prodi LIKE '%$search%' OR email LIKE '%$search%')";
                        }
                        $query .= " ORDER BY nama ASC";

                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                // Mengambil huruf pertama nama untuk inisial foto profil buatan
                                $initial = strtoupper(substr($row['nama'], 0, 1));
                                
                                echo "<tr>
                                        <td class='ps-4'>
                                            <div class='avatar-circle'>{$initial}</div>
                                        </td>
                                        <td class='fw-bold text-secondary'>" . htmlspecialchars($row['nim']) . "</td>
                                        <td class='fw-semibold text-dark'>" . htmlspecialchars($row['nama']) . "</td>
                                        <td><span class='badge bg-light text-dark border'>" . htmlspecialchars($row['prodi']) . "</span></td>
                                        <td class='text-muted small'>" . htmlspecialchars($row['email']) . "</td>
                                        <td class='text-center'>
                                            <button class='btn btn-sm btn-orange px-3 fw-semibold' onclick='alert(\"Detail untuk " . htmlspecialchars($row['nama']) . " (NIM: " . htmlspecialchars($row['nim']) . ")\")'>
                                                <i class='bi bi-eye-fill'></i> Detail
                                            </button>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i class='bi bi-person-x-fill display-6 d-block mb-2 text-danger'></i>Data pengguna tidak ditemukan atau tabel 'mahasiswa' masih kosong.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="aktivitas_pengguna.php" class="btn btn-outline-secondary fw-semibold btn-sm"><i class="bi bi-arrow-left"></i> Kembali ke Aktivitas Saya</a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>