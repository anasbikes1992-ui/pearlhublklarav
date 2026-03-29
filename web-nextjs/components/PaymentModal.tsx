'use client';

import React, { useState, useEffect } from 'react';
import { SL_PAYMENT_OPTIONS, PaymentService, PaymentOption } from '@/lib/payments';

interface PaymentModalProps {
  isOpen: boolean;
  onClose: () => void;
  amount: number;
  vertical: string;
  onSelectPayment: (method: string, breakdown: any) => void;
  showSubscriptionOption?: boolean;
}

export default function PaymentModal({
  isOpen,
  onClose,
  amount,
  vertical,
  onSelectPayment,
  showSubscriptionOption = false,
}: PaymentModalProps) {
  const [availableMethods, setAvailableMethods] = useState<PaymentOption[]>([]);
  const [selectedMethod, setSelectedMethod] = useState<string | null>(null);
  const [promoCode, setPromoCode] = useState('');
  const [promoError, setPromoError] = useState('');
  const [discount, setDiscount] = useState(0);
  const [breakdowns, setBreakdowns] = useState<Record<string, any>>({});
  const [loading, setLoading] = useState(false);
  const [wantSubscription, setWantSubscription] = useState(false);
  const [showPromoInput, setShowPromoInput] = useState(false);

  const paymentService = new PaymentService();

  useEffect(() => {
    if (isOpen && amount > 0) {
      const methods = SL_PAYMENT_OPTIONS.filter(option => {
        if (!option.isEnabled) return false;
        const supportsVertical = option.supportedTypes.includes(vertical);
        const withinLimits = amount >= option.minAmount && amount <= option.maxAmount;
        return supportsVertical && withinLimits;
      });
      setAvailableMethods(methods);

      // Calculate breakdowns for all methods
      const newBreakdowns: Record<string, any> = {};
      methods.forEach(method => {
        const fee = (amount * method.fee) / 100;
        newBreakdowns[method.id] = {
          subtotal: amount,
          fee: parseFloat(fee.toFixed(2)),
          feePercentage: method.fee,
          total: parseFloat((amount + fee).toFixed(2)),
          requiresAdminApproval: method.adminApprovalRequired || false,
        };
      });
      setBreakdowns(newBreakdowns);
      setDiscount(0);
      setPromoCode('');
      setPromoError('');
    }
  }, [isOpen, amount, vertical]);

  const applyPromoCode = async () => {
    if (!promoCode.trim()) {
      setPromoError('Enter promo code');
      return;
    }

    setLoading(true);
    try {
      const result = await paymentService.validatePromoCode(promoCode, vertical, amount);
      if (result.valid) {
        setDiscount(result.discount);
        setPromoError('');
      } else {
        setPromoError('Invalid or expired promo code');
        setDiscount(0);
      }
    } catch (error) {
      setPromoError('Failed to validate promo code');
    } finally {
      setLoading(false);
    }
  };

  const getFinalAmount = () => {
    return Math.max(0, amount - discount);
  };

  if (!isOpen) return null;

  const finalAmount = getFinalAmount();
  const selectedMethodData = selectedMethod
    ? breakdowns[selectedMethod]
    : null;

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 cursor-pointer"
        onClick={onClose}
        style={{
          animation: 'fadeIn 0.2s ease-out',
        }}
      />

      {/* Modal */}
      <div
        className="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-3xl bg-[#0a0e27] rounded-2xl p-8 z-50 border border-[#00d4ff]/20"
        style={{
          animation: 'fadeInDown 0.3s ease-out cubic-bezier(0.34, 1.56, 0.64, 1)',
          boxShadow: `0 0 60px rgba(0, 212, 255, 0.1), 0 25px 50px -12px rgba(0, 0, 0, 0.25)`,
          maxHeight: '90vh',
          overflowY: 'auto',
        }}
      >
        {/* Header */}
        <div className="mb-8">
          <h2 className="text-3xl font-bold mb-2 bg-gradient-to-r from-white via-[#00d4ff] to-[#d4af37] bg-clip-text text-transparent">
            Secure Payment
          </h2>
          <p className="text-[#8892b0] text-sm">
            Choose your preferred payment method
          </p>
        </div>

        {/* Promo Code Section */}
        {!showPromoInput && (
          <div
            className="bg-[#0f1422]/50 rounded-lg p-4 mb-6 border border-[#d4af37]/30 cursor-pointer hover:border-[#d4af37]/60 transition-colors"
            onClick={() => setShowPromoInput(true)}
            style={{ animation: 'fadeInUp 0.3s ease-out' }}
          >
            <div className="flex justify-between items-center">
              <div>
                <p className="text-[#d4af37] font-semibold text-sm">✨ Have a promo code?</p>
                <p className="text-[#8892b0] text-xs">Click to apply discount</p>
              </div>
              <span className="text-2xl">→</span>
            </div>
          </div>
        )}

        {/* Active Promo Input */}
        {showPromoInput && (
          <div
            className="bg-[#0f1422]/50 rounded-lg p-4 mb-6 border border-[#00d4ff]/30"
            style={{ animation: 'fadeIn 0.3s ease-out' }}
          >
            <div className="flex gap-2">
              <input
                type="text"
                value={promoCode}
                onChange={(e) => {
                  setPromoCode(e.target.value);
                  setPromoError('');
                }}
                placeholder="Enter promo code"
                className="flex-1 bg-[#0a0e27] border border-[#1f2937] rounded-lg px-4 py-2 text-white placeholder-[#8892b0] focus:outline-none focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]"
                onKeyPress={(e) => e.key === 'Enter' && applyPromoCode()}
              />
              <button
                onClick={applyPromoCode}
                disabled={loading}
                className="px-6 py-2 bg-gradient-to-r from-[#00d4ff] to-[#d4af37] text-black font-semibold rounded-lg hover:shadow-lg disabled:opacity-50"
              >
                {loading ? '...' : 'Apply'}
              </button>
            </div>
            {promoError && <p className="text-red-400 text-xs mt-2">✗ {promoError}</p>}
            {discount > 0 && (
              <p className="text-[#10b981] text-xs mt-2">✓ Discount applied: LKR {discount.toLocaleString('en-LK')}</p>
            )}
          </div>
        )}

        {/* Amount Summary */}
        <div className="bg-[#0f1422]/50 rounded-lg p-4 mb-6 border border-[#00d4ff]/10">
          <div className="space-y-2">
            <div className="flex justify-between items-center text-sm">
              <span className="text-[#8892b0]">Amount:</span>
              <span className="text-white">LKR {amount.toLocaleString('en-LK')}</span>
            </div>
            {discount > 0 && (
              <div className="flex justify-between items-center text-sm">
                <span className="text-[#10b981]">Discount:</span>
                <span className="text-[#10b981]">- LKR {discount.toLocaleString('en-LK')}</span>
              </div>
            )}
            <div className="flex justify-between items-center text-lg font-bold pt-2 border-t border-[#1f2937]">
              <span className="text-[#8892b0]">After Discount:</span>
              <span className="text-[#00d4ff]">LKR {finalAmount.toLocaleString('en-LK')}</span>
            </div>
          </div>
        </div>

        {/* Payment Methods Grid */}
        <div className="mb-8">
          <h3 className="text-sm font-semibold text-[#d4af37] mb-4 uppercase tracking-wider">
            Payment Methods
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2">
            {availableMethods.map((method, index) => {
              const breakdown = breakdowns[method.id];
              const isSelected = selectedMethod === method.id;

              return (
                <div
                  key={method.id}
                  onClick={() => setSelectedMethod(method.id)}
                  className={`p-4 rounded-lg border-2 cursor-pointer transition-all duration-300 ${
                    isSelected
                      ? 'border-[#00d4ff] bg-[#00d4ff]/5 shadow-lg shadow-[#00d4ff]/20'
                      : 'border-[#1f2937] bg-[#0f1422]/50 hover:border-[#00d4ff]/50 hover:bg-[#0f1422]/70'
                  }`}
                  style={{
                    animation: `fadeInUp 0.3s ease-out ${index * 0.05}s both`,
                  }}
                >
                  <div className="flex items-start justify-between mb-2">
                    <div className="flex-1">
                      <h4 className="font-semibold text-white text-sm mb-1">
                        {method.icon} {method.name}
                      </h4>
                      <p className="text-[#8892b0] text-xs">{method.provider}</p>
                    </div>
                  </div>
                  <div className="flex items-center justify-between mb-2">
                    <p className="text-[#8892b0] text-xs">{method.description}</p>
                  </div>
                  <div className="flex justify-between items-center text-xs">
                    <span className={`px-2 py-1 rounded ${
                      method.fee === 0
                        ? 'bg-[#10b981]/10 text-[#10b981]'
                        : 'bg-[#f59e0b]/10 text-[#f59e0b]'
                    }`}>
                      Fee: {method.fee}% • {method.processingTime}
                    </span>
                    {method.adminApprovalRequired && (
                      <span className="px-2 py-1 bg-[#6366f1]/10 text-[#a5b4fc] rounded text-xs">
                        Approval needed
                      </span>
                    )}
                  </div>
                  {isSelected && breakdown && (
                    <div className="mt-3 pt-3 border-t border-[#1f2937]">
                      <p className="text-[#d4af37] font-semibold text-sm">
                        Total: LKR {(finalAmount + breakdown.fee).toLocaleString('en-LK')}
                      </p>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>

        {/* Detailed Breakdown */}
        {selectedMethod && selectedMethodData && (
          <div
            className="bg-gradient-to-r from-[#00d4ff]/5 to-[#d4af37]/5 rounded-lg p-4 mb-8 border border-[#00d4ff]/20"
            style={{
              animation: 'fadeIn 0.3s ease-out',
            }}
          >
            <h4 className="text-sm font-semibold text-white mb-4">Payment Breakdown</h4>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-[#8892b0] text-xs mb-1">Subtotal</p>
                <p className="text-white font-semibold">
                  LKR {selectedMethodData.subtotal.toLocaleString('en-LK')}
                </p>
              </div>
              {discount > 0 && (
                <div>
                  <p className="text-[#8892b0] text-xs mb-1">Discount</p>
                  <p className="text-[#10b981] font-semibold">
                    - LKR {discount.toLocaleString('en-LK')}
                  </p>
                </div>
              )}
              <div>
                <p className="text-[#8892b0] text-xs mb-1">
                  Payment Fee ({selectedMethodData.feePercentage}%)
                </p>
                <p className="text-[#d4af37] font-semibold">
                  + LKR {selectedMethodData.fee.toLocaleString('en-LK')}
                </p>
              </div>
              <div>
                <p className="text-[#8892b0] text-xs mb-1">Total Amount</p>
                <p className="text-[#00d4ff] font-bold text-lg">
                  LKR {(finalAmount + selectedMethodData.fee).toLocaleString('en-LK')}
                </p>
              </div>
            </div>
            {selectedMethodData.requiresAdminApproval && (
              <div className="mt-4 p-3 bg-[#6366f1]/10 border border-[#6366f1]/30 rounded-lg">
                <p className="text-[#a5b4fc] text-sm">
                  ⏱️ This payment method requires admin approval before processing.
                </p>
              </div>
            )}
          </div>
        )}

        {/* Subscription Option */}
        {showSubscriptionOption && (
          <div
            className="bg-[#0f1422]/50 rounded-lg p-4 mb-8 border border-[#d4af37]/30"
            style={{ animation: 'fadeInUp 0.3s ease-out' }}
          >
            <label className="flex items-center gap-3 cursor-pointer">
              <input
                type="checkbox"
                checked={wantSubscription}
                onChange={(e) => setWantSubscription(e.target.checked)}
                className="w-4 h-4 rounded border-[#00d4ff] text-[#00d4ff]"
              />
              <div>
                <p className="text-[#d4af37] font-semibold text-sm">
                  💎 Add subscription for more benefits
                </p>
                <p className="text-[#8892b0] text-xs">Unlock premium features and discounts</p>
              </div>
            </label>
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex gap-3">
          <button
            onClick={onClose}
            className="flex-1 px-4 py-3 rounded-lg bg-[#0f1422] text-white border border-[#1f2937] hover:border-[#00d4ff]/50 font-semibold transition-all duration-300"
          >
            Cancel
          </button>
          <button
            onClick={() => {
              if (selectedMethod && selectedMethodData) {
                onSelectPayment(selectedMethod, {
                  ...selectedMethodData,
                  finalAmount,
                  discount,
                  promoCode: promoCode || null,
                  withSubscription: wantSubscription,
                });
              }
            }}
            disabled={!selectedMethod}
            className={`flex-1 px-4 py-3 rounded-lg font-semibold transition-all duration-300 ${
              selectedMethod
                ? 'bg-gradient-to-r from-[#00d4ff] to-[#d4af37] text-black hover:shadow-lg hover:shadow-[#00d4ff]/50 active:scale-95'
                : 'bg-gray-700 text-gray-500 cursor-not-allowed'
            }`}
          >
            Proceed to Payment
          </button>
        </div>

        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-[#8892b0] hover:text-white transition-colors"
        >
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </>
  );
}
