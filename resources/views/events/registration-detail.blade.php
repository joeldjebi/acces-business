@extends('layouts.app')

@section('title', ($registration->nom_complet ?: 'Inscription') . ' · ' . $event->titre)

@section('content')
    @include('events.partials.guest-profile', ['context' => 'registration'])
@endsection
