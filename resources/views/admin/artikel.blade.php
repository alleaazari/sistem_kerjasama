@extends('layouts.admin')
@section('title', 'Kelola Artikel')
@section('content')
    @include('admin._cms', ['jenis' => 'artikel', 'tambah' => 'Tambah Artikel', 'labelIsi' => 'Isi Artikel'])
@endsection
