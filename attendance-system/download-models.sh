#!/bin/bash

# Script untuk download face-api.js models
# Jalankan di folder attendance-system: bash download-models.sh

echo "Downloading face-api.js models..."

BASE_URL="https://github.com/justadudwohl/face-api.js/raw/master/model"

# tiny_face_detector
echo "Downloading tiny_face_detector_model..."
curl -L -o models/tiny_face_detector_model-weights_manifest.json "$BASE_URL/tiny_face_detector_model-weights_manifest.json"
curl -L -o models/tiny_face_detector_model-shard1 "$BASE_URL/tiny_face_detector_model-shard1"

# face_landmark_68
echo "Downloading face_landmark_68_model..."
curl -L -o models/face_landmark_68_model-weights_manifest.json "$BASE_URL/face_landmark_68_model-weights_manifest.json"
curl -L -o models/face_landmark_68_model-shard1 "$BASE_URL/face_landmark_68_model-shard1"

# face_recognition
echo "Downloading face_recognition_model..."
curl -L -o models/face_recognition_model-weights_manifest.json "$BASE_URL/face_recognition_model-weights_manifest.json"
curl -L -o models/face_recognition_model-shard1 "$BASE_URL/face_recognition_model-shard1"

echo "Download complete! Models are in the models/ folder."
