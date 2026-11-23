<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $dokumen['judul'] ?: 'Pratinjau MoU' }} - Cetak</title>
    <x-asset path="assets/user/css/mou-dokumen.css" />
    <style>
        body { margin: 0; background: #f4f5f7; }
        .mou-cetak-bilah {
            display: flex; gap: 8px; justify-content: flex-end; align-items: center;
            padding: 12px 20px; background: #fff; border-bottom: 1px solid #d7dbe0;
            position: sticky; top: 0; z-index: 5;
        }
        .mou-cetak-bilah button {
            font: inherit; padding: 8px 16px; border-radius: 8px; border: 1px solid #cfd4da;
            background: #fff; cursor: pointer;
        }
        .mou-cetak-bilah button.primer { background: #0b3d91; color: #fff; border-color: #0b3d91; }
        .mou-cetak-bungkus { padding: 24px; }
        @media print { .mou-cetak-bilah { display: none !important; } .mou-cetak-bungkus { padding: 0; } body { background: #fff; } }
    </style>
</head>
<body>
    <div class="mou-cetak-bilah">
        <button type="button" class="primer" onclick="window.print()">Cetak / Simpan PDF</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>
    <div class="mou-cetak-bungkus">
        @include('user.partials.mou-dokumen', ['dokumen' => $dokumen])
    </div>
</body>
</html>
