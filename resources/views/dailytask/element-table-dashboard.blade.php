@php
    $startDate = \Carbon\Carbon::parse($dailytask->start_date);
    $endDate = \Carbon\Carbon::parse($dailytask->end_date);
    $isOverdue = $dailytask->isOverdue();
@endphp
<tr id="task-row-{{ $dailytask->id }}"> <!-- Added ID for each row -->
    <td>
        <span class="{{ $isOverdue ? 'text-danger' : '' }}">
            {{ $dailytask->dateShow }}
        </span>
    </td>
    <td>
        @switch($dailytask->taskStatus->name)
            @case('todo')
                <i class="fa fa-list-alt"></i> Todo
                @break
            @case('doing')
                <i class="fa fa-hourglass-start"></i> Doing
                @break
            @case('in review')
                <i class="fa fa-eye" style="color: green;"></i> In Review
                @break
            @case('not complete')
                <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                @break
            @case('complete')
                <i class="fa fa-check" style="color: green;"></i> Complete
                @break
            @default
                {{ $dailytask->taskStatus->name }}
        @endswitch
    </td>
    <td class="name-cell">
        <p>{!! $dailytask->head ? $dailytask->nameShow.'  <i class="fa fa-arrow-left"></i>  '. Str::limit($dailytask->head->name,50) : $dailytask->nameShow !!}</p>
    </td>
    <td class="name-cell">
        {{ $dailytask->project ? $dailytask->project->name : '' }}
    </td>
    <td class="name-cell">
        {{ $dailytask->dataProject ? $dailytask->dataProject->title : '' }}
    </td>
    <td class="name-cell">{{ $dailytask->user->name ?? '' }}</td>
    <td class="name-cell">{{ $dailytask->assign->name ?? '' }}</td>
    <td>
        @if(!$dailytask->approved)
        @canAccess('show','dailytasks')
        <button class="btn btn-info btn-sm show-popup-btn" data-task-id="{{ $dailytask->id }}" data-task-slug="{{ $dailytask->slug }}">
            <i class="fa fa-eye"></i>
        </button>
        @endcanAccess
        <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
            @if(($dailytask->user_id == Auth::user()->id) || (Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER && $dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE))
            @canAccess('edit','dailytasks')
            <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
            @endcanAccess
            @csrf
            @method('DELETE')
            @canAccess('destroy','dailytasks')
            <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
            @endcanAccess
            @endif
        </form>
        @else
        @canAccess('show','dailytasks')
        <button class="btn btn-info btn-sm show-popup-btn" data-task-id="{{ $dailytask->id }}" data-task-slug="{{ $dailytask->slug }}">
            <i class="fa fa-eye"></i>
        </button>
        @endcanAccess
        @canAccess('edit','dailytasks')
        @canAccess('approvement','dailytasks')
        <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
        @endcanAccess
        @endcanAccess

        @if(Auth::user()->role->name == \App\Schemas\RoleSchema::ROOT || Auth::user()->role->name == \App\Schemas\RoleSchema::ADMIN || Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER)
        <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            @canAccess('destroy','dailytasks')
            <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
            @endcanAccess
        </form>
        @endif
        @endif
    </td>
</tr>