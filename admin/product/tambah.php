<?php
// =====================================================================
// Halaman admin: tambah data barang  ->  disimpan ke tabel `iklan`
// Lokasi file: admin/product/tambah.php
// =====================================================================
include '../../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$page_admin = 'produk';

// ---- hanya admin yang boleh masuk (sama seperti halaman admin lain) ----
if (isset($_COOKIE['login_admin'])) {
    if ($akun_adm == 'false') {
        header("location: " . $url . "system/admin/logout");
        exit;
    }
} else {
    header("location: " . $url . "admin/login/");
    exit;
}

// ---- pengaturan ----
const MAKS_GAMBAR       = 5;                 // jumlah gambar per barang
const MAKS_UKURAN_FILE  = 2 * 1024 * 1024;   // 2 MB per gambar
const MAKS_VARIAN       = 10;                // warna / ukuran per barang

$folder_gambar = __DIR__ . '/../../assets/image/product/';
$tipe_gambar   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

// ---- fungsi bantu ----
function esc($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function panjang($teks)
{
    return function_exists('mb_strlen') ? mb_strlen($teks, 'UTF-8') : strlen($teks);
}

// Nama varian disimpan dipisah koma dan '===', jadi kedua tanda itu dibuang
function bersihkan_varian($teks)
{
    $teks = preg_replace('/[\r\n\t,]+|===/u', ' ', (string) $teks);
    $teks = preg_replace('/\s+/u', ' ', $teks);
    return trim($teks);
}

function angka($nilai, $min, $maks)
{
    $hasil = filter_var(trim((string) $nilai), FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $maks]]);
    return $hasil === false ? null : $hasil;
}

if (empty($_SESSION['csrf_produk'])) {
    $_SESSION['csrf_produk'] = bin2hex(random_bytes(32));
}

// ---- data kategori untuk dropdown ----
$daftar_kategori = [];
$q = $server->query("SELECT `id`, `nama` FROM `kategori` ORDER BY `nama` ASC");
while ($k = mysqli_fetch_assoc($q)) {
    $daftar_kategori[(int) $k['id']] = $k['nama'];
}

// ---- nilai awal form ----
$errors = [];
$old = [
    'judul' => '', 'id_kategori' => '', 'harga' => '', 'diskon' => '0', 'tipe_iklan' => '',
    'berat' => '', 'stok' => '', 'deskripsi' => '',
    'warna' => [], 'ukuran' => [], 'harga_ukuran' => [],
];

$flash = $_SESSION['flash_produk'] ?? '';
unset($_SESSION['flash_produk']);

