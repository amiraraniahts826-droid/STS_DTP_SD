<?php
include "koneksi.php";

$pesan = "";
$jenis_pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_pelapor = trim($_POST["nama_pelapor"] ?? "");
    $nama_fasilitas = trim($_POST["nama_fasilitas"] ?? "");
    $lokasi = trim($_POST["lokasi"] ?? "");
    $id_kategori = $_POST["id_kategori"] ?? "";
    $deskripsi = trim($_POST["deskripsi"] ?? "");
    $prioritas = $_POST["prioritas"] ?? "";

    if (
        empty($nama_pelapor) ||
        empty($nama_fasilitas) ||
        empty($lokasi) ||
        empty($id_kategori) ||
        empty($deskripsi) ||
        empty($prioritas)
    ) {
        $pesan = "Semua kolom wajib diisi!";
        $jenis_pesan = "error";
    } else {
        try {
            $sql = "INSERT INTO laporan_kerusakan
                    (nama_pelapor, nama_fasilitas, lokasi,
                     id_kategori, deskripsi, prioritas)
                    VALUES
                    (:nama_pelapor, :nama_fasilitas, :lokasi,
                     :id_kategori, :deskripsi, :prioritas)";

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ":nama_pelapor" => $nama_pelapor,
                ":nama_fasilitas" => $nama_fasilitas,
                ":lokasi" => $lokasi,
                ":id_kategori" => $id_kategori,
                ":deskripsi" => $deskripsi,
                ":prioritas" => $prioritas
            ]);

            $pesan = "Data laporan berhasil disimpan!";
            $jenis_pesan = "success";

        } catch (PDOException $e) {
            $pesan = "Data gagal disimpan. Periksa koneksi dan struktur database.";
            $jenis_pesan = "error";
        }
    }
}

// Mengambil data laporan dari database
try {
    $sql_read = "SELECT
                    l.nama_fasilitas,
                    l.lokasi,
                    l.nama_pelapor,
                    l.prioritas,
                    l.status,
                    l.tanggal_laporan,
                    k.nama_kategori
                 FROM laporan_kerusakan AS l
                 LEFT JOIN kategori_fasilitas AS k
                 ON l.id_kategori = k.id_kategori
                 ORDER BY l.tanggal_laporan DESC";

    $stmt_read = $conn->query($sql_read);
    $hasil = $stmt_read->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $hasil = [];
    $pesan = "Data laporan gagal ditampilkan. Periksa tabel database.";
    $jenis_pesan = "error";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
        <p>Catat dan pantau laporan kerusakan fasilitas sekolah.</p>

        <nav aria-label="Navigasi utama">
            <a href="#form-laporan">Buat Laporan</a>
            <a href="#daftar-laporan">Daftar Laporan</a>
        </nav>
    </header>

    <main>
        <section id="form-laporan">
            <h2>Form Pelaporan Kerusakan</h2>
            <p>Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

            <?php if ($pesan !== ""): ?>
                <p style="color: <?= $jenis_pesan === "success" ? "green" : "red" ?>;">
                    <?= htmlspecialchars($pesan) ?>
                </p>
            <?php endif; ?>

            <form action="" method="post">
                <fieldset>
                    <legend>Data Pelaporan</legend>

                    <p>
                        <label for="nama_pelapor">Nama pelapor</label>
                        <input type="text" id="nama_pelapor"
                               name="nama_pelapor" maxlength="100" required>
                    </p>

                    <p>
                        <label for="nama_fasilitas">Nama fasilitas</label>
                        <input type="text" id="nama_fasilitas"
                               name="nama_fasilitas" maxlength="100" required>
                    </p>

                    <p>
                        <label for="lokasi">Lokasi kerusakan</label>
                        <input type="text" id="lokasi"
                               name="lokasi" maxlength="100" required>
                    </p>

                    <p>
                        <label for="id_kategori">Kategori fasilitas</label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="">-- Pilih kategori --</option>
                            <option value="1">Elektronik</option>
                            <option value="2">Furnitur</option>
                            <option value="3">Jaringan</option>
                            <option value="4">Peralatan Kelas</option>
                        </select>
                    </p>

                    <p>
                        <label for="deskripsi">Deskripsi kerusakan</label>
                        <textarea id="deskripsi" name="deskripsi"
                                  rows="4" required></textarea>
                    </p>

                    <p>
                        <label for="prioritas">Prioritas</label>
                        <select id="prioritas" name="prioritas" required>
                            <option value="">-- Pilih prioritas --</option>
                            <option value="Rendah">Rendah</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Tinggi">Tinggi</option>
                        </select>
                    </p>

                    <p>
                        <button type="submit" name="submit">
                            Kirim Laporan
                        </button>
                    </p>
                </fieldset>
            </form>
        </section>

        <section id="daftar-laporan">
            <h2>Daftar Laporan Kerusakan</h2>
            <p>Seluruh laporan yang tercatat pada database ditampilkan di sini.</p>

            <div class="table-wrapper">
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Fasilitas</th>
                            <th>Lokasi</th>
                            <th>Pelapor</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($hasil) > 0): ?>
                            <?php $no = 1; ?>

                            <?php foreach ($hasil as $row): ?>
                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($row["nama_fasilitas"] ?? "") ?>
                                        <br>
                                        <small>
                                            <?= htmlspecialchars($row["nama_kategori"] ?? "Tidak diketahui") ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["lokasi"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["nama_pelapor"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["prioritas"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["status"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row["tanggal_laporan"] ?? "") ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    Belum ada laporan kerusakan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Sistem Pelaporan Kerusakan Fasilitas Sekolah.</p>
    </footer>

</body>
</html>