<x-app-layout title="Edit Ribbon">
    <form method="POST" action="{{ route('admin.ribbons.update', $ribbon) }}" enctype="multipart/form-data">
        @include('admin.ribbons._form')
    </form>
</x-app-layout>
