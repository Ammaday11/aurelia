<x-app-layout title="Edit Product">
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @include('admin.products._form')
    </form>
</x-app-layout>
