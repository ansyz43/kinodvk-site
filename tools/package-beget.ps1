$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$SiteDir = Join-Path $Root "site"
$DistDir = Join-Path $Root "dist"
$ZipPath = Join-Path $DistDir "beget-site.zip"

Set-Location $Root

Write-Host "Generating static site..."
node tools\generate-site.mjs

if (!(Test-Path $SiteDir)) {
  throw "Site directory not found: $SiteDir"
}

if (!(Test-Path $DistDir)) {
  New-Item -ItemType Directory -Path $DistDir | Out-Null
}

if (Test-Path $ZipPath) {
  Remove-Item -LiteralPath $ZipPath -Force
}

Write-Host "Packing site contents..."
$items = Get-ChildItem -LiteralPath $SiteDir -Force
Compress-Archive -Path $items.FullName -DestinationPath $ZipPath -Force

Write-Host "Done: $ZipPath"
