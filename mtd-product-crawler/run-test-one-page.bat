@echo off
setlocal
cd /d "%~dp0"
php run.php --start=1 --end=1 --download-images --confirm-rights --no-resume
pause
endlocal
