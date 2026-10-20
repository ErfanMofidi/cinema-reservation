@extends('layouts.error')

@section('title', 'Too many requests')
@section('code', '429')
@section('eyebrow', 'One moment, please')
@section('heading', 'You are moving quickly')
@section('description', 'Too many attempts were made in a short time. Wait a minute, then refresh the page and try again.')
