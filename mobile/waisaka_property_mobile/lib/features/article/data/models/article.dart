class Article {
  final int id;
  final String title;
  final String? imageUrl;
  final String? category;
  final DateTime publishedAt;

  Article({
    required this.id,
    required this.title,
    this.imageUrl,
    this.category,
    required this.publishedAt,
  });

  factory Article.fromJson(Map<String, dynamic> json) {
    String? fullImageUrl;
    if (json['gambar'] != null) {
      fullImageUrl = 'https://waisakaproperty.com/assets/upload/image/${json['gambar']}';
    }

    return Article(
      id: json['id_berita'],
      title: json['judul_berita'] ?? 'No Title',
      imageUrl: fullImageUrl,
      category: json['nama_kategori'], // Assuming category name is joined in the API response
      publishedAt: DateTime.tryParse(json['tanggal_publish'] ?? '') ?? DateTime.now(),
    );
  }
}
