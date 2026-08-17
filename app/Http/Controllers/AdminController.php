<?php
namespace App\Http\Controllers;
use App\Models\Topic;
use App\Models\User;
class AdminController extends Controller { public function index(){ return view('admin.index',['usersCount'=>User::count(),'topicsCount'=>Topic::count(),'publishedCount'=>Topic::where('is_published',true)->count()]); } }
