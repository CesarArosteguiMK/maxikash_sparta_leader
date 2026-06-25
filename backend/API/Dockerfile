# API Verificación Documentos Mexicanos - imagen autocontenida (Tesseract + RapidOCR + deps)
FROM python:3.11-slim

# Dependencias de sistema: Tesseract OCR, idioma español, OpenCV, pyzbar
RUN apt-get update && apt-get install -y \
    tesseract-ocr \
    tesseract-ocr-spa \
    libgl1 \
    libglib2.0-0 \
    libsm6 \
    libxext6 \
    libxrender-dev \
    libzbar0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Requirements primero (mejor cache)
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Código de la API
COPY . .

RUN mkdir -p logs /tmp/doc_verificacion

EXPOSE 8001

# Healthcheck sin depender de curl en PATH fuera del container; usa Python
HEALTHCHECK --interval=30s --timeout=10s --start-period=15s --retries=3 \
    CMD python -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8001/api/v1/health')" || exit 1

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8001", "--workers", "2"]
