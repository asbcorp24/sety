<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\WindowsConsolePracticeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TopicController as AdminTopicController;
use Illuminate\Support\Facades\Route;
Route::get('/',fn()=>view('home'))->name('home');
Route::middleware('guest')->group(function(){ Route::get('/login',[AuthController::class,'loginForm'])->name('login'); Route::post('/login',[AuthController::class,'login']); Route::get('/register',[AuthController::class,'registerForm'])->name('register'); Route::post('/register',[AuthController::class,'register']); });
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');
Route::get('/topics',[TopicController::class,'index'])->name('topics.index');
Route::get('/topics/{topic}',[TopicController::class,'show'])->name('topics.show');
Route::middleware('auth')->group(function(){
 Route::get('/practice/windows-command-line',[WindowsConsolePracticeController::class,'show'])->name('practice.windows');
 Route::get('/practice/windows-command-line/session',[WindowsConsolePracticeController::class,'session'])->name('practice.windows.session');
 Route::post('/practice/windows-command-line/result',[WindowsConsolePracticeController::class,'store'])->name('practice.windows.result');
});
Route::prefix('admin')->middleware(['auth','admin'])->name('admin.')->group(function(){ Route::get('/',[AdminController::class,'index'])->name('index'); Route::get('/users',fn()=>view('admin.users'))->name('users'); Route::get('/topics',fn()=>view('admin.topics'))->name('topics'); Route::get('/ajax/users',[AdminUserController::class,'index']); Route::post('/ajax/users',[AdminUserController::class,'store']); Route::put('/ajax/users/{user}',[AdminUserController::class,'update']); Route::delete('/ajax/users/{user}',[AdminUserController::class,'destroy']); Route::get('/ajax/topics',[AdminTopicController::class,'index']); Route::post('/ajax/topics',[AdminTopicController::class,'store']); Route::put('/ajax/topics/{topic}',[AdminTopicController::class,'update']); Route::delete('/ajax/topics/{topic}',[AdminTopicController::class,'destroy']); });
