import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/provider_service.dart';

class CreateListingScreen extends StatefulWidget {
  const CreateListingScreen({super.key});

  @override
  State<CreateListingScreen> createState() => _CreateListingScreenState();
}

class _CreateListingScreenState extends State<CreateListingScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleCtrl = TextEditingController();
  final _descCtrl = TextEditingController();
  final _priceCtrl = TextEditingController();

  String _vertical = 'property';
  bool _saving = false;

  static const _verticals = ['property', 'stay', 'vehicle', 'event', 'sme'];

  @override
  void dispose() {
    _titleCtrl.dispose();
    _descCtrl.dispose();
    _priceCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _saving = true);

    try {
      await context.read<ProviderApiService>().createListing({
        'title': _titleCtrl.text.trim(),
        'description': _descCtrl.text.trim(),
        'price': double.parse(_priceCtrl.text.trim()),
        'vertical': _vertical,
        'currency': 'LKR',
        'status': 'pending_verification',
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
                'Listing submitted for verification!'),
            backgroundColor: Color(0xFF00c896),
          ),
        );
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to create listing: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0a0e27),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1a232f),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => context.pop(),
        ),
        title: const Text('Create Listing',
            style:
                TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const _SectionLabel('Category'),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                children: _verticals
                    .map(
                      (v) => ChoiceChip(
                        label: Text(v[0].toUpperCase() + v.substring(1)),
                        selected: _vertical == v,
                        onSelected: (_) => setState(() => _vertical = v),
                        selectedColor:
                            const Color(0xFF00d4ff).withOpacity(0.2),
                        labelStyle: TextStyle(
                          color: _vertical == v
                              ? const Color(0xFF00d4ff)
                              : const Color(0xFF8899aa),
                        ),
                        side: BorderSide(
                          color: _vertical == v
                              ? const Color(0xFF00d4ff)
                              : const Color(0xFF2a3545),
                        ),
                        backgroundColor: const Color(0xFF1a232f),
                      ),
                    )
                    .toList(),
              ),
              const SizedBox(height: 24),
              const _SectionLabel('Title'),
              const SizedBox(height: 8),
              _InputField(
                controller: _titleCtrl,
                hint: 'e.g. Luxury Villa with Ocean Views',
                validator: (v) =>
                    (v?.trim().isEmpty ?? true) ? 'Title is required' : null,
              ),
              const SizedBox(height: 20),
              const _SectionLabel('Description'),
              const SizedBox(height: 8),
              _InputField(
                controller: _descCtrl,
                hint: 'Describe your listing in detail…',
                maxLines: 5,
                validator: (v) =>
                    (v?.trim().isEmpty ?? true) ? 'Description is required' : null,
              ),
              const SizedBox(height: 20),
              const _SectionLabel('Price (LKR)'),
              const SizedBox(height: 8),
              _InputField(
                controller: _priceCtrl,
                hint: 'e.g. 15000',
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v?.trim().isEmpty ?? true) {
                    return 'Price is required';
                  }
                  if (double.tryParse(v!.trim()) == null) {
                    return 'Enter a valid number';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF00d4ff),
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _saving ? null : _submit,
                  child: _saving
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                              color: Colors.black, strokeWidth: 2.5),
                        )
                      : const Text(
                          'Submit for Verification',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                ),
              ),
              const SizedBox(height: 12),
              const Text(
                'Your listing will be reviewed before going live.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF8899aa), fontSize: 12),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel(this.text);

  @override
  Widget build(BuildContext context) {
    return Text(
      text,
      style: const TextStyle(
        color: Colors.white,
        fontWeight: FontWeight.bold,
        fontSize: 14,
      ),
    );
  }
}

class _InputField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final int maxLines;
  final TextInputType keyboardType;
  final String? Function(String?)? validator;

  const _InputField({
    required this.controller,
    required this.hint,
    this.maxLines = 1,
    this.keyboardType = TextInputType.text,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: keyboardType,
      validator: validator,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFF555e6e)),
        filled: true,
        fillColor: const Color(0xFF1a232f),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF2a3545)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFF2a3545)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: Color(0xFF00d4ff), width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.red),
        ),
      ),
    );
  }
}
