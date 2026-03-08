<?php

namespace App\Http\Controllers\Admin\DigitalCommerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DigitalCommerce\StoreDeliveryPayloadRequest;
use App\Models\Digital\DigitalDeliveryPayload;
use App\Models\Digital\DigitalProduct;
use Illuminate\Http\Request;

class DigitalDeliveryPayloadController extends Controller
{
    public function index(Request $request)
    {
        $productId = (int) $request->query('product_id', 0);

        $payloads = DigitalDeliveryPayload::query()
            ->with('product:id,title')
            ->when($productId > 0, fn ($q) => $q->where('digital_product_id', $productId))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $products = DigitalProduct::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.digital-delivery-payloads.index', compact('payloads', 'products', 'productId'));
    }

    public function create()
    {
        $products = DigitalProduct::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']);

        return view('admin.digital-delivery-payloads.create', compact('products'));
    }

    public function store(StoreDeliveryPayloadRequest $request)
    {
        $data = $request->validated();

        DigitalDeliveryPayload::create([
            'digital_product_id' => $data['digital_product_id'],
            'payload_type' => $data['payload_type'],
            'payload' => $this->payloadForType($data['payload_type'], (array) ($data['payload'] ?? [])),
            'notes' => $data['notes'] ?? null,
            'created_by_admin_id' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.digital-delivery-payloads.index')->with('success', 'Delivery payload created.');
    }

    public function destroy(DigitalDeliveryPayload $digital_delivery_payload)
    {
        if ($digital_delivery_payload->is_assigned) {
            return back()->with('error', 'Assigned payload cannot be deleted.');
        }

        $digital_delivery_payload->delete();

        return redirect()->route('admin.digital-delivery-payloads.index')->with('success', 'Payload deleted.');
    }

    private function payloadForType(string $type, array $payload): array
    {
        return match ($type) {
            'credential' => [
                'username' => (string) ($payload['username'] ?? ''),
                'email' => (string) ($payload['email'] ?? ''),
                'password' => (string) ($payload['password'] ?? ''),
            ],
            'code' => [
                'code' => (string) ($payload['code'] ?? ''),
            ],
            'link' => [
                'url' => (string) ($payload['url'] ?? ''),
            ],
            'file' => [
                'file_url' => (string) ($payload['file_url'] ?? ''),
            ],
            default => [
                'note' => (string) ($payload['note'] ?? ''),
            ],
        };
    }
}
