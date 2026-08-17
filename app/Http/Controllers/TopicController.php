<?php
namespace App\Http\Controllers;
use App\Models\Topic;
class TopicController extends Controller {
 public function index(){ $topics=Topic::where('is_published',true)->orderBy('module')->orderBy('sort_order')->get()->groupBy('module'); return view('topics.index',compact('topics')); }
 public function show(Topic $topic){ abort_unless($topic->is_published || (auth()->check() && auth()->user()->isAdmin()),404); return view('topics.show',compact('topic')); }
}
