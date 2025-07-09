<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Nakanakaii\Countries\Countries;

class UserClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }

        $clients = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.clients.index', compact('clients'));
    }


    public function create()
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users',
            'phone' => 'required|unique:users',
        ]);

        $data = $request->all();
        $data['password'] = bcrypt('defaultpassword');

        User::create($data);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(User $client)
    {
        $countries = collect(Countries::all())->sortBy('name');
        return view('admin.clients.edit', compact('client', 'countries'));
    }

    public function update(Request $request, User $client)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $client->id,
            'phone' => 'required|unique:users,phone,' . $client->id,
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function destroy(User $client)
    {
        $client->delete();
        return back()->with('success', 'Client deleted.');
    }

    public function import(Request $request)
    {
        set_time_limit(800);

        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $file = fopen($request->file('csv_file'), 'r');
        $header = fgetcsv($file);

        $imported = 0;

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            $rawPhone = (string) $data['phone'];

            if (stripos($rawPhone, 'e') !== false) {
                $phone = number_format((float)$rawPhone, 0, '', '');
            } else {
                $phone = preg_replace('/[^0-9]/', '', $rawPhone);
            }

            if (substr($phone, 0, 1) === '0') {
                $phone = '+92' . substr($phone, 1);
            }

            if (User::where('phone', $phone)->exists()) {
                continue;
            }

            $email = $data['email'] ?? null;

            if (!empty($email) && User::where('email', $email)->exists()) {
                continue;
            }

            if (empty($email)) {
                $namePart = preg_replace('/[^A-Za-z0-9]/', '', $data['name']);
                $namePart = substr($namePart, 0, 30);
                $baseEmail = strtolower($namePart . '@gmail.com');
                $email = $baseEmail;

                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = strtolower($namePart . $counter . '@gmail.com');
                    $counter++;
                }
            }

            $validator = Validator::make([
                'name' => $data['name'],
                'email' => $email,
                'phone' => $phone,
                'country' => $data['country'] ?? null,
            ], [
                'name' => 'required',
                'email' => 'nullable|email',
                'phone' => 'required',
                'country' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                continue;
            }

            User::create([
                'name' => $data['name'],
                'email' => $email,
                'phone' => $phone,
                'country' => $data['country'] ?? null,
                'password' => Hash::make('defaultpassword'),
            ]);

            $imported++;
        }

        fclose($file);

        return redirect()->route('clients.index')->with('success', "$imported clients imported successfully.");
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('client_ids');

        if (!$ids) {
            return back()->with('success', 'No clients selected.');
        }

        User::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' client(s) deleted successfully.');
    }
}
