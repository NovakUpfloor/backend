@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ asset('admin/paket-iklan/update/'.$paket->id) }}" method="post" accept-charset="utf-8">
{{ csrf_field() }}

<div class="form-group row">
    <label class="col-md-3 text-right">Nama Paket</label>
    <div class="col-md-9">
        <input type="text" name="nama_paket" class="form-control" placeholder="Nama Paket" value="{{ $paket->nama_paket }}" required>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 text-right">Harga Paket (Rp)</label>
    <div class="col-md-9">
        <input type="number" name="harga" class="form-control" placeholder="Contoh: 50000" value="{{ $paket->harga }}" required>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 text-right">Kuota Iklan</label>
    <div class="col-md-9">
        <input type="number" name="kuota_iklan" class="form-control" placeholder="Jumlah Kuota Iklan" value="{{ $paket->kuota_iklan }}" required>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 text-right">Deskripsi</label>
    <div class="col-md-9">
        <textarea name="deskripsi" class="form-control" placeholder="Deskripsi singkat paket">{{ $paket->deskripsi }}</textarea>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 text-right">Status Paket</label>
    <div class="col-md-9">
        <select name="is_active" class="form-control">
            <option value="1" @if($paket->is_active == 1) selected @endif>Aktif</option>
            <option value="0" @if($paket->is_active == 0) selected @endif>Nonaktif</option>
        </select>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3 text-right"></label>
    <div class="col-md-9">
        <div class="btn-group">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-save"></i> Update Data
            </button>
            <a href="{{ asset('admin/paket-iklan') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

</form>
