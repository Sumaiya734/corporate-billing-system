# Download Font Awesome (v6.4.0) CSS and webfonts into public/vendor/fontawesome/
# Run this from the project root (where `public` folder is located):
#   powershell -ExecutionPolicy Bypass -File .\scripts\download-fontawesome.ps1

$version = '6.4.0'
$base = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/$version"
$cssUrl = "$base/css/all.min.css"

$cwd = (Get-Location).Path
$destCssDir = Join-Path $cwd 'public\vendor\fontawesome\css'
$destFontDir = Join-Path $cwd 'public\vendor\fontawesome\webfonts'

Write-Host "Creating directories..."
New-Item -ItemType Directory -Force -Path $destCssDir | Out-Null
New-Item -ItemType Directory -Force -Path $destFontDir | Out-Null

$cssOut = Join-Path $destCssDir 'all.min.css'

Write-Host "Downloading CSS: $cssUrl"
try {
    Invoke-WebRequest -Uri $cssUrl -OutFile $cssOut -UseBasicParsing -ErrorAction Stop
} catch {
    Write-Error "Failed to download CSS: $_"
    exit 1
}

Write-Host "Parsing CSS for webfont filenames..."
$cssContent = Get-Content $cssOut -Raw

# Simple, robust extraction of url(...) entries without complex regex
$filenames = @()
# Use a focused regex to capture url(...) contents reliably
$matches = [regex]::Matches($cssContent, 'url\(([^)]+)\)')
foreach ($m in $matches) {
    $inside = $m.Groups[1].Value.Trim()
    # remove surrounding quotes if any
    if ($inside.StartsWith('"') -or $inside.StartsWith("'")) { $inside = $inside.Substring(1) }
    if ($inside.EndsWith('"') -or $inside.EndsWith("'")) { $inside = $inside.Substring(0, $inside.Length - 1) }
    # only consider entries that reference webfonts or have file extensions woff/woff2/ttf
    if ($inside -match '\.woff2?$' -or $inside -match '\.ttf$' -or $inside -match '\/webfonts\/') {
        # remove ../webfonts/ prefix if present
        $inside = $inside -replace '^\.\./webfonts/', ''
        if ($inside) { $filenames += $inside }
    }
}

$filenames = $filenames | Sort-Object -Unique

if ($filenames.Count -eq 0) {
    Write-Warning "No webfonts found in CSS. The CSS may reference fonts differently. Verify manually."
} else {
    foreach ($fn in $filenames) {
        $fontUrl = "$base/webfonts/$fn"
        $dest = Join-Path $destFontDir $fn
        if (-Not (Test-Path $dest)) {
            Write-Host "Downloading $fontUrl"
            try {
                Invoke-WebRequest -Uri $fontUrl -OutFile $dest -UseBasicParsing -ErrorAction Stop
            } catch {
                Write-Warning ('Failed to download ' + $fontUrl + ': ' + $_.Exception.Message)
            }
        } else {
            Write-Host "Already exists: $fn"
        }
    }
}

Write-Host "Font Awesome files downloaded to: public\vendor\fontawesome\"
Write-Host "You can now reload the app; blade will prefer the local copy if present."