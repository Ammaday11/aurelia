<x-app-layout title="New Extra">
    <form method="POST" action="{{ route('admin.extras.store') }}" enctype="multipart/form-data">
        @include('admin.extras._form')
    </form>
</x-app-layout>
