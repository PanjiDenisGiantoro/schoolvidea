# SSL Certificates untuk Nginx Docker

Letakkan SSL certificate files di directory ini:

## Untuk Production (SSL Certificate dari provider):

1. Copy certificate file: `certificate.crt`
2. Copy private key: `private.key`

Contoh:
```bash
cp /path/to/your/sps.videaclass.com.crt ./certificate.crt
cp /path/to/your/sps.videaclass.com.key ./private.key
```

## Untuk Development (Self-Signed Certificate):

Generate self-signed certificate untuk testing:

```bash
# Generate self-signed certificate (valid 1 tahun)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout private.key \
  -out certificate.crt \
  -subj "/C=ID/ST=Jakarta/L=Jakarta/O=Videa/OU=IT/CN=sps.videaclass.com"
```

## File Structure:

```
docker/nginx/ssl/
├── README.md          (this file)
├── certificate.crt    (SSL certificate - add this)
├── private.key        (Private key - add this)
└── .gitignore         (ignore cert files from git)
```

## Notes:

- **JANGAN** commit file certificate dan private key ke git!
- File `.gitignore` sudah dikonfigurasi untuk mengabaikan file `*.crt` dan `*.key`
- Pastikan permission file private key: `chmod 600 private.key`
