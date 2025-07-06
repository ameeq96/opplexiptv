<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserClientController extends Controller
{
    public function index()
    {
        $clients = User::orderBy('id', 'desc')->get();
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users',
            'phone' => 'required|unique:users',
        ]);

        $data = $request->all();
        $data['password'] = bcrypt('defaultpassword'); // default password

        User::create($data);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(User $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, User $client)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,'.$client->id,
            'phone' => 'required|unique:users,phone,'.$client->id,
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function destroy(User $client)
    {
        $client->delete();
        return back()->with('success', 'Client deleted.');
    }
}
