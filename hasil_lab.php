<?php

// ============================================================================
// 1. FUNGSI LOGIKA
// ============================================================================

function tentukanKategoriUsia($tglLahir, $waktuTes = null)
{
    if (empty($tglLahir)) {
        return '-';
    }

    $lahir = new DateTime($tglLahir);
    $tes   = $waktuTes ? new DateTime($waktuTes) : new DateTime();

    $detik = $tes->getTimestamp() - $lahir->getTimestamp();
    if ($detik < 0) {
        return 'Format Salah';
    }

    $jam = $detik / 3600;

    if ($jam < 72) {
        return 'bayi lahir';
    }
    if ($jam < (365 * 24)) {
        return 'bayi';
    }
    return 'anak dan dewasa';
}

function cekKritis($nilai, $batasBawah, $batasAtas)
{
    if ($nilai === null || $nilai === '') {
        return false;
    }
    $kurangDariBawah = ($batasBawah !== null) && ($nilai < $batasBawah);
    $lebihDariAtas   = ($batasAtas !== null)  && ($nilai > $batasAtas);

    return $kurangDariBawah || $lebihDariAtas;
}

// ============================================================================
// 2. ATURAN LAB & DATA PASIEN (DEFAULT ARRAY)
// ============================================================================

$aturanLab = [
    ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah',      'satuan' => 'mg/dL',   'kategori' => 'bayi lahir',       'batas_bawah' => 30,   'batas_atas' => 300],
    ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah',      'satuan' => 'mg/dL',   'kategori' => 'bayi',             'batas_bawah' => 40,   'batas_atas' => 300],
    ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah',      'satuan' => 'mg/dL',   'kategori' => 'anak dan dewasa',  'batas_bawah' => 45,   'batas_atas' => 450],
    ['id_pemeriksaan' => 'PEM002', 'nama_tes' => 'Bilirubin Total', 'satuan' => 'mg/dL',   'kategori' => 'bayi lahir',       'batas_bawah' => null, 'batas_atas' => 15.0],
    ['id_pemeriksaan' => 'PEM002', 'nama_tes' => 'Bilirubin Total', 'satuan' => 'mg/dL',   'kategori' => 'bayi',             'batas_bawah' => null, 'batas_atas' => 12.0],
    ['id_pemeriksaan' => 'PEM002', 'nama_tes' => 'Bilirubin Total', 'satuan' => 'mg/dL',   'kategori' => 'anak dan dewasa',  'batas_bawah' => null, 'batas_atas' => 15.0],
    ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',       'satuan' => 'ribu/uL', 'kategori' => 'bayi lahir',       'batas_bawah' => 30,   'batas_atas' => null],
    ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',       'satuan' => 'ribu/uL', 'kategori' => 'bayi',             'batas_bawah' => 20,   'batas_atas' => null],
    ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',       'satuan' => 'ribu/uL', 'kategori' => 'anak dan dewasa',  'batas_bawah' => 20,   'batas_atas' => null],
];

$waktuPemeriksaan = '2026-09-30 15:00:00';

$daftarPasien = [
    [
        'id_pasien'         => 'P001',
        'nama'              => 'By. Ny. Siti',
        'tgl_lahir'         => '2026-09-29 10:00:00',
        'waktu_pemeriksaan' => $waktuPemeriksaan,
        'hasil_tes'         => [
            ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah',      'nilai' => 25],
            ['id_pemeriksaan' => 'PEM002', 'nama_tes' => 'Bilirubin Total', 'nilai' => 16.5],
            ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',       'nilai' => 150],
        ],
    ],
    [
        'id_pasien'         => 'P002',
        'nama'              => 'Adik Rizky',
        'tgl_lahir'         => '2026-04-10 08:00:00',
        'waktu_pemeriksaan' => $waktuPemeriksaan,
        'hasil_tes'         => [
            ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah', 'nilai' => 85],
            ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',  'nilai' => 15],
        ],
    ],
    [
        'id_pasien'         => 'P003',
        'nama'              => 'Bapak Budi',
        'tgl_lahir'         => '1995-02-14 06:30:00',
        'waktu_pemeriksaan' => $waktuPemeriksaan,
        'hasil_tes'         => [
            ['id_pemeriksaan' => 'PEM001', 'nama_tes' => 'Gula Darah',      'nilai' => 490],
            ['id_pemeriksaan' => 'PEM002', 'nama_tes' => 'Bilirubin Total', 'nilai' => 1.2],
            ['id_pemeriksaan' => 'PEM003', 'nama_tes' => 'Trombosit',       'nilai' => 250],
        ],
    ],
];

