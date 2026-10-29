@extends('layouts.error')

@section('title', __('messages.errors.temporarily_unavailable'))
@section('code', '503')
@section('eyebrow', __('messages.errors.short_intermission'))
@section('heading', __('messages.errors.right_back'))
@section('description', __('messages.errors.maintenance'))
