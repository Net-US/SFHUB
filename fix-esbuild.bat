@echo off
chcp 65001 >nul
echo Memperbaiki masalah esbuild di Windows...
echo.

:: Hapus node_modules dan package-lock
if exist node_modules (
    echo Menghapus node_modules...
    rmdir /s /q node_modules
)
if exist package-lock.json (
    echo Menghapus package-lock.json...
    del package-lock.json
)

:: Set environment variable untuk esbuild
set ESBUILD_BINARY_PATH=%CD%\node_modules\esbuild\bin\esbuild.exe

echo.
echo Menginstall dependencies tanpa optional dependencies...
npm install --legacy-peer-deps --no-optional

echo.
echo Setup esbuild binary...
if exist node_modules\esbuild\install.js (
    node node_modules\esbuild\install.js
)

echo.
echo Verifikasi esbuild...
if exist node_modules\@esbuild\win32-x64\esbuild.exe (
    echo Binary esbuild Windows ditemukan.
    node_modules\@esbuild\win32-x64\esbuild.exe --version
) else (
    echo Binary tidak ditemukan, mencoba install manual...
    npm install @esbuild/win32-x64 --save-dev
)

echo.
echo Selesai! Coba jalankan 'npm run build' sekarang.
pause
