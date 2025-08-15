class Article {
  final int id;
  final String title;
  final String content;
  final String? imageUrl;
  final DateTime publishedAt;

  Article({
    required this.id,
    required this.title,
    required this.content,
    this.imageUrl,
    required this.publishedAt,
  });

  factory Article.fromJson(Map<String, dynamic> json) {
    return Article(
      id: json['id_berita'],
      title: json['judul_berita'] ?? 'No Title',
      content: json['isi'] ?? '',
      imageUrl: json['gambar'] != null
          ? 'https://waisakaproperty.com/assets/upload/image/${json['gambar']}'
          : null,
      publishedAt: DateTime.parse(json['tanggal_publish']),
    );
  }
}
