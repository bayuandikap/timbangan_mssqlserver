<?php
header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';

// 1. Ambil Semua Antrean & Riwayat Timbangan
if ($action === 'list') {
    try {
        $stmt = $conn->query("
            SELECT 
                id, 
                no_tiket, 
                no_polisi, 
                nama_sopir, 
                komoditas, 
                bruto, 
                tara, 
                netto, 
                status,
                CONVERT(VARCHAR(19), waktu_masuk, 120) AS waktu_masuk,
                CONVERT(VARCHAR(19), waktu_keluar, 120) AS waktu_keluar
            FROM tbl_timbangan 
            ORDER BY id DESC
        ");
        $data = $stmt->fetchAll();
        echo json_encode(["status" => "success", "data" => $data]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

// 2. Transaksi Timbang Masuk (Bruto)
if ($action === 'timbang_masuk' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_polisi  = strtoupper(trim($_POST['no_polisi'] ?? ''));
    $nama_sopir = trim($_POST['nama_sopir'] ?? '');
    $komoditas  = trim($_POST['komoditas'] ?? '');
    $bruto      = floatval($_POST['bruto'] ?? 0);

    if (empty($no_polisi) || empty($nama_sopir) || $bruto <= 0) {
        echo json_encode(["status" => "error", "message" => "Nomor Polisi, Nama Sopir, dan Nilai Bruto wajib valid."]);
        exit;
    }

    $no_tiket = "TKT-" . date("Ymd") . "-" . rand(100, 999);

    try {
        $stmt = $conn->prepare("
            INSERT INTO tbl_timbangan (no_tiket, no_polisi, nama_sopir, komoditas, bruto, status) 
            VALUES (:no_tiket, :no_polisi, :nama_sopir, :komoditas, :bruto, 'MASUK')
        ");
        $stmt->execute([
            ':no_tiket'   => $no_tiket,
            ':no_polisi'  => $no_polisi,
            ':nama_sopir' => $nama_sopir,
            ':komoditas'  => $komoditas,
            ':bruto'      => $bruto
        ]);

        echo json_encode([
            "status"   => "success", 
            "message"  => "Tiket Masuk berhasil disimpan dengan nomor: {$no_tiket}"
        ]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan transaksi: " . $e->getMessage()]);
    }
    exit;
}

// 3. Transaksi Timbang Keluar (Tara & Perhitungan Netto)
if ($action === 'timbang_keluar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = intval($_POST['id'] ?? 0);
    $tara = floatval($_POST['tara'] ?? 0);

    if ($id <= 0 || $tara <= 0) {
        echo json_encode(["status" => "error", "message" => "ID Tiket dan Nilai Tara harus valid."]);
        exit;
    }

    try {
        // Ambil data bruto awal
        $stmt_check = $conn->prepare("SELECT bruto FROM tbl_timbangan WHERE id = :id");
        $stmt_check->execute([':id' => $id]);
        $row = $stmt_check->fetch();

        if (!$row) {
            echo json_encode(["status" => "error", "message" => "Tiket transaksi tidak ditemukan di database."]);
            exit;
        }

        $bruto = floatval($row['bruto']);

        // Validasi integritas operasional: Berat tara mustahil lebih berat dari bruto muatan penuh
        if ($tara >= $bruto) {
            echo json_encode([
                "status"  => "error", 
                "message" => "Validasi Gagal: Bobot tara (kosong) tidak boleh melebihi atau sama dengan bruto!"
            ]);
            exit;
        }

        $netto = $bruto - $tara;

        $stmt_update = $conn->prepare("
            UPDATE tbl_timbangan 
            SET tara = :tara, 
                netto = :netto, 
                status = 'SELESAI', 
                waktu_keluar = GETDATE() 
            WHERE id = :id
        ");
        $stmt_update->execute([
            ':tara'  => $tara,
            ':netto' => $netto,
            ':id'    => $id
        ]);

        echo json_encode([
            "status"  => "success", 
            "message" => "Penimbangan selesai. Bobot Netto: " . number_format($netto, 2) . " Kg"
        ]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Gagal update timbangan: " . $e->getMessage()]);
    }
    exit;
}
?>