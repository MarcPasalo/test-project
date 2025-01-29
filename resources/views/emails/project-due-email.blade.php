<x-mail::message>


{{ __('This is a reminder that the following projects that are due in 24 hours') }}
@foreach ($projects as $project)
    <li>{{ $project->title}}</li>
@endforeach
<br>
Thanks
<br>
</x-mail::message>
