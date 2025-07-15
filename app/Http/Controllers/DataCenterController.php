<?php

use App\Http\Livewire\DataCenter\Index;
use App\Http\Livewire\DataCenter\Form;

class DataCenterController extends Controller
{
    public function index()
    {
        return app(Index::class)->render();
    }

    public function create()
    {
        return app(Form::class)->render();
    }

    public function edit($id)
    {
        return app(Form::class)->mount($id)->render();
    }
}