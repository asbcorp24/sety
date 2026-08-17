<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller {
 public function loginForm(){ return view('auth.login'); }
 public function registerForm(){ return view('auth.register'); }
 public function login(Request $r){ $data=$r->validate(['email'=>'required|email','password'=>'required']); if(Auth::attempt($data, $r->boolean('remember'))){ if(!Auth::user()->is_active){ Auth::logout(); return back()->withErrors(['email'=>'Учетная запись отключена.']); } $r->session()->regenerate(); return redirect()->intended(route('home')); } return back()->withErrors(['email'=>'Неверный email или пароль.'])->onlyInput('email'); }
 public function register(Request $r){ $data=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users,email','password'=>'required|min:8|confirmed']); $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'student','is_active'=>true]); Auth::login($user); $r->session()->regenerate(); return redirect()->route('home'); }
 public function logout(Request $r){ Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken(); return redirect()->route('login'); }
}
