<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

use App\Schemas\ParamSchema;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $order = 'desc'; if($request->order == 'asc') { $order = 'asc'; }

        $tickets = Ticket::where('subject','like', '%' . $request->get('name') . '%')->orWhere('content','like', '%' . $request->get('name') . '%')
        ->OrderBy('created_at',$order)->paginate(10);
        return view('ticket.index', compact('tickets'));
    }

    public function create()
    {
        return view('ticket.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact' => 'nullable|email',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        $ticket = new Ticket();
        $ticket->contact = $request->post('contact');
        $ticket->subject = $request->post('subject');
        $ticket->content = $request->post('content');
        $ticket->status = ParamSchema::NEW;
        $ticket->save();

        return redirect()->back()->with('store', true);
    }

    public function show($slug)
    {
        $ticket = Ticket::where('slug',$slug)->firstOrFail();
        return view('ticket.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        return view('tickets.edit', compact('ticket'));
    }

    public function update(Request $request, $slug)
    {
        $validatedData = $request->validate([
            'note' => 'required|string', // Validasi untuk catatan
            'path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // Validasi untuk foto, max 2MB
        ]);

        $tiket = Ticket::where('slug',$slug)->firstOrFail();
        $tiket->note = $request->note;
        if ($request->hasFile('path')) 
        {
            // Hapus file lama jika ada        
            $file = $request->file('path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('ticket', $filename, 'public');
            $tiket->path = $filename;
            $tiket->status = ParamSchema::DONE;
        }
        $tiket->save();

        return redirect()->route('ticket.index')->with('update',true);
    }

    public function destroy($slug)
    {
        $ticket = Ticket::where('slug',$slug)->firstOrFail();
        $ticket->delete();
        return redirect()->route('ticket.index')->with('delete', true);
    }
}

