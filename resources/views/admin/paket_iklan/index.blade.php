<form action="{{ url('admin/paket-iklan/proses') }}" method="post" accept-charset="utf-8">
{{ csrf_field() }}

<div class="row">
  <div class="col-md-12">
    <div class="btn-group">
      <a href="{{ url('admin/paket-iklan/create') }}" class="btn btn-success">
        <i class="fa fa-plus"></i> Tambah Baru
      </a>
    </div>
    <div class="btn-group">
      <button class="btn btn-danger" type="submit" name="hapus" onClick="check();" >
          <i class="fa fa-trash"></i> Hapus Semua Yang Dipilih
      </button> 
    </div>
  </div>
</div>

<div class="clearfix"><hr></div>

<div class="table-responsive mailbox-messages">
  <table id="example1" class="display table table-bordered table-sm" cellspacing="0" width="100%">
    <thead>
      <tr class="bg-info">
        <th width="5%" class="text-center">
          <div class="mailbox-controls">
            <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i></button>
          </div>
        </th>
        <th width="25%">NAMA PAKET</th>
        <th width="20%">HARGA</th>
        <th width="15%">KUOTA IKLAN</th>
        <th width="10%">STATUS</th>
        <th width="10%">ACTION</th>
      </tr>
    </thead>
    <tbody>

      <?php $i=1; foreach($paket_iklan as $paket) { ?>

      <tr class="odd gradeX">
        <td class="text-center">
          <div class="icheck-primary">
            <input type="checkbox" name="id_paket[]" value="<?php echo $paket->id ?>" id="check<?php echo $i ?>">
            <label for="check<?php echo $i ?>"></label>
          </div>
        </td>
        <td><?php echo $paket->nama_paket ?></td>
        <td>Rp <?php echo number_format($paket->harga, 0, ',', '.') ?></td>
        <td><?php echo $paket->kuota_iklan ?> Iklan</td>
        <td>
          <?php if($paket->is_active == 1) { ?>
            <span class="badge bg-success">Aktif</span>
          <?php } else { ?>
            <span class="badge bg-secondary">Nonaktif</span>
          <?php } ?>
        </td>
        <td>
          <div class="btn-group">
            <a href="{{ url('admin/paket-iklan/edit/'.$paket->id) }}" class="btn btn-warning btn-sm">
              <i class="fa fa-edit"></i>
            </a>
            
            {{-- PERBAIKAN DI SINI: Menggunakan url() dan memastikan variabel $paket->id ada --}}
            <a href="{{ url('admin/paket-iklan/delete/'.$paket->id) }}" class="btn btn-danger btn-sm delete-link">
              <i class="fa fa-trash"></i>
            </a>
          </div>
        </td>
      </tr>

      <?php $i++; } ?>

    </tbody>
  </table>
</div>

</form>

<div class="clearfix"><hr></div>
<div class="pull-right">
  {{ $paket_iklan->links() }}
</div>