<x-app-layout title="Edit User">
    <form method="POST" action="{{ route('admin.users.update', $editUser) }}">
        @include('admin.users._form')
    </form>
</x-app-layout>
