<x-app-layout title="Edit Flower">
    <form method="POST" action="{{ route('admin.flowers.update', $flower) }}" enctype="multipart/form-data">
        @include('admin.flowers._form')
    </form>
</x-app-layout>
