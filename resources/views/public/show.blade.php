@extends('layouts.public')

@push('social-meta')
    <meta property="og:description" content="{{ Str::limit($page->text, 100, '...') }}">
@endpush

@include('entities.body-tag-classes', ['entity' => $page])

@section('body')
    <main class="content-wrap card">
        <div component="page-display"
             option:page-display:page-id="{{ $page->id }}"
             class="page-content clearfix">
            @include('pages.parts.page-display')
        </div>
        @include('pages.parts.pointer', ['page' => $page])
    </main>
@stop

@section('left')

    @if($page->tags->count() > 0)
        <section>
            @include('entities.tag-list', ['entity' => $page])
        </section>
    @endif

    @if ($page->attachments->count() > 0)
        <div id="page-attachments" class="mb-l">
            <h5>{{ trans('entities.pages_attachments') }}</h5>
            <div class="body">
                @include('attachments.list', ['attachments' => $page->attachments])
            </div>
        </div>
    @endif

    <nav id="page-navigation" class="mb-xl" aria-label="{{ trans('entities.pages_navigation') }}">
        <h5>{{ trans('entities.pages_navigation') }}</h5>
        @if (isset($pageNav) && count($pageNav))
            <div class="body">
                <div class="sidebar-page-nav menu">
                    @foreach($pageNav as $navItem)
                        <li class="page-nav-item h{{ $navItem['level'] }}">
                            <a href="{{ $navItem['link'] }}" class="text-limit-lines-1 block">{{ $navItem['text'] }}</a>
                            <div class="link-background sidebar-page-nav-bullet"></div>
                        </li>
                    @endforeach
                </div>
            </div>
        @endif
    </nav>
@stop
