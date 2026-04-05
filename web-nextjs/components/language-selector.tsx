'use client';

import { useEffect, useState } from 'react';

const SUPPORTED_LANGUAGES = [
  { code: 'en', label: 'English' },
  { code: 'si', label: 'සිංහල' },
  { code: 'ta', label: 'தமிழ்' },
  { code: 'hi', label: 'हिन्दी' },
  { code: 'ar', label: 'العربية' },
  { code: 'zh', label: '中文' },
  { code: 'fr', label: 'Français' },
  { code: 'de', label: 'Deutsch' },
  { code: 'es', label: 'Español' },
  { code: 'ja', label: '日本語' },
] as const;

const STORAGE_KEY = 'phb_locale';

export default function LanguageSelector() {
  const [locale, setLocale] = useState(() => {
    if (typeof window === 'undefined') {
      return 'en';
    }

    const saved = window.localStorage.getItem(STORAGE_KEY);
    const autoDetected = navigator.language.slice(0, 2);
    const next = saved ?? autoDetected;

    return SUPPORTED_LANGUAGES.some((item) => item.code === next) ? next : 'en';
  });

  useEffect(() => {
    document.documentElement.lang = locale;
  }, [locale]);

  const onChange = (nextLocale: string) => {
    setLocale(nextLocale);
    window.localStorage.setItem(STORAGE_KEY, nextLocale);
    document.documentElement.lang = nextLocale;
    window.dispatchEvent(new CustomEvent('phb-locale-change', { detail: { locale: nextLocale } }));
  };

  return (
    <label className="market-lang-picker" aria-label="Language selector">
      <span>Lang</span>
      <select value={locale} onChange={(event) => onChange(event.target.value)}>
        {SUPPORTED_LANGUAGES.map((item) => (
          <option value={item.code} key={item.code}>
            {item.label}
          </option>
        ))}
      </select>
    </label>
  );
}
