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

    public function dashboard(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $startDate = null;
        $endDate = null;

        switch ($filter) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case '7days':
                $startDate = now()->subDays(6);
                break;
            case '30days':
                $startDate = now()->subDays(29);
                break;
            case 'custom':
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                break;
            case 'all':
            default:
                $startDate = null;
                $endDate = null;
                break;
        }

        if ($startDate && !$endDate) {
            $endDate = now()->endOfDay();
        }

        // Base order query
        $ordersQuery = Order::whereIn('status', ['active', 'expired']);

        if ($startDate && $endDate) {
            $ordersQuery = $ordersQuery->whereBetween('buying_date', [$startDate, $endDate]);
        }

        $totalOrders = (clone $ordersQuery)->count();
        $activeOrders = (clone $ordersQuery)->where('status', 'active')->count();
        $expiredOrders = (clone $ordersQuery)->where('status', 'expired')->count();
        $users = User::count(); // Keep as total

        $currencies = ['PKR', 'USD', 'CAD', 'AED', 'EUR', 'GBP', 'SAR', 'INR'];
        $earningsByCurrency = [];

        foreach ($currencies as $currency) {
            $query = Order::whereIn('status', ['active', 'expired'])
                ->where('currency', $currency);

            if ($startDate && $endDate) {
                $query->whereBetween('buying_date', [$startDate, $endDate]);
            }

            $earningsByCurrency[$currency] = $query->sum('price');
        }

        return view('admin.dashboard', compact(
            'users',
            'activeOrders',
            'expiredOrders',
            'totalOrders',
            'earningsByCurrency',
            'filter',
            'startDate',
            'endDate'
        ));
    }
}
