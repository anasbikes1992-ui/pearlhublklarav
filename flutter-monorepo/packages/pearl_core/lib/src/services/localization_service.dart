import 'package:shared_preferences/shared_preferences.dart';

class LocalizationService {
  static const _key = 'phb_locale';
  static const supportedLocales = ['en', 'si', 'ta', 'hi', 'ar', 'zh', 'fr', 'de', 'es', 'ja'];

  Future<String> getLocale() async {
    final prefs = await SharedPreferences.getInstance();
    final locale = prefs.getString(_key) ?? 'en';

    return supportedLocales.contains(locale) ? locale : 'en';
  }

  Future<void> setLocale(String locale) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, supportedLocales.contains(locale) ? locale : 'en');
  }
}
