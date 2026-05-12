@echo off
chcp 65001 > nul
echo ===================================================
echo        Lancement des Moteurs IA Protex
echo ===================================================

echo [1/2] Demarrage du Puzzle Engine (Port 5006)...
start "Puzzle CAPTCHA (Port 5006)" cmd /k "python puzzle_engine.py"

echo [2/2] Demarrage de l'OCR Engine (Port 5007)...
start "OCR Engine (Port 5007)" cmd /k "python ocr_engine.py"

echo.
echo Les moteurs tournent en arriere-plan dans de nouvelles fenetres !
echo Laissez ces fenetres ouvertes pendant que vous utilisez l'application.
echo.
pause