// Ambil dari MySQL jika database aktif
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=magang_rs;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 1,
    ]);

    $stmtAturan = $pdo->query("
        SELECT a.id_pemeriksaan, p.nama_tes, p.satuan, a.kategori, a.batas_bawah, a.batas_atas
        FROM aturan_lab a
        JOIN pemeriksaan p ON a.id_pemeriksaan = p.id_pemeriksaan
        ORDER BY a.id_pemeriksaan, a.id_aturan
    ");
    $dbAturan = $stmtAturan ? $stmtAturan->fetchAll() : [];
    if (!empty($dbAturan)) {
        $aturanLab = $dbAturan;
    }

    $stmtPasien = $pdo->query("
        SELECT t.id_transaksi, t.waktu_pemeriksaan, p.id_pasien, p.nama, p.tgl_lahir
        FROM pemeriksaan_lab t
        JOIN pasien p ON t.id_pasien = p.id_pasien
        ORDER BY t.id_transaksi ASC
    ");
    $dbPasien = $stmtPasien ? $stmtPasien->fetchAll() : [];
    if (!empty($dbPasien)) {
        $stmtHasil = $pdo->prepare("
            SELECT h.id_pemeriksaan, p.nama_tes, h.nilai
            FROM hasil_tes h
            JOIN pemeriksaan p ON h.id_pemeriksaan = p.id_pemeriksaan
            WHERE h.id_transaksi = :id_transaksi
            ORDER BY h.id_hasil ASC
        ");

        $list = [];
        foreach ($dbPasien as $dp) {
            $stmtHasil->execute(['id_transaksi' => $dp['id_transaksi']]);
            $list[] = [
                'id_pasien'         => $dp['id_pasien'],
                'nama'              => $dp['nama'],
                'tgl_lahir'         => $dp['tgl_lahir'],
                'waktu_pemeriksaan' => $dp['waktu_pemeriksaan'],
                'hasil_tes'         => $stmtHasil->fetchAll(),
            ];
        }
        $daftarPasien = $list;
    }
} catch (Exception $e) {
    // Gunakan array bawaan
}

// ============================================================================
// 3. PROSES SIMULASI DARI FORM
// ============================================================================

$hasilSimulasi = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['simulasi'])) {
    $idPasienInput = trim($_POST['id_pasien'] ?? '');
    $waktuTes      = trim($_POST['waktu_tes'] ?? date('Y-m-d H:i:s'));

    $pasienTerpilih = null;
    foreach ($daftarPasien as $p) {
        if ($p['id_pasien'] === $idPasienInput) {
            $pasienTerpilih = $p;
            break;
        }
    }

    if (!$pasienTerpilih && !empty($daftarPasien)) {
        $pasienTerpilih = $daftarPasien[0];
    }

    $nama     = $pasienTerpilih['nama'] ?? 'Pasien';
    $tglLahir = $pasienTerpilih['tgl_lahir'] ?? '';
    $idPasien = $pasienTerpilih['id_pasien'] ?? '';
    $kategori = tentukanKategoriUsia($tglLahir, $waktuTes);

    $inputs = [
        'PEM001' => ['nama' => 'Gula Darah',      'nilai' => $_POST['nilai_PEM001'] ?? ''],
        'PEM002' => ['nama' => 'Bilirubin Total', 'nilai' => $_POST['nilai_PEM002'] ?? ''],
        'PEM003' => ['nama' => 'Trombosit',       'nilai' => $_POST['nilai_PEM003'] ?? ''],
    ];

    $items = [];
    foreach ($inputs as $idPem => $data) {
        if ($data['nilai'] === '') {
            continue;
        }

        $nilai = floatval($data['nilai']);

        $aturanDitemukan = null;
        foreach ($aturanLab as $aturan) {
            if ($aturan['id_pemeriksaan'] === $idPem && $aturan['kategori'] === $kategori) {
                $aturanDitemukan = $aturan;
                break;
            }
        }

        if ($aturanDitemukan) {
            $isKritis = cekKritis($nilai, $aturanDitemukan['batas_bawah'], $aturanDitemukan['batas_atas']);

            $items[] = [
                'id_pemeriksaan' => $idPem,
                'nama_tes'       => $data['nama'],
                'nilai'          => $nilai,
                'batas_bawah'    => $aturanDitemukan['batas_bawah'] !== null ? $aturanDitemukan['batas_bawah'] : 'null',
                'batas_atas'     => $aturanDitemukan['batas_atas'] !== null ? $aturanDitemukan['batas_atas'] : 'null',
                'satuan'         => $aturanDitemukan['satuan'],
                'is_kritis'      => $isKritis,
            ];
        }
    }

    $hasilSimulasi = [
        'id_pasien' => $idPasien,
        'nama'      => $nama,
        'tgl_lahir' => $tglLahir,
        'waktu_tes' => $waktuTes,
        'kategori'  => $kategori,
        'hasil_tes' => $items,
    ];
}

