import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:speech_to_text/speech_to_text.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/gemini/presentation/bloc/gemini_bloc.dart';
import 'package:waisaka_property_mobile/features/property/data/models/agent.dart';
import 'package:waisaka_property_mobile/features/property/data/models/property.dart';
import 'package:waisaka_property_mobile/features/property/presentation/bloc/property_detail_bloc.dart';

class PropertyDetailScreen extends StatelessWidget {
  final String propertyId;

  const PropertyDetailScreen({super.key, required this.propertyId});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider(
          create: (context) =>
              sl<PropertyDetailBloc>()..add(FetchPropertyDetails(propertyId)),
        ),
        BlocProvider(
          create: (context) => sl<GeminiBloc>(),
        ),
      ],
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Property Details'),
        ),
        body: BlocBuilder<PropertyDetailBloc, PropertyDetailState>(
          builder: (context, state) {
            if (state is PropertyDetailLoading) {
              return const Center(child: CircularProgressIndicator());
            }
            if (state is PropertyDetailLoadFailure) {
              return Center(child: Text('Error: ${state.error}'));
            }
            if (state is PropertyDetailLoadSuccess) {
              return _PropertyDetailView(property: state.property);
            }
            return const Center(child: Text('Loading property...'));
          },
        ),
        floatingActionButton: const _GeminiMicButton(),
      ),
    );
  }
}

class _GeminiMicButton extends StatefulWidget {
  const _GeminiMicButton();

  @override
  State<_GeminiMicButton> createState() => _GeminiMicButtonState();
}

class _GeminiMicButtonState extends State<_GeminiMicButton> {
  final SpeechToText _speechToText = SpeechToText();
  bool _isListening = false;

  void _listen(BuildContext context) async {
    if (!_isListening) {
      bool available = await _speechToText.initialize();
      if (available) {
        setState(() => _isListening = true);
        _speechToText.listen(
          onResult: (val) {
            if (val.hasConfidenceRating && val.confidence > 0) {
              context.read<GeminiBloc>().add(SendCommandToGemini(
                    textCommand: val.recognizedWords,
                    pageContext: 'property_detail',
                  ));
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
    return BlocListener<GeminiBloc, GeminiState>(
      listener: (context, state) {
        if (state is GeminiLoading) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Processing command...')),
          );
        }
        if (state is GeminiActionSuccess) {
          final action = state.action['action'];
          final propertyState = context.read<PropertyDetailBloc>().state;
          if (propertyState is PropertyDetailLoadSuccess) {
             _handleAction(action, propertyState.property);
          }
        }
        if (state is GeminiFailure) {
           ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: ${state.error}')),
          );
        }
      },
      child: FloatingActionButton(
        onPressed: () => _listen(context),
        child: Icon(_isListening ? Icons.mic_off : Icons.mic),
        tooltip: 'Voice Command',
      ),
    );
  }

  void _handleAction(String? action, Property property) {
    final agentWhatsapp = property.agent?.telepon?.replaceAll(RegExp(r'[^0-9]'), '') ?? '';
    final agentPhone = property.agent?.telepon ?? '';

    if (agentPhone.isEmpty && (action == 'contact_phone' || action == 'contact_whatsapp')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Agent contact information is not available.')),
      );
      return;
    }

    switch (action) {
      case 'contact_whatsapp':
        _launchUrl('https://wa.me/$agentWhatsapp?text=Halo, saya tertarik dengan properti ${property.namaProperty}');
        break;
      case 'contact_phone':
        _launchUrl('tel:$agentPhone');
        break;
      case 'share_facebook':
         _launchUrl('https://www.facebook.com/sharer/sharer.php?u=https://waisakaproperty.com/properti/${property.id}/${property.namaProperty.replaceAll(' ', '-')}');
        break;
      case 'share_whatsapp':
        final shareText = 'Check out this property: https://waisakaproperty.com/properti/${property.id}/${property.namaProperty.replaceAll(' ', '-')}';
        _launchUrl('https://wa.me/?text=${Uri.encodeComponent(shareText)}');
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Sorry, I did not understand that command.')),
        );
    }
  }

   Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      debugPrint('Could not launch $url');
    }
  }
}

