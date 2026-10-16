<x-app-layout title="New User">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @include('admin.users._form')
    </form>
</x-app-layout>
