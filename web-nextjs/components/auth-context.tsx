'use client';

import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import type { AuthUser } from '../lib/api';

type AuthContextType = {
  user: AuthUser | null;
  token: string | null;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
  logout: () => void;
  loading: boolean;
};

const AuthContext = createContext<AuthContextType | null>(null);

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    try {
      const stored = localStorage.getItem('pearl_auth');
      if (stored) {
        const parsed = JSON.parse(stored) as { user: AuthUser; token: string };
        setUser(parsed.user);
        setToken(parsed.token);
      }
    } catch {
      // ignore parse errors
    }
  }, []);

  const persist = useCallback((u: AuthUser, t: string) => {
    setUser(u);
    setToken(t);
    localStorage.setItem('pearl_auth', JSON.stringify({ user: u, token: t }));
  }, []);

  const login = useCallback(
    async (email: string, password: string) => {
      setLoading(true);
      try {
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password }),
        });
        const data = (await res.json()) as { user?: AuthUser; token?: string; message?: string };
        if (!res.ok) throw new Error(data.message ?? 'Login failed');
        persist(data.user!, data.token!);
      } finally {
        setLoading(false);
      }
    },
    [persist]
  );

  const register = useCallback(
    async (name: string, email: string, password: string, password_confirmation: string) => {
      setLoading(true);
      try {
        const res = await fetch('/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, password, password_confirmation }),
        });
        const data = (await res.json()) as { user?: AuthUser; token?: string; message?: string };
        if (!res.ok) throw new Error(data.message ?? 'Registration failed');
        persist(data.user!, data.token!);
      } finally {
        setLoading(false);
      }
    },
    [persist]
  );

  const logout = useCallback(() => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('pearl_auth');
    fetch('/api/auth/logout', { method: 'POST' }).catch(() => {});
  }, []);

  return <AuthContext.Provider value={{ user, token, login, register, logout, loading }}>{children}</AuthContext.Provider>;
}