// ============================================================================
// 4. MODE CLI TERMINAL
// ============================================================================

if (php_sapi_name() === 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    echo "WAKTU PEMERIKSAAN : {$waktuPemeriksaan}\n";
    echo "======================================================================================================================\n\n";

    foreach ($daftarPasien as $pasien) {
        $waktuTes = $pasien['waktu_pemeriksaan'] ?? $waktuPemeriksaan;
        $kategoriPasien = tentukanKategoriUsia($pasien['tgl_lahir'], $waktuTes);

        echo "ID Pasien     : {$pasien['id_pasien']}\n";
        echo "Nama Pasien   : {$pasien['nama']}\n";
        echo "Waktu Lahir   : {$pasien['tgl_lahir']}\n";
        echo "Kategori Usia : {$kategoriPasien}\n";
        echo "------------------------------------------------------------------------------------------------------\n";
        printf(
            "%-6s | %-32s | %-8s | %-12s | %-12s | %s\n",
            'ID', 'Pemeriksaan', 'Hasil', 'Batas Bawah', 'Batas Atas', 'Satuan'
        );
        echo "------------------------------------------------------------------------------------------------------\n";

        foreach ($pasien['hasil_tes'] as $tes) {
            $aturanDitemukan = null;
            foreach ($aturanLab as $aturan) {
                $cocok = isset($tes['id_pemeriksaan'], $aturan['id_pemeriksaan'])
                    ? ($aturan['id_pemeriksaan'] === $tes['id_pemeriksaan'])
                    : ($aturan['nama_tes'] === $tes['nama_tes']);

                if ($cocok && $aturan['kategori'] === $kategoriPasien) {
                    $aturanDitemukan = $aturan;
                    break;
                }
            }

            if ($aturanDitemukan) {
                $isKritis = cekKritis(
                    $tes['nilai'],
                    $aturanDitemukan['batas_bawah'],
                    $aturanDitemukan['batas_atas']
                );

                $idPemeriksaan = $tes['id_pemeriksaan'] ?? $aturanDitemukan['id_pemeriksaan'] ?? '-';
                $bawahTampil   = $aturanDitemukan['batas_bawah'] !== null ? $aturanDitemukan['batas_bawah'] : 'null';
                $atasTampil    = $aturanDitemukan['batas_atas'] !== null  ? $aturanDitemukan['batas_atas']  : 'null';
                $namaTesTampil = $aturanDitemukan['nama_tes'] . ($isKritis ? ' *nilai kritis' : '');

                printf(
                    "%-6s | %-32s | %-8s | %-12s | %-12s | %s\n",
                    $idPemeriksaan,
                    $namaTesTampil,
                    $tes['nilai'],
                    $bawahTampil,
                    $atasTampil,
                    $aturanDitemukan['satuan']
                );
            }
        }
        echo "======================================================================================================\n\n";
    }
    exit(0);
}

