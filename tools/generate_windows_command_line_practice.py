import json
import os
import re
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from copy import deepcopy
from pathlib import Path

import ollama
from tqdm import tqdm

MODEL_NAME = os.getenv("OLLAMA_MODEL", "oxw/saiga_yandexgpt_8b:q4_k_m")
OUTPUT_FILE = Path(os.getenv("OUTPUT_FILE", "public/practice-data/windows-command-line.json"))
CHECKPOINT_FILE = Path(os.getenv("CHECKPOINT_FILE", "storage/app/windows_command_line_checkpoint.json"))
MAX_WORKERS = int(os.getenv("MAX_WORKERS", "4"))
MAX_RETRIES = int(os.getenv("MAX_RETRIES", "3"))
PAUSE_BETWEEN_CALLS = float(os.getenv("PAUSE_BETWEEN_CALLS", "0.2"))
TASKS_PER_SECTION = 20
MIXED_TASKS = 20

TOPIC = {
    "key": "windows-command-line",
    "title": "Знакомство с командной строкой Windows",
    "description": "CMD, PowerShell, файловые команды, переменные окружения, процессы и системная информация",
}

SECTIONS = [
    {"key": "cmd_powershell", "title": "CMD и PowerShell", "scope": "Запуск CMD и PowerShell, справка, очистка экрана, вывод текста, текущий каталог, история, базовые командлеты и различия синтаксиса."},
    {"key": "files", "title": "Файлы и каталоги: dir, cd, mkdir, copy, move, del", "scope": "Навигация по каталогам, просмотр содержимого, создание каталогов, копирование, перемещение и удаление файлов. Используй безопасные учебные пути C:\\Lab, C:\\Lab\\Docs, C:\\Lab\\Backup, C:\\Lab\\Temp."},
    {"key": "environment", "title": "Переменные окружения", "scope": "Просмотр и изменение переменных окружения в CMD и PowerShell: USERNAME, USERPROFILE, TEMP, PATH, WINDIR, COMPUTERNAME, set, %VAR%, $env:VAR."},
    {"key": "processes_system", "title": "Процессы и системная информация", "scope": "tasklist, Get-Process, systeminfo, hostname, whoami, ipconfig, Get-ComputerInfo, Get-CimInstance. Только просмотр, без завершения процессов и изменения системы."},
]

SYSTEM_PROMPT = r"""
Ты преподаватель практикума по дисциплине «Администрирование сетевых ОС (Начало)».
Создаёшь задания для БЕЗОПАСНОГО браузерного эмулятора Windows Terminal.

Тема: {topic}
Раздел: {section}
Содержание раздела: {scope}

Сгенерируй РОВНО {count} разных практических заданий начального уровня.
Задания должны проверять ввод конкретной команды в CMD или Windows PowerShell 5.1.

Критические требования:
- никаких опасных команд: format, diskpart, shutdown, reg delete, Stop-Process, taskkill;
- файловые операции только внутри C:\\Lab и его подкаталогов;
- не использовать Linux-команды как основной правильный ответ;
- одно задание = одно чёткое действие;
- accepted_commands содержит 1-4 допустимых конкретных варианта команды;
- команды должны быть реально корректны для указанного shell;
- success_output — короткий правдоподобный учебный вывод терминала, не более 6 строк;
- explanation — 1-3 предложения, почему команда решает задачу;
- hint не должен полностью раскрывать ответ;
- difficulty только 1, 2 или 3;
- shell только "CMD" или "PowerShell";
- prompt_path только учебный путь, обычно C:\\Lab или один из его подкаталогов;
- не добавляй markdown и ```.

Верни ТОЛЬКО JSON-массив объектов:
[
  {{
    "shell": "CMD",
    "title": "Короткое название задания",
    "task": "Что должен сделать студент",
    "prompt_path": "C:\\Lab",
    "accepted_commands": ["dir"],
    "hint": "Короткая подсказка",
    "explanation": "Почему команда подходит",
    "success_output": "Правдоподобный вывод",
    "difficulty": 1
  }}
]
"""

