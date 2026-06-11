<?php
session_start();
include 'connection.php';

// Menangkap kata kunci pencarian dari form saring
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// PROSES HAPUS DATA (Sudah diselaraskan ke pencarian_pengguna.php)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nim'])) {
    $nim_to_delete = mysqli_real_escape_string($conn, $_GET['nim']);
    $delete_query = "DELETE FROM mahasiswa WHERE nim = '$nim_to_delete'";
    if ($conn->query($delete_query)) {
        header("Location: pencarian_pengguna.php?status=deleted");
        exit();
    }
}

// Menghitung total mahasiswa terdaftar untuk widget statistik
$total_mhs = 0;
$res_count = $conn->query("SELECT COUNT(*) as total FROM mahasiswa");
if ($res_count) {
    $row_count = $res_count->fetch_assoc();
    $total_mhs = $row_count['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen & Pencarian Mahasiswa - Masagena ITH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <style>
        :root { 
            --primary-blue: #0A4D68; 
            --secondary-blue: #083D54;
            --light-blue: #F0F5F9; 
            --orange-accent: #FF6B35;
            --dark-text: #1E293B;
            --bg-body: #F8FAFC;
        }
        
        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif;
            color: var(--dark-text);
        }

        .page-header h2 {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .custom-card { 
            border: none; 
            box-shadow: 0 4px 20px rgba(10, 77, 104, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02); 
            border-radius: 16px; 
            background-color: #ffffff; 
        }
        
        .widget-stat {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .widget-icon {
            width: 52px;
            height: 52px;
            background-color: var(--light-blue);
            color: var(--primary-blue);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .search-input {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .search-input:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(10, 77, 104, 0.15);
        }
        
        .btn-gradient { 
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); 
            color: #ffffff; 
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 500;
        }
        .btn-gradient:hover { color: #ffffff; opacity: 0.95; }

        .table-container { border-radius: 16px; overflow: hidden; }
        .custom-card-header { background: #ffffff; border-bottom: 1px solid #F1F5F9; padding: 20px 24px; }
        .custom-card-header h5 { font-weight: 600; margin: 0; }
        
        .table thead th { 
            font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;
            color: #64748B; background-color: #F8FAFC; padding: 16px 24px; border-bottom: 1px solid #E2E8F0;
        }
        .table tbody td { padding: 16px 24px; font-size: 0.95rem; color: #334155; border-bottom: 1px solid #F1F5F9; }
        .table tbody tr:last-child td { border-bottom: none; }
        .table-hover tbody tr:hover { background-color: #F8FAFC; }

        .btn-action {
            width: 36px; height: 36px; border-radius: 50%; display: inline-flex;
            align-items: center; justify-content: center; border: none; transition: all 0.2s;
        }
        .btn-edit { background-color: #FEF3C7; color: #D97706; }
        .btn-edit:hover { background-color: #FDE68A; }
        .btn-delete { background-color: #FEE2E2; color: #DC2626; }
        .btn-delete:hover { background-color: #FCA5A5; }
        
        .badge-prodi { background-color: #E0F2FE; color: #0369A1; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 1200px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 page-header">
            <div>
                <h2><span style="color: var(--orange-accent)">Manajemen</span> Mahasiswa</h2>
                <p class="text-muted small mb-0">Kelola, saring, dan telusuri data seluruh mahasiswa aktif Masagena ITH.</p>
            </div>
            <a href="tambah_mahasiswa.php" class="btn btn-gradient d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg fs-5"></i> Tambah Mahasiswa
            </a>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px; background-color: #FEE2E2; color: #991B1B;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Data mahasiswa berhasil dihapus dari sistem.</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-5">
                <div class="card custom-card widget-stat h-100">
                    <div class="widget-icon"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <span class="text-muted small d-block mb-1 fw-medium">Total Terdaftar</span>
                        <h4 class="mb-0 fw-bold" style="color: var(--primary-blue);"><?php echo $total_mhs; ?> <span class="fs-6 text-muted fw-normal">Mahasiswa</span></h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7">
                <div class="card custom-card p-3 h-100 d-flex justify-content-center">
                    <form action="pencarian_pengguna.php" method="GET" class="row g-2 m-0 w-100">
                        <div class="col-sm-9 p-1">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: #E2E8F0; border-radius: 10px 0 0 10px;"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control search-input border-start-0" placeholder="Masukkan NIM atau Nama Lengkap..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 0 10px 10px 0;">
                            </div>
                        </div>
                        <div class="col-sm-3 p-1">
                            <button type="submit" class="btn btn-gradient w-100">Saring</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card custom-card table-container">
            <div class="custom-card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Mahasiswa Terdaftar</h5>
                <?php if($search != ''): ?>
                    <span class="badge bg-light text-secondary border px-3 py-2" style="border-radius: 8px;">Hasil pencarian: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Program Studi</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // PERBAIKAN UTAMA: Menggunakan AS untuk mengubah nama kolom asli database (nama & prodi) menjadi nama_lengkap & program_studi secara virtual
                        $query = "SELECT nim, email, no_hp, nama AS nama_lengkap, prodi AS program_studi FROM mahasiswa WHERE 1=1";
                        if ($search != '') { 
                            $query .= " AND (nim LIKE '%$search%' OR nama LIKE '%$search%')"; 
                        }
                        
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td class='fw-semibold text-secondary'>{$row['nim']}</td>
                                        <td class='fw-semibold'>{$row['nama_lengkap']}</td>
                                        <td><span class='badge-prodi'>{$row['program_studi']}</span></td>
                                        <td class='text-muted'>{$row['email']}</td>
                                        <td>" . ($row['no_hp'] ? $row['no_hp'] : '<span class="text-muted">-</span>') . "</td>
                                        <td class='text-center'>
                                            <a href='edit_mahasiswa.php?nim={$row['nim']}' class='btn-action btn-edit me-1' title='Edit Data'><i class='bi bi-pencil-fill'></i></a>
                                            <a href='pencarian_pengguna.php?action=delete&nim={$row['nim']}' class='btn-action btn-delete' title='Hapus Data' onclick='return confirm(\"Apakah Anda yakin ingin menghapus mahasiswa ini?\")'><i class='bi bi-trash3-fill'></i></a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='6' class='text-center py-5 text-muted'>
                                        <i class='bi bi-folder-x d-block mb-2 text-muted' style='font-size: 2.5rem;'></i>
                                        Data mahasiswa tidak ditemukan.
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>