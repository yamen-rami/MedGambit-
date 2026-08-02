<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Display</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/quiz.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('assets/css/question.css') }}"> --}}

</head>

<body>
  <div class="main-layout">
    {{ $slot }}
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
</body>

</html>