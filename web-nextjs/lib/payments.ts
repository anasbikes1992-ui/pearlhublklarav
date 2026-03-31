// Sri Lankan Payment Platforms Integration
// Replaces PayHere with all major SL payment gateways

export interface PaymentOption {
  id: string;
  name: string;
  provider: string;
  icon: string;
  description: string;
  supportedTypes: string[];
  minAmount: number;
  maxAmount: number;
  fee: number; // percentage
  processingTime: string;
  adminApprovalRequired?: boolean; // For cash/bank transfer
  isEnabled?: boolean;
}

export interface PromoCode {
  id: string;
  code: string;
  discountType: 'percentage' | 'fixed'; // percentage or fixed amount
  discountValue: number;
  maxUses: number;
  usedCount: number;
  validServices: string[]; // which services this applies to
  minAmount?: number;
  expiryDate: string;
  createdBy: string; // admin ID
  isActive: boolean;
}

export interface Subscription {
  id: string;
  planName: string;
  planType: 'monthly' | 'yearly' | 'custom';
  price: number;
  benefits: string[];
  maxListings?: number;
  renewalDate: string;
  status: 'active' | 'inactive' | 'cancelled';
  paymentMethod: string;
}

export interface PaymentRequest {
  bookingId: string;
  amount: number;
  currency: string; // LKR
  paymentMethod: string;
  customerEmail?: string;
  customerPhone?: string;
  description?: string;
}

export interface PaymentResponse {
  transactionId: string;
  status: 'pending' | 'success' | 'failed' | 'cancelled';
  timestamp: string;
  reference: string;
}

export const SL_PAYMENT_OPTIONS: PaymentOption[] = [
  {
    id: 'dialog-money',
    name: 'Dialog Money',
    provider: 'Dialog Axiata',
    icon: '💳',
    description: 'Mobile money service via Dialog network',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 100,
    maxAmount: 1000000,
    fee: 2.5,
    processingTime: 'Instant',
    isEnabled: true,
  },
  {
    id: 'digi-money',
    name: 'Dialog Digital Money',
    provider: 'Dialog Axiata',
    icon: '📱',
    description: 'Digital payments through Dialog app',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 100,
    maxAmount: 500000,
    fee: 2.0,
    processingTime: 'Instant',
    isEnabled: true,
  },
  {
    id: 'sampath-smartpay',
    name: 'Sampath SMARTPAY',
    provider: 'Sampath Bank',
    icon: '🏦',
    description: 'Direct bank transfer via Sampath SMARTPAY',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 500,
    maxAmount: 5000000,
    fee: 1.5,
    processingTime: '1-2 hours',
    isEnabled: true,
  },
  {
    id: 'lolc-cash',
    name: 'LOLC Finance',
    provider: 'LOLC Finance',
    icon: '💰',
    description: 'LOLC finance payment solution',
    supportedTypes: ['stay', 'property', 'vehicle'],
    minAmount: 1000,
    maxAmount: 2000000,
    fee: 2.0,
    processingTime: '2-4 hours',
    isEnabled: true,
  },
  {
    id: 'ideamart-mtc',
    name: 'IdeaMart (Mobile Money)',
    provider: 'Idea Corp',
    icon: '📲',
    description: 'Multi-operator mobile payment platform',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 100,
    maxAmount: 999999,
    fee: 2.75,
    processingTime: 'Instant',
    isEnabled: true,
  },
  {
    id: 'mobitel-money',
    name: 'Mobitel Money',
    provider: 'Mobitel',
    icon: '💵',
    description: 'Mobitel mobile money wallet',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 50,
    maxAmount: 500000,
    fee: 2.5,
    processingTime: 'Instant',
    isEnabled: true,
  },
  {
    id: 'hutch-money',
    name: 'Hutch Money',
    provider: 'Hutchison',
    icon: '📞',
    description: 'Hutch mobile money service',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 50,
    maxAmount: 500000,
    fee: 2.5,
    processingTime: 'Instant',
    isEnabled: true,
  },
  {
    id: 'bank-transfer',
    name: 'Bank Transfer',
    provider: 'Multiple Banks',
    icon: '🏧',
    description: 'Direct bank transfer to PearlHub account',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 500,
    maxAmount: 10000000,
    fee: 0,
    processingTime: '1-3 business days',
    adminApprovalRequired: true,
    isEnabled: true,
  },
  {
    id: 'cash-payment',
    name: 'Cash Payment',
    provider: 'In-Person',
    icon: '💵',
    description: 'Pay in cash at office location',
    supportedTypes: [],
    minAmount: 1000,
    maxAmount: 5000000,
    fee: 0,
    processingTime: 'Instant (upon verification)',
    adminApprovalRequired: true,
    isEnabled: false, // Admin controls this per service
  },
  {
    id: 'card-visa',
    name: 'Visa/MasterCard',
    provider: 'Global Card Networks',
    icon: '💳',
    description: 'International card payments (Visa/MasterCard)',
    supportedTypes: ['stay', 'property', 'vehicle', 'event', 'experience'],
    minAmount: 100,
    maxAmount: 5000000,
    fee: 3.5,
    processingTime: 'Instant',
    isEnabled: true,
  },
];

