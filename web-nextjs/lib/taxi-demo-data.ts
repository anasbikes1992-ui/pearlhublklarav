export const DEMO_DRIVERS = [
  { id: 'd1', name: 'Kasun Perera',     vehicle: 'Toyota Aqua', plate: 'WP CAA-1234', rating: 4.9, trips: 342, eta: '3 min', avatar: '🧑' },
  { id: 'd2', name: 'Nuwan Silva',      vehicle: 'Suzuki Alto', plate: 'SP CAB-5678', rating: 4.8, trips: 218, eta: '5 min', avatar: '👨' },
  { id: 'd3', name: 'Chamara Fernando', vehicle: 'Honda Fit',   plate: 'CP CAC-9012', rating: 4.7, trips: 185, eta: '8 min', avatar: '🧔' },
] as const;

export type DemoDriver = (typeof DEMO_DRIVERS)[number];
