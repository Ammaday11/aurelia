<x-app-layout title="New Custom Charge">
    <form method="POST" action="{{ route('admin.custom-charges.store') }}">
        @include('admin.custom-charges._form')
    </form>
</x-app-layout>
