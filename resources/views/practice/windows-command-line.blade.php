@extends('layouts.app')
@section('title','Знакомство с командной строкой Windows')
@section('content')
<style>
.practice-shell{background:#0c0c0c;color:#ddd;border-radius:10px;min-height:410px;box-shadow:0 8px 28px #0002;overflow:hidden}.terminal-head{background:#202020;color:#fff;padding:9px 14px;font-size:14px}.terminal-body{height:350px;overflow:auto;padding:14px;font:15px/1.45 Consolas,'Courier New',monospace;white-space:pre-wrap}.terminal-input-row{display:flex;align-items:center;padding:0 14px 14px;font:15px Consolas,monospace}.terminal-prompt{color:#eee;white-space:nowrap}.terminal-input{flex:1;background:transparent;border:0;outline:0;color:#fff;font:inherit;min-width:0}.line-ok{color:#67e480}.line-error{color:#ff7777}.line-info{color:#6cb8ff}.task-card{border-left:4px solid #0d6efd}.stat{min-width:110px}.section-chip{font-size:.8rem}.practice-disabled{opacity:.55;pointer-events:none}
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
 <div><h1 class="h3 mb-1">Знакомство с командной строкой Windows</h1><div class="text-muted">CMD, PowerShell, файловые команды, переменные окружения, процессы и системная информация.</div></div>
 <a href="{{ route('topics.index') }}" class="btn btn-outline-secondary">← К темам</a>
</div>
<div class="row g-3 mb-3">
 <div class="col-lg-8"><div class="card task-card h-100"><div class="card-body">
  <div class="d-flex justify-content-between gap-2"><span id="sectionBadge" class="badge text-bg-primary section-chip">Практикум</span><strong id="progressText">0 / 0</strong></div>
  <h2 id="taskTitle" class="h5 mt-3">Выберите режим и нажмите «Начать»</h2>
  <p id="taskText" class="mb-2 text-secondary">В полном режиме задания из всех разделов перемешиваются случайным образом.</p>
  <div id="hintBox" class="alert alert-warning py-2 d-none mb-0"></div>
 </div></div></div>
 <div class="col-lg-4"><div class="card h-100"><div class="card-body">
  <label class="form-label fw-semibold">Режим</label><select id="mode" class="form-select mb-2"><option value="all">Весь практикум — 110 заданий</option></select>
  <div class="d-flex gap-2"><button id="startBtn" class="btn btn-success flex-fill">Начать</button><button id="hintBtn" class="btn btn-outline-warning" disabled>Подсказка</button></div>
 </div></div></div>
</div>
<div class="d-flex flex-wrap gap-2 mb-3">
 <div class="card stat"><div class="card-body py-2"><small class="text-muted">Время</small><div id="timer" class="fs-5 fw-bold">00:00</div></div></div>
 <div class="card stat"><div class="card-body py-2"><small class="text-muted">Ошибки</small><div id="errors" class="fs-5 fw-bold">0</div></div></div>
 <div class="card stat"><div class="card-body py-2"><small class="text-muted">Верно</small><div id="correct" class="fs-5 fw-bold">0</div></div></div>
 <div class="card stat"><div class="card-body py-2"><small class="text-muted">Результат</small><div id="score" class="fs-5 fw-bold">0%</div></div></div>
</div>
<div class="practice-shell mb-4" id="shell">
 <div class="terminal-head d-flex justify-content-between"><span>Windows Terminal — учебный эмулятор</span><span id="shellMode">CMD</span></div>
 <div id="terminal" class="terminal-body"><div>Microsoft Windows [Version 10.0.19045.0000]</div><div>(c) Microsoft Corporation. Учебный эмулятор.</div><div class="line-info">Команды выполняются только в виртуальной среде браузера.</div><br></div>
 <div class="terminal-input-row"><span id="prompt" class="terminal-prompt">C:\Users\Student&gt;&nbsp;</span><input id="commandInput" class="terminal-input" autocomplete="off" spellcheck="false" disabled></div>
</div>
<div id="resultBox" class="alert d-none"></div>
@if($attempts->count())
<div class="card"><div class="card-header fw-semibold">Последние попытки</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Дата</th><th>Результат</th><th>Ошибки</th><th>Время</th><th>Статус</th></tr></thead><tbody>@foreach($attempts as $a)<tr><td>{{ $a->created_at->format('d.m.Y H:i') }}</td><td>{{ $a->score }}%</td><td>{{ $a->errors_count }}</td><td>{{ gmdate('i:s',$a->duration_seconds) }}</td><td>{!! $a->passed ? '<span class="badge text-bg-success">Зачёт</span>' : '<span class="badge text-bg-danger">Не зачёт</span>' !!}</td></tr>@endforeach</tbody></table></div></div>
@endif
@endsection
@push('scripts')
<script>
(()=>{
const $=id=>document.getElementById(id), sections={
 cmd:'CMD — основы',powershell:'PowerShell — основы',dir:'DIR',cd:'CD',mkdir:'MKDIR',copy:'COPY',move:'MOVE',del:'DEL',env:'Переменные окружения',process:'Процессы',system:'Системная информация'
};
const tasks=[]; const add=(section,title,prompt,answers,hint,shell='CMD')=>tasks.push({id:section+'-'+(tasks.filter(x=>x.section===section).length+1),section,title,prompt,answers:Array.isArray(answers)?answers:[answers],hint,shell});
[
 ['Запустите классическую командную строку Windows.','cmd','Введите команду запуска CMD.'],['Очистите окно CMD.','cls','Команда очистки экрана.'],['Выведите текст Hello Windows.','echo Hello Windows','Используйте echo.'],['Откройте встроенную справку CMD.','help','Введите help.'],['Измените заголовок окна на Practice.','title Practice','Используйте title.'],['Покажите, где расположен cmd.exe.','where cmd','Используйте where.'],['Покажите справку по cmd.exe.','cmd /?','Ключ /?.'],['Выполните echo test через новый cmd и завершите его.','cmd /c echo test','Ключ /c.'],['Откройте новый CMD и оставьте его открытым.','cmd /k','Ключ /k.'],['Выведите текущую версию Windows короткой командой.','ver','Введите ver.']
].forEach(x=>add('cmd','Практика CMD',x[0],x[1],x[2]));
[
 ['Запустите Windows PowerShell.','powershell','Введите powershell.'],['Получите список доступных команд.','Get-Command','Командлет Get-Command.'],['Откройте общую справку PowerShell.','Get-Help','Командлет Get-Help.'],['Очистите экран PowerShell.',['Clear-Host','cls'],'Можно Clear-Host.'],['Выведите строку Hello PowerShell.','Write-Output Hello PowerShell','Используйте Write-Output.'],['Покажите таблицу версии PowerShell.','$PSVersionTable','Системная переменная $PSVersionTable.'],['Покажите текущий каталог.',['Get-Location','pwd'],'Командлет Get-Location.'],['Перейдите в C:\\Windows.',['Set-Location C:\\Windows','cd C:\\Windows'],'Командлет Set-Location.'],['Покажите историю введённых команд.','Get-History','Командлет Get-History.'],['Покажите псевдоним команды dir.','Get-Alias dir','Командлет Get-Alias.']
].forEach(x=>add('powershell','Практика PowerShell',x[0],x[1],x[2],'PowerShell'));
[
 ['Покажите содержимое текущего каталога.','dir'],['Покажите корень диска C:.','dir C:\\'],['Покажите каталог Windows.','dir C:\\Windows'],['Покажите также скрытые элементы.','dir /a'],['Выведите только имена файлов и папок.','dir /b'],['Покажите дерево файлов рекурсивно.','dir /s'],['Найдите TXT-файлы в текущем каталоге.','dir *.txt'],['Покажите EXE-файлы каталога Windows.','dir C:\\Windows\\*.exe'],['Отсортируйте вывод по имени.','dir /o:n'],['Покажите каталоги без файлов.','dir /ad']
].forEach(x=>add('dir','Команда dir',x[0],x[1],'Используйте dir и требуемый параметр.'));
const cdTargets=['C:\\','C:\\Windows','C:\\Users','C:\\Users\\Student','C:\\Temp','C:\\Program Files','C:\\Windows\\System32','..','\\','C:\\Users\\Public']; cdTargets.forEach((p,i)=>add('cd','Команда cd','Перейдите '+(p==='..'?'на один каталог выше':p==='\\'?'в корень текущего диска':'в каталог '+p)+'.','cd '+p,'Используйте cd.'));
['Practice','Labs','Backup','Reports','Scripts','TempLab','Network','PowerShellLab','CMDLab','Archive'].forEach(n=>add('mkdir','Команда mkdir','Создайте каталог '+n+'.',['mkdir '+n,'md '+n],'Используйте mkdir.'));
[['report.txt','Backup\\report.txt'],['notes.txt','Archive\\notes.txt'],['config.ini','Backup\\config.ini'],['test.bat','Scripts\\test.bat'],['lab1.txt','Reports\\lab1.txt'],['users.csv','Backup\\users.csv'],['network.txt','Reports\\network.txt'],['a.txt','TempLab\\a.txt'],['script.ps1','Scripts\\script.ps1'],['readme.txt','Archive\\readme.txt']].forEach(x=>add('copy','Команда copy','Скопируйте '+x[0]+' в '+x[1]+'.','copy '+x[0]+' '+x[1],'Используйте copy источник назначение.'));
[['old.txt','new.txt'],['report.txt','Reports\\report.txt'],['test.bat','Scripts\\test.bat'],['draft.txt','Archive\\draft.txt'],['a.txt','TempLab\\a.txt'],['log.txt','Backup\\log.txt'],['users.csv','Reports\\users.csv'],['notes.txt','Archive\\notes.txt'],['script.ps1','Scripts\\script.ps1'],['network.txt','Reports\\network.txt']].forEach(x=>add('move','Команда move','Переместите '+x[0]+' в '+x[1]+'.','move '+x[0]+' '+x[1],'Используйте move источник назначение.'));
['old.txt','temp.txt','draft.txt','test.log','a.tmp','report.old','notes.bak','cache.tmp','unused.txt','debug.log'].forEach(n=>add('del','Команда del','Удалите файл '+n+'.',['del '+n,'erase '+n],'Используйте del.'));
[
 ['Выведите имя текущего пользователя CMD.','echo %USERNAME%'],['Выведите домашний каталог пользователя CMD.','echo %USERPROFILE%'],['Выведите каталог Windows CMD.','echo %WINDIR%'],['Выведите временный каталог CMD.','echo %TEMP%'],['Покажите все переменные окружения CMD.','set'],['Создайте переменную COURSE со значением NETWORK.','set COURSE=NETWORK'],['Выведите USERNAME в PowerShell.','Write-Output $env:USERNAME','PowerShell'],['Покажите все переменные окружения PowerShell.','Get-ChildItem Env:','PowerShell'],['Выведите PATH в PowerShell.','Write-Output $env:PATH','PowerShell'],['Создайте LAB=WINDOWS в PowerShell.','$env:LAB="WINDOWS"','PowerShell']
].forEach(x=>add('env','Переменные окружения',x[0],x[1],'Используйте синтаксис переменных выбранной оболочки.',x[2]||'CMD'));
[
 ['Покажите все процессы CMD.','tasklist','CMD'],['Найдите процессы cmd.exe.','tasklist /FI "IMAGENAME eq cmd.exe"','CMD'],['Покажите процессы подробно.','tasklist /v','CMD'],['Покажите процессы и службы.','tasklist /svc','CMD'],['Покажите процессы PowerShell.','Get-Process','PowerShell'],['Найдите процесс explorer.','Get-Process explorer','PowerShell'],['Отсортируйте процессы по CPU.','Get-Process | Sort-Object CPU -Descending','PowerShell'],['Покажите первые 5 процессов.','Get-Process | Select-Object -First 5','PowerShell'],['Покажите имя и Id процессов.','Get-Process | Select-Object Name,Id','PowerShell'],['Покажите процесс текущего PowerShell.','Get-Process -Id $PID','PowerShell']
].forEach(x=>add('process','Просмотр процессов',x[0],x[1],'Используйте tasklist или Get-Process.',x[2]));
[
 ['Покажите полную системную информацию CMD.','systeminfo','CMD'],['Покажите имя компьютера.','hostname','CMD'],['Покажите текущего пользователя.','whoami','CMD'],['Покажите версию Windows.','ver','CMD'],['Покажите сетевую конфигурацию.','ipconfig /all','CMD'],['Получите сведения о компьютере PowerShell.','Get-ComputerInfo','PowerShell'],['Получите сведения об ОС через CIM.','Get-CimInstance Win32_OperatingSystem','PowerShell'],['Получите сведения о процессоре через CIM.','Get-CimInstance Win32_Processor','PowerShell'],['Покажите имя компьютера PowerShell.','$env:COMPUTERNAME','PowerShell'],['Покажите дату последней загрузки ОС.','(Get-CimInstance Win32_OperatingSystem).LastBootUpTime','PowerShell']
].forEach(x=>add('system','Системная информация',x[0],x[1],'Используйте системную команду или командлет.',x[2]));

const mode=$('mode'); Object.entries(sections).forEach(([k,v])=>{const o=document.createElement('option');o.value=k;o.textContent=v+' — 10 заданий';mode.appendChild(o)});
let queue=[],idx=0,errorCount=0,correctCount=0,startAt=0,timerId=null,active=false,details=[];
const normalize=s=>s.trim().replace(/\s+/g,' ').replace(/\//g,'/').toLowerCase();
const shuffle=a=>{a=[...a];for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]]}return a};
function line(text,cls=''){const d=document.createElement('div');d.textContent=text;if(cls)d.className=cls;$('terminal').appendChild(d);$('terminal').scrollTop=$('terminal').scrollHeight}
function elapsed(){return Math.max(1,Math.floor((Date.now()-startAt)/1000))} function fmt(s){return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0')}
function updateStats(){const e=active?elapsed():0;$('timer').textContent=fmt(e);$('errors').textContent=errorCount;$('correct').textContent=correctCount;const total=Math.max(1,correctCount+errorCount);$('score').textContent=Math.round(correctCount/total*100)+'%'}
function current(){return queue[idx]}
function showTask(){const t=current();if(!t)return finish();$('sectionBadge').textContent=sections[t.section];$('taskTitle').textContent=t.title;$('taskText').textContent=t.prompt;$('progressText').textContent=(idx+1)+' / '+queue.length;$('shellMode').textContent=t.shell;$('prompt').innerHTML=t.shell==='PowerShell'?'PS C:\\Users\\Student&gt;&nbsp;':'C:\\Users\\Student&gt;&nbsp;';$('hintBox').classList.add('d-none');$('commandInput').focus()}
function emulate(c,t){const lc=normalize(c); if(lc==='cls'||lc==='clear-host'){$('terminal').innerHTML='';return} if(lc.startsWith('dir')) line(' Directory of C:\\Users\\Student\n08/17/2026  08:44 PM    <DIR>          Practice\n08/17/2026  08:44 PM               128 report.txt'); else if(lc.startsWith('tasklist')||lc.startsWith('get-process')) line('Image Name / ProcessName        PID\nSystem                           4\nexplorer.exe                  4120\npowershell.exe                7312'); else if(lc.startsWith('systeminfo')||lc.includes('get-computerinfo')) line('OS Name: Microsoft Windows 10 Pro\nSystem Type: x64-based PC\nTotal Physical Memory: 16,384 MB'); else if(lc==='hostname'||lc==='$env:computername') line('LAB-PC-01'); else if(lc==='whoami') line('lab-pc-01\\student'); else if(lc.includes('%username%')||lc.includes('$env:username')) line('Student'); else if(lc.startsWith('echo ')) line(c.substring(5)); else if(lc==='ver') line('Microsoft Windows [Version 10.0.19045.0000]'); else if(lc.startsWith('mkdir ')||lc.startsWith('md ')) line('Каталог создан.'); else if(lc.startsWith('copy ')) line('        1 file(s) copied.'); else if(lc.startsWith('move ')) line('        1 file(s) moved.'); else if(lc.startsWith('del ')||lc.startsWith('erase ')) line('Файл удалён.'); else line('Команда выполнена в учебной среде.');}
function submitCommand(){if(!active)return;const input=$('commandInput'),raw=input.value.trim();if(!raw)return;const t=current();line(($('shellMode').textContent==='PowerShell'?'PS C:\\Users\\Student> ':'C:\\Users\\Student> ')+raw);input.value='';const ok=t.answers.some(a=>normalize(a)===normalize(raw));emulate(raw,t);if(ok){correctCount++;details.push({task:t.id,ok:true,command:raw});line('✓ Верно','line-ok');idx++;updateStats();setTimeout(showTask,250)}else{errorCount++;details.push({task:t.id,ok:false,command:raw});line('✗ Команда не соответствует заданию. Попробуйте ещё раз.','line-error');updateStats()}}
function start(){queue=mode.value==='all'?shuffle(tasks):shuffle(tasks.filter(t=>t.section===mode.value));idx=0;errorCount=0;correctCount=0;details=[];active=true;startAt=Date.now();$('commandInput').disabled=false;$('hintBtn').disabled=false;$('resultBox').classList.add('d-none');$('terminal').innerHTML='';line('Практикум запущен. Задания перемешаны случайным образом.','line-info');clearInterval(timerId);timerId=setInterval(updateStats,1000);updateStats();showTask()}
async function finish(){active=false;clearInterval(timerId);$('commandInput').disabled=true;$('hintBtn').disabled=true;const duration=elapsed(),total=queue.length,score=Math.round(correctCount/Math.max(1,correctCount+errorCount)*100),passed=correctCount===total&&score>=70;updateStats();$('progressText').textContent=total+' / '+total;line('Практикум завершён. Результат '+score+'%.','line-info');const box=$('resultBox');box.className='alert '+(passed?'alert-success':'alert-danger');box.classList.remove('d-none');box.textContent=(passed?'Зачёт. ':'Не зачёт. ')+'Верно: '+correctCount+'/'+total+', ошибок: '+errorCount+', время: '+fmt(duration)+'. Сохраняю результат...';try{const r=await fetch('{{ route('practice.windows.result') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({duration_seconds:duration,errors_count:errorCount,correct_count:correctCount,total_count:total,score,passed,details})});const j=await r.json();if(!r.ok)throw new Error(j.message||'Ошибка сервера');box.textContent+=' Результат сохранён на сервере.'}catch(e){box.textContent+=' Не удалось сохранить: '+e.message}}
$('commandInput').addEventListener('keydown',e=>{if(e.key==='Enter')submitCommand()});$('startBtn').addEventListener('click',start);$('hintBtn').addEventListener('click',()=>{if(!active)return;const t=current();errorCount++;details.push({task:t.id,ok:false,hint:true});$('hintBox').textContent='Подсказка: '+t.hint;$('hintBox').classList.remove('d-none');updateStats()});
})();
</script>
@endpush