// Payment Gateway APIs
export class PaymentService {
  private proxyBase: string;

  constructor() {
    // All payment API calls route through Next.js server proxy to attach httpOnly cookie token
    this.proxyBase = '/api/payments';
  }

  private async authedFetch(path: string, options: RequestInit = {}): Promise<Response> {
    return fetch(`${this.proxyBase}${path}`, {
      ...options,
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
    });
  }

  // ========== PAYMENT METHODS ==========

  async getAvailablePaymentMethods(vertical: string, amount: number): Promise<PaymentOption[]> {
    return SL_PAYMENT_OPTIONS.filter(option => {
      if (!option.isEnabled) return false;
      const supportsVertical = option.supportedTypes.includes(vertical);
      const withinLimits = amount >= option.minAmount && amount <= option.maxAmount;
      return supportsVertical && withinLimits;
    });
  }

  async initiateDialogMoney(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await this.authedFetch('/dialog-money/init', {
      method: 'POST',
      body: JSON.stringify({ ...request, reference: `PM-${Date.now()}` }),
    });
    if (!response.ok) throw new Error('Dialog Money payment failed');
    return response.json();
  }

  async initiateSmartpay(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await this.authedFetch('/sampath/init', {
      method: 'POST',
      body: JSON.stringify({ ...request, reference: `SP-${Date.now()}` }),
    });
    if (!response.ok) throw new Error('Sampath SMARTPAY payment failed');
    return response.json();
  }

  async initiateIdeaMart(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await this.authedFetch('/ideamart/init', {
      method: 'POST',
      body: JSON.stringify({ ...request, reference: `IM-${Date.now()}` }),
    });
    if (!response.ok) throw new Error('IdeaMart payment failed');
    return response.json();
  }

  async initiateBankTransfer(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await this.authedFetch('/bank-transfer/init', {
      method: 'POST',
      body: JSON.stringify({
        ...request,
        reference: `BT-${Date.now()}`,
        status: 'pending-approval',
      }),
    });
    if (!response.ok) throw new Error('Bank transfer setup failed');
    return response.json();
  }

  async requestCashPayment(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await this.authedFetch('/cash/request', {
      method: 'POST',
      body: JSON.stringify({ ...request, reference: `CASH-${Date.now()}`, status: 'pending-approval' }),
    });
    if (!response.ok) throw new Error('Cash payment request failed');
    return response.json();
  }

  async verifyPaymentStatus(transactionId: string): Promise<PaymentResponse> {
    const response = await this.authedFetch(`/verify/${transactionId}`);
    if (!response.ok) throw new Error('Payment verification failed');
    return response.json();
  }

