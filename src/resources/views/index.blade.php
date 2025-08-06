@extends('layouts.default')
<style>
    th {
        background-color: #289ADC;
        color: white;
        padding: 5px 40px;
    }

    tr:nth-child(odd) td {
        background-color: #FFFFFF;
    }

    td {
        padding: 25px 40px;
        background-color: #EEEEEE;
        text-align: center;
    }

    svg.w-5.h-5 {
        /*paginateメソッドの矢印の大きさ調整のために追加*/
        width: 30px;
        height: 30px;
        fill: #289ADC;
        /* 青色に変更 */
        margin: 5px;
        transition: fill 0.3s ease;
    }

    svg.w-5.h-5:hover {
        fill: #FF5722;
        /* ホバーでオレンジに変化 */
    }
</style>
@section('title', 'index.blade.php')

@section('content')
<table>
    <tr>
        <th>DATA</th>
    </tr>
    @foreach ($authors as $author)
    <tr>
        <td>{{$author->getDetail()}}</td>
    </tr>
    @endforeach
</table>
{{ $authors->links() }}
@endsection