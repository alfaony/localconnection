<div class="d-flex justify-content-end w-100">
    <div class="me-auto">
    <button type="button" class="btn btn-sm btn-primary ml-2 copy-link-button" data-task-slug="{{ $dailytask->slug }}"><i class="fa fa-link"></i></button>
    @if(!$dailytask->approved)
        <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
            @canAccess('show','dailytasks')
            <a href="{{ route('dailytask.show', $dailytask->slug) }}" class="btn btn-info btn-sm text-white"><i class="fa fa-eye"></i></a>
            @endcanAccess
            @if(($dailytask->user_id == Auth::user()->id) || (Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER && $dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE))
            @canAccess('edit','dailytasks')
            <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm text-white"><i class="fa fa-edit"></i></a>
            @endcanAccess
            @csrf
            @method('DELETE')
            @canAccess('destroy','dailytasks')
            <input type="hidden" name="redirect" value="back">
            <button type="button" class="btn btn-danger delete-button btn-sm text-white"><i class="fa fa-trash"></i></button>
            @endcanAccess
            @endif
        </form>
        @else
        @canAccess('show','dailytasks')
        <a href="{{ route('dailytask.show', $dailytask->slug) }}" class="btn btn-info btn-sm text-white"><i class="fa fa-eye"></i></a>
        @endcanAccess
        @canAccess('edit','dailytasks')
        @canAccess('approvement','dailytasks')
        <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm text-white"><i class="fa fa-edit"></i></a>
        @endcanAccess
        @endcanAccess

        @if(Auth::user()->role->name == \App\Schemas\RoleSchema::ROOT || Auth::user()->role->name == \App\Schemas\RoleSchema::ADMIN || Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER)
        <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            @canAccess('destroy','dailytasks')
            <input type="hidden" name="redirect" value="back">
            <button type="button" class="btn btn-danger delete-button btn-sm text-white"><i class="fa fa-trash"></i></button>
            @endcanAccess
        </form>
        @endif
    @endif
    </div>
    <button type="btn button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i class="btn btn-trash"></i></button>
</div>