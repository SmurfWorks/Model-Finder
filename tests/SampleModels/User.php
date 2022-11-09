<?php

namespace SmurfWorks\ModelFinderTests\SampleModels;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Name("User")]
#[Describe("A user record represents a person's access to this system")]
class User extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The database table used by this model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Mass-assignable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id',
        'receive_newsletter'
    ];

    /**
     * Cast attributes as dictated
     *
     * @var array
     */
    protected $casts = [
        'receive_newsletter' => 'boolean'
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Each user has a role to determine how they interact with this system.
     *
     * @return BelongsTo
     */
    #[Name("User role")]
    #[Describe("The user's system role")]
    public function role()
    {
        return $this->belongsTo(User\Role::class);
    }

    /**
     * Scope a user query to activated users only.
     *
     * @param Builder $query The query the scope is applied to
     *
     * @return Builder
     */
    #[Name("Activated users")]
    #[Describe("Activated users have set a password.")]
    public function scopeActivated(Builder $query)
    {
        return $query->whereNotNull('password');
    }

    /**
     * Scope a user query to user's who want to receive the newsletter.
     *
     * @param Builder $query The query the scope is applied to
     *
     * @return Builder
     */
    #[Name("Subscribed")]
    #[Describe("Users that are opted in to receive the newsletter.")]
    public function scopeSubscribed(Builder $query)
    {
        return $query->where('receive_newsletter', true);
    }
}
