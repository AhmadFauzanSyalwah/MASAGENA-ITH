<?php
session_start();
// Pastikan path ke database sesuai dengan struktur folder Anda
require_once '../../config/database.php';

// Proteksi Halaman: Pastikan yang mengakses adalah Admin
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href='../../auth/login.php';</script>";
    exit;
}

$pesan = "";
$tipe_pesan = "";

// ==========================================
// 1. PROSES HAPUS PENGURUS
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['hapus_pengurus'])) {
    $id_pengurus = $_POST['id_pengurus'];
    $tab_aktif   = $_POST['tab_aktif_hapus']; 
    try {
        $sql = "DELETE FROM pengurus_organisasi WHERE id_pengurus = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_pengurus]);
        
        header("Location: manajemen_pengurus.php?tab=" . $tab_aktif . "&msg=del_success");
        exit;
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage(); 
        $tipe_pesan = "error";
    }
}

// ==========================================
// 2. PROSES EDIT PENGURUS
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_pengurus'])) {
    $id_pengurus   = $_POST['id_pengurus_edit'];
    $id_organisasi = $_POST['id_organisasi']; // Mengambil ID Organisasi yang baru
    $nama_pengurus = $_POST['nama_pengurus'];
    $jabatan       = $_POST['jabatan'];
    $no_hp         = $_POST['no_hp'];
    $password_baru = trim($_POST['password_baru']);

    try {
        if (!empty($password_baru)) {
            // Jika password diisi, update beserta password dan organisasinya
            $pass_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql = "UPDATE pengurus_organisasi SET id_organisasi = ?, nama_pengurus = ?, jabatan = ?, no_hp = ?, password = ? WHERE id_pengurus = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_organisasi, $nama_pengurus, $jabatan, $no_hp, $pass_hash, $id_pengurus]);
        } else {
            // Jika password kosong, update data lainnya (termasuk organisasi)
            $sql = "UPDATE pengurus_organisasi SET id_organisasi = ?, nama_pengurus = ?, jabatan = ?, no_hp = ? WHERE id_pengurus = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_organisasi, $nama_pengurus, $jabatan, $no_hp, $id_pengurus]);
        }
        
        // Cerdas: Arahkan layar kembali ke Tab Organisasi yang dipilih di form edit (berguna jika admin memindahkan pengurus ke org lain)
        header("Location: manajemen_pengurus.php?tab=" . $id_organisasi . "&msg=edit_success");
        exit;
    } catch (PDOException $e) {
        $pesan = "Gagal mengupdate data: " . $e->getMessage(); 
        $tipe_pesan = "error";
    }
}

// Menangkap Notifikasi Redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'del_success') {
        $pesan = "Data pengurus berhasil dihapus.";
        $tipe_pesan = "success";
    } elseif ($_GET['msg'] == 'edit_success') {
        $pesan = "Data pengurus berhasil diperbarui.";
        $tipe_pesan = "success";
    }
}

// ==========================================
// 3. READ DATA DROPDOWN & TAB KATEGORI
// ==========================================
$data_organisasi = [];
try {
    $data_organisasi = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC")->fetchAll() ?: [];
} catch (PDOException $e) {
    die("Error SQL (Organisasi): " . $e->getMessage());
}

$tab_awal = isset($_GET['tab']) ? intval($_GET['tab']) : (count($data_organisasi) > 0 ? $data_organisasi[0]['id_organisasi'] : null);

// ==========================================
// 4. READ SEMUA DATA PENGURUS
// ==========================================
$data_pengurus = [];
try {
    $query = "
        SELECT p.id_pengurus, p.id_organisasi, p.nama_pengurus, p.jabatan, p.no_hp, p.status_verifikasi, o.nama_organisasi 
        FROM pengurus_organisasi p 
        LEFT JOIN organisasi o ON p.id_organisasi = o.id_organisasi 
        ORDER BY p.id_pengurus DESC
    ";
    $data_pengurus = $pdo->query($query)->fetchAll() ?: [];
} catch (PDOException $e) {
    $pesan = "Error SQL: " . $e->getMessage();
    $tipe_pesan = "error";
}

