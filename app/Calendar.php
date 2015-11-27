<?php

namespace plunner;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Calendar
 *
 * @package plunner
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @property integer $id
 * @property string $name
 * @property integer $employee_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \plunner\Employee $employees
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Timeslot[] $timeslots
 * @method static \Illuminate\Database\Query\Builder|\plunner\Calendar whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\plunner\Calendar whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\plunner\Calendar whereEmployeeId($value)
 * @method static \Illuminate\Database\Query\Builder|\plunner\Calendar whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\plunner\Calendar whereUpdatedAt($value)
 */
class Calendar extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employees()
    {
        return $this->belongsTo('plunner\Employee');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeslots()
    {
        return $this->hasMany('App\Timeslot');
    }
}
