import * as faceapi from 'face-api.js';

export default (wireModel = 'photo') => ({
    stream: null,
    isLoading: true,
    isDetecting: false,
    faceDetected: false,
    faceQuality: null, // Track face quality: null, 'poor', 'good'
    qualityIssues: [], // Array of quality issues detected
    showQualityWarning: false, // Only show warning after stable poor quality
    qualityCheckCount: 0, // Count consecutive poor quality detections
    error: null,
    canvas: null,
    video: null,
    capturedImage: null,

    async init() {
        this.video = this.$refs.video;
        this.canvas = this.$refs.canvas;

        try {
            // Load face-api.js models
            await this.loadModels();

            // Start webcam
            await this.startWebcam();

            // Start face detection
            this.detectFace();

            this.isLoading = false;
        } catch (err) {
            console.error('Initialization error:', err);
            this.error = this.getErrorMessage(err);
            this.isLoading = false;
        }
    },

    async loadModels() {
        const MODEL_URL = '/models';

        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL),
        ]);
    },

    async startWebcam() {
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: 'user'
                },
                audio: false
            });

            this.video.srcObject = this.stream;

            return new Promise((resolve) => {
                this.video.onloadedmetadata = () => {
                    this.video.play();
                    resolve();
                };
            });
        } catch (err) {
            throw new Error('camera_access_denied');
        }
    },

    async detectFace() {
        if (!this.video || this.video.paused || this.video.ended) {
            return;
        }

        this.isDetecting = true;

        try {
            const detections = await faceapi
                .detectAllFaces(this.video, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.5
                }))
                .withFaceLandmarks()
                .withFaceExpressions();

            // Clear canvas
            const ctx = this.canvas.getContext('2d');
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            // Update canvas size to match video
            this.canvas.width = this.video.videoWidth;
            this.canvas.height = this.video.videoHeight;

            if (detections.length === 0) {
                this.faceDetected = false;
                this.faceQuality = null;
                this.qualityIssues = [];
                this.showQualityWarning = false;
                this.qualityCheckCount = 0;
                this.error = null;
            } else if (detections.length > 1) {
                this.faceDetected = false;
                this.faceQuality = null;
                this.qualityIssues = [];
                this.showQualityWarning = false;
                this.qualityCheckCount = 0;
                this.error = 'multiple_faces';
                this.drawDetections(detections, 'red');
            } else {
                // Single face detected - check quality
                const detection = detections[0];
                const quality = this.checkFaceQuality(detection);

                this.faceDetected = true;
                this.faceQuality = quality.isGood ? 'good' : 'poor';
                this.qualityIssues = quality.issues;
                this.error = null;

                // Only show quality warning after 10 consecutive poor detections (1 second)
                if (!quality.isGood) {
                    this.qualityCheckCount++;
                    if (this.qualityCheckCount >= 10) {
                        this.showQualityWarning = true;
                    }
                } else {
                    this.qualityCheckCount = 0;
                    this.showQualityWarning = false;
                }

                // Draw with color based on quality
                const color = quality.isGood ? 'green' : 'orange';
                this.drawDetections(detections, color);
            }
        } catch (err) {
            console.error('Detection error:', err);
        }

        this.isDetecting = false;

        // Continue detecting
        setTimeout(() => this.detectFace(), 100);
    },

    checkFaceQuality(detection) {
        const issues = [];

        // 1. Check face size (should be large enough)
        const box = detection.detection.box;
        const videoArea = this.video.videoWidth * this.video.videoHeight;
        const faceArea = box.width * box.height;
        const faceRatio = faceArea / videoArea;

        if (faceRatio < 0.1) {
            issues.push('face_too_small');
        } else if (faceRatio > 0.7) {
            issues.push('face_too_close');
        }

        // 2. Check if face is centered
        const faceCenterX = box.x + box.width / 2;
        const faceCenterY = box.y + box.height / 2;
        const videoCenterX = this.video.videoWidth / 2;
        const videoCenterY = this.video.videoHeight / 2;

        const offsetX = Math.abs(faceCenterX - videoCenterX) / this.video.videoWidth;
        const offsetY = Math.abs(faceCenterY - videoCenterY) / this.video.videoHeight;

        if (offsetX > 0.2 || offsetY > 0.2) {
            issues.push('face_not_centered');
        }

        // 3. Check face angle using landmarks (frontal check)
        if (detection.landmarks) {
            const landmarks = detection.landmarks.positions;

            // Compare left and right eye positions to check if face is frontal
            const leftEye = landmarks[36]; // Left eye outer corner
            const rightEye = landmarks[45]; // Right eye outer corner
            const nose = landmarks[30]; // Nose tip

            // Calculate if nose is roughly centered between eyes
            const eyeMidpoint = (leftEye.x + rightEye.x) / 2;
            const noseOffset = Math.abs(nose.x - eyeMidpoint);
            const eyeDistance = Math.abs(rightEye.x - leftEye.x);

            if (noseOffset / eyeDistance > 0.15) {
                issues.push('face_not_frontal');
            }
        }

        // 4. Check detection confidence
        const confidence = detection.detection.score;
        if (confidence < 0.7) {
            issues.push('low_confidence');
        }

        // Face is good quality if no critical issues
        return {
            isGood: issues.length === 0,
            issues: issues,
            confidence: confidence
        };
    },

    drawDetections(detections, color) {
        const resizedDetections = faceapi.resizeResults(detections, {
            width: this.video.videoWidth,
            height: this.video.videoHeight
        });

        const ctx = this.canvas.getContext('2d');

        resizedDetections.forEach(detection => {
            const box = detection.detection.box;

            // Draw rectangle
            ctx.strokeStyle = color;
            ctx.lineWidth = 3;
            ctx.strokeRect(box.x, box.y, box.width, box.height);

            // Draw landmarks
            if (detection.landmarks) {
                const landmarks = detection.landmarks.positions;
                ctx.fillStyle = color;
                landmarks.forEach(point => {
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, 2, 0, 2 * Math.PI);
                    ctx.fill();
                });
            }
        });
    },

    async capture() {
        if (!this.faceDetected) {
            this.error = 'no_face_detected';
            return;
        }

        // Check face quality before capturing
        if (this.faceQuality === 'poor') {
            this.error = 'poor_face_quality';
            return;
        }

        try {
            // Create a temporary canvas for capture
            const captureCanvas = document.createElement('canvas');
            captureCanvas.width = this.video.videoWidth;
            captureCanvas.height = this.video.videoHeight;

            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(this.video, 0, 0);

            // Convert to blob
            const blob = await new Promise(resolve => {
                captureCanvas.toBlob(resolve, 'image/jpeg', 0.95);
            });

            // Validate image dimensions and size
            const validation = await this.validateImage(blob);
            if (!validation.valid) {
                this.error = validation.error;
                return;
            }

            // Final face detection on captured image to ensure quality
            const finalCheck = await this.performFinalFaceCheck(captureCanvas);
            if (!finalCheck.valid) {
                this.error = finalCheck.error;
                return;
            }

            // Create File object
            const file = new File([blob], 'face-capture.jpg', { type: 'image/jpeg' });

            // Show preview
            this.capturedImage = URL.createObjectURL(blob);

            // Upload to Livewire
            this.$wire.upload(wireModel, file, () => {
                // Success callback
                this.stopWebcam();
            }, (error) => {
                // Error callback
                console.error('Upload error:', error);
                this.error = 'upload_failed';
            });

        } catch (err) {
            console.error('Capture error:', err);
            this.error = 'capture_failed';
        }
    },

    async performFinalFaceCheck(canvas) {
        try {
            // Detect face in captured image
            const detections = await faceapi
                .detectAllFaces(canvas, new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416,
                    scoreThreshold: 0.5
                }))
                .withFaceLandmarks();

            // Ensure exactly one face
            if (detections.length === 0) {
                return { valid: false, error: 'no_face_in_capture' };
            }

            if (detections.length > 1) {
                return { valid: false, error: 'multiple_faces_in_capture' };
            }

            // Check quality of detected face
            const quality = this.checkFaceQuality(detections[0]);
            if (!quality.isGood) {
                return { valid: false, error: 'poor_face_quality' };
            }

            return { valid: true };
        } catch (err) {
            console.error('Final face check error:', err);
            return { valid: false, error: 'face_check_failed' };
        }
    },

    async validateImage(blob) {
        // Check file size (max 2MB)
        if (blob.size > 2 * 1024 * 1024) {
            return { valid: false, error: 'file_too_large' };
        }

        // Load image to check dimensions
        const img = await this.loadImage(blob);

        const width = img.width;
        const height = img.height;

        // Check dimensions (48x48 to 4096x4096)
        if (width < 48 || height < 48) {
            return { valid: false, error: 'image_too_small' };
        }

        if (width > 4096 || height > 4096) {
            return { valid: false, error: 'image_too_large' };
        }

        return { valid: true };
    },

    loadImage(blob) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = URL.createObjectURL(blob);
        });
    },

    retake() {
        this.capturedImage = null;
        this.error = null;
        this.startWebcam().then(() => {
            this.detectFace();
        });
    },

    stopWebcam() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    },

    getErrorMessage(err) {
        if (err.message === 'camera_access_denied') {
            return 'camera_access_denied';
        }
        return 'unknown_error';
    },

    destroy() {
        this.stopWebcam();
        if (this.capturedImage) {
            URL.revokeObjectURL(this.capturedImage);
        }
    }
});
