<?php

namespace App\Http\Controllers;

use App\Models\{Admin, Order, User};
use Illuminate\Support\Facades\{Hash, Session};
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            Session::put('admin_id', $admin->id);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout()
    {
        Session::forget('admin_id');
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $users = User::count();
        $activeOrders = Order::where('status', 'active')->count();
        $expiredOrders = Order::where('status', 'expired')->count();
        $totalOrders = Order::count();
        $admins = Admin::count();

        $currencies = ['PKR', 'USD', 'CAD', 'AED', 'EUR', 'GBP', 'SAR', 'INR'];
        $earningsByCurrency = [];
        $dailyEarningsByCurrency = [];

        foreach ($currencies as $currency) {
            $earningsByCurrency[$currency] = Order::whereIn('status', ['active', 'expired'])
                ->where('currency', $currency)
                ->sum('price');

            $dailyEarningsByCurrency[$currency] = Order::whereIn('status', ['active', 'expired'])
                ->where('currency', $currency)
                ->whereDate('buying_date', now())
                ->sum('price');
        }

        return view('admin.dashboard', [
            'users' => $users,
            'activeOrders' => $activeOrders,
            'expiredOrders' => $expiredOrders,
            'totalOrders' => $totalOrders,
            'admins' => $admins,
            'earningsByCurrency' => $earningsByCurrency,
            'dailyEarningsByCurrency' => $dailyEarningsByCurrency, // ✅ NEW
        ]);
    }
}
