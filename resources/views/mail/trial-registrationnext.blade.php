<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
</head>
<body>
<h1>Selamat, Pendaftaran Anda Berhasil!</h1>
<p>Terima kasih telah mendaftar untuk portal VideaClass.</p>

<p>Berikut adalah informasi login Anda:</p>
<table>
    <tr>
        <td><strong>Email:</strong></td>
        <td>{{ $email }}</td>
    </tr>
    <tr>
        <td><strong>Password:</strong></td>
        <td>{{ $password }}</td>
    </tr>
    <tr>
        <td><strong>Unit Code:</strong></td>
        <td>{{ $unit_code }}</td>
    </tr>
</table>

<p>Gunakan informasi di atas untuk login ke portal VideaClass.</p>

<p>Terima kasih telah memilih VideaClass sebagai solusi ERP Sekolah Anda!</p>
</body>
</html>
