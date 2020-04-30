@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../evert/phpdoc-md/bin/phpdocmd
php "%BIN_TARGET%" %*