MIXED_SYSTEM_PROMPT = r"""
Ты преподаватель практикума по дисциплине «Администрирование сетевых ОС (Начало)».
Создай РОВНО {count} СМЕШАННЫХ практических заданий по всей теме «{topic}».
Равномерно охвати: CMD и PowerShell; dir/cd/mkdir/copy/move/del; переменные окружения; процессы и системную информацию.
Те же правила безопасности: файловые изменения только C:\\Lab, одно действие на задание, shell только CMD/PowerShell.

Верни ТОЛЬКО JSON-массив объектов с полями:
shell, title, task, prompt_path, accepted_commands, hint, explanation, success_output, difficulty, source_section.
source_section: cmd_powershell, files, environment или processes_system.
Не добавляй markdown и ```.
"""

FORBIDDEN = re.compile(r"\b(format|diskpart|shutdown|restart-computer|stop-computer|taskkill|stop-process|reg\s+delete|bcdedit|cipher\s+/w)\b", re.I)


def ollama_generate(system_prompt: str, user_text: str) -> str:
    for attempt in range(1, MAX_RETRIES + 1):
        try:
            response = ollama.chat(model=MODEL_NAME, messages=[{"role": "system", "content": system_prompt}, {"role": "user", "content": user_text}], options={"temperature": 0.7})
            time.sleep(PAUSE_BETWEEN_CALLS)
            return response["message"]["content"].strip()
        except Exception as exc:
            print(f"⚠ Ollama ошибка, попытка {attempt}/{MAX_RETRIES}: {exc}")
            time.sleep(2 * attempt)
    raise RuntimeError("Ollama не ответил после повторных попыток")


def extract_json_array(text: str):
    text = text.strip()
    text = re.sub(r"^```(?:json)?\s*", "", text, flags=re.I)
    text = re.sub(r"\s*```$", "", text)
    start, end = text.find("["), text.rfind("]")
    if start < 0 or end < start:
        raise ValueError("В ответе Ollama не найден JSON-массив")
    return json.loads(text[start:end + 1])


def clean_task(task: dict, section_key: str, index: int, mixed: bool = False) -> dict:
    required = ["shell", "title", "task", "accepted_commands", "hint", "explanation", "success_output"]
    if not isinstance(task, dict) or any(not task.get(k) for k in required):
        raise ValueError(f"Некорректное задание #{index + 1}")
    shell = str(task["shell"]).strip()
    if shell.lower() in {"powershell", "ps", "windows powershell"}:
        shell = "PowerShell"
    elif shell.lower() in {"cmd", "command prompt"}:
        shell = "CMD"
    else:
        raise ValueError(f"Недопустимый shell: {shell}")
    commands = task["accepted_commands"] if isinstance(task["accepted_commands"], list) else [task["accepted_commands"]]
    commands = [str(x).strip() for x in commands if str(x).strip()]
    if not commands or any(FORBIDDEN.search(command) for command in commands):
        raise ValueError("Обнаружена пустая или запрещённая команда")
    valid_keys = {s["key"] for s in SECTIONS}
    source_section = task.get("source_section", section_key) if mixed else section_key
    if source_section not in valid_keys:
        source_section = section_key if section_key in valid_keys else "cmd_powershell"
    difficulty = min(3, max(1, int(task.get("difficulty", 1))))
    return {
        "id": f"{'mixed' if mixed else section_key}-{index + 1:02d}",
        "section": source_section,
        "shell": shell,
        "title": str(task["title"]).strip(),
        "task": str(task["task"]).strip(),
        "prompt_path": str(task.get("prompt_path") or "C:\\Lab").strip(),
        "accepted_commands": commands[:4],
        "hint": str(task["hint"]).strip(),
        "explanation": str(task["explanation"]).strip(),
        "success_output": str(task["success_output"]).strip(),
        "difficulty": difficulty,
    }


