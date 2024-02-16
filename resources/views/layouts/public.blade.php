<!DOCTYPE html>
<html lang="{{ isset($locale) ? $locale->htmlLang() : config('app.default_locale') }}"
      dir="{{ isset($locale) ? $locale->htmlDirection() : 'auto' }}"
      class="{{ setting()->getForCurrentUser('dark-mode-enabled') ? 'dark-mode ' : '' }}">
<head>
    <title>{{ isset($pageTitle) ? $pageTitle . ' | ' : '' }}{{ setting('app-name') }}</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta name="token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="theme-color" content="{{(setting()->getForCurrentUser('dark-mode-enabled') ? setting('app-color-dark') : setting('app-color'))}}"/>

    <!-- Social Cards Meta -->
    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' | ' : '' }}{{ setting('app-name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @stack('social-meta')

    <!-- Styles -->
    <link rel="stylesheet" href="{{ versioned_asset('dist/styles.css') }}">

    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="256x256" href="{{ setting('app-icon') ?: url('/icon.png') }}">
    <link rel="icon" type="image/png" sizes="180x180" href="{{ setting('app-icon-180') ?: url('/icon-180.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ setting('app-icon-180') ?: url('/icon-180.png') }}">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ setting('app-icon-128') ?: url('/icon-128.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ setting('app-icon-64') ?: url('/icon-64.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ setting('app-icon-32') ?: url('/icon-32.png') }}">

    <!-- PWA -->
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <meta name="mobile-web-app-capable" content="yes">

    @yield('head')

    <!-- Custom Styles & Head Content -->
    @include('layouts.parts.custom-styles')
    @include('layouts.parts.custom-head')

    @stack('head')

    <!-- Translations for JS -->
    @stack('translations')
</head>
<body
    @if(setting()->getForCurrentUser('ui-shortcuts-enabled', false))
        component="shortcuts"
        option:shortcuts:key-map="{{ \BookStack\Settings\UserShortcutMap::fromUserPreferences()->toJson() }}"
    @endif
      class="@stack('body-class')">

    @include('layouts.parts.base-body-start')
    @include('layouts.parts.skip-to-content')

    <div id="content" components="@yield('content-components')" class="block">
        <div class="tri-layout-mobile-tabs print-hidden">
            <div class="grid half no-break no-gap">
                <button type="button"
                        refs="tri-layout@tab"
                        data-tab="info"
                        aria-label="{{ trans('common.tab_info_label') }}"
                        class="tri-layout-mobile-tab px-m py-m text-link">
                    {{ trans('common.tab_info') }}
                </button>
                <button type="button"
                        refs="tri-layout@tab"
                        data-tab="content"
                        aria-label="{{ trans('common.tab_content_label') }}"
                        aria-selected="true"
                        class="tri-layout-mobile-tab px-m py-m text-link active">
                    {{ trans('common.tab_content') }}
                </button>
            </div>
        </div>

        <div refs="tri-layout@container" class="tri-layout-container" @yield('container-attrs') >

            <div class="tri-layout-sides print-hidden">
                <div class="tri-layout-sides-content">
                    <div class="tri-layout-right print-hidden">
                        <aside class="tri-layout-right-contents">
                            @yield('right')
                        </aside>
                    </div>

                    <div class="tri-layout-left print-hidden" id="sidebar">
                        <aside class="tri-layout-left-contents">
                            @yield('left')
                        </aside>
                    </div>
                </div>
            </div>

            <div class="@yield('body-wrap-classes') tri-layout-middle">
                <div id="main-content" class="tri-layout-middle-contents">
                    @yield('body')
                </div>
            </div>
        </div>
    </div>

    @include('layouts.parts.footer')

    <div component="back-to-top" class="back-to-top print-hidden">
        <div class="inner">
            @icon('chevron-up') <span>{{ trans('common.back_to_top') }}</span>
        </div>
    </div>

    @yield('bottom')
    @if($cspNonce ?? false)
        <script src="{{ versioned_asset('dist/app.js') }}" nonce="{{ $cspNonce }}"></script>
    @endif
    @yield('scripts')

    @include('layouts.parts.base-body-end')
</body>
</html>

