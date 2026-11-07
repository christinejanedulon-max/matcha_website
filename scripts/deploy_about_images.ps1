Add-Type -AssemblyName System.Drawing

$projectRoot = Split-Path -Parent $PSScriptRoot
$aboutImagesDir = Join-Path $projectRoot "public\images\about"
if (-not (Test-Path $aboutImagesDir)) {
    New-Item -ItemType Directory -Path $aboutImagesDir -Force | Out-Null
}

$artifactDir = "C:\Users\tintin\.gemini\antigravity\brain\85202b2d-0a12-4785-a1b3-99cbc6afe5e0"

$mapping = @{
    "tea_farm_1794053374756.jpg"     = "tea-farm.jpg"
    "stone_mill_1794053391883.jpg"   = "stone-mill.jpg"
    "tea_ceremony_1794053411822.jpg" = "tea-ceremony.jpg"
    "cafe_interior_1794053435042.jpg"= "cafe-interior.jpg"
}

function Save-ResizedWebJpeg($srcPath, $dstPath, $maxWidth = 1400, $quality = 85) {
    $img = [System.Drawing.Image]::FromFile($srcPath)
    $w = $img.Width
    $h = $img.Height
    if ($w -gt $maxWidth) {
        $scale = $maxWidth / $w
        $w = $maxWidth
        $h = [int]($h * $scale)
    }
    $bmp = New-Object System.Drawing.Bitmap($w, $h)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    $g.DrawImage($img, 0, 0, $w, $h)
    
    $codecs = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders()
    $jpegCodec = $codecs | Where-Object { $_.MimeType -eq 'image/jpeg' }
    $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]$quality)
    
    $bmp.Save($dstPath, $jpegCodec, $encoderParams)
    $g.Dispose()
    $bmp.Dispose()
    $img.Dispose()
    
    $kb = [Math]::Round((Get-Item $dstPath).Length / 1KB, 1)
    Write-Output "Copied & optimized: $(Split-Path $dstPath -Leaf) ($kb KB)"
}

foreach ($key in $mapping.Keys) {
    $src = Join-Path $artifactDir $key
    $dst = Join-Path $aboutImagesDir $mapping[$key]
    if (Test-Path $src) {
        Save-ResizedWebJpeg $src $dst 1200 84
    } else {
        Write-Warning "Source not found: $src"
    }
}

Write-Output "About images ready in $aboutImagesDir"

