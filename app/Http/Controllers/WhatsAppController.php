<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function broadcast(Request $request)
    {
        $query = User::whereNotNull('phone');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return view('admin.whatsapp.broadcast', compact('users'));
    }
}
