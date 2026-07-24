@echo off
setlocal
cd /d "%~dp0"

echo ================================================
echo  MTD CRAWLER - XOA DU LIEU LOI VA CHAY LAI
echo ================================================
echo.

if exist "storage\products.json" copy /Y "storage\products.json" "storage\products-before-fix.json" >nul
if exist "storage\products.csv" copy /Y "storage\products.csv" "storage\products-before-fix.csv" >nul

if exist "storage\products.json" del /Q "storage\products.json"
if exist "storage\products.csv" del /Q "storage\products.csv"
if exist "storage\errors.log" del /Q "storage\errors.log"
if exist "storage\images" rmdir /S /Q "storage\images"
mkdir "storage\images" >nul 2>&1

echo Da sao luu JSON cu thanh storage\products-before-fix.json
echo Dang chay lai voi bo loc anh da sua...
echo.

php run.php --start=1 --end=100 --download-images --confirm-rights

echo.
echo Hoan tat. Nhan phim bat ky de dong.
pause >nul
endlocal
