<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Kegiatan Baru</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; padding: 40px; }
        .box { background: white; border: 1px solid #dbdbdb; padding: 25px; border-radius: 8px; max-width: 450px; margin: 0 auto; }
        input, textarea { width: 100%; padding: 8px; margin: 8px 0; border: 1px solid #dbdbdb; border-radius: 4px; box-sizing: border-box; }
        button { background: #0095f6; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<div class="box">
    <h3>Tambah Postingan Carousel & Agenda</h3>
    <form action="campusportal.php" method="POST" enctype="multipart/form-data">
        <label>Judul Kegiatan:</label>
        <input type="text" name="judul_kegiatan" required>

        <label>Deskripsi Acara:</label>
        <textarea name="isi_kegiatan" rows="4" required></textarea>

        <label>Pilih Beberapa Foto Sekaligus:</label>
        <input type="file" name="fotos[]" accept="image/*" multiple required>

        <button type="submit" name="submit">Bagikan Postingan</button>
    </form>
</div>

</body>
</html>