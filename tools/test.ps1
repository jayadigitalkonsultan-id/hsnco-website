param([Parameter(ValueFromRemainingArguments=$true)][string[]]$TestArguments)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$phpBinary = (Get-Command php -ErrorAction Stop).Source
$hasSqlite = (& $phpBinary -m) -contains 'pdo_sqlite'
Push-Location $projectRoot
try {
    # Enable SQLite only for this test process; the Laragon php.ini is untouched.
    if ($hasSqlite) {
        & $phpBinary vendor/phpunit/phpunit/phpunit @TestArguments
    } else {
        & $phpBinary -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit @TestArguments
    }
    $testExitCode = $LASTEXITCODE
} finally {
    Pop-Location
}
exit $testExitCode
