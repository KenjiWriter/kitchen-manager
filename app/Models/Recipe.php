<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'mealdb_id',
        'name',
        'category',
        'area',
        'instructions',
        'thumbnail',
        'youtube',
        'user_group_id',
        'created_by',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Sprawdza dostępność składników w spiżarni grupy
     */
    public function checkIngredientAvailability($userGroupId = null)
    {
        $groupId = $userGroupId ?? $this->user_group_id;
        
        $availability = [
            'available' => [],
            'missing' => [],
            'partial' => []
        ];

        foreach ($this->ingredients as $ingredient) {
            $status = $ingredient->checkAvailability($groupId);
            
            if ($status['available']) {
                $availability['available'][] = [
                    'ingredient' => $ingredient,
                    'status' => $status
                ];
            } elseif ($status['partial']) {
                $availability['partial'][] = [
                    'ingredient' => $ingredient,
                    'status' => $status
                ];
            } else {
                $availability['missing'][] = [
                    'ingredient' => $ingredient,
                    'status' => $status
                ];
            }
        }

        return $availability;
    }
}
