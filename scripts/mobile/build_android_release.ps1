param(
  [Parameter(Mandatory = $true)]
  [string]$ApiUrl,

  [Parameter(Mandatory = $false)]
  [string]$AppsBaseDir = "D:\Contabo\apps",

  [Parameter(Mandatory = $false)]
  [string]$BuildName = "1.0.0",

  [Parameter(Mandatory = $false)]
  [int]$BuildNumber = 1,

  [Parameter(Mandatory = $false)]
  [ValidateSet("apk", "aab", "both")]
  [string]$Artifact = "both"
)

$apps = @(
  (Join-Path $AppsBaseDir "customer"),
  (Join-Path $AppsBaseDir "provider"),
  (Join-Path $AppsBaseDir "admin")
)

foreach ($app in $apps) {
  Write-Host "\n=== Building app: $app ===" -ForegroundColor Cyan

  if (-not (Test-Path $app)) {
    throw "App folder not found: $app"
  }

  Push-Location $app
  try {
    flutter pub get
    if ($LASTEXITCODE -ne 0) { throw "flutter pub get failed in $app" }

    flutter analyze
    if ($LASTEXITCODE -ne 0) { throw "flutter analyze failed in $app" }

    if ($Artifact -eq "apk" -or $Artifact -eq "both") {
      flutter build apk --release --build-name=$BuildName --build-number=$BuildNumber --dart-define=API_URL=$ApiUrl
      if ($LASTEXITCODE -ne 0) { throw "APK build failed in $app" }
    }

    if ($Artifact -eq "aab" -or $Artifact -eq "both") {
      flutter build appbundle --release --build-name=$BuildName --build-number=$BuildNumber --dart-define=API_URL=$ApiUrl
      if ($LASTEXITCODE -ne 0) { throw "AAB build failed in $app" }
    }

    Write-Host "Build completed for $app" -ForegroundColor Green
    Write-Host "APK path: $app\build\app\outputs\flutter-apk\app-release.apk"
    Write-Host "AAB path: $app\build\app\outputs\bundle\release\app-release.aab"
  }
  finally {
    Pop-Location
  }
}

Write-Host "\nAll requested Android builds completed." -ForegroundColor Green
