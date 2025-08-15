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
            padding: const EdgeInsets.all(8.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.packageName, style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 4),
                Text('User: ${item.transactionCode}'), // Assuming transactionCode holds user name for now
                Text('Date: ${DateFormat.yMMMd().format(item.purchaseDate)}'),
                if (item.paymentProofUrl != null) ...[
                  const SizedBox(height: 8),
                  InkWell(
                    onTap: () { /* TODO: Show image in a dialog */ },
                    child: const Text('View Payment Proof', style: TextStyle(color: Colors.blue, decoration: TextDecoration.underline)),
                  )
                ],
                const Divider(),
                _ActionButtons(transactionId: item.id),
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
  const _ActionButtons({required this.transactionId});

  @override
  Widget build(BuildContext context) {
    final bloc = context.read<PurchaseConfirmationBloc>();
    return BlocBuilder<PurchaseConfirmationBloc, PurchaseConfirmationState>(
      builder: (context, state) {
        if (state is PurchaseConfirmationUpdateInProgress) {
          return const Center(child: CircularProgressIndicator());
        }
        return Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            ElevatedButton(
              onPressed: () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'confirmed')),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
              child: const Text('Approve'),
            ),
            ElevatedButton(
              onPressed: () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'rejected')),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
              child: const Text('Reject'),
            ),
             ElevatedButton(
              onPressed: () => bloc.add(UpdateConfirmationStatus(transactionId: transactionId, status: 'unverified')),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
              child: const Text('Unverify'),
            ),
          ],
        );
      },
    );
  }
}
