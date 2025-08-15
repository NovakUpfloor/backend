import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/models/purchase_history.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/presentation/bloc/purchase_history_bloc.dart';

class PurchaseHistoryScreen extends StatelessWidget {
  const PurchaseHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<PurchaseHistoryBloc>()..add(FetchPurchaseHistory()),
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Purchase History'),
        ),
        body: BlocBuilder<PurchaseHistoryBloc, PurchaseHistoryState>(
          builder: (context, state) {
            if (state is PurchaseHistoryLoading) {
              return const Center(child: CircularProgressIndicator());
            }
            if (state is PurchaseHistoryLoadFailure) {
              return Center(child: Text('Error: ${state.error}'));
            }
            if (state is PurchaseHistoryLoadSuccess) {
              if (state.history.isEmpty) {
                return const Center(child: Text('You have no purchase history.'));
              }
              return _HistoryList(history: state.history);
            }
            return const Center(child: Text('Loading history...'));
          },
        ),
      ),
    );
  }
}

class _HistoryList extends StatelessWidget {
  final List<PurchaseHistory> history;
  const _HistoryList({required this.history});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: history.length,
      itemBuilder: (context, index) {
        final item = history[index];
        return Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: ListTile(
            title: Text(item.packageName, style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Date: ${DateFormat.yMMMd().format(item.purchaseDate)}'),
                Text('Price: Rp. ${NumberFormat.decimalPattern('id_ID').format(item.price)}'),
                Text('Code: ${item.transactionCode}'),
              ],
            ),
            trailing: _StatusChip(status: item.status),
          ),
        );
      },
    );
  }
}

class _StatusChip extends StatelessWidget {
  final String status;
  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    Color chipColor;
    String chipText;
    switch (status) {
      case 'confirmed':
        chipColor = Colors.green;
        chipText = 'Confirmed';
        break;
      case 'rejected':
        chipColor = Colors.red;
        chipText = 'Rejected';
        break;
      case 'unverified':
        chipColor = Colors.orange;
        chipText = 'Unverified';
        break;
      default:
        chipColor = Colors.grey;
        chipText = 'Pending';
    }
    return Chip(
      label: Text(chipText),
      backgroundColor: chipColor.withOpacity(0.2),
      labelStyle: TextStyle(color: chipColor),
    );
  }
}
