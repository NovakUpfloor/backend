class User {
  final int idUser;
  final int? idStaff;
  final String nama;
  final String username;
  final String email;
  final String aksesLevel;
  final int sisaKuota;

  User({
    required this.idUser,
    this.idStaff,
    required this.nama,
    required this.username,
    required this.email,
    required this.aksesLevel,
    required this.sisaKuota,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      idUser: json['id_user'],
      idStaff: json['id_staff'],
      nama: json['nama'],
      username: json['username'],
      email: json['email'],
      aksesLevel: json['akses_level'],
      sisaKuota: json['sisa_kuota'] ?? 0,
    );
  }
}
