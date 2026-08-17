@extends('layouts.app')
@section('title',$topic->title)
@section('content')
<div class="row justify-content-center"><div class="col-xl-9"><a href="{{ route('topics.index') }}" class="text-decoration-none">← Все темы</a><div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 mt-3"><div class="text-secondary mb-2">{{ $topic->module }}</div><div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4"><h1 class="mb-0">{{ $topic->title }}</h1>@if($topic->html_path)@auth<a href="{{ route('practice.show',$topic) }}" class="btn btn-success btn-lg">Запустить практикум</a>@else<a href="{{ route('login') }}" class="btn btn-success btn-lg">Войти и начать практикум</a>@endauth@endif</div>@if($topic->content)<article class="topic-content">{!! $topic->content !!}</article>@elseif($topic->html_path)<div class="alert alert-info mb-0">Для этой темы доступен интерактивный HTML-практикум. Нажмите кнопку выше, чтобы начать.</div>@endif</div></div></div>
@endsection
