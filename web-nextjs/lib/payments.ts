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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
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
    supportedTypes: ['stay', 'property', 'vehicle', 'event'],
    minAmount: 100,
    maxAmount: 5000000,
    fee: 3.5,
    processingTime: 'Instant',
    isEnabled: true,
  },
];

// Payment Gateway APIs
export class PaymentService {
  private baseUrl: string;

  constructor(baseUrl: string = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1') {
    this.baseUrl = baseUrl;
  }

  // ========== PAYMENT METHODS ==========

  /**
   * Get available payment options for a specific vertical
   */
  async getAvailablePaymentMethods(vertical: string, amount: number): Promise<PaymentOption[]> {
    return SL_PAYMENT_OPTIONS.filter(option => {
      if (!option.isEnabled) return false;
      const supportsVertical = option.supportedTypes.includes(vertical);
      const withinLimits = amount >= option.minAmount && amount <= option.maxAmount;
      return supportsVertical && withinLimits;
    });
  }

  /**
   * Initiate payment with Dialog Money
   */
  async initiateDialogMoney(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/dialog-money/init`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...request,
        reference: `PM-${Date.now()}`,
      }),
    });

    if (!response.ok) throw new Error('Dialog Money payment failed');
    return response.json();
  }

  /**
   * Initiate payment with Sampath SMARTPAY
   */
  async initiateSmartpay(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/sampath/init`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...request,
        reference: `SP-${Date.now()}`,
      }),
    });

    if (!response.ok) throw new Error('Sampath SMARTPAY payment failed');
    return response.json();
  }

  /**
   * Initiate payment with IdeaMart (Multi-operator)
   */
  async initiateIdeaMart(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/ideamart/init`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...request,
        reference: `IM-${Date.now()}`,
      }),
    });

    if (!response.ok) throw new Error('IdeaMart payment failed');
    return response.json();
  }

  /**
   * Initiate bank transfer (admin approval required)
   */
  async initiateBankTransfer(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/bank-transfer/init`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...request,
        reference: `BT-${Date.now()}`,
        status: 'pending-approval',
        bankAccounts: {
          primary: 'BOC Account: XXXX XXXX XXXX XXXX',
          secondary: 'Sampath Bank: XXXX XXXX XXXX XXXX',
          tertiary: 'DFCC Bank: XXXX XXXX XXXX XXXX',
        },
      }),
    });

    if (!response.ok) throw new Error('Bank transfer setup failed');
    return response.json();
  }

  /**
   * Request cash payment (admin approval required)
   */
  async requestCashPayment(request: PaymentRequest): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/cash/request`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...request,
        reference: `CASH-${Date.now()}`,
        status: 'pending-approval',
      }),
    });

    if (!response.ok) throw new Error('Cash payment request failed');
    return response.json();
  }

  /**
   * Verify payment status
   */
  async verifyPaymentStatus(transactionId: string): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/verify/${transactionId}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok) throw new Error('Payment verification failed');
    return response.json();
  }

  /**
   * Handle payment callback from provider
   */
  async handlePaymentCallback(provider: string, data: any): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/payments/callback/${provider}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });

    if (!response.ok) throw new Error('Callback processing failed');
    return response.json();
  }

  // ========== PROMO CODES ==========

  /**
   * Validate and apply promo code
   */
  async validatePromoCode(
    code: string,
    vertical: string,
    amount: number
  ): Promise<{ valid: boolean; discount: number; promo: PromoCode | null }> {
    const response = await fetch(`${this.baseUrl}/promo-codes/validate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        code,
        vertical,
        amount,
      }),
    });

    if (!response.ok) {
      return { valid: false, discount: 0, promo: null };
    }

    const data = await response.json();
    return data;
  }

  /**
   * Generate promo code (admin only)
   */
  async generatePromoCode(promoData: Partial<PromoCode>): Promise<PromoCode> {
    const response = await fetch(`${this.baseUrl}/admin/promo-codes/generate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        ...promoData,
        createdBy: localStorage.getItem('pearl_user_id'),
      }),
    });

    if (!response.ok) throw new Error('Promo code generation failed');
    return response.json();
  }

  /**
   * Get all promo codes (admin only)
   */
  async getAllPromoCodes(): Promise<PromoCode[]> {
    const response = await fetch(`${this.baseUrl}/admin/promo-codes`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch promo codes');
    return response.json();
  }

  /**
   * Generate promo code for cash payment confirmation
   */
  async generateCashPaymentPromo(bookingId: string, amount: number): Promise<PromoCode> {
    const response = await fetch(`${this.baseUrl}/payments/cash/generate-promo`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        bookingId,
        amount,
      }),
    });

    if (!response.ok) throw new Error('Failed to generate cash payment promo');
    return response.json();
  }

  // ========== SUBSCRIPTIONS ==========

  /**
   * Get available subscription plans
   */
  async getSubscriptionPlans(): Promise<Subscription[]> {
    const response = await fetch(`${this.baseUrl}/subscriptions/plans`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch subscription plans');
    return response.json();
  }

  /**
   * Subscribe to a plan
   */
  async subscribeToPlan(
    planId: string,
    paymentMethod: string,
    promoCode?: string
  ): Promise<Subscription> {
    const response = await fetch(`${this.baseUrl}/subscriptions/subscribe`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        planId,
        paymentMethod,
        promoCode,
      }),
    });

    if (!response.ok) throw new Error('Subscription failed');
    return response.json();
  }

  /**
   * Get user's current subscription
   */
  async getCurrentSubscription(): Promise<Subscription | null> {
    const response = await fetch(`${this.baseUrl}/subscriptions/current`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok || response.status === 404) return null;
    return response.json();
  }

  // ========== ADMIN OPERATIONS ==========

  /**
   * Enable/disable payment method for a service (admin only)
   */
  async updatePaymentMethodForService(
    paymentMethodId: string,
    vertical: string,
    enabled: boolean
  ): Promise<void> {
    const response = await fetch(
      `${this.baseUrl}/admin/payments/${paymentMethodId}/services/${vertical}`,
      {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
        },
        body: JSON.stringify({ enabled }),
      }
    );

    if (!response.ok) throw new Error('Failed to update payment method');
  }

  /**
   * Get admin payment configuration
   */
  async getAdminPaymentConfig(): Promise<{
    [key: string]: PaymentOption[];
  }> {
    const response = await fetch(`${this.baseUrl}/admin/payments/config`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch payment config');
    return response.json();
  }

  /**
   * Approve/Reject cash or bank transfer payment (admin only)
   */
  async approvePaymentRequest(
    transactionId: string,
    approve: boolean,
    reason?: string
  ): Promise<PaymentResponse> {
    const response = await fetch(`${this.baseUrl}/admin/payments/${transactionId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({
        approved: approve,
        reason,
      }),
    });

    if (!response.ok) throw new Error('Failed to process approval');
    return response.json();
  }

  // ========== UTILITIES ==========

  /**
   * Calculate payment breakdown with promo code
   */
  async calculatePaymentBreakdown(
    amount: number,
    paymentMethod: string,
    promoCode?: string
  ) {
    const option = SL_PAYMENT_OPTIONS.find(p => p.id === paymentMethod);
    if (!option) throw new Error('Payment method not found');

    let discount = 0;
    let finalAmount = amount;

    if (promoCode) {
      const promoValidation = await this.validatePromoCode(paymentMethod, '', amount);
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

  /**
   * Get transaction history
   */
  async getTransactionHistory(limit: number = 10): Promise<PaymentResponse[]> {
    const response = await fetch(`${this.baseUrl}/payments/history?limit=${limit}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch transaction history');
    return response.json();
  }

  /**
   * Request refund for payment
   */
  async requestRefund(transactionId: string, reason: string): Promise<any> {
    const response = await fetch(`${this.baseUrl}/payments/${transactionId}/refund`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('pearl_auth_token')}`,
      },
      body: JSON.stringify({ reason }),
    });

    if (!response.ok) throw new Error('Refund request failed');
    return response.json();
  }
}

export default new PaymentService();
