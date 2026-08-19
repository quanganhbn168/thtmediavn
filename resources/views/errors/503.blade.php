<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Website đang bảo trì</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-soft text-ink">
    <main class="flex min-h-screen items-center py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-4 text-2xl font-bold">{{ $website['name'] }}</div>
            <h1 class="mb-3 text-4xl font-bold">Website đang được bảo trì</h1>
            <p class="mx-auto max-w-xl text-muted">Hệ thống đang được cập nhật. Vui lòng quay lại sau hoặc liên hệ trực tiếp với chúng tôi.</p>
        </div>
    </main>
</body>
</html>
