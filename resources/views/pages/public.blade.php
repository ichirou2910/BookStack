@extends('layouts.simple')

@section('body')

    <div class="container small">

        <div class="my-s">
            @include('entities.breadcrumbs', ['crumbs' => [
                $page->book,
                $page->chapter,
                $page,
                $page->getUrl('/public') => [
                    'text' => trans('entities.pages_public'),
                    'icon' => 'export',
                ]
            ]])
        </div>

        <main class="card content-wrap">
            <h1 class="list-heading">{{ trans('entities.pages_public') }}</h1>

            <form action="{{ $page->getUrl('/public') }}" method="POST">
                {!! csrf_field() !!}
                <input type="hidden" name="_method" value="PUT">

                <div class="stretch-inputs">
                    <div class="form-group">
                        <label for="public_path">Public Path</label>
                        @include('form.text', ['name' => 'public_path', 'model' => $page, 'autofocus' => true])
                    </div>
                </div>

                <div class="form-group text-right">
                    <a href="{{ $page->getUrl() }}" class="button outline">{{ trans('common.cancel') }}</a>
                    <button type="submit" class="button">{{ trans('entities.pages_public') }}</button>
                </div>
            </form>

        </main>
    </div>

@stop

