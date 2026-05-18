<?php

namespace App\Http\Controllers;

use App\Models\ReportLink;
use App\Models\ReportLinkImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportLinkController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportLink::with(['user', 'images'])
            ->orderByDesc('date');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $reportLinks = $query->paginate(12)->withQueryString();

        return view('report_link.index', compact('reportLinks'));
    }

    public function create()
    {
        return view('report_link.createOrEdit', [
            'mode'       => 'create',
            'reportLink' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'date'            => 'required|date',
            'link'            => 'required|url|max:2048',
            'description'     => 'nullable|string',
            'images'          => 'nullable|array|max:10',
            'images.*'        => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'descriptions'    => 'nullable|array',
            'descriptions.*'  => 'nullable|string|max:255',
        ]);

        $reportLink = ReportLink::create([
            'user_id'     => auth()->id(),
            'name'        => $request->name,
            'date'        => $request->date,
            'link'        => $request->link,
            'description' => $request->description,
        ]);

        if ($request->hasFile('images')) {
            $descriptions = $request->input('descriptions', []);
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('report-links', 'public');
                ReportLinkImage::create([
                    'report_link_id' => $reportLink->id,
                    'path'           => $path,
                    'description'    => $descriptions[$index] ?? null,
                    'order'          => $index,
                ]);
            }
        }

        return redirect()->route('report-link.index')->with('store', true);
    }

    public function show(ReportLink $reportLink)
    {
        $reportLink->load(['user', 'images']);

        return view('report_link.show', compact('reportLink'));
    }

    public function edit(ReportLink $reportLink)
    {
        return view('report_link.createOrEdit', [
            'mode'       => 'edit',
            'reportLink' => $reportLink->load('images'),
        ]);
    }

    public function update(Request $request, ReportLink $reportLink)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'date'                    => 'required|date',
            'link'                    => 'required|url|max:2048',
            'description'             => 'nullable|string',
            'images'                  => 'nullable|array|max:10',
            'images.*'                => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'descriptions'            => 'nullable|array',
            'descriptions.*'          => 'nullable|string|max:255',
            'existing_descriptions'   => 'nullable|array',
            'existing_descriptions.*' => 'nullable|string|max:255',
            'delete_images'           => 'nullable|array',
            'delete_images.*'         => 'integer|exists:report_link_images,id',
        ]);

        $reportLink->update([
            'name'        => $request->name,
            'date'        => $request->date,
            'link'        => $request->link,
            'description' => $request->description,
        ]);

        // Update descriptions of existing images
        if ($request->filled('existing_descriptions')) {
            foreach ($request->existing_descriptions as $imgId => $desc) {
                ReportLinkImage::where('id', $imgId)
                    ->where('report_link_id', $reportLink->id)
                    ->update(['description' => $desc]);
            }
        }

        // Delete marked images
        if ($request->filled('delete_images')) {
            $toDelete = ReportLinkImage::whereIn('id', $request->delete_images)
                ->where('report_link_id', $reportLink->id)
                ->get();
            foreach ($toDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            $nextOrder   = ($reportLink->images()->max('order') ?? -1) + 1;
            $descriptions = $request->input('descriptions', []);
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('report-links', 'public');
                ReportLinkImage::create([
                    'report_link_id' => $reportLink->id,
                    'path'           => $path,
                    'description'    => $descriptions[$index] ?? null,
                    'order'          => $nextOrder + $index,
                ]);
            }
        }

        return redirect()->route('report-link.show', $reportLink->id)->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(ReportLink $reportLink)
    {
        foreach ($reportLink->images as $img) {
            Storage::disk('public')->delete($img->path);
        }
        $reportLink->delete();

        return redirect()->route('report-link.index')->with('success', 'Laporan berhasil dihapus.');
    }
}
