@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/bmvc
php "%BIN_TARGET%" %*
