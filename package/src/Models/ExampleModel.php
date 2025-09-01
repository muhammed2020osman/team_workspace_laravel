<?php

namespace LaraCore\Models;

/**
 * مثال على استخدام BaseModel
 *
 * هذا الملف يوضح كيفية استخدام BaseModel
 * في إنشاء Models جديدة
 */
class ExampleModel extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'examples';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'is_active',
        'status',
        'tags',
        'metadata'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'tags' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'deleted_at',
        'metadata'
    ];

    /**
     * The attributes that should be appended.
     */
    protected $appends = [
        'display_name',
        'status_badge',
        'status_text'
    ];

    /**
     * Get the category that owns the example.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags for the example.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'example_tag');
    }

    /**
     * Get the author of the example.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope a query to only include active examples.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include examples by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include examples by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include examples with tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->whereHas('tags', function ($q) use ($tags) {
            $q->whereIn('name', $tags);
        });
    }

    /**
     * Scope a query to only include examples created by specific author.
     */
    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Get the example's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? 'Example #' . $this->id;
    }

    /**
     * Get the example's short description.
     */
    public function getShortDescriptionAttribute(): string
    {
        if (empty($this->description)) {
            return '';
        }

        return strlen($this->description) > 100
            ? substr($this->description, 0, 100) . '...'
            : $this->description;
    }

    /**
     * Get the example's tag names as array.
     */
    public function getTagNamesAttribute(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    /**
     * Get the example's tag names as string.
     */
    public function getTagNamesStringAttribute(): string
    {
        return implode(', ', $this->tag_names);
    }

    /**
     * Check if the example has a specific tag.
     */
    public function hasTag(string $tagName): bool
    {
        return $this->tags->contains('name', $tagName);
    }

    /**
     * Add a tag to the example.
     */
    public function addTag(string $tagName): void
    {
        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $this->tags()->attach($tag->id);
    }

    /**
     * Remove a tag from the example.
     */
    public function removeTag(string $tagName): void
    {
        $tag = Tag::where('name', $tagName)->first();
        if ($tag) {
            $this->tags()->detach($tag->id);
        }
    }

    /**
     * Get the example's metadata value.
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set the example's metadata value.
     */
    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    /**
     * Remove a metadata key from the example.
     */
    public function removeMetadata(string $key): void
    {
        $metadata = $this->metadata ?? [];
        unset($metadata[$key]);
        $this->metadata = $metadata;
    }

    /**
     * Check if the example has metadata.
     */
    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Get the example's full name with category.
     */
    public function getFullNameAttribute(): string
    {
        $categoryName = $this->category ? $this->category->name : 'Unknown';
        return "{$this->name} ({$categoryName})";
    }

    /**
     * Check if the example is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->is_active;
    }

    /**
     * Check if the example is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the example is pending review.
     */
    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    /**
     * Publish the example.
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => 'published',
            'is_active' => true
        ]);
    }

    /**
     * Mark the example as draft.
     */
    public function markAsDraft(): bool
    {
        return $this->update([
            'status' => 'draft',
            'is_active' => false
        ]);
    }

    /**
     * Mark the example as pending review.
     */
    public function markAsPendingReview(): bool
    {
        return $this->update([
            'status' => 'pending_review',
            'is_active' => false
        ]);
    }

    /**
     * Get the example's creation date in a readable format.
     */
    public function getCreatedAtReadableAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the example's update date in a readable format.
     */
    public function getUpdatedAtReadableAttribute(): string
    {
        return $this->updated_at->diffForHumans();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating an example
        static::creating(function ($example) {
            if (empty($example->status)) {
                $example->status = 'draft';
            }

            if (!isset($example->is_active)) {
                $example->is_active = false;
            }
        });

        // When updating an example
        static::updating(function ($example) {
            // Log changes if needed
        });

        // When deleting an example
        static::deleting(function ($example) {
            // Clean up related data if needed
        });
    }
}
