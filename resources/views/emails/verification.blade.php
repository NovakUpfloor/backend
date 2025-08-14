<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email</title>
</head>
<body>
    <h2>Selamat Datang di Waisaka Property!</h2>
    <p>Terima kasih telah mendaftar. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
    <a href="{{ url('verify-email?token=' . $token) }}" 
       style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
       Aktivasi Akun Saya
    </a>
    <p>Jika Anda tidak bisa mengklik tombol di atas, salin dan tempel URL berikut di browser Anda:</p>
    <p>{{ url('verify-email?token=' . $token) }}</p>
    <br>
    <p>Terima kasih,</p>
    <p>Team Waisaka Property</p>
</body>
</html>
