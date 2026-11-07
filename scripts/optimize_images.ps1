Add-Type -AssemblyName System.Drawing

$projectRoot = Split-Path -Parent $PSScriptRoot
$imagesDir = Join-Path $projectRoot "public\images"

function Save-OptimizedJpeg($srcFile, $dstFile, $quality = 85, $maxWidth = 1920) {
    if (-not (Test-Path $srcFile)) { return }
    $img = [System.Drawing.Image]::FromFile($srcFile)
    
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
    
    # Fill with background color if transparent
    $g.Clear([System.Drawing.Color]::FromArgb(46, 82, 39))
    $g.DrawImage($img, 0, 0, $w, $h)
    
    $codecs = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders()
    $jpegCodec = $codecs | Where-Object { $_.MimeType -eq 'image/jpeg' }
    
    $encoderParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
    $encoderParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [long]$quality)
    
    $bmp.Save($dstFile, $jpegCodec, $encoderParams)
    
    $g.Dispose()
    $bmp.Dispose()
    $img.Dispose()
    
    $oldSize = (Get-Item $srcFile).Length
    $newSize = (Get-Item $dstFile).Length
    Write-Output "Optimized: $(Split-Path $srcFile -Leaf) ($([Math]::Round($oldSize/1MB, 2)) MB) -> $(Split-Path $dstFile -Leaf) ($([Math]::Round($newSize/1KB, 1)) KB)"
}

Write-Output "Optimizing heavy static assets..."
Save-OptimizedJpeg (Join-Path $imagesDir "hero.png") (Join-Path $imagesDir "hero.jpg") 82 1920
Save-OptimizedJpeg (Join-Path $imagesDir "bowl.png") (Join-Path $imagesDir "bowl.jpg") 85 1200
Save-OptimizedJpeg (Join-Path $imagesDir "whisk.png") (Join-Path $imagesDir "whisk.jpg") 85 1200
Save-OptimizedJpeg (Join-Path $imagesDir "banner.png") (Join-Path $imagesDir "banner.jpg") 85 1920
Save-OptimizedJpeg (Join-Path $imagesDir "col.png") (Join-Path $imagesDir "col.jpg") 85 1400

Write-Output "Asset optimization finished."

