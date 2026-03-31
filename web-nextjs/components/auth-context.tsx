'use client';

import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import type { AuthUser } from '../lib/api';

type AuthContextType = {
  user: AuthUser | null;
  login: (email: string, password: string) => Promise<void>;
  register: (fullName: string, email: string, password: string, passwordConfirmation: string) => Promise<void>;
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
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    let mounted = true;

    // Hydrate auth state from secure httpOnly cookie via server route.
    const loadCurrentUser = async () => {
      try {
        const res = await fetch('/api/auth/me', {
          method: 'GET',
          credentials: 'same-origin',
        });

        if (!res.ok) {
          if (mounted) {
            setUser(null);
          }
          return;
        }

        const data = (await res.json()) as { user?: AuthUser };
        if (mounted) {
          setUser(data.user ?? null);
        }
      } catch {
        if (mounted) {
          setUser(null);
        }
      }
    };

    loadCurrentUser();

    return () => {
      mounted = false;
    };
  }, []);

  const persist = useCallback((u: AuthUser) => {
    setUser(u);
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
        const data = (await res.json()) as {
          data?: { user?: AuthUser; token?: string };
          user?: AuthUser;
          token?: string;
          message?: string;
        };
        if (!res.ok) throw new Error(data.message ?? 'Login failed');
        // Cookie is set server-side. Client stores only user profile in memory.
        const u = data.data?.user ?? data.user;
        if (!u) throw new Error('Invalid response from server');
        persist(u);
      } finally {
        setLoading(false);
      }
    },
    [persist]
  );

  const register = useCallback(
    async (fullName: string, email: string, password: string, password_confirmation: string) => {
      setLoading(true);
      try {
        const res = await fetch('/api/auth/register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          // Laravel expects full_name, not name
          body: JSON.stringify({ full_name: fullName, email, password, password_confirmation }),
        });
        const data = (await res.json()) as {
          data?: { user?: AuthUser; token?: string };
          user?: AuthUser;
          token?: string;
          message?: string;
        };
        if (!res.ok) throw new Error(data.message ?? 'Registration failed');
        const u = data.data?.user ?? data.user;
        if (!u) throw new Error('Invalid response from server');
        persist(u);
      } finally {
        setLoading(false);
      }
    },
    [persist]
  );

  const logout = useCallback(() => {
    setUser(null);
    fetch('/api/auth/logout', {
      method: 'POST',
      credentials: 'same-origin',
    }).catch(() => {});
  }, []);

  return (
    <AuthContext.Provider value={{ user, login, register, logout, loading }}>
      {children}
    </AuthContext.Provider>
  );
}
