<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Services\CaptchaService;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function showLogin(CaptchaService $captcha)
    {
        return view('admin.auth.login', $captcha->generate());
    }


    public function login(LoginRequest $request, CaptchaService $captcha)
    {
        if (!$captcha->check($request->input('captcha'))) {
            return back()->withErrors([
                'captcha' => __('document_ui.contact.captcha_error'),
            ]);
        }

        $credentials = $request->only('email', 'password');


        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }


        return back()->withErrors(['email' => 'Invalid credentials.']);
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
