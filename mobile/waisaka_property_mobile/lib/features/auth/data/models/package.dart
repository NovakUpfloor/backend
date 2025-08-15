class AdPackage {
  final int id;
  final String name;
  final double price;
  final int adQuota;
  final String? description;

  AdPackage({
    required this.id,
    required this.name,
    required this.price,
    required this.adQuota,
    this.description,
  });

  factory AdPackage.fromJson(Map<String, dynamic> json) {
    return AdPackage(
      id: json['id'],
      name: json['nama_paket'] ?? 'Unnamed Package',
      price: double.tryParse(json['harga'].toString()) ?? 0.0,
      adQuota: json['kuota_iklan'] ?? 0,
      description: json['deskripsi'],
    );
  }
}
