@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_index.css')}}">
@endsection

@section('content')
<div class="staff-index">
    <h1 class="staff-index__heading">スタッフ一覧</h1>
    <div class="staff-table">
        <table class="staff-table__inner">
            <tr class="staff-table_row">
                <th class="staff-table__header">名前</th>
                <th class="staff-table__header">メールアドレス</th>
                <th class="staff-table__header">月次勤怠</th>
            </tr>
            @foreach($staffs as $staff)
            <tr class="staff-table__row">
                <td class="staff-table__item">{{ $staff->name }}</td>
                <td class="staff-table__item">{{ $staff->email }}</td>
                <td class="staff-table__item"><a href="{{ route('admin.attendance.staff', $staff->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection