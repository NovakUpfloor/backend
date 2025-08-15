import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:speech_to_text/speech_to_text.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/article/data/models/article.dart';
import 'package:waisaka_property_mobile/features/article/presentation/views/article_detail_screen.dart';
import 'package:waisaka_property_mobile/features/home/presentation/bloc/home_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<HomeBloc>()..add(HomeDataFetched()),
      child: BlocListener<HomeBloc, HomeState>(
        listener: (context, state) {
          if (state is HomeNavigateToSearch) {
            final location = state.location ?? 'anywhere';
            final type = state.type ?? 'any type';
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Searching for $type in $location...')),
            );
          }
        },
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
                return Center(child: Text('Failed to load data: ${state.error}'));
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
          floatingActionButton: const _HomeGeminiMicButton(),
        ),
      ),
    );
  }
}

class _HomeGeminiMicButton extends StatefulWidget {
  const _HomeGeminiMicButton();

  @override
  State<_HomeGeminiMicButton> createState() => _HomeGeminiMicButtonState();
}

class _HomeGeminiMicButtonState extends State<_HomeGeminiMicButton> {
  final SpeechToText _speechToText = SpeechToText();
  bool _isListening = false;

  void _listen(BuildContext context) async {
    if (!_isListening) {
      bool available = await _speechToText.initialize();
      if (available) {
        setState(() => _isListening = true);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Listening...')),
        );
        _speechToText.listen(
          onResult: (val) {
            if (val.hasConfidenceRating && val.confidence > 0) {
              context.read<HomeBloc>().add(HomeVoiceCommandReceived(val.recognizedWords));
              setState(() => _isListening = false);
              _speechToText.stop();
            }
          },
        );
      }
    } else {
      setState(() => _isListening = false);
      _speechToText.stop();
    }
  }

  @override
  Widget build(BuildContext context) {
    return FloatingActionButton(
      onPressed: () => _listen(context),
      child: Icon(_isListening ? Icons.mic_off : Icons.mic),
      tooltip: 'Search with Voice',
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
        _SectionHeader(
          title: 'Latest Properties',
          onSeeAll: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Property list screen coming soon!')),
            );
          },
        ),
        if (properties.isEmpty)
          const Center(child: Padding(padding: EdgeInsets.all(16.0), child: Text('No properties found.')))
        else
          _PropertyList(properties: properties),
        const SizedBox(height: 16),
        _SectionHeader(
          title: 'News & Updates',
          onSeeAll: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Article list screen coming soon!')),
            );
          },
        ),
        if (articles.isEmpty)
          const Center(child: Padding(padding: EdgeInsets.all(16.0), child: Text('No articles found.')))
        else
          _ArticleList(articles: articles),
      ],
    );
  }
}

class _SectionHeader extends StatelessWidget {
  final String title;
  final VoidCallback? onSeeAll;

  const _SectionHeader({required this.title, this.onSeeAll});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0, horizontal: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title,
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
          ),
          if (onSeeAll != null)
            TextButton(
              onPressed: onSeeAll,
              child: const Text('See All'),
            )
        ],
      ),
    );
  }
}

class _PropertyList extends StatelessWidget {
  final List<Property> properties;
  const _PropertyList({required this.properties});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 300,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: properties.length,
        itemBuilder: (context, index) {
          final property = properties[index];
          return SizedBox(
            width: 250,
            child: Card(
              elevation: 2.0,
              margin: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
              clipBehavior: Clip.antiAlias,
              child: InkWell(
                onTap: () => context.go('/property/${property.id}'),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (property.gambar != null)
                      Image.network(
                        'https://waisakaproperty.com/assets/upload/property/${property.gambar}',
                        height: 150,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const SizedBox(height: 150, child: Center(child: Icon(Icons.broken_image))),
                        loadingBuilder: (_, child, progress) => progress == null ? child : const SizedBox(height: 150, child: Center(child: CircularProgressIndicator())),
                      )
                    else
                      const SizedBox(height: 150, child: Center(child: Icon(Icons.house, size: 40, color: Colors.grey))),
                    Padding(
                      padding: const EdgeInsets.all(8.0),
                      child: Text(
                        property.namaProperty,
                        style: Theme.of(context).textTheme.titleMedium,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const Spacer(),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
                      child: Text(
                        'Rp. ${NumberFormat.decimalPattern('id_ID').format(property.harga ?? 0)}',
                        style: Theme.of(context).textTheme.titleSmall?.copyWith(color: Colors.green[800], fontWeight: FontWeight.bold),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(8.0, 0, 8.0, 8.0),
                      child: Row(
                        children: [
                          const Icon(Icons.location_on, size: 14, color: Colors.grey),
                          const SizedBox(width: 4),
                          Expanded(child: Text('${property.namaKabupaten}', style: Theme.of(context).textTheme.bodySmall, overflow: TextOverflow.ellipsis)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
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
                ? Image.network(article.imageUrl!, width: 100, fit: BoxFit.cover, errorBuilder: (_, __, ___) => const SizedBox(width: 100, child: Icon(Icons.image_not_supported)))
                : const SizedBox(width: 100, child: Icon(Icons.article)),
            title: Text(article.title, maxLines: 2, overflow: TextOverflow.ellipsis),
            subtitle: Text(DateFormat.yMMMd().format(article.publishedAt)),
            onTap: () => context.go('/article', extra: article),
          ),
        );
      },
    );
  }
}
