@extends('admin.layout.app')

@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Total Clients</h5>
                    <h3>{{$users}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Active Orders</h5>
                    <h3>{{$orders}}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Admins</h5>
                    <h3>{{$admins}}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
