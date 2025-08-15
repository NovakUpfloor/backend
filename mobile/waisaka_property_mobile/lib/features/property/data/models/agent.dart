class Agent {
  final int id;
  final String nama;
  final String? email;
  final String? telepon;
  final String? gambar;

  Agent({
    required this.id,
    required this.nama,
    this.email,
    this.telepon,
    this.gambar,
  });

  factory Agent.fromJson(Map<String, dynamic> json) {
    return Agent(
      id: json['id_staff'],
      nama: json['nama_staff'] ?? 'N/A',
      email: json['email'],
      telepon: json['telepon'],
      gambar: json['gambar'],
    );
  }
}
