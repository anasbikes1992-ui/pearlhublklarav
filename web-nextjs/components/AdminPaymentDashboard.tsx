'use client';

import React, { useState, useEffect } from 'react';
import { PaymentService, SL_PAYMENT_OPTIONS, PromoCode } from '@/lib/payments';

interface CashPaymentRequest {
  id: string;
  bookingId: string;
  amount: number;
  status: 'pending' | 'approved' | 'rejected';
  createdAt: string;
  customerName: string;
}

export default function AdminPaymentDashboard() {
  const [activeTab, setActiveTab] = useState<'payments' | 'promo' | 'config'>('payments');
  const [cashRequests, setCashRequests] = useState<CashPaymentRequest[]>([]);
  const [promoCodes, setPromoCodes] = useState<PromoCode[]>([]);
  const [loading, setLoading] = useState(false);

  // Promo Code Form
  const [promoForm, setPromoForm] = useState({
    code: '',
    discountType: 'percentage' as 'percentage' | 'fixed',
    discountValue: 0,
    maxUses: 100,
    validServices: ['property', 'stay', 'vehicle', 'event'],
    expiryDate: '',
  });

  const paymentService = new PaymentService();

  useEffect(() => {
    if (activeTab === 'promo') {
      loadPromoCodes();
    }
  }, [activeTab]);

  const loadPromoCodes = async () => {
    setLoading(true);
    try {
      const codes = await paymentService.getAllPromoCodes();
      setPromoCodes(codes);
    } catch (error) {
      console.error('Failed to load promo codes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleGeneratePromo = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!promoForm.code.trim()) {
      alert('Enter promo code');
      return;
    }

    setLoading(true);
    try {
      const newPromo = await paymentService.generatePromoCode(promoForm);
      setPromoCodes([...promoCodes, newPromo]);
      setPromoForm({
        code: '',
        discountType: 'percentage',
        discountValue: 0,
        maxUses: 100,
        validServices: ['property', 'stay', 'vehicle', 'event'],
        expiryDate: '',
      });
      alert('Promo code generated successfully!');
    } catch (error) {
      alert('Failed to generate promo code');
    } finally {
      setLoading(false);
    }
  };

  const approveCashPayment = async (requestId: string) => {
    setLoading(true);
    try {
      await paymentService.approvePaymentRequest(requestId, true);
      setCashRequests(
        cashRequests.map(r => (r.id === requestId ? { ...r, status: 'approved' } : r))
      );
      alert('Payment approved');
    } catch (error) {
      alert('Failed to approve payment');
    } finally {
      setLoading(false);
    }
  };

  const rejectCashPayment = async (requestId: string) => {
    setLoading(true);
    try {
      await paymentService.approvePaymentRequest(requestId, false, 'Admin rejected');
      setCashRequests(
        cashRequests.map(r => (r.id === requestId ? { ...r, status: 'rejected' } : r))
      );
      alert('Payment rejected');
    } catch (error) {
      alert('Failed to reject payment');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#0a0e27] text-white p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-4xl font-bold mb-2 bg-gradient-to-r from-white via-[#00d4ff] to-[#d4af37] bg-clip-text text-transparent">
          Payment Management Dashboard
        </h1>
        <p className="text-[#8892b0]">Manage payment methods, cash requests, and promo codes</p>
      </div>

      {/* Tabs */}
      <div className="flex gap-4 mb-8 border-b border-[#1f2937]">
        {[
          { id: 'payments', label: '💰 Cash Requests', icon: '📋' },
          { id: 'promo', label: '🎟️ Promo Codes', icon: '✨' },
          { id: 'config', label: '⚙️ Configuration', icon: '🔧' },
        ].map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id as any)}
            className={`px-6 py-3 font-semibold transition-all duration-300 border-b-2 ${
              activeTab === tab.id
                ? 'border-[#00d4ff] text-[#00d4ff]'
                : 'border-transparent text-[#8892b0] hover:text-white'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Content */}
      <div className="max-w-6xl">
        {/* Cash Payment Requests Tab */}
        {activeTab === 'payments' && (
          <div>
            <h2 className="text-2xl font-bold mb-6">Pending Cash Payment Requests</h2>
            {cashRequests.length === 0 ? (
              <div className="bg-[#0f1422]/50 rounded-lg p-8 border border-[#1f2937] text-center">
                <p className="text-[#8892b0]">No pending cash payment requests</p>
              </div>
            ) : (
              <div className="grid gap-4">
                {cashRequests
                  .filter(r => r.status === 'pending')
                  .map(request => (
                    <div
                      key={request.id}
                      className="bg-[#0f1422]/50 rounded-lg p-6 border border-[#1f2937] hover:border-[#00d4ff]/30 transition-colors"
                    >
                      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                          <p className="text-[#8892b0] text-sm">Customer</p>
                          <p className="font-semibold text-white">{request.customerName}</p>
                        </div>
                        <div>
                          <p className="text-[#8892b0] text-sm">Amount</p>
                          <p className="font-semibold text-[#d4af37]">LKR {request.amount.toLocaleString('en-LK')}</p>
                        </div>
                        <div>
                          <p className="text-[#8892b0] text-sm">Booking ID</p>
                          <p className="font-semibold text-white">{request.bookingId}</p>
                        </div>
                        <div>
                          <p className="text-[#8892b0] text-sm">Requested</p>
                          <p className="font-semibold text-white">
                            {new Date(request.createdAt).toLocaleDateString()}
                          </p>
                        </div>
                      </div>
                      <div className="flex gap-3">
                        <button
                          onClick={() => approveCashPayment(request.id)}
                          disabled={loading}
                          className="flex-1 px-4 py-2 bg-[#10b981] text-white rounded-lg hover:bg-[#059669] font-semibold transition-colors disabled:opacity-50"
                        >
                          ✓ Approve
                        </button>
                        <button
                          onClick={() => rejectCashPayment(request.id)}
                          disabled={loading}
                          className="flex-1 px-4 py-2 bg-[#ef4444] text-white rounded-lg hover:bg-[#dc2626] font-semibold transition-colors disabled:opacity-50"
                        >
                          ✗ Reject
                        </button>
                      </div>
                    </div>
                  ))}
              </div>
            )}
          </div>
        )}

        {/* Promo Codes Tab */}
        {activeTab === 'promo' && (
          <div className="grid md:grid-cols-2 gap-8">
            {/* Generate Promo Form */}
            <div className="bg-[#0f1422]/50 rounded-lg p-6 border border-[#1f2937]">
              <h3 className="text-xl font-bold mb-6 text-[#d4af37]">Generate New Promo Code</h3>
              <form onSubmit={handleGeneratePromo} className="space-y-4">
                <div>
                  <label className="block text-[#8892b0] text-sm mb-2">Promo Code</label>
                  <input
                    type="text"
                    value={promoForm.code}
                    onChange={e => setPromoForm({ ...promoForm, code: e.target.value.toUpperCase() })}
                    placeholder="e.g., SUMMER20"
                    className="w-full bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white placeholder-[#8892b0] focus:outline-none focus:border-[#00d4ff]"
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-[#8892b0] text-sm mb-2">Type</label>
                    <select
                      value={promoForm.discountType}
                      onChange={e =>
                        setPromoForm({ ...promoForm, discountType: e.target.value as any })
                      }
                      className="w-full bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#00d4ff]"
                    >
                      <option value="percentage">Percentage (%)</option>
                      <option value="fixed">Fixed Amount (LKR)</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-[#8892b0] text-sm mb-2">Discount Value</label>
                    <input
                      type="number"
                      value={promoForm.discountValue}
                      onChange={e =>
                        setPromoForm({ ...promoForm, discountValue: parseFloat(e.target.value) })
                      }
                      placeholder="0"
                      className="w-full bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white placeholder-[#8892b0] focus:outline-none focus:border-[#00d4ff]"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[#8892b0] text-sm mb-2">Max Uses</label>
                  <input
                    type="number"
                    value={promoForm.maxUses}
                    onChange={e => setPromoForm({ ...promoForm, maxUses: parseInt(e.target.value) })}
                    placeholder="100"
                    className="w-full bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white placeholder-[#8892b0] focus:outline-none focus:border-[#00d4ff]"
                  />
                </div>

                <div>
                  <label className="block text-[#8892b0] text-sm mb-2">Valid Services</label>
                  <div className="grid grid-cols-2 gap-2">
                    {['property', 'stay', 'vehicle', 'event'].map(service => (
                      <label key={service} className="flex items-center gap-2 cursor-pointer">
                        <input
                          type="checkbox"
                          checked={promoForm.validServices.includes(service)}
                          onChange={e => {
                            if (e.target.checked) {
                              setPromoForm({
                                ...promoForm,
                                validServices: [...promoForm.validServices, service],
                              });
                            } else {
                              setPromoForm({
                                ...promoForm,
                                validServices: promoForm.validServices.filter(s => s !== service),
                              });
                            }
                          }}
                          className="w-4 h-4 rounded border-[#00d4ff]"
                        />
                        <span className="text-[#8892b0] text-sm capitalize">{service}</span>
                      </label>
                    ))}
                  </div>
                </div>

                <div>
                  <label className="block text-[#8892b0] text-sm mb-2">Expiry Date</label>
                  <input
                    type="date"
                    value={promoForm.expiryDate}
                    onChange={e => setPromoForm({ ...promoForm, expiryDate: e.target.value })}
                    className="w-full bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#00d4ff]"
                  />
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="w-full px-4 py-2 bg-gradient-to-r from-[#00d4ff] to-[#d4af37] text-black font-semibold rounded-lg hover:shadow-lg disabled:opacity-50 transition-all"
                >
                  {loading ? 'Creating...' : '✨ Generate Promo Code'}
                </button>
              </form>
            </div>

            {/* Active Promo Codes */}
            <div>
              <h3 className="text-xl font-bold mb-6 text-[#d4af37]">Active Promo Codes</h3>
              <div className="space-y-4 max-h-96 overflow-y-auto">
                {promoCodes.filter(p => p.isActive).length === 0 ? (
                  <div className="bg-[#0f1422]/50 rounded-lg p-4 border border-[#1f2937] text-center">
                    <p className="text-[#8892b0]">No active promo codes</p>
                  </div>
                ) : (
                  promoCodes
                    .filter(p => p.isActive)
                    .map(promo => (
                      <div key={promo.id} className="bg-[#0f1422]/50 rounded-lg p-4 border border-[#1f2937]">
                        <div className="flex justify-between items-start mb-2">
                          <p className="font-bold text-[#00d4ff]">{promo.code}</p>
                          <span className="text-[#d4af37] font-semibold">
                            {promo.discountType === 'percentage' ? `${promo.discountValue}%` : `LKR ${promo.discountValue}`}
                          </span>
                        </div>
                        <div className="text-xs text-[#8892b0] space-y-1">
                          <p>
                            Uses: <span className="text-white">{promo.usedCount}/{promo.maxUses}</span>
                          </p>
                          <p>
                            Valid: <span className="text-white">{promo.validServices.join(', ')}</span>
                          </p>
                          <p>
                            Expires: <span className="text-white">{new Date(promo.expiryDate).toLocaleDateString()}</span>
                          </p>
                        </div>
                      </div>
                    ))
                )}
              </div>
            </div>
          </div>
        )}

        {/* Configuration Tab */}
        {activeTab === 'config' && (
          <div>
            <h2 className="text-2xl font-bold mb-6">Payment Method Configuration</h2>
            <div className="grid md:grid-cols-2 gap-6">
              {SL_PAYMENT_OPTIONS.map(option => (
                <div key={option.id} className="bg-[#0f1422]/50 rounded-lg p-6 border border-[#1f2937]">
                  <div className="flex justify-between items-start mb-4">
                    <div>
                      <h3 className="text-lg font-bold text-white">
                        {option.icon} {option.name}
                      </h3>
                      <p className="text-[#8892b0] text-sm">{option.provider}</p>
                    </div>
                    <div
                      className={`px-3 py-1 rounded-full text-sm font-semibold ${
                        option.isEnabled
                          ? 'bg-[#10b981]/10 text-[#10b981]'
                          : 'bg-[#ef4444]/10 text-[#ef4444]'
                      }`}
                    >
                      {option.isEnabled ? 'Enabled' : 'Disabled'}
                    </div>
                  </div>
                  <div className="space-y-2 text-sm mb-4">
                    <p className="text-[#8892b0]">
                      Fee: <span className="text-white font-semibold">{option.fee}%</span>
                    </p>
                    <p className="text-[#8892b0]">
                      Processing: <span className="text-white font-semibold">{option.processingTime}</span>
                    </p>
                    <p className="text-[#8892b0]">
                      Limits:{' '}
                      <span className="text-white font-semibold">
                        LKR {option.minAmount.toLocaleString()} - {option.maxAmount.toLocaleString()}
                      </span>
                    </p>
                    {option.adminApprovalRequired && (
                      <p className="text-[#a5b4fc] bg-[#6366f1]/10 px-2 py-1 rounded">
                        ⚠️ Requires Admin Approval
                      </p>
                    )}
                  </div>
                  <button className="w-full px-4 py-2 bg-[#1f2937] text-white rounded-lg hover:bg-[#374151] transition-colors font-semibold">
                    {option.isEnabled ? 'Disable' : 'Enable'}
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
