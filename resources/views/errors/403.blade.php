@extends('layouts.error')

@section('title', 'Access denied')
@section('code', '403')
@section('eyebrow', 'Members only')
@section('heading', 'That door is locked')
@section('description', 'Your account does not have permission to open this page. If you believe this is a mistake, sign in with an administrator account.')
@section('extra')
    <p class="mt-6 text-xs text-muted">
        <a href="{{ route('login') }}" class="text-gold underline-offset-4 hover:underline">Sign in with a different account</a>
    </p>
@endsection
