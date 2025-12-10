<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="api-token" content="{{ session('api_token', '') }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">
    <meta name="user-email" content="{{ auth()->user()->email }}">
    @endauth
    <title>Admin Dashboard - Quiz Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div id="admin-app"></div>
</body>
</html>



