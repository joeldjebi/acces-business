@extends('errors.layout')

@php
    // Nos propres messages d'accès (en français) sont affichés ; les messages par défaut du framework ne le sont pas.
    $reason = $exception->getMessage();
    $isOwnMessage = $reason !== '' && !str_starts_with($reason, 'This action') && $reason !== 'Forbidden' && !str_contains($reason, 'User does not have');
@endphp

@section('code', '403')
@section('title', 'Accès refusé')
@section('message', $isOwnMessage ? $reason : 'Vous n’avez pas les droits nécessaires pour accéder à cette page.')
