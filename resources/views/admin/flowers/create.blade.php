<x-app-layout title="New Flower">
    <form method="POST" action="{{ route('admin.flowers.store') }}" enctype="multipart/form-data">
        @include('admin.flowers._form')
    </form>
</x-app-layout>
