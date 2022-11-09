<?php

namespace SmurfWorks\ModelFinderTests\SampleModels\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Name("User role")]
class Role extends Model
{
    use SoftDeletes;

    /**
     * The database table used by this model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * Mass-assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * A single role can have and share many permissions.
     *
     * @return BelongsToMany
     */
    #[Name("Assigned permissions")]
    #[Describe("Permissions that have been assigned to this role")]
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles__permissions');
    }

    /**
     * A single role can be assigned to multiple users.
     *
     * @return HasMany
     */
    #[Name("Assigned users")]
    #[Describe("Users assigned this role")]
    public function users()
    {
        return $this->hasMany('SmurfWorks\ModelFinderTests\SampleModels\User');
    }
}
