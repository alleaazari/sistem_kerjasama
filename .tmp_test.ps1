Set-Location $PSScriptRoot
& php artisan test 2>&1 | Out-File -FilePath .tmp_test.txt -Encoding ascii
$tail = Get-Content .tmp_test.txt -Tail 50
[System.IO.File]::WriteAllText((Join-Path $PSScriptRoot '.tmp_tail.txt'), ($tail -join "`n"), [System.Text.Encoding]::ASCII)