def generate_section(section: dict) -> dict:
    prompt = SYSTEM_PROMPT.format(topic=TOPIC["title"], section=section["title"], scope=section["scope"], count=TASKS_PER_SECTION)
    last_error = None
    for attempt in range(1, MAX_RETRIES + 1):
        try:
            items = extract_json_array(ollama_generate(prompt, f"Сформируй {TASKS_PER_SECTION} заданий для раздела {section['title']}."))
            if len(items) != TASKS_PER_SECTION:
                raise ValueError(f"Получено {len(items)}, требуется {TASKS_PER_SECTION}")
            return {**section, "tasks": [clean_task(item, section["key"], i) for i, item in enumerate(items)]}
        except Exception as exc:
            last_error = exc
            print(f"⚠ {section['key']}, валидация {attempt}/{MAX_RETRIES}: {exc}")
            time.sleep(1)
    raise RuntimeError(f"Не удалось сгенерировать {section['key']}: {last_error}")


def generate_mixed() -> list:
    prompt = MIXED_SYSTEM_PROMPT.format(topic=TOPIC["title"], count=MIXED_TASKS)
    last_error = None
    for attempt in range(1, MAX_RETRIES + 1):
        try:
            items = extract_json_array(ollama_generate(prompt, f"Сформируй {MIXED_TASKS} итоговых смешанных заданий."))
            if len(items) != MIXED_TASKS:
                raise ValueError(f"Получено {len(items)}, требуется {MIXED_TASKS}")
            return [clean_task(item, item.get("source_section", "cmd_powershell"), i, True) for i, item in enumerate(items)]
        except Exception as exc:
            last_error = exc
            print(f"⚠ mixed, валидация {attempt}/{MAX_RETRIES}: {exc}")
            time.sleep(1)
    raise RuntimeError(f"Не удалось сгенерировать смешанный блок: {last_error}")


def load_checkpoint() -> dict:
    if CHECKPOINT_FILE.exists():
        print(f"♻ Найден checkpoint: {CHECKPOINT_FILE}")
        with CHECKPOINT_FILE.open("r", encoding="utf-8") as fh:
            return json.load(fh)
    return {"topic": deepcopy(TOPIC), "sections": {}, "mixed_tasks": None}


def save_checkpoint(data: dict):
    CHECKPOINT_FILE.parent.mkdir(parents=True, exist_ok=True)
    with CHECKPOINT_FILE.open("w", encoding="utf-8") as fh:
        json.dump(data, fh, ensure_ascii=False, indent=2)


def main():
    checkpoint = load_checkpoint()
    missing = [s for s in SECTIONS if s["key"] not in checkpoint.get("sections", {})]
    if missing:
        with ThreadPoolExecutor(max_workers=min(MAX_WORKERS, len(missing))) as executor:
            futures = {executor.submit(generate_section, section): section for section in missing}
            for future in tqdm(as_completed(futures), total=len(futures), desc="Разделы"):
                section = futures[future]
                checkpoint.setdefault("sections", {})[section["key"]] = future.result()
                save_checkpoint(checkpoint)
                print(f"💾 Раздел сохранён: {section['title']}")
    if not checkpoint.get("mixed_tasks"):
        print("🎲 Генерация 20 смешанных заданий")
        checkpoint["mixed_tasks"] = generate_mixed()
        save_checkpoint(checkpoint)
    sections = [checkpoint["sections"][section["key"]] for section in SECTIONS]
    output = {
        "schema_version": 1,
        "generated_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "model": MODEL_NAME,
        "topic": TOPIC,
        "settings": {"tasks_per_section": TASKS_PER_SECTION, "mixed_tasks": MIXED_TASKS, "shuffle": True, "real_commands_are_not_executed": True},
        "sections": sections,
        "mixed_tasks": checkpoint["mixed_tasks"],
    }
    OUTPUT_FILE.parent.mkdir(parents=True, exist_ok=True)
    with OUTPUT_FILE.open("w", encoding="utf-8") as fh:
        json.dump(output, fh, ensure_ascii=False, indent=2)
    total = sum(len(s["tasks"]) for s in sections) + len(output["mixed_tasks"])
    print(f"\n🎉 Готово: {OUTPUT_FILE}")
    print(f"Всего заданий: {total} (4×20 + 20 смешанных)")


if __name__ == "__main__":
    main()
