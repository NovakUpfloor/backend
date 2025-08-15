class PurchaseHistory {
  final int id;
  final String transactionCode;
  final String packageName;
  final double price;
  final String status;
  final String? paymentProofUrl;
  final DateTime purchaseDate;

  PurchaseHistory({
    required this.id,
    required this.transactionCode,
    required this.packageName,
    required this.price,
    required this.status,
    this.paymentProofUrl,
    required this.purchaseDate,
  });

  factory PurchaseHistory.fromJson(Map<String, dynamic> json) {
    return PurchaseHistory(
      id: json['id'],
      transactionCode: json['kode_transaksi'],
      packageName: json['nama_paket'],
      price: double.tryParse(json['harga'].toString()) ?? 0.0,
      status: json['status_pembayaran'],
      paymentProofUrl: json['bukti_pembayaran'],
      purchaseDate: DateTime.parse(json['tanggal_pembelian']),
    );
  }
}
