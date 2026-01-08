@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css')}}">
@endsection

@section('content')
<div class="login-form__content">
    <div class="login-form__heading">
        <h1>ログイン</h1>
    </div>
    <form class="login-form" action="/login" method="post">
        @csrf
        <div class="login-form__group">
            <label class="login-form__label" for="email">メールアドレス</label>
            <input class="login-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
            <p class="form__error">
                @error('email')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="login-form__group">
            <label class="login-form__label" for="password">パスワード</label>
            <input class="login-form__input" type="password" name="password" id="password">
            <p class="form__error">
                @error('password')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">ログインする</button>
        </div>
        <div class="register__button">
            <a class="register__button--link" href="/register">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection