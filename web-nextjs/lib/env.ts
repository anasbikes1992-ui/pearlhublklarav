const PROD_API_BASE = 'http://k3atnf4hmmc6gyjvuenzljk1.84.247.149.182.sslip.io/api/v1';
const DEV_API_BASE = 'http://127.0.0.1:8000/api/v1';

export const PUBLIC_API_BASE =
  process.env.NEXT_PUBLIC_API_URL ??
  (process.env.NODE_ENV === 'production' ? PROD_API_BASE : DEV_API_BASE);

export const SERVER_API_BASE = process.env.API_INTERNAL_URL ?? PUBLIC_API_BASE;
