import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';
import 'package:waisaka_property_mobile/features/home/presentation/bloc/home_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<HomeBloc>()..add(HomeDataFetched()),
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Waisaka Property'),
          actions: [
            TextButton(
              onPressed: () => context.go('/login'),
              child: const Text('Login', style: TextStyle(color: Colors.white)),
            )
          ],
        ),
        body: BlocBuilder<HomeBloc, HomeState>(
          builder: (context, state) {
            if (state is HomeLoading) {
              return const Center(child: CircularProgressIndicator());
            }
            if (state is HomeLoadFailure) {
              return Center(
                child: Text('Failed to load data: ${state.error}'),
              );
            }
            if (state is HomeLoadSuccess) {
              return _HomeContentView(
                properties: state.properties,
                articles: state.articles,
              );
            }
            return const Center(child: Text('Welcome!'));
          },
        ),
      ),
    );
  }
}

class _HomeContentView extends StatelessWidget {
  final List<Property> properties;
  final List<Article> articles;

  const _HomeContentView({required this.properties, required this.articles});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(8.0),
      children: [
        _SectionHeader(title: 'Latest Properties'),
        if (properties.isEmpty)
          const Center(child: Padding(
            padding: EdgeInsets.all(16.0),
            child: Text('No properties found.'),
          ))
        else
          _PropertyList(properties: properties),

        const SizedBox(height: 16),
        _SectionHeader(title: 'News & Updates'),
        if (articles.isEmpty)
           const Center(child: Padding(
            padding: EdgeInsets.all(16.0),
            child: Text('No articles found.'),
          ))
        else
          _ArticleList(articles: articles),
      ],
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  const _SectionHeader({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0, horizontal: 8.0),
      child: Text(
        title,
        style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
      ),
    );
  }
}

class _PropertyList extends StatelessWidget {
  final List<Property> properties;
  const _PropertyList({required this.properties});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: properties.length,
      itemBuilder: (context, index) {
        final property = properties[index];
        return Card(
          elevation: 2.0,
          margin: const EdgeInsets.symmetric(vertical: 8.0),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: () => context.go('/property/${property.id}'),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (property.gambar != null)
                  Image.network(
                    'https://waisakaproperty.com/assets/upload/property/${property.gambar}',
                    height: 200,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const SizedBox(height: 200, child: Center(child: Icon(Icons.broken_image, size: 40))),
                    loadingBuilder: (_, child, progress) => progress == null ? child : const SizedBox(height: 200, child: Center(child: CircularProgressIndicator())),
                  )
                else
                  const SizedBox(height: 200, child: Center(child: Icon(Icons.house, size: 40, color: Colors.grey))),

                Padding(
                  padding: const EdgeInsets.all(12.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        property.namaProperty,
                        style: Theme.of(context).textTheme.titleLarge,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4.0),
                      Text(
                        'Rp. ${NumberFormat.decimalPattern('id_ID').format(property.harga ?? 0)}',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(color: Colors.green[800], fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8.0),
                       Row(
                        children: [
                          const Icon(Icons.location_on, size: 16, color: Colors.grey),
                          const SizedBox(width: 4),
                          Expanded(child: Text('${property.namaKabupaten}, ${property.namaProvinsi}', style: Theme.of(context).textTheme.bodySmall)),
                        ],
                      ),
                      const Divider(height: 20.0),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          _InfoChip(icon: Icons.bed, label: '${property.kamarTidur ?? 0}'),
                          _InfoChip(icon: Icons.bathtub, label: '${property.kamarMandi ?? 0}'),
                          _InfoChip(icon: Icons.square_foot, label: '${property.lb ?? 0}m²'),
                          _InfoChip(icon: Icons.area_chart, label: '${property.lt ?? 0}m²'),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _ArticleList extends StatelessWidget {
  final List<Article> articles;
  const _ArticleList({required this.articles});

  @override
  Widget build(BuildContext context) {
     return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: articles.length,
      itemBuilder: (context, index) {
        final article = articles[index];
        return Card(
          margin: const EdgeInsets.symmetric(vertical: 8.0),
          child: ListTile(
            leading: article.imageUrl != null
              ? Image.network(article.imageUrl!, width: 100, fit: BoxFit.cover, errorBuilder: (_,__,___) => const SizedBox(width:100, child: Icon(Icons.image_not_supported)))
              : const SizedBox(width: 100, child: Icon(Icons.article)),
            title: Text(article.title, maxLines: 2, overflow: TextOverflow.ellipsis),
            subtitle: Text(DateFormat.yMMMd().format(article.publishedAt)),
            onTap: () { /* TODO: Navigate to article detail */ },
          ),
        );
      },
    );
  }
}

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;
  const _InfoChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Chip(
      avatar: Icon(icon, size: 16, color: Colors.blue[800]),
      label: Text(label),
      visualDensity: VisualDensity.compact,
      padding: EdgeInsets.zero,
      backgroundColor: Colors.blue[50],
    );
  }
}
