<x-app-layout title="New Product">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @include('admin.products._form')
    </form>
</x-app-layout>
