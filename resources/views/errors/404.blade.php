@extends('layouts.error')

@section('title', __('messages.errors.page_not_found'))
@section('code', '404')
@section('eyebrow', __('messages.errors.nothing_in_this_frame'))
@section('heading', __('messages.errors.this_page_is_missing'))
@section('description', __('messages.errors.link_out_of_date'))
