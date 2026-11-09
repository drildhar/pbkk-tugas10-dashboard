@extends('adminlte::page')

@section('title', 'Ubah Dosen')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h1 class="m-0">Ubah Data Dosen</h1>
        <span class="badge badge-light border">NIDN {{ $dosen->nidn }}</span>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-pen mr-1"></i> {{ $dosen->nama }}</h3>
            </div>
            <div class="card-body">
                @include('admin.dosen._form')
            </div>
        </div>
    </div>
@stop
