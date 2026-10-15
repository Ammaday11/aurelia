<x-app-layout title="Edit Custom Charge">
    <form method="POST" action="{{ route('admin.custom-charges.update', $customCharge) }}">
        @include('admin.custom-charges._form')
    </form>
</x-app-layout>
