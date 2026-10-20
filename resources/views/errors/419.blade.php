@extends('layouts.error')

@section('title', 'Page expired')
@section('code', '419')
@section('eyebrow', 'The session timed out')
@section('heading', 'This page expired')
@section('description', 'For your security, forms expire after a while. Go back, refresh the page, and submit again.')
@section('extra')
    <p class="mt-6 text-xs text-muted">
        <a href="{{ route('login') }}" class="text-gold underline-offset-4 hover:underline">Return to sign in</a>
    </p>
@endsection
