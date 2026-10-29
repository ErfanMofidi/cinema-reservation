@extends('layouts.error')

@section('title', __('messages.errors.access_denied'))
@section('code', '403')
@section('eyebrow', __('messages.errors.members_only'))
@section('heading', __('messages.errors.door_locked'))
@section('description', __('messages.errors.no_permission'))
@section('extra')
    <p class="mt-6 text-xs text-muted">
        <a href="{{ route('login') }}" class="text-gold underline-offset-4 hover:underline">{{ __('messages.errors.sign_in_different') }}</a>
    </p>
@endsection
