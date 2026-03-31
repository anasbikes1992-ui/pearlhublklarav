<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends BaseApiController
{
    public function __construct(private readonly BookingService $bookingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->bookingService->listForUser($request->user()->id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'uuid', 'exists:listings,id'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
        ]);

        $booking = $this->bookingService->createBooking($request->user()->id, $validated);

        return $this->success($booking, 'Booking created', 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        // Customers may only view their own bookings.
        if ($booking->customer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Forbidden', [], 403);
        }

        return $this->success($booking->load('listing'));
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        // Only the booking owner or an admin may update.
        if ($booking->customer_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return $this->error('Forbidden', [], 403);
        }
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,confirmed,cancelled,completed'],
            'payment_status' => ['sometimes', 'string', 'in:pending,paid,failed,refunded'],
        ]);

        return $this->success($this->bookingService->updateBooking($booking, $validated), 'Booking updated');
    }
}
