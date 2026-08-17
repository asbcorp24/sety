<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class TopicController extends Controller {
 public function index(){ return response()->json(Topic::orderBy('module')->orderBy('sort_order')->get()); }
 public function store(Request $r){ $d=$this->data($r); $d['slug']=$this->uniqueSlug($d['slug'] ?: $d['title']); return response()->json(Topic::create($d),201); }
 public function update(Request $r, Topic $topic){ $d=$this->data($r); $d['slug']=$this->uniqueSlug($d['slug'] ?: $d['title'],$topic->id); $topic->update($d); return response()->json($topic->fresh()); }
 public function destroy(Topic $topic){ $topic->delete(); return response()->json(['ok'=>true]); }
 private function data(Request $r){ return $r->validate(['title'=>'required|max:255','slug'=>'nullable|max:255','module'=>'required|max:150','excerpt'=>'nullable|max:1000','content'=>'required|string','sort_order'=>'required|integer|min:0','is_published'=>'required|boolean']); }
 private function uniqueSlug($value,$ignore=null){ $base=Str::slug($value); if(!$base)$base='topic'; $slug=$base;$i=2; while(Topic::where('slug',$slug)->when($ignore,fn($q)=>$q->where('id','!=',$ignore))->exists()) $slug=$base.'-'.$i++; return $slug; }
}
