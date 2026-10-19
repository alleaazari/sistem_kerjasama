@extends('layouts.admin')
@section('title', 'Kelola Galeri')
@section('content')
    @include('admin._cms', ['jenis' => 'galeri', 'tambah' => 'Tambah Foto', 'labelIsi' => 'Keterangan Foto'])
@endsection
