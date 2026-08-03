@echo off
chcp 65001 >nul
echo ====================================
echo   Download Face Recognition Models
echo ====================================
echo.

REM Create models folder if not exists
if not exist "models" mkdir models

echo Downloading models...
echo.

REM tiny_face_detector
echo [1/6] Downloading tiny_face_detector_model-weights_manifest.json...
curl -L -o "models\tiny_face_detector_model-weights_manifest.json" "https://github.com/justadudwohl/face-api.js/raw/master/model/tiny_face_detector_model-weights_manifest.json"

echo [2/6] Downloading tiny_face_detector_model-shard1...
curl -L -o "models\tiny_face_detector_model-shard1" "https://github.com/justadudwohl/face-api.js/raw/master/model/tiny_face_detector_model-shard1"

REM face_landmark_68
echo [3/6] Downloading face_landmark_68_model-weights_manifest.json...
curl -L -o "models\face_landmark_68_model-weights_manifest.json" "https://github.com/justadudwohl/face-api.js/raw/master/model/face_landmark_68_model-weights_manifest.json"

echo [4/6] Downloading face_landmark_68_model-shard1...
curl -L -o "models\face_landmark_68_model-shard1" "https://github.com/justadudwohl/face-api.js/raw/master/model/face_landmark_68_model-shard1"

REM face_recognition
echo [5/6] Downloading face_recognition_model-weights_manifest.json...
curl -L -o "models\face_recognition_model-weights_manifest.json" "https://github.com/justadudwohl/face-api.js/raw/master/model/face_recognition_model-weights_manifest.json"

echo [6/6] Downloading face_recognition_model-shard1...
curl -L -o "models\face_recognition_model-shard1" "https://github.com/justadudwohl/face-api.js/raw/master/model/face_recognition_model-shard1"

echo.
echo ====================================
echo   Download Complete!
echo ====================================
echo.
echo Verifying files...
echo.

REM Verify files
set ALL_OK=1"

if not exist "models\tiny_face_detector_model-weights_manifest.json" set ALL_OK=0 & echo [ERROR] tiny_face_detector_model-weights_manifest.json not found
if not exist "models\tiny_face_detector_model-shard1" set ALL_OK=0 & echo [ERROR] tiny_face_detector_model-shard1 not found
if not exist "models\face_landmark_68_model-weights_manifest.json" set ALL_OK=0 & echo [ERROR] face_landmark_68_model-weights_manifest.json not found
if not exist "models\face_landmark_68_model-shard1" set ALL_OK=0 & echo [ERROR] face_landmark_68_model-shard1 not found
if not exist "models\face_recognition_model-weights_manifest.json" set ALL_OK=0 & echo [ERROR] face_recognition_model-weights_manifest.json not found
if not exist "models\face_recognition_model-shard1" set ALL_OK=0 & echo [ERROR] face_recognition_model-shard1 not found

if "%ALL_OK%"=="1" (
    echo.
    echo [SUCCESS] All models downloaded!
    echo Now refresh face-login.php page.
) else (
    echo.
    echo [WARNING] Some files failed to download.
    echo Please check your internet connection and try again.
)

echo.
pause
