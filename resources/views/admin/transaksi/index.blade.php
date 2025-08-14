<div class="table-responsive mailbox-messages">
  <table id="example1" class="display table table-bordered table-sm" cellspacing="0" width="100%">
    <thead>
      <tr class="bg-info">
        <th width="5%">No</th>
        <th width="15%">Tanggal Transaksi</th>
        <th width="20%">Nama User</th>
        <th width="20%">Paket yang Dibeli</th>
        <th width="15%">Status</th>
        <th width="20%">Aksi</th>
      </tr>
    </thead>
    <tbody>

      <?php $i=1; foreach($transaksi_pending as $transaksi) { ?>

      <tr class="odd gradeX">
        <td class="text-center"><?php echo $i ?></td>
        <td><?php echo date('d-m-Y H:i', strtotime($transaksi->created_at)) ?></td>
        <td><?php echo $transaksi->nama_user ?></td>
        <td><?php echo $transaksi->nama_paket ?></td>
        <td>
            <span class="badge bg-warning"><?php echo ucfirst($transaksi->status_pembayaran) ?></span>
        </td>
        <td>
          <div class="btn-group">
            <a href="{{ url('admin/transaksi/confirm/'.$transaksi->id) }}" class="btn btn-success btn-sm">
              <i class="fa fa-check"></i> Konfirmasi
            </a>
            <a href="{{ url('admin/transaksi/reject/'.$transaksi->id) }}" class="btn btn-danger btn-sm delete-link">
              <i class="fa fa-times"></i> Tolak
            </a>
          </div>
        </td>
      </tr>

      <?php $i++; } ?>

    </tbody>
  </table>
</div>

<div class="clearfix"><hr></div>
<div class="pull-right">
  {{ $transaksi_pending->links() }}
</div>
