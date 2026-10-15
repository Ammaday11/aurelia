<x-app-layout title="New Wrapping Paper">
    <form method="POST" action="{{ route('admin.wrapping-papers.store') }}" enctype="multipart/form-data">
        @include('admin.wrapping-papers._form')
    </form>
</x-app-layout>
