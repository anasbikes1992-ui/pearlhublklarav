'use client';

import { FormEvent, useEffect, useState } from 'react';

type VerticalFee = {
  vertical: string;
  listing_fee: number;
  commission_rate: number;
  vat_rate: number;
  tourism_tax_rate: number;
  service_charge_rate: number;
  is_active: boolean;
};

type PlatformSetting = {
  key: string;
  value: Record<string, unknown>;
  description?: string | null;
};

export default function AdminConfigsPage() {
  const [fees, setFees] = useState<VerticalFee[]>([]);
  const [settings, setSettings] = useState<PlatformSetting[]>([]);
  const [saving, setSaving] = useState<string>('');
  const [error, setError] = useState('');

  const load = async () => {
    setError('');
    try {
      const res = await fetch('/api/admin/configs', { credentials: 'same-origin' });
      if (!res.ok) {
        throw new Error(`Failed to load configs (${res.status})`);
      }
      const json = (await res.json()) as { data?: { vertical_fees?: VerticalFee[]; platform_settings?: PlatformSetting[] } };
      setFees(json.data?.vertical_fees ?? []);
      setSettings(json.data?.platform_settings ?? []);
    } catch (e) {
      setFees([]);
      setSettings([]);
      setError(e instanceof Error ? e.message : 'Failed to load configs');
    }
  };

  useEffect(() => {
    void load();
  }, []);

  const saveFee = async (fee: VerticalFee) => {
    setSaving(fee.vertical);
    await fetch(`/api/admin/configs/vertical-fees/${fee.vertical}`, {
      method: 'PUT',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(fee),
    });
    
    setSaving('');
    await load();
  };

  const saveSetting = async (e: FormEvent<HTMLFormElement>, key: string) => {
    e.preventDefault();
    const form = e.currentTarget;
    const value = (form.elements.namedItem('value') as HTMLInputElement).value;
    const description = (form.elements.namedItem('description') as HTMLInputElement).value;

    setSaving(key);
    await fetch(`/api/admin/configs/platform/${encodeURIComponent(key)}`, {
      method: 'PUT',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ value: { value }, description }),
    });
    setSaving('');
    await load();
  };

  return (
    <section className="admin-chart-section">
      <h1 className="admin-page-title">Config Management</h1>
      {error ? <p style={{ color: '#fca5a5', marginBottom: 8 }}>{error}</p> : null}

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        <div className="admin-table-card__title">Vertical fee configs</div>
        <table className="admin-table">
          <thead>
            <tr>
              <th>Vertical</th>
              <th>Listing fee</th>
              <th>Commission</th>
              <th>VAT</th>
              <th>Tourism</th>
              <th>Service</th>
              <th>Active</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {fees.map((f) => (
              <tr key={f.vertical}>
                <td>{f.vertical}</td>
                <td>{f.listing_fee}</td>
                <td>{f.commission_rate}</td>
                <td>{f.vat_rate}</td>
                <td>{f.tourism_tax_rate}</td>
                <td>{f.service_charge_rate}</td>
                <td>{f.is_active ? 'yes' : 'no'}</td>
                <td>
                  <button className="market-btn market-btn--ghost market-btn--sm" disabled={saving === f.vertical} onClick={() => void saveFee({ ...f, is_active: !f.is_active })}>
                    Toggle active
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="admin-table-card" style={{ marginTop: 16 }}>
        <div className="admin-table-card__title">Platform settings</div>
        <div style={{ display: 'grid', gap: 12 }}>
          {settings.map((s) => (
            <form key={s.key} onSubmit={(e) => void saveSetting(e, s.key)} style={{ display: 'grid', gridTemplateColumns: '180px 1fr 1fr auto', gap: 8 }}>
              <div>{s.key}</div>
              <input className="admin-input" name="value" defaultValue={String((s.value as { value?: unknown })?.value ?? '')} />
              <input className="admin-input" name="description" defaultValue={s.description ?? ''} />
              <button className="market-btn market-btn--primary market-btn--sm" disabled={saving === s.key} type="submit">
                Save
              </button>
            </form>
          ))}
        </div>
      </div>
    </section>
  );
}
