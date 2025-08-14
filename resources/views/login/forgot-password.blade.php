<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ $title }}</title>
  <link rel="shortcut icon" href="{{ asset('assets/upload/image/'.$site->icon) }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/dist/css/adminlte.min.css') }}">
  <script src="{{ asset('assets/sweetalert/js/sweetalert.min.js') }}"></script>
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/sweetalert/css/sweetalert.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card">
    <div class="card-body login-card-body">
        <div class="login-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/upload/image/'.$site->logo) }}" class="img-fluid" style="max-width: 200px;">
            </a>
        </div>
      <p class="login-box-msg">Lupa password Anda? Masukkan email Anda yang terdaftar untuk menerima link reset password.</p>

      <form action="{{ url('forgot-password') }}" method="post">
      {{ csrf_field() }}
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Masukkan Email Anda" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset Password</button>
          </div>
        </div>
      </form>

      <p class="mt-3 mb-1 text-center">
        <a href="{{ url('login') }}">Kembali ke halaman Login</a>
      </p>
    </div>
  </div>
</div>

<script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
<script>
@if ($message = Session::get('warning'))
swal ( "Oops.." ,  "<?php echo $message ?>" ,  "warning" )
@endif

@if ($message = Session::get('sukses'))
swal ( "Berhasil" ,  "<?php echo $message ?>" ,  "success" )
@endif
</script>
</body>
</html>