class _PropertyDetailView extends StatelessWidget {
  final Property property;
  const _PropertyDetailView({required this.property});

  @override
  Widget build(BuildContext context) {
    final agentWhatsapp = property.agent?.telepon?.replaceAll(RegExp(r'[^0-9]'), '') ?? '';
    final agentPhone = property.agent?.telepon ?? '';

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (property.gallery.isNotEmpty)
            _ImageCarousel(imageUrls: property.gallery)
          else if (property.gambar != null)
             Image.network(
              'https://waisakaproperty.com/assets/upload/property/${property.gambar}',
              height: 250, width: double.infinity, fit: BoxFit.cover,
            )
          else
            const SizedBox(height: 250, child: Center(child: Icon(Icons.house, size: 60, color: Colors.grey))),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(property.namaProperty, style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                Text(
                  'Rp. ${NumberFormat.decimalPattern('id_ID').format(property.harga ?? 0)}',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(color: Colors.green[800], fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 16),
                _SectionTitle(title: 'Details'),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                     _InfoChip(icon: Icons.bed, label: '${property.kamarTidur ?? 'N/A'} Beds'),
                     _InfoChip(icon: Icons.bathtub, label: '${property.kamarMandi ?? 'N/A'} Baths'),
                     _InfoChip(icon: Icons.square_foot, label: '${property.lb ?? 'N/A'}m²'),
                  ],
                ),
                const SizedBox(height: 16),
                if(property.deskripsi != null && property.deskripsi!.isNotEmpty) ...[
                  _SectionTitle(title: 'Description'),
                  Text(property.deskripsi!, style: Theme.of(context).textTheme.bodyLarge),
                  const SizedBox(height: 24),
                ],
                _SectionTitle(title: 'Listed By'),
                _AgentInfoCard(agent: property.agent),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.call),
                        label: const Text('Call Agent'),
                        onPressed: () => _launchUrl('tel:$agentPhone'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.blue, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 12)),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.chat),
                        label: const Text('WhatsApp'),
                        onPressed: () => _launchUrl('https://wa.me/$agentWhatsapp?text=Halo, saya tertarik dengan properti ${property.namaProperty}'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 12)),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 60),
        ],
      ),
    );
  }

  Future<void> _launchUrl(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      debugPrint('Could not launch $url');
    }
  }
}

class _ImageCarousel extends StatelessWidget {
  final List<String> imageUrls;
  const _ImageCarousel({required this.imageUrls});

  @override
  Widget build(BuildContext context) {
    return CarouselSlider(
      options: CarouselOptions(
        height: 250.0,
        autoPlay: true,
        viewportFraction: 1.0,
        enlargeCenterPage: false,
      ),
      items: imageUrls.map((imageUrl) {
        return Builder(
          builder: (BuildContext context) {
            return Image.network(
              'https://waisakaproperty.com/assets/upload/property/$imageUrl',
              fit: BoxFit.cover,
              width: double.infinity,
               errorBuilder: (_, __, ___) => const Center(child: Icon(Icons.broken_image, size: 50)),
            );
          },
        );
      }).toList(),
    );
  }
}

class _AgentInfoCard extends StatelessWidget {
  final Agent? agent;
  const _AgentInfoCard({this.agent});

  @override
  Widget build(BuildContext context) {
    if (agent == null) {
      return const Card(child: ListTile(title: Text('Agent info not available.')));
    }
    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Row(
          children: [
            CircleAvatar(
              radius: 30,
              backgroundImage: agent!.gambar != null
                  ? NetworkImage('https://waisakaproperty.com/assets/upload/staff/${agent!.gambar}')
                  : null,
              child: agent!.gambar == null ? const Icon(Icons.person, size: 30) : null,
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(agent!.nama, style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text(agent!.email ?? 'No email', style: Theme.of(context).textTheme.bodySmall),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle({required this.title});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0, top: 16.0),
      child: Text(
        title,
        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w600),
      ),
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
      avatar: Icon(icon, size: 18, color: Colors.blue[800]),
      label: Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
      visualDensity: VisualDensity.compact,
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      backgroundColor: Colors.blue[50],
    );
  }
}