  async handlePaymentCallback(provider: string, data: Record<string, unknown>): Promise<PaymentResponse> {
    const response = await this.authedFetch(`/callback/${provider}`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
    if (!response.ok) throw new Error('Callback processing failed');
    return response.json();
  }

  // ========== PROMO CODES ==========

  async validatePromoCode(
    code: string,
    vertical: string,
    amount: number
  ): Promise<{ valid: boolean; discount: number; promo: PromoCode | null }> {
    const response = await this.authedFetch('/promo-codes/validate', {
      method: 'POST',
      body: JSON.stringify({ code, vertical, amount }),
    });
    if (!response.ok) return { valid: false, discount: 0, promo: null };
    return response.json();
  }

  async generatePromoCode(promoData: Partial<PromoCode>): Promise<PromoCode> {
    const response = await this.authedFetch('/promo-codes', {
      method: 'POST',
      body: JSON.stringify(promoData),
    });
    if (!response.ok) throw new Error('Promo code generation failed');
    return response.json();
  }

  async getAllPromoCodes(): Promise<PromoCode[]> {
    const response = await this.authedFetch('/promo-codes');
    if (!response.ok) throw new Error('Failed to fetch promo codes');
    return response.json();
  }

  // ========== SUBSCRIPTIONS ==========

  async getSubscriptionPlans(): Promise<Subscription[]> {
    const response = await this.authedFetch('/subscriptions/plans');
    if (!response.ok) throw new Error('Failed to fetch subscription plans');
    return response.json();
  }

  async subscribeToPlan(planId: string, paymentMethod: string, promoCode?: string): Promise<Subscription> {
    const response = await this.authedFetch('/subscriptions/subscribe', {
      method: 'POST',
      body: JSON.stringify({ planId, paymentMethod, promoCode }),
    });
    if (!response.ok) throw new Error('Subscription failed');
    return response.json();
  }

  async getCurrentSubscription(): Promise<Subscription | null> {
    const response = await this.authedFetch('/subscriptions/current');
    if (!response.ok || response.status === 404) return null;
    return response.json();
  }

  // ========== UTILITIES ==========

  async calculatePaymentBreakdown(amount: number, paymentMethod: string, promoCode?: string) {
    const option = SL_PAYMENT_OPTIONS.find(p => p.id === paymentMethod);
    if (!option) throw new Error('Payment method not found');

    let discount = 0;
    let finalAmount = amount;

    if (promoCode) {
      const promoValidation = await this.validatePromoCode(promoCode, '', amount);
      if (promoValidation.valid) {
        discount = promoValidation.discount;
        finalAmount = amount - discount;
      }
    }

    const fee = (finalAmount * option.fee) / 100;
    const total = finalAmount + fee;

    return {
      subtotal: amount,
      discount,
      afterDiscount: finalAmount,
      fee: parseFloat(fee.toFixed(2)),
      feePercentage: option.fee,
      total: parseFloat(total.toFixed(2)),
      provider: option.provider,
      processingTime: option.processingTime,
      requiresAdminApproval: option.adminApprovalRequired || false,
    };
  }

  async getTransactionHistory(limit: number = 10): Promise<PaymentResponse[]> {
    const response = await this.authedFetch(`/history?limit=${limit}`);
    if (!response.ok) throw new Error('Failed to fetch transaction history');
    return response.json();
  }

  async requestRefund(transactionId: string, reason: string): Promise<Record<string, unknown>> {
    const response = await this.authedFetch(`/${transactionId}/refund`, {
      method: 'POST',
      body: JSON.stringify({ reason }),
    });
    if (!response.ok) throw new Error('Refund request failed');
    return response.json();
  }

  // ========== ADMIN ==========

  async approvePaymentRequest(requestId: string, approved: boolean, reason?: string): Promise<void> {
    const action = approved ? 'approve' : 'reject';
    const response = await this.authedFetch(`/cash/${requestId}/${action}`, {
      method: 'POST',
      body: JSON.stringify({ reason }),
    });
    if (!response.ok) throw new Error(`Failed to ${action} payment request`);
  }

  async getCashPaymentRequests(): Promise<Array<{ id: string; bookingId: string; amount: number; status: string; createdAt: string; customerName: string }>> {
    const response = await this.authedFetch('/cash/requests');
    if (!response.ok) throw new Error('Failed to fetch cash payment requests');
    return response.json();
  }
}

const paymentService = new PaymentService();
export default paymentService;
