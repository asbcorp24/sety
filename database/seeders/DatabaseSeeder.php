<?php
namespace Database\Seeders;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder { public function run(){ User::updateOrCreate(['email'=>'admin@example.com'],['name'=>'Администратор','password'=>Hash::make('admin12345'),'role'=>'admin','is_active'=>true]); $topics=[['Командная строка Windows','cmd-windows','Командная строка и файловая система'],['Командная строка Linux','bash-linux','Командная строка и файловая система'],['Пользователи и группы Windows','users-windows','Пользователи и права'],['Пользователи и группы Linux','users-linux','Пользователи и права'],['Основы IP-адресации','ip-basics','Сетевые технологии'],['Диагностика сети','network-diagnostics','Сетевые технологии'],['Основы Windows Server','windows-server-basics','Серверное администрирование'],['SSH и удаленное администрирование','ssh-basics','Серверное администрирование']]; foreach($topics as $i=>$t) Topic::updateOrCreate(['slug'=>$t[1]],['title'=>$t[0],'module'=>$t[2],'excerpt'=>'Заготовка практической работы. Содержание редактируется администратором.','content'=>'<h2>'.$t[0].'</h2><p>Содержание практической работы будет добавлено через административную панель.</p>','sort_order'=>$i+1,'is_published'=>true]); } }
