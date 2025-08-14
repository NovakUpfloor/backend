{{-- ... (kode <style> dan bagian Pilihan Paket & Informasi Pembayaran tidak berubah) ... --}}
<style>
    .pricing-table { display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; }
    .pricing-card { flex: 1; min-width: 280px; max-width: 320px; background: #fff; border: 1px solid #eee; border-radius: 8px; text-align: center; padding: 1.5rem; transition: all 0.3s ease; }
    .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .pricing-card.popular { border-top: 4px solid #007bff; }
    .pricing-card h3 { font-size: 1.25rem; margin-bottom: 0.5rem; }
    .pricing-card .price { font-size: 2rem; font-weight: bold; color: #007bff; margin: 0.75rem 0; }
    .pricing-card .price small { font-size: 0.9rem; color: #6c757d; }
    .pricing-card .quota { font-size: 1rem; margin-bottom: 1.25rem; color: #343a40; }
    .btn-pilih-paket { width: 100%; }
</style>

<div class="pricing-table mb-4">
    @foreach($paket_iklan as $paket)
    <div class="pricing-card @if(strtolower($paket->nama_paket) == 'gold') popular @endif">
        <h3>{{ $paket->nama_paket }}</h3>
        <div class="price">Rp {{ number_format($paket->harga, 0, ',', '.') }}<small>/tahun</small></div>
        <div class="quota">{{ $paket->kuota_iklan }} Kuota Iklan</div>
        <button type="button" class="btn btn-primary btn-pilih-paket" data-toggle="modal" data-target="#orderModal"
                data-id="{{ $paket->id }}" data-nama="{{ $paket->nama_paket }}" data-harga="Rp {{ number_format($paket->harga, 0, ',', '.') }}">
            Pilih Paket
        </button>
    </div>
    @endforeach
</div>

{{-- BAGIAN 2: INFORMASI PEMBAYARAN --}}
<div class="card">
    <div class="card-header">
        <h4>Informasi Pembayaran</h4>
    </div>
    <div class="card-body row">
        <div class="col-md-8">
    
            <ul class="list-group">
                @foreach($rekening as $rek)
                <li class="list-group-item">
                    <strong>{{ $rek->nama_bank }}</strong><br>
                    No. Rekening: {{ $rek->nomor_rekening }}<br>
                    Atas Nama: {{ $rek->atas_nama }}
                </li>
                @endforeach
            </ul>
        </div>
        <div class="col-md-4 text-center">
            <div class="alert alert-light mt-3">
                <p>Setelah membayar, mohon konfirmasi via WhatsApp untuk aktivasi lebih cepat.</p>
                <a href="https://wa.me/{{ $site->telepon }}" class="btn btn-success" target="_blank">
                    <i class="fab fa-whatsapp"></i> Hubungi Admin
                </a>
            </div>
        </div>
    </div>
</div>


{{-- ====================================================== --}}
{{-- POPUP (MODAL) UNTUK FORM PEMESANAN --}}
{{-- ====================================================== --}}
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Formulir Detail Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ url('admin/paket-iklan/proses-pembelian') }}" method="post" enctype="multipart/form-data" accept-charset="utf-8">
                {{ csrf_field() }}

                <input type="hidden" name="paket_id" id="paket_id_input" required>

                <div id="detail-pesanan-modal" class="alert alert-info">
                    Detail paket akan muncul di sini.
                </div>

                {{-- --- AWAL PERBAIKAN DROPDOWN --- --}}
                <div class="form-group">
                    <label>Pilih Member/Staff</label>
                    
                    {{-- Jika yang login adalah User, dropdown akan disabled --}}
                    <select name="staff_id" class="form-control select2" required style="width: 100%;" @if(Session::get('akses_level') == 'User') disabled @endif>
                        
                        @if(Session::get('akses_level') == 'Admin')
                            <option value="">Pilih Member</option>
                        @endif

                        @foreach($staff_list as $staff)
                        <option value="{{ $staff->id_staff }}" selected>{{ $staff->nama_staff }} ({{ $staff->email }})</option>
                        @endforeach

                    </select>

                    {{-- Jika yang login adalah User, tambahkan input hidden untuk mengirim ID staff --}}
                    @if(Session::get('akses_level') == 'User' && $staff_list->isNotEmpty())
                        <input type="hidden" name="staff_id" value="{{ $staff_list->first()->id_staff }}">
                    @endif

                </div>
                {{-- --- AKHIR PERBAIKAN DROPDOWN --- --}}

                <div class="form-group">
                    <label>Unggah Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" class="form-control" required>
                </div>

                <hr>

                <div class="form-group text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Kirim Pesanan
                    </button>
                </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- ... (kode <script> tidak berubah) ... --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menangani event saat modal akan ditampilkan
    $('#orderModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Tombol yang memicu modal
        
        // Ambil data dari atribut data-*
        var paketId = button.data('id');
        var paketNama = button.data('nama');
        var paketHarga = button.data('harga');

        var modal = $(this);
        
        // Update konten modal
        modal.find('.modal-title').text('Konfirmasi Pesanan: ' + paketNama);
        modal.find('#detail-pesanan-modal').html(
            `Anda akan memesan paket <strong>${paketNama}</strong> dengan harga <strong>${paketHarga}</strong>.`
        );
        modal.find('#paket_id_input').val(paketId);
    });
});
</script>
