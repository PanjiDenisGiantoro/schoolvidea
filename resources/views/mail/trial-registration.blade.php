<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
    <style>
        .button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<h1>Selamat, Pendaftaran Anda Berhasil!</h1>
<p>Terima kasih telah mendaftar untuk portal VideaClass.</p>
<p>Berikut adalah informasi pendaftaran Anda:</p>
<table>
    <tr>
        <td><strong>Nama Sekolah:</strong></td>
        <td>{{ $trialRegistration->school_name }}</td>
    </tr>
    <tr>
        <td><strong>NPSN Sekolah:</strong></td>
        <td>{{ $trialRegistration->npsn }}</td>
    </tr>
    <tr>
        <td><strong>Alamat:</strong></td>
        <td>{{ $trialRegistration->address }}</td>
    </tr>
    <tr>
        <td><strong>Nama Lengkap:</strong></td>
        <td>{{ $trialRegistration->full_name }}</td>
    </tr>
    <tr>
        <td><strong>Email:</strong></td>
        <td>{{ $trialRegistration->email }}</td>
    </tr>
    <tr>
        <td><strong>No HP:</strong></td>
        <td>{{ $trialRegistration->no_hp }}</td>
    </tr>
</table>

<p>Selamat datang di VideaClass! Kami sangat senang Anda bergabung bersama kami. Anda sekarang dapat memulai pengalaman Anda dengan mengatur portal sekolah Anda.</p>

<!-- Button Setup Portal -->
<p>
    <a href="{{ $setupPortalUrl }}" class="button">Setup Portal →</a>
</p>

<p>Terima kasih telah memilih VideaClass sebagai solusi ERP Sekolah Anda. Kami berkomitmen untuk memberikan layanan terbaik kepada Anda.</p>

<p>Selamat berpetualang di VideaClass, dan semoga aplikasi ini membantu sekolah Anda tumbuh dan berkembang!</p>
</body>
</html>
