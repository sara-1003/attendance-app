@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request/list.css')}}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__heading">申請一覧</h1>
    <div class="attendance-tabs">
        <a class="tab__pending {{ request('status','pending') === 'pending' ? 'active' : '' }}" href="{{ route('request.list', ['status' => 'pending']) }}">承認待ち</a>
        <a class="tab__approved {{ request('status','pending') === 'approved' ? 'active' : '' }}" href="{{ route('request.list', ['status' => 'approved']) }}">承認済み</a>
    </div>
    <div class="attendance-table">
        <table class="attendance-table__inner">
            <tr class="attendance-table__row">
                <th class="attendance-table__header">状態</th>
                <th class="attendance-table__header">名前</th>
                <th class="attendance-table__header">対象日時</th>
                <th class="attendance-table__header">申請理由</th>
                <th class="attendance-table__header">申請日時</th>
                <th class="attendance-table__header">詳細</th>
            </tr>
            @foreach($requests as $request)
            <tr class="attendance-table__row">
                <td class="attendance-table__item">
                    @if($request->approvalHistories->count() > 0)
                        承認済み
                    @else
                        承認待ち
                    @endif
                </td>
                <td class="attendance-table__item">
                    {{ $request->attendance->user->name }}
                </td>
                <td class="attendance-table__item">
                    {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                </td>
                <td class="attendance-table__item">
                    {{ $request->reason }}
                </td>
                <td class="attendance-table__item">
                    {{ $request->created_at->format('Y/m/d') }}
                </td>
                <td class="attendance-table__item">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('request.approval',$request->id) }}">詳細</a>
                    @else
                        <a href="{{ route('attendance.show', $request->attendance->id) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection