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
            <form action="{{ route('purchasing.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">

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

                <a href="{{ route('purchasing.create') }}" class="btn btn-dark ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Add New Purchase
                </a>
            </form>
        </div>
    </div>


    <form id="bulkDeleteForm" action="{{ route('purchasing.bulkDelete') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="checkAll">
                        </th>
                        <th style="min-width: 80px;">Image</th>
                        <th style="min-width: 150px;">Panel Name</th>
                        <th style="min-width: 120px;">Cost Price</th>
                        <th style="min-width: 90px;">Currency</th>
                        <th style="min-width: 100px;">Quantity</th>
                        <th style="min-width: 140px;">Purchase Date</th>
                        <th style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td><input type="checkbox" name="purchase_ids[]" value="{{ $purchase->id }}"></td>
                            <td>
                                @if ($purchase->screenshot)
                                    <img src="{{ asset($purchase->screenshot) }}" 
                                         alt="purchase-img" class="img-thumbnail" width="60" height="60">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>
                            <td>{{ $purchase->item_name }}</td>
                            <td>{{ $purchase->cost_price }}</td>
                            <td>{{ $purchase->currency }}</td>
                            <td>{{ $purchase->quantity }}</td>
                            <td>{{ $purchase->purchase_date }}</td>
                            <td>
                                <a href="{{ route('purchasing.edit', $purchase) }}"
                                    class="btn btn-sm btn-outline-primary me-1">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-muted">No purchases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-danger mt-2" 
            onclick="return confirm('Delete selected purchases?')">
            Delete Selected
        </button>
    </form>

    <div class="mt-3 d-flex justify-content-center flex-column">
        {{ $purchases->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

@endsection