// Include Header
include '../../include/header.php';
?>

<style>
    .page-title { margin-bottom: 20px; font-size: 24px; color: #1F3D68; font-family: 'Montserrat', sans-serif; }
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border: 1px solid #eee; }
    .card-header { margin-bottom: 20px; border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; }
    .card-header h3 { font-size: 18px; color: #1F3D68; display: flex; align-items: center; gap: 8px; }
    
    /* GAYA TAB */
    .tab-container { display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; overflow-x: auto; white-space: nowrap; }
    .tab-item { padding: 10px 20px; background: transparent; cursor: pointer; outline: none; color: #4b5563; font-weight: 600; font-size: 14px; border-radius: 8px; transition: 0.2s; border: 1px solid transparent; }
    .tab-item:hover { background-color: #f3f4f6; color: #1F3D68; }
    .tab-item.active { background-color: #F59E0B; color: white; font-weight: bold; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2); }

    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .data-table th { background: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 12px; }
    .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    
    /* Tombol Aksi */
    .action-buttons { display: flex; justify-content: center; gap: 8px; align-items: center; }
    .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .btn-warning { background: #F59E0B; color: #fff; }
    .btn-warning:hover { background: #d97706; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-danger:hover { background: #dc2626; }
    .btn-submit { background: #1F3D68; color: #fff; padding: 10px 20px; border-radius: 8px; font-size: 14px; width: 100%; justify-content: center;}
    .btn-submit:hover { background: #162c4a; }
    
    .table-body-transition { transition: opacity 0.25s ease-in-out; }
    .row-hidden { display: none !important; }

    /* STYLING MODAL (POP-UP) */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5);
        display: flex; justify-content: center; align-items: center; z-index: 1000;
        opacity: 0; visibility: hidden; transition: 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    
    .modal-content {
        background: white; width: 90%; max-width: 500px; border-radius: 12px; overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-20px); transition: 0.3s ease;
    }
    .modal-overlay.active .modal-content { transform: translateY(0); }
    
    .modal-header { background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .modal-header h3 { margin: 0; color: #1F3D68; font-size: 18px; display: flex; align-items: center; gap: 8px; }

    .close-btn {
        background: transparent;
        border: none;
        font-size: 24px; /* Ukuran disesuaikan agar pas di tengah */
        font-weight: 300;
        color: #a0aec0;
        cursor: pointer;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        
        /* PENTING: Wajib 0 dan border-box agar poros lingkaran presisi di tengah */
        padding: 0; 
        margin: 0;
        box-sizing: border-box;
        
        /* Mengunci titik poros rotasi tepat di pusat lingkaran */
        transform-origin: center center; 
        
        /* Animasi transisi halus */
        transition: transform 0.25s ease-in-out, background-color 0.25s ease-in-out, color 0.25s ease-in-out;
    }

    /* Efek Hover - Berputar Murni di Tempat */
    .close-btn:hover {
        background-color: #fee2e2;
        color: #ef4444;
        transform: rotate(90deg); /* Berputar pas di tengah tanpa goyang */
    }
        
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: 14px; }
    .form-control { width: 100%; padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Inter', sans-serif; outline: none; transition: 0.2s; box-sizing: border-box; }
    .form-control:focus { border-color: #F59E0B; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1); }
    .help-text { font-size: 12px; color: #6b7280; margin-top: 5px; display: block; }
</style>

<div style="padding: 20px;">
    <h1 class="page-title"><i class="fa-solid fa-users-gear" style="color: #F59E0B;"></i> Manajemen Pengurus Organisasi</h1>

    <?php if ($pesan): ?>
        <div class="alert <?= $tipe_pesan == 'success' ? 'alert-success' : 'alert-error' ?>">
            <i class="fa-solid <?= $tipe_pesan == 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-list-ul"></i> Daftar Pengurus Berdasarkan Organisasi</h3>
        </div>

        <div class="tab-container">
            <?php if (empty($data_organisasi)): ?>
                <span style="color: #9ca3af; font-size: 14px;">Belum ada kategori organisasi.</span>
            <?php else: ?>
                <?php foreach($data_organisasi as $org): ?>
                    <button type="button" 
                       class="tab-item" 
                       data-tab-id="<?= $org['id_organisasi'] ?>"
                       onclick="switchTab(<?= $org['id_organisasi'] ?>)">
                        <?= htmlspecialchars($org['nama_organisasi']) ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Pengurus</th>
                        <th>Jabatan</th>
                        <th>Kontak WA</th>
                        <th>Status Verifikasi</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tabel-pengurus-body" class="table-body-transition" style="opacity: 1;">
                    <tr id="pesan-kosong" class="row-hidden">
                        <td colspan="5" style="text-align: center; color: #6b7280; padding: 30px 0;">
                            📭 Tidak ada data pengurus di organisasi ini saat ini.
                        </td>
                    </tr>

                    <?php foreach($data_pengurus as $pg): ?>
                    <?php 
                        $status_teks = htmlspecialchars($pg['status_verifikasi']);
                        $is_verified = (strtolower($status_teks) === 'sudah' || strtolower($status_teks) === 'terverifikasi');
                    ?>
                    <tr class="baris-pengurus row-hidden" data-org="<?= $pg['id_organisasi'] ?>">
                        <td style="font-weight: bold; color: #1F3D68;"><?= htmlspecialchars($pg['nama_pengurus']) ?></td>
                        <td><span style="background: #f3f4f6; padding: 4px 8px; border-radius: 6px; font-weight: 500;"><?= htmlspecialchars($pg['jabatan']) ?></span></td>
                        <td>
                            <?php if (!empty($pg['no_hp'])): ?>
                                <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> <?= htmlspecialchars($pg['no_hp']) ?>
                            <?php else: ?>
                                <span style="color: #9ca3af;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-status" style="background: <?= $is_verified ? '#d1fae5' : '#fee2e2' ?>; color: <?= $is_verified ? '#065f46' : '#991b1b' ?>;">
                                <?= $status_teks ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn btn-warning" 
                                    onclick="bukaModalEdit('<?= $pg['id_pengurus'] ?>', '<?= htmlspecialchars(addslashes($pg['nama_pengurus'])) ?>', '<?= htmlspecialchars(addslashes($pg['jabatan'])) ?>', '<?= htmlspecialchars(addslashes($pg['no_hp'])) ?>', '<?= $pg['id_organisasi'] ?>')">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>

                                <form action="" method="POST" onsubmit="return confirm('Hapus akun pengurus <?= htmlspecialchars($pg['nama_pengurus']) ?>?');" style="margin: 0;">
                                    <input type="hidden" name="id_pengurus" value="<?= $pg['id_pengurus'] ?>">
                                    <input type="hidden" name="tab_aktif_hapus" class="input-tab-aktif" value="">
                                    <button type="submit" name="hapus_pengurus" class="btn btn-danger">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.hidden {
    display: none !important;
}
</style>

<div id="modalEdit" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <h2 style="margin: 0;">Edit Data Pengurus</h2>
            <button type="button" class="close-btn" onclick="tutupModal('modalEdit')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="POST">
                <input type="hidden" name="tab_aktif" value="<?= $tab ?>">
                <input type="hidden" name="id_pengurus" id="edit_id">
                
                <div class="form-group">
                    <label>Organisasi</label>
                    <select name="id_organisasi" id="edit_organisasi" class="form-control" required>
                        <option value="" hidden>-- Pilih Organisasi --</option>
                        <?php
                        // Memuat daftar organisasi dari database
                        $stmtOrg = $pdo->query("SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC");
                        while ($org = $stmtOrg->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="'.$org['id_organisasi'].'">'.htmlspecialchars($org['nama_organisasi']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" placeholder="Masukkan nama pengurus..." required>
                </div>
                
                <div class="form-group">
                    <label>Jabatan di Organisasi</label>
                    <select id="edit_jabatan_select" class="form-control" onchange="toggleJabatanCustom('edit')" style="margin-bottom: 8px;">
                        <option value="Ketua">Ketua</option>
                        <option value="Wakil Ketua">Wakil Ketua</option>
                        <option value="Sekretaris">Sekretaris</option>
                        <option value="Bendahara">Bendahara</option>
                        <option value="Lainnya">Lainnya (Ketik Manual...)</option>
                    </select>
                    <input type="hidden" name="jabatan" id="edit_jabatan_asli">
                    <input type="text" id="edit_jabatan_custom" class="form-control hidden" placeholder="Ketik nama jabatan lainnya di sini...">
                </div>

                <div class="form-group">
                    <label>Level Pengurus</label>
                    <select name="level" id="edit_level" class="form-control" required>
                        <option value="" hidden>-- Pilih Level Pengurus --</option>
                        <option value="Pengurus Inti">Pengurus Inti</option>
                        <option value="Pengurus Departemen">Pengurus Departemen</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="text" name="nohp" id="edit_nohp" class="form-control" placeholder="Contoh: 08123456789" required>
                </div>
                
                <div class="form-group">
                    <label>Password Baru (Opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                    <small style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">Biarkan kosong jika Anda tidak ingin mengganti password pengurus ini.</small>
                </div>
                
                <button type="submit" name="edit_pengurus" class="btn-submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let activeTabId = <?= $tab_awal ? $tab_awal : 'null' ?>;
        
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.has('tab')) {
            activeTabId = urlParams.get('tab');
        } else if(sessionStorage.getItem("lastOrgTab")) {
            activeTabId = sessionStorage.getItem("lastOrgTab");
        }

        if(activeTabId) {
            switchTab(activeTabId, false);
        }

        // Pertahankan posisi scroll setelah refresh
        if (sessionStorage.getItem("scrollPosition") !== null) {
            window.scrollTo({
                top: sessionStorage.getItem("scrollPosition"),
                behavior: "instant"
            });
        }
    });

    window.addEventListener("beforeunload", function() {
        sessionStorage.setItem("scrollPosition", window.scrollY);
    });

    // FUNGSI GANTI TAB
    function switchTab(orgId, pakaiAnimasi = true) {
        sessionStorage.setItem("lastOrgTab", orgId);
        document.querySelectorAll('.input-tab-aktif').forEach(el => el.value = orgId);

        document.querySelectorAll('.tab-item').forEach(el => {
            el.classList.remove('active');
            if(el.getAttribute('data-tab-id') == orgId) {
                el.classList.add('active');
            }
        });

        const tbody = document.getElementById('tabel-pengurus-body');
        const rows = document.querySelectorAll('.baris-pengurus');
        const pesanKosong = document.getElementById('pesan-kosong');
        let adaData = false;

        function perbaruiTabel() {
            rows.forEach(row => {
                if(row.getAttribute('data-org') == orgId) {
                    row.classList.remove('row-hidden');
                    adaData = true;
                } else {
                    row.classList.add('row-hidden');
                }
            });

            if(!adaData) { pesanKosong.classList.remove('row-hidden'); } 
            else { pesanKosong.classList.add('row-hidden'); }
            tbody.style.opacity = 1;
        }

        if(pakaiAnimasi) {
            tbody.style.opacity = 0;
            setTimeout(perbaruiTabel, 250);
        } else { perbaruiTabel(); }
    }

// FUNGSI MODAL EDIT YANG SUDAH DIPERBAIKI DAN ANTI-ERROR
    function bukaModalEdit(id, nama, jabatan, nohp, id_organisasi) {
        try {
            // Isi form data standar dengan aman
            const editId = document.getElementById('edit_id');
            if (editId) editId.value = id;

            const editNama = document.getElementById('edit_nama');
            if (editNama) editNama.value = nama;

            const editNohp = document.getElementById('edit_nohp');
            if (editNohp) editNohp.value = nohp;

            const editOrganisasi = document.getElementById('edit_organisasi');
            if (editOrganisasi) editOrganisasi.value = id_organisasi; 
            
            // Logika Sinkronisasi Dropdown Jabatan Edit
            const jabatSelect = document.getElementById('edit_jabatan_select');
            const jabatCustom = document.getElementById('edit_jabatan_custom');
            const jabatAsli = document.getElementById('edit_jabatan_asli');
            const opsiUtama = ['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara'];

            if (jabatAsli) jabatAsli.value = jabatan || '';

            if (jabatSelect) {
                if (jabatan && opsiUtama.includes(jabatan)) {
                    jabatSelect.value = jabatan;
                    if (jabatCustom) {
                        jabatCustom.classList.add('hidden');
                        jabatCustom.removeAttribute('required');
                        jabatCustom.value = '';
                    }
                } else if (jabatan) {
                    jabatSelect.value = 'Lainnya';
                    if (jabatCustom) {
                        jabatCustom.classList.remove('hidden');
                        jabatCustom.setAttribute('required', 'required');
                        jabatCustom.value = jabatan;
                    }
                } else {
                    jabatSelect.value = 'Ketua'; // Default
                    if (jabatAsli) jabatAsli.value = 'Ketua';
                    if (jabatCustom) jabatCustom.classList.add('hidden');
                }
            }
            
            // Buka Modalnya
            const modal = document.getElementById('modalEdit');
            if (modal) {
                modal.classList.add('active');
            } else {
                console.error("Kotak HTML Modal Edit tidak ditemukan di file ini!");
            }
        } catch (error) {
            console.error("Terjadi error di fungsi bukaModalEdit:", error);
        }
    }

    // FUNGSI TOGGLE UNTUK MENYEMBUNYIKAN/MENAMPILKAN INPUT KETIK MANUAL
    function toggleJabatanCustom(mode) {
        try {
            const jabatSelect = document.getElementById(`${mode}_jabatan_select`);
            const jabatCustom = document.getElementById(`${mode}_jabatan_custom`);
            const jabatAsli = document.getElementById(`${mode}_jabatan_asli`);

            if (jabatSelect && jabatSelect.value === 'Lainnya') {
                if (jabatCustom) {
                    jabatCustom.classList.remove('hidden');
                    jabatCustom.setAttribute('required', 'required');
                    jabatCustom.value = '';
                    jabatCustom.focus();
                }
                if (jabatAsli) jabatAsli.value = '';
            } else {
                if (jabatCustom) {
                    jabatCustom.classList.add('hidden');
                    jabatCustom.removeAttribute('required');
                }
                if (jabatAsli && jabatSelect) jabatAsli.value = jabatSelect.value;
            }
        } catch (e) {
            console.error("Terjadi error di toggleJabatanCustom:", e);
        }
    }

    // Event listener otomatis untuk mengalirkan teks yang diketik manual ke sistem PHP
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'tambah_jabatan_custom') {
            const inputAsli = document.getElementById('tambah_jabatan_asli');
            if (inputAsli) inputAsli.value = e.target.value;
        }
        if (e.target && e.target.id === 'edit_jabatan_custom') {
            const inputAsliEdit = document.getElementById('edit_jabatan_asli');
            if (inputAsliEdit) inputAsliEdit.value = e.target.value;
        }
    });

        function tutupModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    }
</script>

<?php include '../../include/footer.php'; ?>