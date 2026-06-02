<?php
session_start();
include 'connection.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pencarian Pengguna - Admin Masagena ITH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <style>
        :root { --primary-blue: #0A4D68; --light-blue: #EBF4F6; --orange-accent: #FF6B35; }
        body { background-color: #f4f7f9; font-family: 'Segoe UI', sans-serif; }
        .card { border: 1px solid rgba(10, 77, 104, 0.08); box-shadow: 0 10px 30px rgba(10, 77, 104, 0.04); border-radius: 14px; background-color: #ffffff; }
        .custom-card-header { background: linear-gradient(135deg, var(--primary-blue), #083D54); color: #ffffff; padding: 15px 20px; border-radius: 14px 14px 0 0 !important; }
        .table th { color: var(--primary-blue); background-color: var(--light-blue); }
        .btn-blue { background-color: var(--primary-blue); color: #ffffff; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="mb-4">
            <h2><span style="color: var(--orange-accent)">Manajemen</span> Pengguna</h2>
            <p class="text-muted small">Cari data seluruh mahasiswa yang terdaftar dalam sistem Masagena ITH.</p>
        </div>

        <div class="card p-3 mb-4">
            <form action="pencarian_pengguna.php" method="GET" class="row g-2">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Masukkan NIM atau Nama..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-blue w-100">Cari Data</button>
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="custom-card-header"><h5>Daftar Pengguna (Mahasiswa)</h5></div>
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>NIM</th><th>Nama Lengkap</th><th>Program Studi</th><th>Email</th></tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM mahasiswa WHERE 1=1";
                    if ($search != '') { $query .= " AND (nim LIKE '%$search%' OR nama LIKE '%$search%')"; }
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr><td>{$row['nim']}</td><td>{$row['nama']}</td><td>{$row['prodi']}</td><td>{$row['email']}</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Data tidak ditemukan.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>