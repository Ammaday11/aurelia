<x-app-layout title="Edit Category">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @include('admin.categories._form')
    </form>
</x-app-layout>
