<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Sign Up Member Baru</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/admin/dist/css/adminlte.min.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition login-page" style="background-color: #007bff;">
<div class="login-box" style="width: 500px;">
  <div class="login-logo">
    <a href="{{ asset('/') }}" class="text-white"><b>WAISAKA</b>PROPERTY</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Daftar sebagai Member Baru</p>

      @if ($errors->any())
          <div class="alert alert-danger">
              <ul>
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif

      <form action="{{ asset('signup/proses') }}" enctype="multipart/form-data" method="post" accept-charset="utf-8">
      {{ csrf_field() }}

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right">Nama lengkap</label>
            <div class="col-sm-8">
                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" value="{{ old('nama') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right">Email</label>
            <div class="col-sm-8">
                <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
            </div>
        </div>				

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right">Username</label>
            <div class="col-sm-8">
                <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right">Password</label>
            <div class="col-sm-8">
                <input type="password" name="password" class="form-control" placeholder="Password" value="{{ old('password') }}" required>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right">Upload foto profil</label>
            <div class="col-sm-8">
                <input type="file" name="gambar" class="form-control" placeholder="Upload Foto" value="{{ old('gambar') }}">
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 control-label text-right"></label>
            <div class="col-sm-8">
                <button type="submit" class="btn btn-primary btn-block">Daftar Sekarang</button>
            </div>
        </div>
      </form>

      <hr>
      <p class="mb-0 text-center">
        <a href="{{ asset('login') }}">Sudah punya akun? Login di sini</a>
      </p>

    </div>
  </div>
</div>
<!-- jQuery -->
<script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
