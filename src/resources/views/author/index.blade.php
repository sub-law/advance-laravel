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

    tr:nth-child(even) td {
        background-color: #EEEEEE;
    }

    td {
        padding: 25px 40px;
        background-color: #EEEEEE;
        text-align: center;
    }

    td table tbody tr td {
        background-color: #EEEEEE !important;
    }
</style>
@section('title', 'author.index.blade.php')

@section('content')
<table>
    <tr>
        <th>Author</th>
        <th>Book</th>
    </tr>
    @foreach ($hasItems as $item)
    <tr>
        <td>
            {{$item->getDetail()}}
        </td>
        <td>
            <table>
                @foreach ($item->books as $obj)
                <tr>
                    <td>{{ $obj->getTitle() }}</td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
    @endforeach
</table>
<table>
    <tr>
        <th>Author</th>
    </tr>
    @foreach ($noItems as $item)
    <tr>
        <td>{{ $item->getDetail() }}</td>
    </tr>
    @endforeach
</table>
@endsection