// ============================================================================
// 5. MODE WEB (HTML SEDERHANA & BERSIH)
// ============================================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Simulasi Hasil Lab</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px auto;
            max-width: 850px;
            color: #333;
            line-height: 1.4;
        }
        h2, h3 {
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        form {
            background: #fafafa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .form-row {
            margin-bottom: 10px;
        }
        label {
            display: inline-block;
            width: 180px;
        }
        input[type="text"], input[type="datetime-local"], input[type="number"], select {
            padding: 5px 8px;
            border: 1px solid #bbb;
            width: 250px;
            box-sizing: border-box;
        }
        button {
            padding: 6px 16px;
            background: #0066cc;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #004c99;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 7px 10px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        .kritis-tebal {
            color: #cc0000;
            font-weight: bold;
            margin-left: 6px;
        }
        .pasien-info {
            background: #f5f5f5;
            padding: 8px 12px;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

    <h2>Simulasi Pemeriksaan Laboratorium</h2>

    <!-- FORM INPUT -->
    <form method="POST" action="">
        <input type="hidden" name="simulasi" value="1">

        <div class="form-row">
            <label for="waktu_tes">Waktu Pemeriksaan:</label>
            <input type="datetime-local" id="waktu_tes" name="waktu_tes" value="<?= htmlspecialchars($_POST['waktu_tes'] ?? '2026-09-30T15:00') ?>" required onchange="updateKategori()">
        </div>

        <div class="form-row">
            <label for="id_pasien">Nama Pasien:</label>
            <select id="id_pasien" name="id_pasien" onchange="updateKategori()">
                <?php 
                    $selectedId = $_POST['id_pasien'] ?? ($daftarPasien[0]['id_pasien'] ?? '');
                    foreach ($daftarPasien as $p): 
                ?>
                    <option value="<?= htmlspecialchars($p['id_pasien']) ?>" data-tgl="<?= htmlspecialchars($p['tgl_lahir']) ?>" <?= ($selectedId === $p['id_pasien']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['id_pasien']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label>Kategori Pasien:</label>
            <span id="kategori_label">-</span>
        </div>

        <div class="form-row">
            <label for="nilai_PEM001">Gula Darah (mg/dL):</label>
            <input type="number" step="0.1" id="nilai_PEM001" name="nilai_PEM001" value="<?= htmlspecialchars($_POST['nilai_PEM001'] ?? '85') ?>">
        </div>

        <div class="form-row">
            <label for="nilai_PEM002">Bilirubin Total (mg/dL):</label>
            <input type="number" step="0.1" id="nilai_PEM002" name="nilai_PEM002" value="<?= htmlspecialchars($_POST['nilai_PEM002'] ?? '') ?>">
        </div>

        <div class="form-row">
            <label for="nilai_PEM003">Trombosit (ribu/uL):</label>
            <input type="number" step="0.1" id="nilai_PEM003" name="nilai_PEM003" value="<?= htmlspecialchars($_POST['nilai_PEM003'] ?? '15') ?>">
        </div>

        <div class="form-row">
            <label></label>
            <button type="submit">Submit</button>
        </div>
    </form>

    <!-- HASIL EVALUASI FORM -->
    <?php if ($hasilSimulasi): ?>
        <h3>Hasil Evaluasi Pasien</h3>
        <div class="pasien-info">
            ID: <strong><?= htmlspecialchars($hasilSimulasi['id_pasien']) ?></strong> |
            Nama: <strong><?= htmlspecialchars($hasilSimulasi['nama']) ?></strong> |
            Waktu Pemeriksaan: <strong><?= htmlspecialchars($hasilSimulasi['waktu_tes']) ?></strong> |
            Kategori Pasien: <strong><?= htmlspecialchars($hasilSimulasi['kategori']) ?></strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pemeriksaan</th>
                    <th>Hasil</th>
                    <th>Batas Bawah</th>
                    <th>Batas Atas</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hasilSimulasi['hasil_tes'] as $tes): ?>
                    <tr>
                        <td><?= htmlspecialchars($tes['id_pemeriksaan']) ?></td>
                        <td>
                            <?= htmlspecialchars($tes['nama_tes']) ?>
                            <?php if ($tes['is_kritis']): ?>
                                <strong class="kritis-tebal">*nilai kritis</strong>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tes['nilai']) ?></td>
                        <td><?= htmlspecialchars($tes['batas_bawah']) ?></td>
                        <td><?= htmlspecialchars($tes['batas_atas']) ?></td>
                        <td><?= htmlspecialchars($tes['satuan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- DAFTAR PASIEN -->
    <h3>Daftar Pasien Terdaftar</h3>

    <?php foreach ($daftarPasien as $pasien): ?>
        <?php 
            $waktuTes = $pasien['waktu_pemeriksaan'] ?? $waktuPemeriksaan;
            $kategoriPasien = tentukanKategoriUsia($pasien['tgl_lahir'], $waktuTes); 
        ?>
        <div class="pasien-info">
            ID: <strong><?= htmlspecialchars($pasien['id_pasien']) ?></strong> |
            Nama: <strong><?= htmlspecialchars($pasien['nama']) ?></strong> |
            Kategori Usia: <strong><?= htmlspecialchars($kategoriPasien) ?></strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pemeriksaan</th>
                    <th>Hasil</th>
                    <th>Batas Bawah</th>
                    <th>Batas Atas</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pasien['hasil_tes'] as $tes): ?>
                    <?php
                        $aturanDitemukan = null;
                        foreach ($aturanLab as $aturan) {
                            $cocok = isset($tes['id_pemeriksaan'], $aturan['id_pemeriksaan'])
                                ? ($aturan['id_pemeriksaan'] === $tes['id_pemeriksaan'])
                                : ($aturan['nama_tes'] === $tes['nama_tes']);

                            if ($cocok && $aturan['kategori'] === $kategoriPasien) {
                                $aturanDitemukan = $aturan;
                                break;
                            }
                        }

                        if ($aturanDitemukan):
                            $isKritis = cekKritis(
                                $tes['nilai'],
                                $aturanDitemukan['batas_bawah'],
                                $aturanDitemukan['batas_atas']
                            );
                            $idPemeriksaan = $tes['id_pemeriksaan'] ?? $aturanDitemukan['id_pemeriksaan'] ?? '-';
                            $bawahTampil   = $aturanDitemukan['batas_bawah'] !== null ? $aturanDitemukan['batas_bawah'] : 'null';
                            $atasTampil    = $aturanDitemukan['batas_atas'] !== null  ? $aturanDitemukan['batas_atas']  : 'null';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($idPemeriksaan) ?></td>
                        <td>
                            <?= htmlspecialchars($aturanDitemukan['nama_tes']) ?>
                            <?php if ($isKritis): ?>
                                <strong class="kritis-tebal">*nilai kritis</strong>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($tes['nilai']) ?></td>
                        <td><?= htmlspecialchars($bawahTampil) ?></td>
                        <td><?= htmlspecialchars($atasTampil) ?></td>
                        <td><?= htmlspecialchars($aturanDitemukan['satuan']) ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <script>
        function updateKategori() {
            const select = document.getElementById('id_pasien');
            const waktuTesVal = document.getElementById('waktu_tes').value;
            const label = document.getElementById('kategori_label');

            if (!select || !select.selectedOptions.length || !waktuTesVal) return;

            const tglLahirVal = select.selectedOptions[0].getAttribute('data-tgl');
            if (!tglLahirVal) return;

            const lahir = new Date(tglLahirVal);
            const tes = new Date(waktuTesVal);
            const diffHours = (tes - lahir) / (1000 * 60 * 60);

            if (diffHours < 0) {
                label.textContent = 'Format Salah';
            } else if (diffHours < 72) {
                label.textContent = 'bayi lahir';
            } else if (diffHours < (365 * 24)) {
                label.textContent = 'bayi';
            } else {
                label.textContent = 'anak dan dewasa';
            }
        }
        document.addEventListener('DOMContentLoaded', updateKategori);
    </script>
</body>
</html>
