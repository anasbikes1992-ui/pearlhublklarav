/* Platform Types */
export type Platform = 'properties' | 'stays' | 'vehicles' | 'events' | 'experiences';

export interface PlatformConfig {
  id: Platform;
  name: string;
  description: string;
  icon: string;
  slug: string;
  color: string;
}

/* User Types */
export type UserRole = 'customer' | 'provider' | 'admin';

export interface User {
  id: string;
  email: string;
  name: string;
  role: UserRole;
  avatar?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface AuthSession {
  user: User | null;
  token: string | null;
  expiresAt: Date | null;
}

/* Search Types */
export interface SearchFilters {
  query?: string;
  platform?: Platform;
  location?: string;
  priceMin?: number;
  priceMax?: number;
  rating?: number;
  sortBy?: 'relevance' | 'price' | 'rating' | 'newest';
  page?: number;
  limit?: number;
}

export interface SearchResult<T> {
  items: T[];
  total: number;
  page: number;
  limit: number;
  hasMore: boolean;
}

/* Listing Types */
export interface Listing {
  id: string;
  title: string;
  description: string;
  platform: Platform;
  images: string[];
  price: number;
  currency: string;
  location: {
    latitude: number;
    longitude: number;
    address: string;
    city: string;
    country: string;
  };
  provider: {
    id: string;
    name: string;
    avatar?: string;
    rating: number;
  };
  amenities?: string[];
  rating: number;
  reviews: number;
  createdAt: Date;
  updatedAt: Date;
}

/* Review Types */
export interface Review {
  id: string;
  listingId: string;
  userId: string;
  rating: number;
  comment: string;
  createdAt: Date;
  updatedAt: Date;
}

/* Booking Types */
export interface Booking {
  id: string;
  listingId: string;
  userId: string;
  checkIn: Date;
  checkOut: Date;
  totalPrice: number;
  currency: string;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  createdAt: Date;
  updatedAt: Date;
}

/* Response Types */
export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface PaginatedResponse<T> {
  items: T[];
  total: number;
  page: number;
  pageSize: number;
  hasMore: boolean;
}
