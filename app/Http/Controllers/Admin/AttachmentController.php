<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /** Upload file đính kèm (AJAX hoặc form) */
    public function store(Request $request)
    {
        $request->validate([
            'file'             => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,gif,xlsx,xls,doc,docx',
            'attachable_type'  => 'required|string',
            'attachable_id'    => 'required|integer',
            'label'            => 'nullable|string|max:100',
        ]);

        $file = $request->file('file');
        $path = $file->store("attachments/{$request->attachable_type}/{$request->attachable_id}", 'public');

        $att = Attachment::create([
            'attachable_type' => $request->attachable_type,
            'attachable_id'   => $request->attachable_id,
            'file_name'       => $file->getClientOriginalName(),
            'file_path'       => $path,
            'file_size'       => $file->getSize(),
            'mime_type'       => $file->getMimeType(),
            'label'           => $request->label,
            'uploaded_by'     => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id'        => $att->id,
                'file_name' => $att->file_name,
                'url'       => $att->url,
                'size'      => $att->file_size_human,
                'is_image'  => $att->is_image,
            ]);
        }

        return redirect()->back()->with('success', "Đã tải lên: {$att->file_name}");
    }

    public function destroy(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->back()->with('success', 'Đã xóa file đính kèm.');
    }
}
