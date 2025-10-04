@extends('layouts.admin')

@section('content')
<div class="container-fluid">
  <h3 class="mb-3">WhatsApp Trial Clicks</h3>

  <div class="row g-3 mb-3">
    <div class="col-auto"><span class="badge bg-primary">Today: {{ $today }}</span></div>
    <div class="col-auto"><span class="badge bg-success">Last 7d: {{ $last7 }}</span></div>
    <div class="col-auto"><span class="badge bg-secondary">Last 30d: {{ $last30 }}</span></div>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-md-3">
      <input type="text" name="q" class="form-control" placeholder="Search (page, fbp, fbc, campaign...)" value="{{ request('q') }}">
    </div>
    <div class="col-md-2">
      <input type="date" name="from" class="form-control" value="{{ request('from') }}">
    </div>
    <div class="col-md-2">
      <input type="date" name="to" class="form-control" value="{{ request('to') }}">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-3 text-end">
      <a class="btn btn-outline-secondary" href="{{ route('admin.trial_clicks.export') }}">Export CSV</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Time</th>
          <th>Event ID</th>
          <th>Page</th>
          <th>Destination</th>
          <th>UTM Campaign</th>
          <th>fbp</th>
          <th>fbc</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        @forelse($clicks as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
            <td title="{{ $c->event_id }}">{{ \Illuminate\Support\Str::limit($c->event_id, 8, '…') }}</td>
            <td><a href="{{ $c->page }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($c->page, 40) }}</a></td>
            <td><a href="{{ $c->destination }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($c->destination, 40) }}</a></td>
            <td>{{ $c->utm_campaign }}</td>
            <td title="{{ $c->fbp }}">{{ \Illuminate\Support\Str::limit($c->fbp, 10, '…') }}</td>
            <td title="{{ $c->fbc }}">{{ \Illuminate\Support\Str::limit($c->fbc, 10, '…') }}</td>
            <td>{{ $c->ip }}</td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted">No trial clicks yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $clicks->links() }}</div>
</div>
@endsection