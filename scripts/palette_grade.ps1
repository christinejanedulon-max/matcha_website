# Colour-grades lifestyle photos onto the Matcha Moments forest-green palette.
# Each pixel's luminance is mapped onto a gradient built from the brand palette
# (#162813 -> #2E5227 -> #4C6B46 -> #7D9778 -> #A4B9A1 -> #E8F1E6 -> #FFFFFF)
# and blended with the original pixel so texture/detail is preserved while
# warm browns/yellows are pulled into the green family.
#
# Usage: powershell -ExecutionPolicy Bypass -File scripts\palette_grade.ps1
# Originals are kept in public/images/_originals/ so the grade is re-runnable.

Add-Type -AssemblyName System.Drawing

$projectRoot = Split-Path -Parent $PSScriptRoot
$imagesDir   = Join-Path $projectRoot 'public\images'
$backupDir   = Join-Path $imagesDir '_originals'
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

# Gradient stops: position (0-1 luminance) and RGB
$stops = @(
    @(0.00,  22,  40,  19),
    @(0.22,  46,  82,  39),
    @(0.40,  76, 107,  70),
    @(0.58, 125, 151, 120),
    @(0.74, 164, 185, 161),
    @(0.90, 232, 241, 230),
    @(1.00, 255, 255, 255)
)

# Pre-compute a 256-entry lookup table
$lut = New-Object 'int[,]' 256, 3
for ($i = 0; $i -lt 256; $i++) {
    $t = $i / 255.0
    for ($s = 0; $s -lt $stops.Count - 1; $s++) {
        $a = $stops[$s]; $b = $stops[$s + 1]
        if ($t -ge $a[0] -and $t -le $b[0]) {
            $f = ($t - $a[0]) / ($b[0] - $a[0])
            for ($c = 0; $c -lt 3; $c++) {
                $lut[$i, $c] = [int]($a[$c + 1] + ($b[$c + 1] - $a[$c + 1]) * $f)
            }
            break
        }
    }
}

function Grade-Image([string]$relPath, [double]$strength) {
    $path = Join-Path $imagesDir $relPath
    $backup = Join-Path $backupDir ($relPath -replace '[\\/]', '__')
    if (-not (Test-Path $backup)) { Copy-Item $path $backup }

    $src = [System.Drawing.Bitmap]::FromFile($backup)
    $bmp = New-Object System.Drawing.Bitmap($src.Width, $src.Height, [System.Drawing.Imaging.PixelFormat]::Format24bppRgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp); $g.DrawImage($src, 0, 0, $src.Width, $src.Height); $g.Dispose(); $src.Dispose()

    $rect = New-Object System.Drawing.Rectangle(0, 0, $bmp.Width, $bmp.Height)
    $data = $bmp.LockBits($rect, [System.Drawing.Imaging.ImageLockMode]::ReadWrite, $bmp.PixelFormat)
    $bytes = New-Object byte[] ($data.Stride * $bmp.Height)
    [System.Runtime.InteropServices.Marshal]::Copy($data.Scan0, $bytes, 0, $bytes.Length)

    $keep = 1.0 - $strength
    for ($y = 0; $y -lt $bmp.Height; $y++) {
        $row = $y * $data.Stride
        for ($x = 0; $x -lt $bmp.Width; $x++) {
            $p = $row + $x * 3
            $bl = $bytes[$p]; $gr = $bytes[$p + 1]; $rd = $bytes[$p + 2]
            $lum = [int](0.299 * $rd + 0.587 * $gr + 0.114 * $bl)
            $bytes[$p + 2] = [byte][Math]::Min(255, [int]($rd * $keep + $lut[$lum, 0] * $strength))
            $bytes[$p + 1] = [byte][Math]::Min(255, [int]($gr * $keep + $lut[$lum, 1] * $strength))
            $bytes[$p]     = [byte][Math]::Min(255, [int]($bl * $keep + $lut[$lum, 2] * $strength))
        }
    }
    [System.Runtime.InteropServices.Marshal]::Copy($bytes, 0, $data.Scan0, $bytes.Length)
    $bmp.UnlockBits($data)

    $codec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
    $ep = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $ep.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]84)
    $bmp.Save($path, $codec, $ep)
    $bmp.Dispose()
    Write-Output ("Graded {0} (strength {1}) -> {2} KB" -f $relPath, $strength, [Math]::Round((Get-Item $path).Length / 1KB))
}

# Strength: 1.0 = pure palette tritone, lower keeps more original colour.
Grade-Image 'about\cafe-interior.jpg' 0.62
Grade-Image 'about\stone-mill.jpg'    0.55
Grade-Image 'about\tea-farm.jpg'      0.50
Grade-Image 'about\tea-ceremony.jpg'  0.50
Grade-Image 'hero.jpg'                0.45
Grade-Image 'banner.jpg'              0.50
Grade-Image 'bowl.jpg'                0.50
Grade-Image 'whisk.jpg'               0.50
Grade-Image 'why-us-craft.jpg'        0.35
Grade-Image 'matcha-loop-3d.jpg'      0.35

