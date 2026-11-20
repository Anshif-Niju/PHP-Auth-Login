<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    // GET /api/projects
    // List and search the logged-in user's projects
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $projects = $request->user('api')
            ->projects()
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return response()->json($projects);
    }

    // POST /api/projects
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store('projects', 'public');
        }

        $project = $request->user('api')
            ->projects()
            ->create($validated);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project,
        ], 201);
    }

    // GET /api/projects/{project}
    public function show(Request $request, Project $project)
    {
        abort_unless(
            $project->user_id === $request->user('api')->id,
            404
        );

        return response()->json($project);
    }

    // PUT /api/projects/{project}
    public function update(Request $request, Project $project)
    {
        abort_unless(
            $project->user_id === $request->user('api')->id,
            404
        );

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $oldImage = $project->image;

            $validated['image'] = $request
                ->file('image')
                ->store('projects', 'public');

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        } elseif ($request->exists('image') && $request->input('image') === null) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }

            $validated['image'] = null;
        }

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->fresh(),
        ]);
    }

    // DELETE /api/projects/{project}
    public function destroy(Request $request, Project $project)
    {
        abort_unless(
            $project->user_id === $request->user('api')->id,
            404
        );

        if ($project->image) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ]);
    }
}
