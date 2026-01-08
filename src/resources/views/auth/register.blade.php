@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register-form__content">
    <div class="register-form__heading">
        <h1>会員登録</h1>
    </div>
    <form class="register-form" action="/register" method="post">
        @csrf
        <div class="register-form__group">
            <label class="register-form__label" for="name">名前</label>
            <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
            <p class="form__error">
                @error('name')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="email">メールアドレス</label>
            <input class="register-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
            <p class="form__error">
                @error('email')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="password">パスワード</label>
            <input class="register-form__input" type="password" name="password" id="password">
            <p class="form__error">
                @error('password')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="password_confirmation">パスワード確認</label>
            <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
            <p class="form__error">
                @error('password_confirmation')
                {{$message}}
                @enderror
            </p>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>
        <div class="login__button">
            <a class="login__button--link" href="/login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection