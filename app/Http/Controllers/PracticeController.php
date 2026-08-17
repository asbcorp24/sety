<?php

namespace App\Http\Controllers;

use App\Models\PracticeAttempt;
use App\Models\Topic;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    public function show(Request $request, Topic $topic)
    {
        abort_unless($topic->is_published || $request->user()->isAdmin(), 404);
        abort_unless($topic->html_path, 404, 'Для этой темы HTML-практикум не загружен');

        $path = storage_path('app/' . ltrim($topic->html_path, '/'));
        abort_unless(is_file($path), 404, 'HTML-файл практикума не найден');

        $html = file_get_contents($path);
        $base = url('/practice/' . $topic->slug);
        $config = '<script>window.PRACTICE_API=' . json_encode([
            'slug' => $topic->slug,
            'title' => $topic->title,
            'sessionUrl' => $base . '/session',
            'resultUrl' => $base . '/result',
            'topicsUrl' => route('topics.index'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';

        $html = str_replace(
            ['{{PRACTICE_SLUG}}', '{{PRACTICE_TITLE}}', '{{PRACTICE_SESSION_URL}}', '{{PRACTICE_RESULT_URL}}', '{{TOPICS_URL}}'],
            [$topic->slug, e($topic->title), $base . '/session', $base . '/result', route('topics.index')],
            $html
        );

        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $config . '</head>', $html, 1);
        } else {
            $html = $config . $html;
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function session(Request $request, Topic $topic)
    {
        abort_unless($topic->is_published || $request->user()->isAdmin(), 404);

        $attempts = PracticeAttempt::where('user_id', $request->user()->id)
            ->where('practice_key', $topic->slug)
            ->latest()->limit(10)->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'score' => $a->score,
                'errors_count' => $a->errors_count,
                'correct_count' => $a->correct_count,
                'total_count' => $a->total_count,
                'duration_seconds' => $a->duration_seconds,
                'passed' => (bool)$a->passed,
                'created_at' => $a->created_at->format('d.m.Y H:i'),
            ]);

        return response()->json([
            'user' => ['id' => $request->user()->id, 'name' => $request->user()->name],
            'topic' => ['id' => $topic->id, 'slug' => $topic->slug, 'title' => $topic->title],
            'csrf_token' => csrf_token(),
            'attempts' => $attempts,
        ]);
    }

    public function store(Request $request, Topic $topic)
    {
        abort_unless($topic->is_published || $request->user()->isAdmin(), 404);

        $data = $request->validate([
            'duration_seconds' => 'required|integer|min:1|max:86400',
            'errors_count' => 'required|integer|min:0|max:10000',
            'correct_count' => 'required|integer|min:0|max:5000',
            'total_count' => 'required|integer|min:1|max:5000',
            'details' => 'nullable|array|max:5000',
        ]);

        abort_if($data['correct_count'] > $data['total_count'], 422, 'Некорректный результат');

        $answerAttempts = max(1, $data['correct_count'] + $data['errors_count']);
        $data['score'] = (int)round($data['correct_count'] / $answerAttempts * 100);
        $data['passed'] = $data['correct_count'] === $data['total_count'] && $data['score'] >= 70;
        $data['user_id'] = $request->user()->id;
        $data['practice_key'] = $topic->slug;

        $attempt = PracticeAttempt::create($data);

        return response()->json([
            'ok' => true,
            'attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'passed' => (bool)$attempt->passed,
            'message' => 'Результат сохранён',
        ]);
    }
}
