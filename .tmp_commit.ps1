Set-Location $PSScriptRoot
$log = @()

git add -A
git reset -- storage/framework/testing-mou 2>&1 | Out-Null
git rm -r --cached storage/framework/testing-mou 2>&1 | Out-Null

$staged = git diff --cached --name-only 2>&1 | Out-String
$log += "=== STAGED ==="
$log += $staged

$msg = @"
feat(area-mitra): redesign UI mitra, MoU, dan landing CMS

- Area mitra: status masa aktif, profil instansi, unggah foto (disk privat),
  MoU (unggah PDF/DOCX, pratinjau, unduh terkontrol).
- MitraController + App\Support (DataDemo, StatusKerjasama, LandingCms).
- Landing CMS: artikel, galeri, petinggi, visi dari config terpusat.
- CSS/JS baru untuk panel admin, navbar, login, area mitra.
- Test: AreaMitraTest, LandingCmsSyncTest, PengajuanFormTest (53 test hijau).
- .gitignore: abaikan storage/framework/testing-mou (artefak test).
"@
$log += "=== COMMIT ==="
$log += (git commit -m $msg 2>&1 | Out-String)

[System.IO.File]::WriteAllText((Join-Path $PSScriptRoot '.tmp_log.txt'), ($log -join "`n"), [System.Text.Encoding]::ASCII)