@extends('adminlte::page')

@section('title', 'Tambah Tugas')

@section('content_header')
    <h1 class="m-0">Tambah Tugas</h1>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus mr-1"></i> Formulir Tugas</h3>
            </div>
            <div class="card-body">
                @include('admin.tugas._form')
            </div>
        </div>
    </div>
@stop
