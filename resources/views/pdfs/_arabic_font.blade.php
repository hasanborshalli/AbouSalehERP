@php
$amiriPath = str_replace('\\', '/', public_path('fonts/Amiri-Bold.ttf'));
$amiriExists = file_exists(public_path('fonts/Amiri-Bold.ttf'));
@endphp
@if($amiriExists)
<style>
    @@font-face {
        font-family: 'Amiri';
        src: url('{{ $amiriPath }}');
        font-weight: normal;
        font-style: normal;
    }

    @@font-face {
        font-family: 'Amiri';
        src: url('{{ $amiriPath }}');
        font-weight: bold;
        font-style: normal;
    }
</style>
@endif