# Тренажёр «Знакомство с командной строкой Windows»

## Структура банка

Генератор создаёт `public/practice-data/windows-command-line.json`:

- 20 смешанных заданий по всей теме;
- 20 заданий «CMD и PowerShell»;
- 20 заданий «Файлы и каталоги: dir, cd, mkdir, copy, move, del»;
- 20 заданий «Переменные окружения»;
- 20 заданий «Процессы и системная информация».

Итого: 100 заданий. В браузере порядок заданий перемешивается при каждом запуске.

## Подготовка Ollama

```bash
ollama pull oxw/saiga_yandexgpt_8b:q4_k_m
python -m pip install -r tools/requirements-practice.txt
```

Убедитесь, что Ollama запущен и модель видна:

```bash
ollama list
```

## Генерация

Из корня Laravel-проекта:

```bash
python tools/generate_windows_command_line_practice.py
```

После выполнения появится:

```text
public/practice-data/windows-command-line.json
```

Checkpoint хранится в:

```text
storage/app/windows_command_line_checkpoint.json
```

Если процесс прервался, повторный запуск продолжит недостающие разделы. Для полной перегенерации удалите checkpoint и итоговый JSON.

## Настройка модели и параллельности

Windows CMD:

```bat
set OLLAMA_MODEL=oxw/saiga_yandexgpt_8b:q4_k_m
set MAX_WORKERS=4
python tools\generate_windows_command_line_practice.py
```

PowerShell:

```powershell
$env:OLLAMA_MODEL="oxw/saiga_yandexgpt_8b:q4_k_m"
$env:MAX_WORKERS="4"
python .\tools\generate_windows_command_line_practice.py
```

Если локальной видеопамяти недостаточно, установите `MAX_WORKERS=1` или `2`.

## Формат одного задания

```json
{
  "id": "files-01",
  "section": "files",
  "shell": "CMD",
  "title": "Просмотр каталога",
  "task": "Покажите содержимое текущего каталога C:\\Lab.",
  "prompt_path": "C:\\Lab",
  "accepted_commands": ["dir"],
  "hint": "Используйте стандартную команду просмотра содержимого каталога.",
  "explanation": "Команда выводит список файлов и подкаталогов текущего каталога.",
  "success_output": " Том в устройстве C имеет метку LAB\n Каталог C:\\Lab\n...",
  "difficulty": 1
}
```

Тренажёр не выполняет команды ОС. Он нормализует введённую строку и сравнивает её с `accepted_commands`, после чего показывает `success_output` как эмуляцию консоли.

## URL тренажёра

После входа пользователя:

```text
/practice/windows-command-line
```

Результат попытки отправляется AJAX-запросом в Laravel и сохраняется в `practice_attempts`.
