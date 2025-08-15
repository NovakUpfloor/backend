import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:waisaka_property_mobile/core/di/service_locator.dart';
import 'package:waisaka_property_mobile/features/admin_dashboard/presentation/bloc/purchase_confirmation_bloc.dart';
import 'package:waisaka_property_mobile/features/user_dashboard/data/models/purchase_history.dart';

class PurchaseConfirmationScreen extends StatelessWidget {
  const PurchaseConfirmationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => sl<PurchaseConfirmationBloc>()..add(FetchConfirmations()),
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Purchase Confirmations'),
        ),
        body: BlocConsumer<PurchaseConfirmationBloc, PurchaseConfirmationState>(
          listener: (context, state) {
            if (state is PurchaseConfirmationUpdateSuccess) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Status updated successfully!'), backgroundColor: Colors.green),
              );
            }
            if (state is PurchaseConfirmationUpdateFailure) {
               ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Failed to update status: ${state.error}'), backgroundColor: Colors.red),
              );
            }
          },
          builder: (context, state) {
            if (state is PurchaseConfirmationLoading) {
              return const Center(child: CircularProgressIndicator());
            }
            if (state is PurchaseConfirmationLoadFailure) {
              return Center(child: Text('Error: ${state.error}'));
            }
            if (state is PurchaseConfirmationLoadSuccess) {
              if (state.confirmations.isEmpty) {
                return const Center(child: Text('No pending confirmations.'));
              }
              return _ConfirmationList(confirmations: state.confirmations);
            }
            return const Center(child: Text('Loading confirmations...'));
          },
        ),
      ),
    );
  }
}

class _ConfirmationList extends StatelessWidget {
  final List<PurchaseHistory> confirmations;
  const _ConfirmationList({required this.confirmations});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: confirmations.length,
      itemBuilder: (context, index) {
        final item = confirmations[index];
        return Card(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Padding(
            padding: const EdgeInsets.all(12.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.packageName, style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 4),
                // Assuming transactionCode contains the user's name for this context
                Text('User: ${item.transactionCode}', style: Theme.of(context).textTheme.bodyMedium),
                Text('Date: ${DateFormat.yMMMd().format(item.purchaseDate)}'),
                if (item.paymentProofUrl != null) ...[
                  const SizedBox(height: 8),
                  InkWell(
                    onTap: () {
                      showDialog(
                        context: context,
                        builder: (_) => Dialog(
                          child: Image.network(item.paymentProofUrl!),
                        ),
                      );
                    },
                    child: const Text('View Payment Proof', style: TextStyle(color: Colors.blue, decoration: TextDecoration.underline)),
                  )
                ],
                const Divider(),
                _ActionButtons(transactionId: item.id, currentStatus: item.status),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _ActionButtons extends StatelessWidget {
  final int transactionId;
  final String currentStatus;
  const _ActionButtons({required this.transactionId, required this.currentStatus});

  @override
  Widget build(BuildContext context) {
    final bloc = context.read<PurchaseConfirmationBloc>();
    return BlocBuilder<PurchaseConfirmationBloc, PurchaseConfirmationState>(
      builder: (context, state) {
        if (state is PurchaseConfirmationUpdateInProgress) {
          return const Center(child: CircularProgressIndicator());
        }
        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          children: [
            TextButton(
              onPressed: currentStatus == 'confirmed' ? null : () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'confirmed')),
              child: const Text('Approve', style: TextStyle(color: Colors.green)),
            ),
            TextButton(
              onPressed: currentStatus == 'rejected' ? null : () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'rejected')),
              child: const Text('Reject', style: TextStyle(color: Colors.red)),
            ),
             TextButton(
              onPressed: currentStatus == 'unverified' ? null : () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'unverified')),
              child: const Text('Unverify', style: TextStyle(color: Colors.orange)),
            ),
          ],
        );
      },
    );
  }
}
