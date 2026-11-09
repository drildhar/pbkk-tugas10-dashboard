@extends('adminlte::page')

@section('title', 'Tambah Mahasiswa')

@section('content_header')
    <h1 class="m-0">Tambah Mahasiswa</h1>
@stop

@section('content')
    <div class="container-fluid">
        @include('admin.partials.flash')

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-1"></i> Formulir Data Mahasiswa
                </h3>
            </div>
            <div class="card-body">
                @include('admin.mahasiswa._form')
            </div>
        </div>
    </div>
@stop
