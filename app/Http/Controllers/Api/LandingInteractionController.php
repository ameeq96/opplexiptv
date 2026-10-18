<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Clients\LandingInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LandingInteractionController extends Controller
{
    public function event(Request $request, LandingInteractionService $interactions): JsonResponse
    {
        $this->checkRequest($request);
        $data = $request->validate($this->baseRules() + $this->dimensionRules() + [
            'event' => ['required', Rule::in(['visit', 'checkout_clicked'])],
            'session_id' => ['required', 'uuid'],
            'analytics_consent' => ['required', 'accepted'],
            'package_id' => ['nullable', 'integer', 'min:1', 'max:2147483647'],
        ]);
        $data = $this->validatedDimensions($data, $interactions);
        $interactions->assertOrigin($data['brand'], (string) $request->header('Origin'));
        $interactions->recordEvent($data);

        return response()->json(['ok' => true], 202);
    }

    public function lead(Request $request, LandingInteractionService $interactions): JsonResponse
    {
        $this->checkRequest($request);
        $data = $request->validate($this->baseRules() + $this->dimensionRules() + [
            'name' => ['required', 'string', 'min:2', 'max:120', 'not_regex:/[\x00-\x1F\x7F]/'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9 ()-]+$/'],
            'message' => ['nullable', 'string', 'max:2000', 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'],
            'channel' => ['required', Rule::in(['whatsapp', 'tawk'])],
            'contact_consent' => ['required', 'accepted'],
            'analytics_consent' => ['sometimes', 'boolean'],
            'session_id' => [Rule::prohibitedIf(!$request->boolean('analytics_consent')), 'nullable', 'uuid'],
        ]);
        $data = $this->validatedDimensions($data, $interactions);
        $interactions->assertOrigin($data['brand'], (string) $request->header('Origin'));
        $data['phone'] = User::normalizePhone($data['phone']);
        if ($data['phone'] === null) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid phone number with its country code (8–15 digits).']);
        }
        $interactions->recordLead($data);

        return response()->json(['ok' => true], 202);
    }

    private function checkRequest(Request $request): void
    {
        abort_unless($request->isJson(), 415, 'Send a JSON request.');
        abort_if(strlen($request->getContent()) > 8192, 413, 'Request is too large.');
    }

    private function baseRules(): array
    {
        return [
            'brand' => ['required', 'string', Rule::in(array_keys(LandingInteractionService::BRANDS))],
            'event_id' => ['required', 'uuid'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    private function dimensionRules(): array
    {
        return ['locale' => ['sometimes', 'nullable', 'string', 'max:12'], 'page' => ['sometimes', 'nullable', 'string', 'max:160']];
    }

    private function validatedDimensions(array $data, LandingInteractionService $interactions): array
    {
        if (!array_key_exists('locale', $data) && !array_key_exists('page', $data)) return $data;
        if (empty($data['analytics_consent']) || empty($data['session_id'])) {
            throw ValidationException::withMessages(['locale' => 'Page/language measurement requires analytics permission and a valid session.']);
        }
        if (!$interactions->validLocale($data['locale'] ?? null)) {
            throw ValidationException::withMessages(['locale' => 'Choose one of the supported landing languages.']);
        }
        if (!array_key_exists('page', $data) || !$interactions->validPage($data['brand'], $data['page'] ?? '')) {
            throw ValidationException::withMessages(['page' => 'Send an existing brand page slug, or an empty string for its homepage.']);
        }
        $data['page'] = $data['page'] ?? '';

        return $data;
    }
}
