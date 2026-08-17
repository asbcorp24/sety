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
   'details'=>'nullable|array|max:1000'
  ]);
  abort_if($data['correct_count']>$data['total_count'],422,'Некорректный результат');
  $attempts=max(1,$data['correct_count']+$data['errors_count']);
  $data['score']=(int)round($data['correct_count']/$attempts*100);
  $data['passed']=$data['correct_count']===$data['total_count'] && $data['score']>=70;
  $data['user_id']=auth()->id(); $data['practice_key']='windows-command-line';
  $attempt=PracticeAttempt::create($data);
  return response()->json(['ok'=>true,'attempt_id'=>$attempt->id,'score'=>$attempt->score,'passed'=>$attempt->passed,'message'=>'Результат сохранён']);
 }
}
