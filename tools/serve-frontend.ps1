param([int]$Port=4180,[string]$Php='C:/laragon/bin/php/php-8.2.24-nts-Win32-vs16-x64/php.exe')
$ErrorActionPreference='Stop'
$projectRoot=Split-Path $PSScriptRoot -Parent
if(!(Test-Path -LiteralPath $Php)){throw 'PHP executable not found. Provide -Php with an installed PHP >=8.2 executable.'}
$previousSession=$env:SESSION_DRIVER
$previousCache=$env:CACHE_STORE
Push-Location $projectRoot
try {
    $env:SESSION_DRIVER='file'
    $env:CACHE_STORE='file'
    & $Php artisan serve --host=127.0.0.1 --port=$Port --no-reload
} finally {
    $env:SESSION_DRIVER=$previousSession
    $env:CACHE_STORE=$previousCache
    Pop-Location
}