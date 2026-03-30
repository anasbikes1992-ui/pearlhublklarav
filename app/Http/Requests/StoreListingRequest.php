<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vertical' => ['required', 'string', 'in:property,stay,vehicle,taxi,event,sme'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['sometimes', 'string', 'in:draft,pending_verification,published,paused,archived'],
            'metadata' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'listing_type' => ['required', 'array'],
            'listing_type.type' => ['required', 'string', 'in:property,stay,vehicle,event,sme'],
            'listing_type.extra_json' => ['nullable', 'array'],
        ];
    }

    /**
     * Inject the authenticated user's ID as the provider.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated)) {
            $validated['provider_id'] = $this->user()->id;
        }

        return $validated;
    }
}
