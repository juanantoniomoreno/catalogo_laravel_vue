<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    {{-- El elemento donde se montará toda tu aplicación Vue --}}
    <div id="app"></div>

    {{-- Tu script principal de Vue/JS, que contendrá Vue Router --}}
    @vite('resources/js/app.js')
</body>
</html>