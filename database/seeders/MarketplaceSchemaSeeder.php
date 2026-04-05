<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BrokerConsent;
use App\Models\CashbackRecord;
use App\Models\Escrow;
use App\Models\IdempotencyKey;
use App\Models\Listing;
use App\Models\ListingType;
use App\Models\Message;
use App\Models\OwnershipDocument;
use App\Models\PromoCode;
use App\Models\Review;
use App\Models\TaxiRide;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VerificationAudit;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceSchemaSeeder extends Seeder
{
    public function run(): void
    {
        $providers = User::query()->where('role', User::ROLE_PROVIDER)->get();
        $customers = User::query()->where('role', User::ROLE_CUSTOMER)->get();
        $drivers = User::query()->where('role', User::ROLE_DRIVER)->get();
        $admins = User::query()->where('role', User::ROLE_ADMIN)->get();
        $brokers = User::query()->whereIn('role', ['broker', User::ROLE_PROVIDER])->get();

        $listings = Listing::query()->with('provider')->get();

        foreach ($listings as $listing) {
            ListingType::query()->firstOrCreate(
                ['listing_id' => $listing->id],
                $this->listingTypePayload($listing->vertical)
            );

            VerificationAudit::query()->firstOrCreate(
                ['listing_id' => $listing->id],
                [
                    'inspector_id' => $listing->inspector_id ?? $admins->first()?->id ?? $providers->first()?->id,
                    'status' => 'approved',
                    'notes' => 'Seeded QA verification for demo listing.',
                    'inspected_at' => now()->subDays(fake()->numberBetween(1, 45)),
                    'photo_urls' => ['/storage/seed/inspection-' . fake()->numberBetween(100, 999) . '.jpg'],
                ]
            );

            if ($listing->vertical === 'property') {
                $broker = $brokers->random();

                OwnershipDocument::query()->firstOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'uploaded_by' => $listing->provider_id,
                        'type' => 'deed_title',
                        'file_path' => 'ownership/deed-' . Str::lower(Str::random(10)) . '.pdf',
                        'owner_name' => $listing->provider?->full_name ?? 'Property Owner',
                        'nic_or_company' => 'PV-REG-' . fake()->numberBetween(1000, 9999),
                        'status' => 'approved',
                        'reviewed_by' => $admins->first()?->id,
                        'review_notes' => 'Auto-approved for seeded dataset.',
                        'reviewed_at' => now()->subDays(fake()->numberBetween(1, 15)),
                    ]
                );

                BrokerConsent::query()->firstOrCreate(
                    ['listing_id' => $listing->id],
                    [
                        'broker_id' => $broker->id,
                        'owner_id' => $listing->provider_id,
                        'deed_file_path' => 'broker/deed-consent-' . Str::lower(Str::random(8)) . '.pdf',
                        'authorization_file_path' => 'broker/auth-' . Str::lower(Str::random(8)) . '.pdf',
                        'indemnity_accepted' => true,
                        'status' => 'approved',
                        'reviewed_by' => $admins->first()?->id,
                        'review_notes' => 'Broker mandate validated in seed flow.',
                        'reviewed_at' => now()->subDays(fake()->numberBetween(1, 10)),
                    ]
                );
            }
        }

        $seededBookings = collect();

        foreach ($listings->take(24) as $listing) {
            $customer = $customers->random();
            $startAt = now()->addDays(fake()->numberBetween(1, 21));
            $endAt = (clone $startAt)->addDays(fake()->numberBetween(1, 4));

            $booking = Booking::query()->create([
                'listing_id' => $listing->id,
                'customer_id' => $customer->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'status' => fake()->randomElement(['pending', 'confirmed', 'completed']),
                'total_amount' => $listing->price,
                'currency' => 'LKR',
                'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded']),
                'notes' => 'Seeded booking for integration and dashboard testing.',
            ]);

            Escrow::query()->firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'amount' => $booking->total_amount,
                    'currency' => $booking->currency,
                    'status' => $booking->status === 'completed' ? 'released' : 'held',
                    'released_at' => $booking->status === 'completed' ? now()->subDay() : null,
                    'meta' => ['seed' => true, 'flow' => 'booking-escrow'],
                ]
            );

            $customerWallet = Wallet::query()->where('user_id', $customer->id)->first();
            $providerWallet = Wallet::query()->where('user_id', $listing->provider_id)->first();

            if ($customerWallet) {
                Transaction::query()->create([
                    'wallet_id' => $customerWallet->id,
                    'booking_id' => $booking->id,
                    'provider' => fake()->randomElement(['payhere', 'stripe', 'mock_gateway']),
                    'external_reference' => 'TX-' . Str::upper(Str::random(10)),
                    'amount' => $booking->total_amount,
                    'currency' => 'LKR',
                    'status' => $booking->payment_status === 'paid' ? 'completed' : 'pending',
                    'meta' => ['seed' => true, 'listing_id' => $listing->id],
                ]);

                WalletTransaction::query()->create([
                    'user_id' => $customer->id,
                    'type' => 'debit',
                    'amount' => $booking->total_amount,
                    'currency' => 'LKR',
                    'reference_type' => 'booking',
                    'reference_id' => $booking->id,
                    'status' => $booking->payment_status === 'paid' ? 'success' : 'pending',
                    'meta' => ['seed' => true],
                ]);
            }

            if ($providerWallet) {
                WalletTransaction::query()->create([
                    'user_id' => $listing->provider_id,
                    'type' => 'credit',
                    'amount' => round((float) $booking->total_amount * 0.92, 2),
                    'currency' => 'LKR',
                    'reference_type' => 'booking_payout',
                    'reference_id' => $booking->id,
                    'status' => $booking->status === 'completed' ? 'success' : 'pending',
                    'meta' => ['seed' => true],
                ]);
            }

            $seededBookings->push($booking);
        }

        foreach ($listings as $listing) {
            $reviewers = $customers->shuffle()->take(2);

            foreach ($reviewers as $reviewer) {
                Review::query()->firstOrCreate(
                    [
                        'listing_id' => $listing->id,
                        'reviewer_id' => $reviewer->id,
                    ],
                    [
                        'rating' => fake()->randomFloat(1, 3.5, 5.0),
                        'comment' => fake()->sentence(14),
                    ]
                );
            }

            PromoCode::query()->firstOrCreate(
                ['code' => 'PHB' . strtoupper(Str::random(6))],
                [
                    'listing_id' => fake()->boolean(65) ? $listing->id : null,
                    'issued_by' => $listing->provider_id,
                    'type' => fake()->randomElement(['sale_confirmation', 'discount_fixed', 'discount_percent']),
                    'value' => fake()->randomElement([500, 1000, 1500, 5, 10]),
                    'vertical' => $listing->vertical,
                    'max_uses' => fake()->numberBetween(1, 15),
                    'used_count' => fake()->numberBetween(0, 3),
                    'expires_at' => now()->addDays(fake()->numberBetween(15, 120)),
                    'is_active' => true,
                ]
            );
        }

        foreach ($seededBookings->take(12) as $booking) {
            $listing = $listings->firstWhere('id', $booking->listing_id);
            if (! $listing) {
                continue;
            }

            CashbackRecord::query()->firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'customer_id' => $booking->customer_id,
                    'provider_id' => $listing->provider_id,
                    'sale_amount' => $booking->total_amount,
                    'cashback_rate' => 0.005,
                    'cashback_amount' => round((float) $booking->total_amount * 0.005, 2),
                    'currency' => 'LKR',
                    'status' => fake()->randomElement(['pending', 'approved', 'credited']),
                    'confirmed_at' => now()->subDays(fake()->numberBetween(1, 5)),
                    'credited_at' => fake()->boolean(60) ? now()->subDays(fake()->numberBetween(1, 3)) : null,
                ]
            );
        }

        foreach ($customers->take(8) as $customer) {
            TaxiRide::query()->create([
                'customer_id' => $customer->id,
                'driver_id' => $drivers->isNotEmpty() ? $drivers->random()->id : null,
                'status' => fake()->randomElement(['searching', 'accepted', 'in_progress', 'completed']),
                'pickup_latitude' => fake()->latitude(5.9, 9.9),
                'pickup_longitude' => fake()->longitude(79.6, 81.9),
                'dropoff_latitude' => fake()->latitude(5.9, 9.9),
                'dropoff_longitude' => fake()->longitude(79.6, 81.9),
                'fare_estimate' => fake()->randomFloat(2, 500, 4500),
                'final_fare' => fake()->boolean(60) ? fake()->randomFloat(2, 600, 5200) : null,
                'currency' => 'LKR',
                'started_at' => fake()->boolean(65) ? now()->subMinutes(fake()->numberBetween(30, 360)) : null,
                'completed_at' => fake()->boolean(45) ? now()->subMinutes(fake()->numberBetween(10, 120)) : null,
            ]);
        }

        foreach (range(1, 15) as $index) {
            IdempotencyKey::query()->firstOrCreate(
                ['key' => 'seed-key-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT)],
                [
                    'gateway' => fake()->randomElement(['payhere', 'stripe', 'manual']),
                    'payload_hash' => hash('sha256', 'seed-payload-' . $index),
                    'status' => fake()->randomElement(['received', 'processing', 'completed']),
                    'processed_at' => fake()->boolean(50) ? now()->subMinutes(fake()->numberBetween(5, 600)) : null,
                    'meta' => ['seed' => true, 'index' => $index],
                ]
            );
        }

        foreach ($seededBookings->take(10) as $booking) {
            $listing = $listings->firstWhere('id', $booking->listing_id);
            if (! $listing) {
                continue;
            }

            Message::query()->create([
                'listing_id' => $booking->listing_id,
                'sender_id' => $booking->customer_id,
                'receiver_id' => $listing->provider_id,
                'message' => fake()->sentence(18),
                'is_voice' => false,
                'original_text' => null,
                'translated_text' => null,
            ]);
        }
    }

    private function listingTypePayload(string $vertical): array
    {
        return match ($vertical) {
            'property' => [
                'type' => 'property',
                'sqft' => fake()->numberBetween(800, 5200),
                'beds' => fake()->numberBetween(1, 6),
                'baths' => fake()->numberBetween(1, 5),
                'deed_status' => fake()->randomElement(['clear', 'mortgaged', 'pending_transfer']),
            ],
            'stay' => [
                'type' => 'stay',
                'room_type' => fake()->randomElement(['single', 'double', 'suite', 'villa']),
                'amenities_json' => ['wifi', 'pool', 'parking'],
                'check_in_out_times' => ['check_in' => '14:00', 'check_out' => '11:00'],
            ],
            'vehicle', 'taxi' => [
                'type' => $vertical,
                'transmission' => fake()->randomElement(['auto', 'manual']),
                'fuel_type' => fake()->randomElement(['petrol', 'diesel', 'hybrid', 'electric']),
                'daily_rate' => fake()->randomFloat(2, 3500, 30000),
                'with_driver' => $vertical === 'taxi' || fake()->boolean(40),
            ],
            'event' => [
                'type' => 'event',
                'venue_map_json' => ['zoneA' => 120, 'zoneB' => 80],
                'ticket_tiers_json' => [
                    ['name' => 'Standard', 'price' => 2500],
                    ['name' => 'VIP', 'price' => 7000],
                ],
                'qr_secret' => Str::lower(Str::random(20)),
            ],
            default => [
                'type' => $vertical,
                'extra_json' => ['category' => 'seeded', 'vertical' => $vertical],
            ],
        };
    }
}
