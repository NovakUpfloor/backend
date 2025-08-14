<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password Akun Waisaka Property Anda</h2>
    <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.</p>
    <p>Silakan klik tombol di bawah ini untuk mereset password Anda:</p>
    <a href="{{ url('reset-password/'.$token) }}" 
       style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
       Reset Password
    </a>
    <p>Link reset password ini hanya valid selama 60 menit.</p>
    <p>Jika Anda tidak merasa melakukan permintaan ini, Anda dapat mengabaikan email ini.</p>
    <br>
    <p>Terima kasih,</p>
    <p>Tim Waisaka Property</p>
</body>
</html>