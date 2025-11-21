<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserGroupController extends Controller
{
    /**
     * Get all groups for authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        
        $groups = $user->groups()
            ->with('creator')
            ->withCount('members')
            ->get()
            ->map(function ($group) use ($user) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'is_default' => $group->is_default,
                    'members_count' => $group->members_count,
                    'role' => $group->pivot->role,
                    'joined_at' => $group->pivot->joined_at,
                    'created_by' => [
                        'id' => $group->creator->id,
                        'name' => $group->creator->name,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Create a new group.
     */
    public function store(Request $request)
    {
        $user = $request->attributes->get('auth_user');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $group = UserGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $user->id,
            'is_default' => $request->is_default ?? false,
        ]);

        // Add creator as owner
        $group->addMember($user, 'owner');

        return response()->json([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'is_default' => $group->is_default,
            ],
            'message' => 'Grupa została utworzona pomyślnie'
        ], 201);
    }

    /**
     * Get single group details.
     */
    public function show(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');
        
        $group = UserGroup::with(['members', 'creator'])->find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupa nie została znaleziona'
            ], 404);
        }

        // Check if user is a member
        if (!$group->hasMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Brak dostępu do tej grupy'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'is_default' => $group->is_default,
                'created_by' => [
                    'id' => $group->creator->id,
                    'name' => $group->creator->name,
                ],
                'members' => $group->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'role' => $member->pivot->role,
                        'joined_at' => $member->pivot->joined_at,
                    ];
                }),
                'user_role' => $group->getMemberRole($user),
            ]
        ]);
    }

    /**
     * Update group details.
     */
    public function update(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');
        
        $group = UserGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupa nie została znaleziona'
            ], 404);
        }

        // Check if user can manage this group
        if (!$group->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Brak uprawnień do edycji tej grupy'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $group->name = $request->name;
        }
        
        if ($request->has('description')) {
            $group->description = $request->description;
        }

        $group->save();

        return response()->json([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
            ],
            'message' => 'Grupa została zaktualizowana'
        ]);
    }

    /**
     * Delete a group.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');
        
        $group = UserGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupa nie została znaleziona'
            ], 404);
        }

        // Only owner can delete
        if ($group->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tylko właściciel może usunąć grupę'
            ], 403);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupa została usunięta'
        ]);
    }

    /**
     * Add member to group.
     */
    public function addMember(Request $request, $id)
    {
        $user = $request->attributes->get('auth_user');
        
        $group = UserGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupa nie została znaleziona'
            ], 404);
        }

        if (!$group->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Brak uprawnień do zarządzania członkami'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'in:member,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $newMember = User::find($request->user_id);
        $role = $request->role ?? 'member';

        if ($group->hasMember($newMember)) {
            return response()->json([
                'success' => false,
                'message' => 'Użytkownik już jest członkiem tej grupy'
            ], 400);
        }

        $group->addMember($newMember, $role);

        return response()->json([
            'success' => true,
            'message' => 'Użytkownik został dodany do grupy'
        ]);
    }

    /**
     * Remove member from group.
     */
    public function removeMember(Request $request, $id, $userId)
    {
        $user = $request->attributes->get('auth_user');
        
        $group = UserGroup::find($id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Grupa nie została znaleziona'
            ], 404);
        }

        if (!$group->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Brak uprawnień do zarządzania członkami'
            ], 403);
        }

        $memberToRemove = User::find($userId);

        if (!$memberToRemove) {
            return response()->json([
                'success' => false,
                'message' => 'Użytkownik nie został znaleziony'
            ], 404);
        }

        // Cannot remove owner
        if ($group->created_by === $memberToRemove->id) {
            return response()->json([
                'success' => false,
                'message' => 'Nie można usunąć właściciela grupy'
            ], 400);
        }

        $group->removeMember($memberToRemove);

        return response()->json([
            'success' => true,
            'message' => 'Użytkownik został usunięty z grupy'
        ]);
    }
}
