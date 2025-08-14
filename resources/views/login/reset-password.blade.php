<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
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
      <p class="login-box-msg">Anda hanya satu langkah lagi dari password baru Anda, pulihkan password Anda sekarang.</p>

      <form action="{{ url('reset-password') }}" method="post">
      {{ csrf_field() }}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email Anda" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Password Baru" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Ubah Password</button>
          </div>
        </div>
      </form>

      <p class="mt-3 mb-1 text-center">
        <a href="{{ url('login') }}">Login</a>
      </p>
    </div>
  </div>
</div>
<script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
<script>
@if ($message = Session::get('warning'))
swal ( "Oops.." ,  "<?php echo $message ?>" ,  "warning" )
@endif
</script>
</body>
</html>