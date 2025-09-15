@extends('admin.layout.app')

@section('page_title', 'All Purchases')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.purchasing.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">

                <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach ([10, 20, 30, 40, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            Show {{ $size }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="search" value="{{ request('search') }}" class="form-control w-auto"
                    placeholder="Search purchases...">

                <button type="submit" class="btn btn-primary">Search</button>

                <a href="{{ route('admin.purchasing.create') }}" class="btn btn-dark ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Add New Purchase
                </a>
            </form>
        </div>
    </div>


    <form id="bulkDeleteForm" action="{{ route('admin.purchasing.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 120px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="min-width: 80px;">Images</th>
                        <th style="min-width: 150px;">Panel Name</th>
                        <th style="min-width: 120px;">Cost Price</th>
                        <th style="min-width: 90px;">Currency</th>
                        <th style="min-width: 100px;">Quantity</th>
                        <th style="min-width: 140px;">Purchase Date</th>
                        <th style="min-width: 220px;">Note</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td><input type="checkbox" name="purchase_ids[]" value="{{ $purchase->id }}"></td>

                            {{-- Images (horizontal strip) --}}
                            <td>
                                @php
                                    $pics = $purchase->pictures ?? collect();
                                    $count = $pics->count();
                                @endphp

                                @if ($count)
                                    <div class="d-flex overflow-auto" style="max-width: 260px; gap: 6px;">
                                        @foreach ($pics as $pic)
                                            <img src="{{ asset($pic->path) }}" alt="ss" width="50" height="50"
                                                style="object-fit: cover; border-radius: 4px; cursor: pointer; flex-shrink: 0;"
                                                data-bs-toggle="modal" data-bs-target="#screenshotModal"
                                                onclick="showScreenshot('{{ asset($pic->path) }}')">
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>{{ $purchase->item_name }}</td>
                            <td>{{ $purchase->cost_price }}</td>
                            <td>{{ $purchase->currency }}</td>
                            <td>{{ $purchase->quantity }}</td>
                            <td>{{ $purchase->purchase_date }}</td>

                            {{-- Note (truncated) --}}
                            <td class="text-start">
                                @if (!empty($purchase->note))
                                    <span title="{{ $purchase->note }}">
                                        {{ \Illuminate\Support\Str::limit($purchase->note, 120) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('admin.purchasing.edit', $purchase) }}"
                                    class="btn btn-sm btn-outline-primary me-1">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2" onclick="return confirm('Delete selected purchases?')">
            Delete Selected
        </button>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $purchases->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <!-- Screenshot Lightbox Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark position-relative border-0">

                <!-- Close button -->
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2"
                    data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Image -->
                <div class="modal-body p-0 text-center">
                    <img id="modalScreenshot" src="" class="img-fluid"
                        style="width: 100%; max-height: 90vh; object-fit: contain;" alt="Screenshot">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Lightbox setter
        function showScreenshot(src) {
            const img = document.getElementById('modalScreenshot');
            if (img) img.src = src;
        }

        // Select all
        (function () {
            const all = document.getElementById('checkAll');
            if (!all) return;
            all.addEventListener('change', function () {
                document.querySelectorAll('input[name="purchase_ids[]"]').forEach(el => el.checked = all.checked);
            });
        })();
    </script>

@endsection
