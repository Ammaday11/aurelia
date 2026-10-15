<x-app-layout title="New Ribbon">
    <form method="POST" action="{{ route('admin.ribbons.store') }}" enctype="multipart/form-data">
        @include('admin.ribbons._form')
    </form>
</x-app-layout>
