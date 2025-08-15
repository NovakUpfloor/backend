class Member {
  final int idStaff;
  final String namaStaff;
  final String email;
  final String statusStaff;
  final int totalKuotaIklan;
  final int sisaKuotaIklan;

  Member({
    required this.idStaff,
    required this.namaStaff,
    required this.email,
    required this.statusStaff,
    required this.totalKuotaIklan,
    required this.sisaKuotaIklan,
  });

  factory Member.fromJson(Map<String, dynamic> json) {
    return Member(
      idStaff: json['id_staff'],
      namaStaff: json['nama_staff'] ?? 'N/A',
      email: json['email'] ?? 'N/A',
      statusStaff: json['status_staff'] ?? 'N/A',
      totalKuotaIklan: json['total_kuota_iklan'] ?? 0,
      sisaKuotaIklan: json['sisa_kuota_iklan'] ?? 0,
    );
  }
}
