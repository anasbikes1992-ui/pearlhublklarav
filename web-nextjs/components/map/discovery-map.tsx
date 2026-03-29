'use client';

import { useEffect, useRef } from 'react';
import maplibregl from 'maplibre-gl';

type Pin = {
  lat: number;
  lng: number;
  label: string;
};

type DiscoveryMapProps = {
  pins: Pin[];
};

export default function DiscoveryMap({ pins }: DiscoveryMapProps) {
  const mapRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    if (!mapRef.current) {
      return;
    }

    const map = new maplibregl.Map({
      container: mapRef.current,
      style: 'https://demotiles.maplibre.org/style.json',
      center: [80.7718, 7.8731],
      zoom: 7,
    });

    pins.forEach((pin) => {
      new maplibregl.Marker().setLngLat([pin.lng, pin.lat]).setPopup(new maplibregl.Popup().setText(pin.label)).addTo(map);
    });

    return () => map.remove();
  }, [pins]);

  return <div ref={mapRef} style={{ width: '100%', height: 420, borderRadius: 16, overflow: 'hidden' }} />;
}
