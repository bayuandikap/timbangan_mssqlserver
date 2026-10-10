<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Jembatan Timbang Truk - PT PG Rajawali I</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .digital-indicator {
            background-color: #0f172a;
            color: #22c55e;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }
    </style>
</head>
<body class="py-4">

<div class="container-fluid px-4">
    <!-- Header Aplikasi -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom bg-white p-3 rounded shadow-sm">
        <div>
            <h4 class="mb-0 text-primary fw-bold">
                <i class="bi bi-truck-flatbed me-2"></i>Sistem Penimbangan & Verifikasi Muatan Truk
            </h4>
            <small class="text-muted">PT PG Rajawali I — Modul Operasional Jembatan Timbang Terpadu (MS SQL Server)</small>
        </div>
        <div class="text-end">
            <span class="badge bg-primary px-3 py-2"><i class="bi bi-database-check me-1"></i> Microsoft SQL Server Connected</span>
        </div>
    </div>

    <div class="row">
        <!-- Panel Form Timbang Masuk -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> Input Timbang Masuk (Bruto)
                </div>
                <div class="card-body">
                    <form id="formTimbangMasuk">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nomor Polisi Truk</label>
                            <input type="text" class="form-control" id="no_polisi" name="no_polisi" placeholder="Misal: N 8123 AB" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Nama Sopir</label>
                            <input type="text" class="form-control" id="nama_sopir" name="nama_sopir" placeholder="Nama pengemudi armada" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Komoditas / Muatan</label>
                            <select class="form-select" id="komoditas" name="komoditas">
                                <option value="Tebu Masuk (Raw Cane)">Tebu Masuk (Raw Cane)</option>
                                <option value="Gula Kristal Putih (GKP)">Gula Kristal Putih (GKP)</option>
                                <option value="Tetes Tebu (Molasses)">Tetes Tebu (Molasses)</option>
                                <option value="Ampas Tebu (Bagasse)">Ampas Tebu (Bagasse)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Berat Bruto (Kg)</label>
                                <button type="button" class="btn btn-sm btn-outline-dark py-0" id="btnSimulasiBruto">
                                    <i class="bi bi-cpu me-1"></i>Simulasikan Load Cell
                                </button>
                            </div>
                            <input type="number" step="10" class="form-control digital-indicator fs-4 text-end" id="bruto" name="bruto" placeholder="0" required>
                            <small class="text-muted fst-italic">*Nilai simulasi pembacaan indikator timbangan digital</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-semibold" id="btnSimpanMasuk">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Tiket Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Tabel Antrean & Rekap -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-card-checklist me-1"></i> Data Transaksi & Antrean Timbangan</span>
                    <button class="btn btn-sm btn-light py-0" id="btnRefresh">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light text-secondary small text-uppercase">
                                <tr>
                                    <th>No. Tiket</th>
                                    <th>Armada / Sopir</th>
                                    <th>Komoditas</th>
                                    <th class="text-end">Bruto (Kg)</th>
                                    <th class="text-end">Tara (Kg)</th>
                                    <th class="text-end">Netto (Kg)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelData" class="small">
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Memuat data transaksi...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Timbang Keluar (Tara) -->
<div class="modal fade" id="modalTimbangKeluar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-arrow-up-right me-1"></i> Verifikasi Timbang Keluar (Tara)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_id">
                <div class="mb-3 p-3 bg-light rounded border">
                    <div class="row">
                        <div class="col-6"><small class="text-muted">No. Tiket:</small><br><b id="modal_tiket" class="font-monospace"></b></div>
                        <div class="col-6"><small class="text-muted">Armada / Sopir:</small><br><span id="modal_armada" class="fw-semibold"></span></div>
                        <div class="col-12 mt-2"><small class="text-muted">Berat Bruto Awal:</small><br><span id="modal_bruto" class="fs-5 fw-bold text-primary"></span> Kg</div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-secondary mb-0">Bobot Tara Truk Kosong (Kg)</label>
                        <button type="button" class="btn btn-sm btn-outline-dark py-0" id="btnSimulasiTara">
                            <i class="bi bi-cpu me-1"></i>Simulasikan Load Cell
                        </button>
                    </div>
                    <input type="number" step="10" class="form-control digital-indicator fs-4 text-end" id="modal_tara" placeholder="0" required>
                    <small class="text-muted fst-italic">*Ditimbang setelah pembongkaran tebu/muatan tuntas</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success fw-bold" id="btnSimpanKeluar">Hitung Netto & Selesaikan Tiket</button>
            </div>
        </div>
    </div>
</div>

<!-- Library jQuery & Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let currentBruto = 0;
    const modalKeluar = new bootstrap.Modal(document.getElementById('modalTimbangKeluar'));

    // Fungsi memuat data tabel secara realtime via AJAX
    function loadData() {
        $.ajax({
            url: 'api.php?action=list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let html = '';
                    if (!res.data || res.data.length === 0) {
                        html = `<tr><td colspan="8" class="text-center py-4 text-muted">Belum ada antrean penimbangan aktif.</td></tr>`;
                    } else {
                        res.data.forEach(function(item) {
                            let badge = item.status === 'MASUK' 
                                ? `<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Menunggu Tara</span>` 
                                : `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>`;
                            
                            let aksi = item.status === 'MASUK' 
                                ? `<button class="btn btn-sm btn-primary py-0 btn-tara" data-id="${item.id}" data-tiket="${item.no_tiket}" data-nopol="${item.no_polisi}" data-sopir="${item.nama_sopir}" data-bruto="${item.bruto}">Timbang Keluar</button>` 
                                : `<span class="text-muted"><i class="bi bi-lock-fill"></i> Tiket Ditutup</span>`;

                            html += `
                                <tr>
                                    <td><span class="font-monospace fw-bold text-dark">${item.no_tiket}</span></td>
                                    <td><b>${item.no_polisi}</b><br><small class="text-muted">${item.nama_sopir}</small></td>
                                    <td>${item.komoditas}</td>
                                    <td class="text-end fw-semibold">${Number(item.bruto).toLocaleString('id-ID')}</td>
                                    <td class="text-end">${Number(item.tara).toLocaleString('id-ID')}</td>
                                    <td class="text-end fw-bold text-success">${Number(item.netto).toLocaleString('id-ID')}</td>
                                    <td class="text-center">${badge}</td>
                                    <td class="text-center">${aksi}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#tabelData').html(html);
                } else {
                    alert('Gagal mengambil data: ' + res.message);
                }
            },
            error: function() {
                $('#tabelData').html(`<tr><td colspan="8" class="text-center py-4 text-danger">Koneksi ke server terputus. Pastikan service MS SQL Server berjalan.</td></tr>`);
            }
        });
    }

    loadData();

    $('#btnRefresh').click(function() {
        loadData();
    });

    // Simulasi sensor indikator bruto (13.000 kg - 18.500 kg)
    $('#btnSimulasiBruto').click(function() {
        let simGross = Math.floor(Math.random() * (18500 - 13000 + 1) + 13000);
        $('#bruto').val(simGross);
    });

    // Submit form timbang masuk via AJAX
    $('#formTimbangMasuk').submit(function(e) {
        e.preventDefault();
        $('#btnSimpanMasuk').prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'api.php?action=timbang_masuk',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#formTimbangMasuk')[0].reset();
                    loadData();
                } else {
                    alert(res.message);
                }
                $('#btnSimpanMasuk').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Tiket Masuk');
            },
            error: function() {
                alert('Terjadi kesalahan pada backend server.');
                $('#btnSimpanMasuk').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Simpan Tiket Masuk');
            }
        });
    });

    // Buka modal timbang keluar
    $(document).on('click', '.btn-tara', function() {
        let id     = $(this).data('id');
        let tiket  = $(this).data('tiket');
        let nopol  = $(this).data('nopol');
        let sopir  = $(this).data('sopir');
        currentBruto = parseFloat($(this).data('bruto'));

        $('#modal_id').val(id);
        $('#modal_tiket').text(tiket);
        $('#modal_armada').text(nopol + ' (' + sopir + ')');
        $('#modal_bruto').text(currentBruto.toLocaleString('id-ID'));
        $('#modal_tara').val('');
        modalKeluar.show();
    });

    // Simulasi sensor indikator tara truk kosong (4.500 kg - 6.200 kg)
    $('#btnSimulasiTara').click(function() {
        let simTara = Math.floor(Math.random() * (6200 - 4500 + 1) + 4500);
        $('#modal_tara').val(simTara);
    });

    // Simpan timbang keluar & hitung netto
    $('#btnSimpanKeluar').click(function() {
        let id   = $('#modal_id').val();
        let tara = parseFloat($('#modal_tara').val());

        if (!tara || tara <= 0) {
            alert('Nilai tara harus diisi dengan angka valid!');
            return;
        }

        if (tara >= currentBruto) {
            alert('Bobot tara tidak boleh melebihi atau sama dengan bruto!');
            return;
        }

        $.ajax({
            url: 'api.php?action=timbang_keluar',
            type: 'POST',
            data: { id: id, tara: tara },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    modalKeluar.hide();
                    loadData();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Gagal memproses data keluar.');
            }
        });
    });
});
</script>
</body>
</html>