import { Platform, PlatformConfig } from '../types';

export const PLATFORM_CONFIGS: Record<Platform, PlatformConfig> = {
  properties: {
    id: 'properties',
    name: 'Properties',
    description: 'Premium real estate listings',
    icon: '🏠',
    slug: 'properties',
    color: 'teal',
  },
  stays: {
    id: 'stays',
    name: 'Luxury Stays',
    description: 'Exclusive holiday accommodations',
    icon: '🏨',
    slug: 'stays',
    color: 'gold',
  },
  vehicles: {
    id: 'vehicles',
    name: 'Vehicles',
    description: 'Premium car rentals & sales',
    icon: '🚗',
    slug: 'vehicles',
    color: 'emerald',
  },
  events: {
    id: 'events',
    name: 'Events',
    description: 'Luxury event spaces & services',
    icon: '🎉',
    slug: 'events',
    color: 'rose',
  },
  experiences: {
    id: 'experiences',
    name: 'Experiences',
    description: 'Curated luxury experiences',
    icon: '✨',
    slug: 'experiences',
    color: 'purple',
  },
};

export const ALL_PLATFORMS: Platform[] = [
  'properties',
  'stays',
  'vehicles',
  'events',
  'experiences',
];

export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001/api';

export const AUTH_COOKIE_NAME = 'pearlhub_auth';
export const AUTH_TOKEN_EXPIRY = 24 * 60 * 60 * 1000; // 24 hours

export const PAGINATION_DEFAULTS = {
  PAGE_SIZE: 20,
  MAX_PAGE_SIZE: 100,
};

export const SEARCH_DEBOUNCE_MS = 300;

export const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'LKR'];

export const RATING_SCALE = {
  ONE_STAR: 1,
  TWO_STAR: 2,
  THREE_STAR: 3,
  FOUR_STAR: 4,
  FIVE_STAR: 5,
};
