<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description"
        content="A powerful and conceptual apps base dashboard template that especially build for developers and programmers.">
    @include('dashboard.layouts.header')
    @yield('page_css')
</head>

<body class="nk-body bg-lighter npc-general has-sidebar ">
    @yield('content')
    @include('dashboard.layouts.scripts')
    @yield('page_js')
</body>

</html>
