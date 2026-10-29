@extends('layouts.error')

@section('title', __('messages.errors.too_many_requests'))
@section('code', '429')
@section('eyebrow', __('messages.errors.one_moment'))
@section('heading', __('messages.errors.moving_quickly'))
@section('description', __('messages.errors.too_many_attempts'))
