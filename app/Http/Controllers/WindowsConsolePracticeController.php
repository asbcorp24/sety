<?php
namespace App\Http\Controllers;
use App\Models\PracticeAttempt;
use Illuminate\Http\Request;
class WindowsConsolePracticeController extends Controller {
 public function show(){
  $attempts=PracticeAttempt::where('user_id',auth()->id())->where('practice_key','windows-command-line')->latest()->limit(5)->get();
  return view('practice.windows-command-line',compact('attempts'));
 }
 public function store(Request $request){
  $data=$request->validate([
   'duration_seconds'=>'required|integer|min:1|max:86400','errors_count'=>'required|integer|min:0|max:10000',
   'correct_count'=>'required|integer|min:0|max:1000','total_count'=>'required|integer|min:1|max:1000',
   'score'=>'required|integer|min:0|max:100','passed'=>'required|boolean','details'=>'nullable|array|max:1000'
  ]);
  $data['user_id']=auth()->id(); $data['practice_key']='windows-command-line';
  $attempt=PracticeAttempt::create($data);
  return response()->json(['ok'=>true,'attempt_id'=>$attempt->id,'message'=>'Результат сохранён']);
 }
}
