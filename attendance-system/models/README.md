# Face Recognition Models

## Download Instructions

Model face-api.js harus didownload secara manual karena terlalu besar untuk disimpan di GitHub.

### Langkah Download:

1. Download model dari GitHub face-api.js:
   - tiny_face_detector: https://github.com/justadudwohl/face-api.js/raw/master/model/tiny_face_detector_model-weights_manifest.json
   - face_landmark_68: https://github.com/justadudwohl/face-api.js/raw/master/model/face_landmark_68_model-weights_manifest.json
   - face_recognition: https://github.com/justadudwohl/face-api.js/raw/master/model/face_recognition_model-weights_manifest.json

2. Atau clone repository face-api.js:
```bash
git clone https://github.com/justadudwohl/face-api.js.git
cp -r face-api.js/model/* ./models/
```

3. Pastikan file-file ini ada di folder models:
```
models/
├── tiny_face_detector_model-weights_manifest.json
├── tiny_face_detector_model-shard1
├── face_landmark_68_model-weights_manifest.json
├── face_landmark_68_model-shard1
├── face_recognition_model-weights_manifest.json
└── face_recognition_model-shard1
```

4. Setelah didownload, face recognition akan bekerja secara offline tanpa perlu koneksi internet.
