Add-Type -AssemblyName System.Drawing

$projectRoot = Split-Path -Parent $PSScriptRoot
$srcPath = Join-Path $projectRoot "public\images\logo.png"

if (-not (Test-Path $srcPath)) {
    Write-Error "logo.png not found at $srcPath"
    exit 1
}

$src = [System.Drawing.Bitmap]::FromFile($srcPath)
$w = $src.Width
$h = $src.Height

$transparentBmp = New-Object System.Drawing.Bitmap($w, $h, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
$lightBmp = New-Object System.Drawing.Bitmap($w, $h, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)

for ($x = 0; $x -lt $w; $x++) {
    for ($y = 0; $y -lt $h; $y++) {
        $c = $src.GetPixel($x, $y)
        # Background is white or near-white
        if ($c.R -gt 235 -and $c.G -gt 235 -and $c.B -gt 235) {
            $transparentBmp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, 0, 0, 0))
            $lightBmp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, 0, 0, 0))
        } else {
            # Logo has dark content
            $transparentBmp.SetPixel($x, $y, $c)
            # Create light version for dark green footer: crisp soft sage / white
            # Calculate intensity/alpha based on darkness
            $luminance = [int](0.299 * $c.R + 0.587 * $c.G + 0.114 * $c.B)
            $alpha = [Math]::Max(0, [Math]::Min(255, [int]((255 - $luminance) * 1.3)))
            if ($alpha -gt 25) {
                $lightBmp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb($alpha, 255, 255, 255))
            } else {
                $lightBmp.SetPixel($x, $y, [System.Drawing.Color]::FromArgb(0, 0, 0, 0))
            }
        }
    }
}

$transPath = Join-Path $projectRoot "public\images\logo-transparent.png"
$lightPath = Join-Path $projectRoot "public\images\logo-light.png"

$transparentBmp.Save($transPath, [System.Drawing.Imaging.ImageFormat]::Png)
$lightBmp.Save($lightPath, [System.Drawing.Imaging.ImageFormat]::Png)

$src.Dispose()
$transparentBmp.Dispose()
$lightBmp.Dispose()

Write-Output "Successfully generated: $transPath and $lightPath"
