<x-app-layout title="New Category">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
</x-app-layout>
