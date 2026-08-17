<?php
namespace Database\Seeders;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class DatabaseSeeder extends Seeder {
 public function run(){
  User::updateOrCreate(['email'=>'admin@example.com'],['name'=>'Администратор','password'=>Hash::make('admin12345'),'role'=>'admin','is_active'=>true]);
  $topics=[['Знакомство с командной строкой Windows','cmd-windows','Командная строка и файловая система'],['Командная строка Linux','bash-linux','Командная строка и файловая система'],['Пользователи и группы Windows','users-windows','Пользователи и права'],['Пользователи и группы Linux','users-linux','Пользователи и права'],['Основы IP-адресации','ip-basics','Сетевые технологии'],['Диагностика сети','network-diagnostics','Сетевые технологии'],['Основы Windows Server','windows-server-basics','Серверное администрирование'],['SSH и удаленное администрирование','ssh-basics','Серверное администрирование']];
  foreach($topics as $i=>$t){
   $content='<h2>'.$t[0].'</h2><p>Содержание практической работы будет добавлено через административную панель.</p>';
   $data=['title'=>$t[0],'module'=>$t[2],'excerpt'=>'Практическая работа с интерактивными заданиями.','content'=>$content,'sort_order'=>$i+1,'is_published'=>true];
   if($t[1]==='cmd-windows'){
    $source=resource_path('practice-pages/windows-command-line.html');
    if(is_file($source)){
     $htmlPath='practice-pages/windows-command-line.html';
     Storage::disk('local')->put($htmlPath,file_get_contents($source));
     $data['html_path']=$htmlPath;
     $data['content']='<h2>Знакомство с командной строкой Windows</h2><p>Интерактивный практикум: CMD, PowerShell, dir, cd, mkdir, copy, move, del, переменные окружения, процессы и системная информация.</p>';
    }
   }
   Topic::updateOrCreate(['slug'=>$t[1]],$data);
  }
 }
}
