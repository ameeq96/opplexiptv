<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Clients\{
    StoreClientRequest,
    UpdateClientRequest,
    ImportClientsRequest
};
use App\Models\User;
use App\Services\Clients\{
    ClientQueryService,
    ClientCrudService,
    ClientImportService
};
use Illuminate\Http\Request;
use Nakanakaii\Countries\Countries;

class UserClientController extends Controller
{
    public function __construct(
        private ClientQueryService $query,
        private ClientCrudService $crud,
        private ClientImportService $importer,
    ) {}

    public function index(Request $request)
    {
        $builder = $this->query->base();
        $this->query->applyFilters($builder, $request);
        $this->query->applySorting($builder);
        $clients = $this->query->paginate($builder, $request);

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.create', compact('countries'));
    }

    public function store(StoreClientRequest $request)
    {
        $this->crud->create($request->validated());
        return redirect()->route('admin.clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(User $client)
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.edit', compact('client', 'countries'));
    }

    public function update(UpdateClientRequest $request, User $client)
    {
        $this->crud->update($request->validated(), $client);
        return redirect()->route('admin.clients.index')->with('success', 'Client updated.');
    }

    public function destroy(User $client)
    {
        $this->crud->delete($client);
        return back()->with('success', 'Client deleted.');
    }

    public function import(ImportClientsRequest $request)
    {
        @set_time_limit(800);
        $count = $this->importer->importFromCsv($request->file('csv_file'));
        return redirect()->route('admin.clients.index')->with('success', "{$count} clients imported successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('client_ids', []);
        if (empty($ids)) {
            return back()->with('success', 'No clients selected.');
        }

        $deleted = $this->crud->bulkDelete($ids);
        return back()->with('success', "{$deleted} client(s) deleted successfully.");
    }
}
