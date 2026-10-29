@extends('layouts.error')

@section('title', __('messages.errors.page_expired'))
@section('code', '419')
@section('eyebrow', __('messages.errors.session_timed_out'))
@section('heading', __('messages.errors.page_expired_heading'))
@section('description', __('messages.errors.forms_expire'))
@section('extra')
    <p class="mt-6 text-xs text-muted">
        <a href="{{ route('login') }}" class="text-gold underline-offset-4 hover:underline">{{ __('messages.errors.return_to_sign_in') }}</a>
    </p>
@endsection