// =====================================================================
// PROSES SIMPAN
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_produk'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        die('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }

    foreach (['judul', 'id_kategori', 'harga', 'diskon', 'tipe_iklan', 'berat', 'stok', 'deskripsi'] as $f) {
        $old[$f] = trim((string) ($_POST[$f] ?? ''));
    }
    $old['warna']        = array_map('strval', (array) ($_POST['warna'] ?? []));
    $old['ukuran']       = array_map('strval', (array) ($_POST['ukuran'] ?? []));
    $old['harga_ukuran'] = array_map('strval', (array) ($_POST['harga_ukuran'] ?? []));

    // --- validasi isian ---
    if ($old['judul'] === '' || panjang($old['judul']) > 200) {
        $errors[] = 'Judul wajib diisi (maksimal 200 karakter).';
    }
    $id_kategori = angka($old['id_kategori'], 1, 2147483647);
    if ($id_kategori === null || !isset($daftar_kategori[$id_kategori])) {
        $errors[] = 'Pilih kategori yang tersedia.';
    }
    $harga = angka($old['harga'], 1, 2000000000);
    if ($harga === null) {
        $errors[] = 'Harga harus berupa angka lebih dari 0.';
    }
    $diskon = angka($old['diskon'] === '' ? '0' : $old['diskon'], 0, 99);
    if ($diskon === null) {
        $errors[] = 'Diskon harus angka 0 sampai 99 (persen).';
    }
    $berat = angka($old['berat'], 1, 1000000);
    if ($berat === null) {
        $errors[] = 'Berat harus berupa angka gram lebih dari 0.';
    }
    $stok = angka($old['stok'], 0, 99999);
    if ($stok === null) {
        $errors[] = 'Stok harus berupa angka 0 sampai 99999.';
    }
    if (!in_array($old['tipe_iklan'], ['', 'flash sale'], true)) {
        $errors[] = 'Tipe barang tidak valid.';
    }
    if ($old['deskripsi'] === '') {
        $errors[] = 'Deskripsi wajib diisi.';
    }

    // --- varian warna ---
    $warna = [];
    foreach ($old['warna'] as $w) {
        $w = bersihkan_varian($w);
        if ($w !== '' && !in_array(strtolower($w), array_map('strtolower', $warna), true)) {
            $warna[] = $w;
        }
    }
    if (count($warna) > MAKS_VARIAN) {
        $errors[] = 'Warna maksimal ' . MAKS_VARIAN . '.';
    }

    // --- varian ukuran (nama + harga per ukuran) ---
    $ukuran = [];
    $nama_dipakai = [];
    foreach ($old['ukuran'] as $i => $u) {
        $u = bersihkan_varian($u);
        if ($u === '') {
            continue;
        }
        if (in_array(strtolower($u), $nama_dipakai, true)) {
            continue;
        }
        $nama_dipakai[] = strtolower($u);
        $h_mentah = trim((string) ($old['harga_ukuran'][$i] ?? ''));
        if ($h_mentah === '') {
            $h_u = $harga;                       // kosong -> pakai harga barang
        } else {
            $h_u = angka($h_mentah, 0, 2000000000);
            if ($h_u === null) {
                $errors[] = 'Harga untuk ukuran "' . $u . '" harus berupa angka.';
                continue;
            }
        }
        $ukuran[] = $u . '===' . $h_u;
    }
    if (count($ukuran) > MAKS_VARIAN) {
        $errors[] = 'Ukuran maksimal ' . MAKS_VARIAN . '.';
    }

    // --- validasi gambar (diperiksa semua dulu, baru dipindahkan) ---
    $gambar_siap = [];   // [tmp_name, nama_baru]
    $ada_error_gambar = false;
    $f = $_FILES['gambar'] ?? null;
    if ($f && is_array($f['name'])) {
        for ($i = 0; $i < count($f['name']); $i++) {
            if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $nama_asli = $f['name'][$i];
            if ($f['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = 'Gambar "' . $nama_asli . '" gagal diunggah (kode ' . $f['error'][$i] . ').';
                $ada_error_gambar = true;
                continue;
            }
            if ($f['size'][$i] > MAKS_UKURAN_FILE) {
                $errors[] = 'Gambar "' . $nama_asli . '" lebih dari 2 MB.';
                $ada_error_gambar = true;
                continue;
            }
            if (!is_uploaded_file($f['tmp_name'][$i])) {
                $errors[] = 'File "' . $nama_asli . '" tidak valid.';
                $ada_error_gambar = true;
                continue;
            }
            $info = @getimagesize($f['tmp_name'][$i]);
            if (!$info || !isset($tipe_gambar[$info['mime']])) {
                $errors[] = '"' . $nama_asli . '" bukan gambar JPG, PNG, GIF, atau WEBP.';
                $ada_error_gambar = true;
                continue;
            }
            // ekstensi ditentukan dari isi file, bukan dari nama yang dikirim pengguna
            $gambar_siap[] = [$f['tmp_name'][$i], date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $tipe_gambar[$info['mime']]];
        }
    }
    if (count($gambar_siap) === 0 && !$ada_error_gambar) {
        $errors[] = 'Unggah minimal 1 gambar.';
    }
    if (count($gambar_siap) > MAKS_GAMBAR) {
        $errors[] = 'Gambar maksimal ' . MAKS_GAMBAR . ' file.';
    }

    // --- simpan ---
    if (!$errors) {
        if (!is_dir($folder_gambar) && !@mkdir($folder_gambar, 0755, true)) {
            $errors[] = 'Folder gambar tidak bisa dibuat: assets/image/product/';
        }
    }

    if (!$errors) {
        $dipindah = [];
        $nama_file = [];
        foreach ($gambar_siap as $g) {
            if (!move_uploaded_file($g[0], $folder_gambar . $g[1])) {
                $errors[] = 'Gagal menyimpan gambar ke folder assets/image/product/.';
                break;
            }
            $dipindah[] = $folder_gambar . $g[1];
            $nama_file[] = $g[1];
        }

        if (!$errors) {
            try {
                $stmt = $server->prepare(
                    "INSERT INTO `iklan`
                     (`id_kategori`, `gambar`, `judul`, `harga`, `deskripsi`, `berat`, `warna`, `ukuran`,
                      `stok`, `terjual`, `diskon`, `tipe_iklan`, `waktu`, `status`)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $gambar_str = implode(',', $nama_file);
                $warna_str  = implode(',', $warna);
                $ukuran_str = implode(',', $ukuran);
                $terjual    = 0;
                $waktu      = date('Y-m-d H:i:s');
                $status     = '';
                $tipe       = $old['tipe_iklan'];
                $judul      = $old['judul'];
                $deskripsi  = $old['deskripsi'];

                $stmt->bind_param(
                    'issisissiiisss',
                    $id_kategori, $gambar_str, $judul, $harga, $deskripsi, $berat, $warna_str, $ukuran_str,
                    $stok, $terjual, $diskon, $tipe, $waktu, $status
                );
                $stmt->execute();

                $_SESSION['flash_produk'] = 'Barang "' . $judul . '" berhasil disimpan.';
                header('Location: tambah.php');
                exit;
            } catch (mysqli_sql_exception $e) {
                // gagal menyimpan ke database -> hapus gambar yang sudah terlanjur dipindahkan
                foreach ($dipindah as $p) {
                    @unlink($p);
                }
                $errors[] = 'Data gagal disimpan ke database. Pastikan tabel iklan sudah dibuat (lihat iklan.sql).';
            }
        } else {
            foreach ($dipindah as $p) {
                @unlink($p);
            }
        }
    }
}

// ---- daftar barang terbaru (untuk memastikan data masuk) ----
$total_barang = 0;
$barang_terbaru = null;
$tabel_ada = true;
try {
    $total_barang = (int) mysqli_fetch_row($server->query("SELECT COUNT(*) FROM `iklan`"))[0];
    $barang_terbaru = $server->query(
        "SELECT `iklan`.*, `kategori`.`nama` AS `nama_kategori`
           FROM `iklan` LEFT JOIN `kategori` ON `kategori`.`id` = `iklan`.`id_kategori`
          ORDER BY `iklan`.`id` DESC LIMIT 10"
    );
} catch (mysqli_sql_exception $e) {
    $tabel_ada = false;   // tabel iklan belum dibuat
}

// baris varian awal di form
$baris_warna  = max(3, count($old['warna']));
$baris_ukuran = max(3, count($old['ukuran']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <link rel="icon" href="../../assets/icons/<?php echo esc($logo); ?>" type="image/svg">
    <style>
        .bp { max-width: 980px; font-family: Arial, Helvetica, sans-serif; color: #222; }
        .bp h1 { font-size: 22px; margin-bottom: 14px; }
        .bp h2 { font-size: 16px; margin: 22px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #eee; }
        .bp .kartu { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 18px; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
        .bp label { display: block; font-size: 13px; font-weight: bold; margin: 12px 0 4px; }
        .bp input[type=text], .bp input[type=number], .bp select, .bp textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; font-family: inherit; }
        .bp input:focus, .bp select:focus, .bp textarea:focus { outline: 2px solid #f5a623; border-color: #f5a623; }
        .bp .baris2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .bp .baris3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        .bp .varian { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .bp .varian.dua { grid-template-columns: 1fr 1fr; }
        .bp .catatan { font-size: 12px; color: #777; margin-top: 4px; }
        .bp button, .bp .tombol { padding: 10px 18px; border: none; border-radius: 8px; background: #f5a623; color: #fff;
            font-size: 14px; font-weight: bold; cursor: pointer; }
        .bp button.abu { background: #eee; color: #333; margin-top: 10px; }
        .bp button.besar { width: 100%; padding: 13px; margin-top: 20px; font-size: 15px; }
        .bp .pesan { padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; font-size: 14px; }
        .bp .sukses { background: #e6f4ea; color: #1b5e20; }
        .bp .gagal { background: #fdecea; color: #b71c1c; }
        .bp .preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .bp .preview img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .bp .geser { overflow-x: auto; }
        .bp table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .bp th, .bp td { padding: 8px; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle; }
        .bp th { background: #fafafa; white-space: nowrap; }
        .bp td img { width: 46px; height: 46px; object-fit: cover; border-radius: 6px; }
        @media (max-width: 640px) { .bp .baris2, .bp .baris3 { grid-template-columns: 1fr; } }
    </style>
</head>

<body>
    <div class="admin">
        <?php
        if (file_exists(__DIR__ . '/../partials/menu.php')) {
            include '../partials/menu.php';
        }
        ?>
        <div class="content_admin">
            <div class="bp">
                <h1>Tambah Barang</h1>

                <?php if ($flash) { ?><p class="pesan sukses"><?php echo esc($flash); ?></p><?php } ?>
                <?php foreach ($errors as $pesan) { ?>
                    <p class="pesan gagal"><?php echo esc($pesan); ?></p>
                <?php } ?>

                <form method="post" enctype="multipart/form-data" class="kartu" id="form_barang" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?php echo esc($_SESSION['csrf_produk']); ?>">

                    <h2 style="margin-top:0">Informasi Barang</h2>
                    <label for="judul">Judul Barang</label>
                    <input type="text" id="judul" name="judul" maxlength="200" required value="<?php echo esc($old['judul']); ?>">

                    <div class="baris2">
                        <div>
                            <label for="id_kategori">Kategori</label>
                            <select id="id_kategori" name="id_kategori" required>
                                <option value="" disabled <?php echo $old['id_kategori'] === '' ? 'selected' : ''; ?>>Pilih kategori...</option>
                                <?php foreach ($daftar_kategori as $kid => $knama) { ?>
                                    <option value="<?php echo $kid; ?>" <?php echo (string) $kid === $old['id_kategori'] ? 'selected' : ''; ?>><?php echo esc($knama); ?></option>
                                <?php } ?>
                            </select>
                            <?php if (!$daftar_kategori) { ?>
                                <p class="catatan">Belum ada kategori. Tambahkan dulu di menu Kategori.</p>
                            <?php } ?>
                        </div>
                        <div>
                            <label for="tipe_iklan">Tipe Barang</label>
                            <select id="tipe_iklan" name="tipe_iklan">
                                <option value="" <?php echo $old['tipe_iklan'] === '' ? 'selected' : ''; ?>>Biasa</option>
                                <option value="flash sale" <?php echo $old['tipe_iklan'] === 'flash sale' ? 'selected' : ''; ?>>Flash Sale</option>
                            </select>
                        </div>
                    </div>

                    <div class="baris3">
                        <div>
                            <label for="harga">Harga (Rp)</label>
                            <input type="number" id="harga" name="harga" min="1" max="2000000000" required value="<?php echo esc($old['harga']); ?>">
                        </div>
                        <div>
                            <label for="diskon">Diskon (%)</label>
                            <input type="number" id="diskon" name="diskon" min="0" max="99" value="<?php echo esc($old['diskon']); ?>">
                        </div>
                        <div>
                            <label for="stok">Stok</label>
                            <input type="number" id="stok" name="stok" min="0" max="99999" required value="<?php echo esc($old['stok']); ?>">
                        </div>
                    </div>

                    <label for="berat">Berat (gram)</label>
                    <input type="number" id="berat" name="berat" min="1" max="1000000" required value="<?php echo esc($old['berat']); ?>">

                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" required><?php echo esc($old['deskripsi']); ?></textarea>

                    <h2>Gambar (1 sampai <?php echo MAKS_GAMBAR; ?>, maks. 2 MB per file)</h2>
                    <input type="file" id="gambar" name="gambar[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    <p class="catatan">Gambar pertama menjadi gambar utama. Format: JPG, PNG, GIF, atau WEBP.</p>
                    <div class="preview" id="preview"></div>

                    <h2>Varian Warna (opsional)</h2>
                    <div class="varian" id="wadah_warna">
                        <?php for ($i = 0; $i < $baris_warna; $i++) { ?>
                            <input type="text" name="warna[]" maxlength="50" placeholder="Contoh: Merah" value="<?php echo esc($old['warna'][$i] ?? ''); ?>">
                        <?php } ?>
                    </div>
                    <button type="button" class="abu" id="tambah_warna">+ Tambah warna</button>

                    <h2>Varian Ukuran (opsional)</h2>
                    <p class="catatan" style="margin-bottom:8px">Contoh: XL untuk pakaian, 42 untuk sepatu. Kosongkan harga jika sama dengan harga barang.</p>
                    <div class="varian dua" id="wadah_ukuran">
                        <?php for ($i = 0; $i < $baris_ukuran; $i++) { ?>
                            <input type="text" name="ukuran[]" maxlength="50" placeholder="Ukuran" value="<?php echo esc($old['ukuran'][$i] ?? ''); ?>">
                            <input type="number" name="harga_ukuran[]" min="0" max="2000000000" placeholder="Harga ukuran (Rp)" value="<?php echo esc($old['harga_ukuran'][$i] ?? ''); ?>">
                        <?php } ?>
                    </div>
                    <button type="button" class="abu" id="tambah_ukuran">+ Tambah ukuran</button>

                    <button type="submit" class="besar">Simpan Barang</button>
                </form>

                <div class="kartu">
                    <h2 style="margin-top:0">Barang Terbaru<?php echo $tabel_ada ? ' (' . $total_barang . ' barang tersimpan)' : ''; ?></h2>
                    <?php if (!$tabel_ada) { ?>
                        <p class="pesan gagal">Tabel <b>iklan</b> belum ada di database. Jalankan <b>iklan.sql</b> di phpMyAdmin terlebih dahulu.</p>
                    <?php } elseif ($total_barang === 0) { ?>
                        <p class="catatan">Belum ada barang. Isi form di atas lalu klik Simpan.</p>
                    <?php } else { ?>
                        <div class="geser">
                            <table>
                                <thead>
                                    <tr><th>Gambar</th><th>Judul</th><th>Kategori</th><th>Harga</th><th>Diskon</th><th>Stok</th><th>Tipe</th><th>Waktu</th></tr>
                                </thead>
                                <tbody>
                                    <?php while ($b = mysqli_fetch_assoc($barang_terbaru)) {
                                        $gambar_utama = explode(',', $b['gambar'])[0]; ?>
                                        <tr>
                                            <td><img src="<?php echo esc($url . 'assets/image/product/' . rawurlencode($gambar_utama)); ?>" alt=""></td>
                                            <td><?php echo esc($b['judul']); ?></td>
                                            <td><?php echo esc($b['nama_kategori'] ?? '-'); ?></td>
                                            <td>Rp <?php echo number_format((int) $b['harga'], 0, ',', '.'); ?></td>
                                            <td><?php echo (int) $b['diskon']; ?>%</td>
                                            <td><?php echo (int) $b['stok']; ?></td>
                                            <td><?php echo $b['tipe_iklan'] === '' ? 'Biasa' : esc($b['tipe_iklan']); ?></td>
                                            <td><?php echo esc($b['waktu']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var MAKS_GAMBAR = <?php echo MAKS_GAMBAR; ?>;
            var MAKS_BYTES = <?php echo MAKS_UKURAN_FILE; ?>;
            var MAKS_VARIAN = <?php echo MAKS_VARIAN; ?>;

            // --- pratinjau gambar + pemeriksaan di sisi browser ---
            var input = document.getElementById('gambar');
            var kotak = document.getElementById('preview');
            input.addEventListener('change', function () {
                kotak.innerHTML = '';
                var berkas = Array.prototype.slice.call(input.files);
                if (berkas.length > MAKS_GAMBAR) {
                    alert('Maksimal ' + MAKS_GAMBAR + ' gambar.');
                    input.value = '';
                    return;
                }
                for (var i = 0; i < berkas.length; i++) {
                    if (berkas[i].size > MAKS_BYTES) {
                        alert('"' + berkas[i].name + '" lebih dari 2 MB.');
                        input.value = '';
                        kotak.innerHTML = '';
                        return;
                    }
                    var img = document.createElement('img');
                    img.src = URL.createObjectURL(berkas[i]);
                    kotak.appendChild(img);
                }
            });

            // --- tambah baris varian ---
            function jumlahBaris(wadah, per) {
                return wadah.querySelectorAll('input').length / per;
            }

            document.getElementById('tambah_warna').addEventListener('click', function () {
                var wadah = document.getElementById('wadah_warna');
                if (jumlahBaris(wadah, 1) >= MAKS_VARIAN) { alert('Maksimal ' + MAKS_VARIAN + ' warna.'); return; }
                var el = document.createElement('input');
                el.type = 'text'; el.name = 'warna[]'; el.maxLength = 50; el.placeholder = 'Contoh: Merah';
                wadah.appendChild(el);
            });

            document.getElementById('tambah_ukuran').addEventListener('click', function () {
                var wadah = document.getElementById('wadah_ukuran');
                if (jumlahBaris(wadah, 2) >= MAKS_VARIAN) { alert('Maksimal ' + MAKS_VARIAN + ' ukuran.'); return; }
                var a = document.createElement('input');
                a.type = 'text'; a.name = 'ukuran[]'; a.maxLength = 50; a.placeholder = 'Ukuran';
                var b = document.createElement('input');
                b.type = 'number'; b.name = 'harga_ukuran[]'; b.min = 0; b.max = 2000000000; b.placeholder = 'Harga ukuran (Rp)';
                wadah.appendChild(a);
                wadah.appendChild(b);
            });
        })();
    </script>
</body>

</html>
