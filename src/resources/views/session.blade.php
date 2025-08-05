@extends('layouts.default')
<style>
    p {
        background-color: #289ADC;
        color: white;
        padding: 5px 10px;
        width: 500px;
    }
</style>
@section('title', 'session.blade.php')

@section('content')
<p>セッションに保存した値:{{ $data }}</p>
<form action="/session" method="post">
    @csrf
    <input type="txt" name="input">
    <button>送信</button>
</form>
@endsection