<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Franzose\ClosureTable\Models\Entity;
use Illuminate\Database\Eloquent\Builder;

class Structure extends Entity
{
    use ModelIDTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'structures';

    /**
     * ClosureTable model instance.
     *
     * @var \App\\Models\StructureClosure
     */
    protected $closure = StructureClosure::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'parent_id',
    ];

    // relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // scope
    public function scopeDescendantsToLevel(Builder $builder, int $toLevel)
    {
        $closureTable = $this->closure->getTable();
        $columnDepth = "{$closureTable}.depth";

        return $builder->descendants()->where($columnDepth, '<=', $toLevel)->where($columnDepth, '>', 0);
    }

    public function scopeDescendantsWithSelfToLevel(Builder $builder, int $toLevel)
    {
        $closureTable = $this->closure->getTable();
        $columnDepth = "{$closureTable}.depth";

        return $builder->descendantsWithSelf()->where($columnDepth, '<=', $toLevel);
    }
}
