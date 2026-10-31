@extends('layouts.app')

@section('title', ($registration->nom_complet ?: 'Présence') . ' · ' . $event->titre)

@section('content')
    @include('events.partials.guest-profile', ['context' => 'attendee'])
@endsection
