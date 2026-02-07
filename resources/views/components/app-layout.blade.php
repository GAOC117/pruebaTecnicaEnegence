<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel + Bootstrap + MySql Estados</title>
    @livewireStyles
    <!-- Bootstrap si lo quieres -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="contenido">
    
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
