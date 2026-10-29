@extends('layouts.error')

@section('title', __('messages.errors.server_error'))
@section('code', '500')
@section('eyebrow', __('messages.errors.backstage_interruption'))
@section('heading', __('messages.errors.slipped_reel'))
@section('description', __('messages.errors.unexpected_error'))
