<x-app-layout title="Edit Extra">
    <form method="POST" action="{{ route('admin.extras.update', $extra) }}" enctype="multipart/form-data">
        @include('admin.extras._form')
    </form>
</x-app-layout>
