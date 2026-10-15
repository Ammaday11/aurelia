<x-app-layout title="Edit Wrapping Paper">
    <form method="POST" action="{{ route('admin.wrapping-papers.update', $wrappingPaper) }}" enctype="multipart/form-data">
        @include('admin.wrapping-papers._form')
    </form>
</x-app-layout>
