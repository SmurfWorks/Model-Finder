<?php

namespace SmurfWorks\ModelFinderTests\SampleModels\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Name("User permission")]
class Permission extends Model
{
    use SoftDeletes;

    /**
     * The database table used by this model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * Mass-assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * A permission can be assigned to multiple roles.
     *
     * @return BelongsToMany
     */
    #[Name("Assigned roles")]
    #[Describe("Roles that have been assigned this permission")]
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles__permissions');
    }
}
