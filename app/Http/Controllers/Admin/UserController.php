<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller {
 public function index(){ return response()->json(User::orderByDesc('id')->get()); }
 public function store(Request $r){ $d=$r->validate(['name'=>'required|max:120','email'=>'required|email|unique:users,email','password'=>'required|min:8','role'=>'required|in:admin,student']); $d['password']=Hash::make($d['password']); $d['is_active']=true; return response()->json(User::create($d),201); }
 public function update(Request $r, User $user){ $d=$r->validate(['name'=>'required|max:120','email'=>'required|email|unique:users,email,'.$user->id,'role'=>'required|in:admin,student','is_active'=>'required|boolean','password'=>'nullable|min:8']); if(empty($d['password'])) unset($d['password']); else $d['password']=Hash::make($d['password']); $user->update($d); return response()->json($user->fresh()); }
 public function destroy(User $user){ abort_if(auth()->id()===$user->id,422,'Нельзя удалить текущего администратора.'); $user->delete(); return response()->json(['ok'=>true]); }
}
