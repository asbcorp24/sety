<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class TopicController extends Controller {
 public function index(){ return response()->json(Topic::orderBy('module')->orderBy('sort_order')->get()); }
 public function store(Request $r){
  $d=$this->data($r); $d['slug']=$this->uniqueSlug($d['slug'] ?: $d['title']);
  $d['content']=$d['content'] ?? '';
  if($r->hasFile('html_file')) $d['html_path']=$this->storeHtml($r,$d['slug']);
  return response()->json(Topic::create($d),201);
 }
 public function update(Request $r, Topic $topic){
  $d=$this->data($r); $d['slug']=$this->uniqueSlug($d['slug'] ?: $d['title'],$topic->id);
  $d['content']=$d['content'] ?? '';
  if($r->boolean('remove_html') && $topic->html_path){ Storage::disk('local')->delete($topic->html_path); $d['html_path']=null; }
  if($r->hasFile('html_file')){ if($topic->html_path) Storage::disk('local')->delete($topic->html_path); $d['html_path']=$this->storeHtml($r,$d['slug']); }
  $topic->update($d); return response()->json($topic->fresh());
 }
 public function destroy(Topic $topic){ if($topic->html_path) Storage::disk('local')->delete($topic->html_path); $topic->delete(); return response()->json(['ok'=>true]); }
 private function data(Request $r){ return $r->validate(['title'=>'required|max:255','slug'=>'nullable|max:255','module'=>'required|max:150','excerpt'=>'nullable|max:1000','content'=>'nullable|string','sort_order'=>'required|integer|min:0','is_published'=>'required|boolean','html_file'=>'nullable|file|max:3072','remove_html'=>'nullable|boolean']); }
 private function storeHtml(Request $r,string $slug): string {
  $file=$r->file('html_file'); $ext=strtolower($file->getClientOriginalExtension()); abort_unless(in_array($ext,['html','htm'],true),422,'Разрешены только .html и .htm файлы');
  $name=date('YmdHis').'-'.Str::random(8).'-'.$slug.'.html'; $path='practice-pages/'.$name; Storage::disk('local')->put($path,file_get_contents($file->getRealPath())); return $path;
 }
 private function uniqueSlug($value,$ignore=null){ $base=Str::slug($value); if(!$base)$base='topic'; $slug=$base;$i=2; while(Topic::where('slug',$slug)->when($ignore,fn($q)=>$q->where('id','!=',$ignore))->exists()) $slug=$base.'-'.$i++; return $slug; }
}
