<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;

class WhatsAppController extends Controller
{
    public function broadcast()
    {
        $users = User::whereNotNull('phone')->get();
        return view('admin.whatsapp.broadcast', compact('users'));
    }
}

