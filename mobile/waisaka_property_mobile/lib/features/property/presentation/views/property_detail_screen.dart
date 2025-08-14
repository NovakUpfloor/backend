import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:speech_to_text/speech_to_text.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/gemini/presentation/bloc/gemini_bloc.dart';
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
      bool available = await _speechToText.initialize(
        onStatus: (val) => debugPrint('onStatus: $val'),
        onError: (val) => debugPrint('onError: $val'),
      );
      if (available) {
        setState(() => _isListening = true);
        _speechToText.listen(
          onResult: (val) {
            if (val.hasConfidenceRating && val.confidence > 0) {
              context.read<GeminiBloc>().add(SendCommandToGemini(
                    textCommand: val.recognizedWords,
                    pageContext: 'property_detail',
                  ));
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
            const SnackBar(content: Text('Listening...')),
          );
        }
        if (state is GeminiActionSuccess) {
          final action = state.action['action'];
          // Find the property from the other BLoC to use its data
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
    const agentWhatsapp = '6281292758175';
    const agentPhone = '081292758175';

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
    const agentWhatsapp = '6281292758175';
    const agentPhone = '081292758175';

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (property.gambar != null)
            Image.network(
              'https://waisakaproperty.com/assets/upload/property/${property.gambar}',
              height: 250,
              width: double.infinity,
              fit: BoxFit.cover,
            ),
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
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                     _InfoChip(icon: Icons.bed, label: '${property.kamarTidur ?? 'N/A'} Beds'),
                     _InfoChip(icon: Icons.bathtub, label: '${property.kamarMandi ?? 'N/A'} Baths'),
                     _InfoChip(icon: Icons.square_foot, label: '${property.lb ?? 'N/A'}m²'),
                  ],
                ),
                const Divider(),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.call),
                        label: const Text('Call Agent'),
                        onPressed: () => _launchUrl('tel:$agentPhone'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.blue, foregroundColor: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: ElevatedButton.icon(
                        icon: const Icon(Icons.chat),
                        label: const Text('WhatsApp'),
                        onPressed: () => _launchUrl('https://wa.me/$agentWhatsapp?text=Halo, saya tertarik dengan properti ${property.namaProperty}'),
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                     Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.facebook),
                        label: const Text('Share'),
                        onPressed: () => _launchUrl('https://www.facebook.com/sharer/sharer.php?u=https://waisakaproperty.com/properti/${property.id}/${property.namaProperty.replaceAll(' ', '-')}'),
                      ),
                    ),
                  ],
                )
              ],
            ),
          ),
          const SizedBox(height: 60), // Space for the FAB
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
