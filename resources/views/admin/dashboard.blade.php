@extends('admin.layout.app')

@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Total Clients</h5>
                    <h3>12</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Active Orders</h5>
                    <h3>8</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Pending Messages</h5>
                    <h3>3</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
