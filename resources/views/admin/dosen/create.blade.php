@extends('adminlte::page')

@section('title', 'Tambah Dosen')

@section('content_header')
    <h1 class="m-0">Tambah Dosen</h1>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Formulir Data Dosen</h3>
            </div>
            <div class="card-body">
                @include('admin.dosen._form')
            </div>
        </div>
    </div>
@stop
